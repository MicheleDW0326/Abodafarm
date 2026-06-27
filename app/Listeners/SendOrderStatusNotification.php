<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\PeternakNotification;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected FcmService $fcmService
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

        // Create in-app notification
        $this->createInAppNotification($order);

        // Send FCM push notification
        $this->sendPushNotification($order);
    }

    /**
     * Create in-app notification based on status.
     */
    protected function createInAppNotification(Order $order): void
    {
        $status = $order->status;
        $orderNumber = $order->order_number;
        $isBooking = $order->isInquiry();

        $notificationConfig = match ($status) {
            'pending' => [
                'type' => CustomerNotification::TYPE_ORDER_STATUS,
                'title' => $isBooking ? 'Booking Baru' : 'Pesanan Baru',
                'message' => $isBooking
                    ? "Booking {$orderNumber} sedang menunggu review."
                    : "Pesanan {$orderNumber} sedang menunggu pembayaran.",
            ],
            'approved' => [
                'type' => CustomerNotification::TYPE_BOOKING_APPROVED,
                'title' => 'Booking Disetujui!',
                'message' => "Booking {$orderNumber} telah disetujui. Silakan lakukan pembayaran.",
            ],
            'rejected' => [
                'type' => CustomerNotification::TYPE_BOOKING_REJECTED,
                'title' => 'Booking Ditolak',
                'message' => "Maaf, booking {$orderNumber} telah ditolak.",
            ],
            'paid' => [
                'type' => CustomerNotification::TYPE_PAYMENT_SUCCESS,
                'title' => 'Pembayaran Berhasil',
                'message' => "Pembayaran untuk {$orderNumber} telah berhasil.",
            ],
            'processing' => [
                'type' => CustomerNotification::TYPE_ORDER_STATUS,
                'title' => 'Pesanan Diproses',
                'message' => "Pesanan {$orderNumber} sedang diproses.",
            ],
            'shipped' => [
                'type' => CustomerNotification::TYPE_ORDER_SHIPPED,
                'title' => 'Pesanan Dikirim',
                'message' => "Pesanan {$orderNumber} sedang dalam perjalanan.",
            ],
            'delivered' => [
                'type' => CustomerNotification::TYPE_ORDER_DELIVERED,
                'title' => 'Pesanan Diterima',
                'message' => "Pesanan {$orderNumber} telah diterima.",
            ],
            'completed' => [
                'type' => CustomerNotification::TYPE_ORDER_STATUS,
                'title' => 'Pesanan Selesai',
                'message' => "Terima kasih! Pesanan {$orderNumber} telah selesai.",
            ],
            'cancelled' => [
                'type' => CustomerNotification::TYPE_ORDER_STATUS,
                'title' => 'Pesanan Dibatalkan',
                'message' => "Pesanan {$orderNumber} telah dibatalkan.",
            ],
            default => null,
        };

        if (!$notificationConfig) {
            return;
        }

        try {
            CustomerNotification::create([
                'user_id' => $order->user_id,
                'type' => $notificationConfig['type'],
                'title' => $notificationConfig['title'],
                'message' => $notificationConfig['message'],
                'data' => json_encode([
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'status' => $status,
                ]),
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create order status notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send FCM push notification.
     */
    protected function sendPushNotification(Order $order): void
    {
        $status = $order->status;
        $user = $order->user;

        if (!$user) {
            return;
        }

        $notificationTitle = match ($status) {
            'pending' => $order->isInquiry() ? 'Booking Baru' : 'Pesanan Baru Diterima',
            'approved' => '🎉 Booking Disetujui!',
            'rejected' => '📋 Booking Ditolak',
            'paid' => '✅ Pembayaran Berhasil',
            'processing' => '🔄 Pesanan Diproses',
            'shipped' => '📦 Pesanan Dikirim',
            'delivered' => '🎊 Pesanan Diterima',
            'completed' => '✨ Pesanan Selesai',
            'cancelled' => '❌ Pesanan Dibatalkan',
            default => "Update Pesanan {$order->order_number}",
        };

        $notificationBody = match ($status) {
            'pending' => $order->isInquiry()
                ? "Booking {$order->order_number} sedang menunggu review."
                : "Pesanan {$order->order_number} sedang menunggu pembayaran.",
            'approved' => "Booking {$order->order_number} telah disetujui. Silakan lakukan pembayaran.",
            'rejected' => "Maaf, booking {$order->order_number} telah ditolak.",
            'paid' => "Pembayaran untuk {$order->order_number} telah berhasil.",
            'processing' => "Pesanan {$order->order_number} sedang diproses oleh peternak.",
            'shipped' => "Pesanan {$order->order_number} sedang dalam perjalanan ke alamat Anda.",
            'delivered' => "Pesanan {$order->order_number} telah diterima. Jangan lupa konfirmasi ya!",
            'completed' => "Terima kasih! Pesanan {$order->order_number} telah selesai. Semoga puas dengan layanannya!",
            'cancelled' => "Pesanan {$order->order_number} telah dibatalkan.",
            default => "Status pesanan {$order->order_number} telah diperbarui.",
        };

        try {
            $this->fcmService->sendNotification(
                $user,
                $notificationTitle,
                $notificationBody,
                [
                    'order_id' => (string) $order->id,
                    'order_number' => $order->order_number,
                    'status' => $status,
                    'type' => $order->isInquiry() ? 'booking' : 'order',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification for order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
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
