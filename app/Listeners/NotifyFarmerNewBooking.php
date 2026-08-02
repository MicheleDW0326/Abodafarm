<?php

namespace App\Listeners;

use App\Events\NewBookingReceived;
use App\Models\Order;
use App\Models\PeternakNotification;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyFarmerNewBooking implements ShouldQueue
{
    public function __construct(
        protected FcmService $fcmService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(NewBookingReceived $event): void
    {
        $booking = $event->booking;
        $farmer = $event->farmer;

        if (!$farmer) {
            return;
        }

        $customerName = $booking->customer_name ?? $booking->user?->name ?? 'Pelanggan';
        $productName = $booking->items->first()?->product_name ?? 'Produk';

        // Check for duplicate
        if ($this->isDuplicate($farmer->id, $booking->id)) {
            Log::debug("Skipping duplicate booking notification", [
                'farmer_id' => $farmer->id,
                'booking_id' => $booking->id,
            ]);
            return;
        }

        // Create database notification
        $this->createDatabaseNotification($farmer, $booking, $customerName, $productName);

        // Send FCM push notification
        $this->sendPushNotification($farmer, $booking, $customerName, $productName);
    }

    /**
     * Check if notification is a duplicate.
     */
    protected function isDuplicate(int $farmerId, int $bookingId): bool
    {
        $cutoff = now()->subMinutes(5);

        return PeternakNotification::where('user_id', $farmerId)
            ->where('type', 'new_booking')
            ->whereJsonContains('data', ['order_id' => $bookingId])
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Create database notification for farmer.
     */
    protected function createDatabaseNotification($farmer, Order $booking, string $customerName, string $productName): void
    {
        try {
            PeternakNotification::create([
                'user_id' => $farmer->id,
                'type' => 'new_booking',
                'title' => 'Booking Baru!',
                'message' => "{$customerName} mengajukan booking untuk {$productName}",
                'data' => [
                    'order_id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                    'total_amount' => $booking->total_amount,
                    'reference_id' => (string) $booking->id,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create farmer booking notification', [
                'farmer_id' => $farmer->id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send FCM push notification to farmer.
     */
    protected function sendPushNotification($farmer, Order $booking, string $customerName, string $productName): void
    {
        try {
            $this->fcmService->sendToUser(
                $farmer,
                'Booking Baru!',
                "{$customerName} mengajukan booking untuk {$productName}",
                [
                    'type' => 'new_booking',
                    'order_id' => (string) $booking->id,
                    'order_number' => $booking->order_number,
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                    'action' => 'view_booking',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification for new booking', [
                'farmer_id' => $farmer->id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(NewBookingReceived $event, \Throwable $exception): void
    {
        Log::error('NotifyFarmerNewBooking job failed', [
            'booking_id' => $event->booking->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
