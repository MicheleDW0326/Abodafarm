<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\PeternakNotification;
use App\Services\FcmService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected FcmService $fcmService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $user = $order->user;

        // Skip if no user
        if (!$user) {
            return;
        }

        // Use NotificationService for customer notifications
        $this->notificationService->notifyOrderStatusChange($order, $event->notes);

        // Also notify farmer if this is a booking status change
        $this->notifyFarmerIfNeeded($order, $event->toStatus);
    }

    /**
     * Notify farmer about booking status changes.
     */
    protected function notifyFarmerIfNeeded(Order $order, string $toStatus): void
    {
        // Only notify farmers for booking-related status changes
        $bookingStatuses = ['approved', 'rejected', 'processing', 'cancelled'];

        if (!in_array($toStatus, $bookingStatuses)) {
            return;
        }

        // Get the farmers who own products in this order
        $farmers = $order->items()
            ->with('product.owner')
            ->get()
            ->pluck('product.owner')
            ->filter()
            ->unique('id');

        $customerName = $order->customer_name ?? $order->user?->name ?? 'Pelanggan';
        $productName = $order->items->first()?->product_name ?? 'Produk';

        foreach ($farmers as $farmer) {
            $this->notifyFarmerAboutBookingChange($farmer, $order, $toStatus, $customerName, $productName);
        }
    }

    /**
     * Notify a specific farmer about booking status change.
     */
    protected function notifyFarmerAboutBookingChange($farmer, Order $order, string $status, string $customerName, string $productName): void
    {
        // Check for duplicate
        if ($this->isFarmerDuplicate($farmer->id, $order->id)) {
            return;
        }

        $config = match ($status) {
            'approved' => [
                'type' => 'booking_approved',
                'title' => 'Booking Disetujui',
                'message' => "Booking dari {$customerName} untuk {$productName} telah disetujui.",
                'icon' => 'fa-solid fa-circle-check text-green-500',
            ],
            'rejected' => [
                'type' => 'booking_rejected',
                'title' => 'Booking Ditolak',
                'message' => "Booking dari {$customerName} untuk {$productName} telah ditolak.",
                'icon' => 'fa-solid fa-circle-xmark text-red-500',
            ],
            'processing' => [
                'type' => 'order_processing',
                'title' => 'Pesanan Diproses',
                'message' => "Pesanan dari {$customerName} untuk {$productName} sedang diproses.",
                'icon' => 'fa-solid fa-cog text-blue-500',
            ],
            'cancelled' => [
                'type' => 'booking_cancelled',
                'title' => 'Booking Dibatalkan',
                'message' => "Booking dari {$customerName} untuk {$productName} telah dibatalkan.",
                'icon' => 'fa-solid fa-ban text-red-500',
            ],
            default => null,
        };

        if (!$config) {
            return;
        }

        try {
            // Create database notification
            PeternakNotification::create([
                'user_id' => $farmer->id,
                'type' => $config['type'],
                'title' => $config['title'],
                'message' => $config['message'],
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'reference_id' => (string) $order->id,
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                ],
                'is_read' => false,
            ]);

            // Send FCM
            $this->fcmService->sendToUser(
                $farmer,
                $config['title'],
                $config['message'],
                [
                    'type' => $config['type'],
                    'order_id' => (string) $order->id,
                    'order_number' => $order->order_number,
                    'action' => 'view_booking',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify farmer about booking change', [
                'farmer_id' => $farmer->id,
                'order_id' => $order->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check for duplicate farmer notification.
     */
    protected function isFarmerDuplicate(int $farmerId, int $orderId): bool
    {
        $cutoff = now()->subMinutes(NotificationService::DUPLICATE_COOLDOWN);

        return PeternakNotification::where('user_id', $farmerId)
            ->whereJsonContains('data', ['order_id' => $orderId])
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::error('SendOrderStatusNotification job failed', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
