<?php

namespace App\Services;

use App\Models\CustomerNotification;
use App\Models\PeternakNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Unified Notification Service
 * Handles both database notifications and FCM push notifications
 */
class NotificationService
{
    private FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send notification to a farmer (peternak)
     */
    public function toPeternak(
        User $farmer,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        // Create database notification
        $notification = PeternakNotification::create([
            'user_id' => $farmer->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);

        // Send FCM push notification
        try {
            $this->fcmService->sendToUser($farmer, $title, $message, array_merge($data, [
                'notification_id' => $notification->id,
                'source' => 'peternak_notification',
            ]));
        } catch (\Exception $e) {
            Log::error('FCM push failed for peternak notification', [
                'farmer_id' => $farmer->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification to a customer (pelanggan)
     */
    public function toCustomer(
        User $customer,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        // Create database notification
        $notification = CustomerNotification::create([
            'user_id' => $customer->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);

        // Send FCM push notification
        try {
            $this->fcmService->sendToUser($customer, $title, $message, array_merge($data, [
                'notification_id' => $notification->id,
                'source' => 'customer_notification',
            ]));
        } catch (\Exception $e) {
            Log::error('FCM push failed for customer notification', [
                'customer_id' => $customer->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ==================== BOOKING NOTIFICATIONS ====================

    /**
     * Notify farmer about new booking
     */
    public function newBooking(User $farmer, User $customer, string $productName, string $orderNumber): void
    {
        $this->toPeternak(
            $farmer,
            'new_booking',
            'Booking Baru! 📋',
            "{$customer->name} mengajukan booking untuk {$productName}",
            [
                'order_number' => $orderNumber,
                'customer_name' => $customer->name,
                'product_name' => $productName,
                'action' => 'view_booking',
                'url' => route('peternak.bookings'),
            ]
        );
    }

    /**
     * Notify customer about booking approved
     */
    public function bookingApproved(User $customer, string $productName, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'booking_approved',
            'Booking Disetujui! 🎉',
            "Booking {$orderNumber} untuk {$productName} telah disetujui.",
            [
                'order_number' => $orderNumber,
                'product_name' => $productName,
                'action' => 'view_booking',
                'url' => route('pelanggan.bookings'),
            ]
        );
    }

    /**
     * Notify customer about booking rejected
     */
    public function bookingRejected(User $customer, string $productName, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'booking_rejected',
            'Booking Ditolak',
            "Maaf, booking {$orderNumber} untuk {$productName} telah ditolak.",
            [
                'order_number' => $orderNumber,
                'product_name' => $productName,
                'action' => 'view_booking',
                'url' => route('pelanggan.bookings'),
            ]
        );
    }

    /**
     * Notify farmer about booking cancelled by customer
     */
    public function bookingCancelledByCustomer(User $farmer, string $productName, string $orderNumber): void
    {
        $this->toPeternak(
            $farmer,
            'booking_cancelled',
            'Booking Dibatalkan',
            "Booking {$orderNumber} untuk {$productName} telah dibatalkan oleh pelanggan.",
            [
                'order_number' => $orderNumber,
                'product_name' => $productName,
                'action' => 'view_booking',
            ]
        );
    }

    // ==================== PAYMENT NOTIFICATIONS ====================

    /**
     * Notify customer about payment success
     */
    public function paymentSuccess(User $customer, string $orderNumber, string $amount, int $itemCount): void
    {
        $this->toCustomer(
            $customer,
            'payment_success',
            'Pembayaran Berhasil! 🎉',
            "Pembayaran untuk pesanan {$orderNumber} ({$itemCount} item) sebesar {$amount} telah berhasil. Pesanan Anda sedang diproses.",
            [
                'order_number' => $orderNumber,
                'amount' => $amount,
                'item_count' => $itemCount,
                'action' => 'view_order',
                'url' => route('pelanggan.orders'),
            ]
        );
    }

    /**
     * Notify customer about payment pending
     */
    public function paymentPending(User $customer, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'payment_pending',
            'Pembayaran Menunggu',
            "Pembayaran untuk pesanan {$orderNumber} sedang menunggu konfirmasi.",
            [
                'order_number' => $orderNumber,
                'action' => 'view_order',
            ]
        );
    }

    /**
     * Notify customer about payment failed
     */
    public function paymentFailed(User $customer, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'payment_failed',
            'Pembayaran Gagal',
            "Maaf, pembayaran untuk pesanan {$orderNumber} gagal. Silakan coba lagi.",
            [
                'order_number' => $orderNumber,
                'action' => 'retry_payment',
            ]
        );
    }

    // ==================== ORDER STATUS NOTIFICATIONS ====================

    /**
     * Notify customer about order being processed
     */
    public function orderProcessing(User $customer, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'order_status',
            'Pesanan Diproses',
            "Pesanan {$orderNumber} sedang diproses. Tim kami sedang menyiapkan pesanan Anda.",
            [
                'order_number' => $orderNumber,
                'action' => 'view_order',
            ]
        );
    }

    /**
     * Notify customer about order shipped
     */
    public function orderShipped(User $customer, string $orderNumber, ?string $trackingNumber = null): void
    {
        $message = $trackingNumber
            ? "Pesanan {$orderNumber} telah dikirim. Nomor tracking: {$trackingNumber}"
            : "Pesanan {$orderNumber} telah dikirim.";

        $this->toCustomer(
            $customer,
            'order_shipped',
            'Pesanan Dikirim! 📦',
            $message,
            [
                'order_number' => $orderNumber,
                'tracking_number' => $trackingNumber,
                'action' => 'view_order',
            ]
        );
    }

    /**
     * Notify customer about order delivered
     */
    public function orderDelivered(User $customer, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'order_delivered',
            'Pesanan Selesai ✅',
            "Pesanan {$orderNumber} telah diterima. Terima kasih telah berbelanja di AbodaFarm!",
            [
                'order_number' => $orderNumber,
                'action' => 'view_order',
            ]
        );
    }

    // ==================== PRODUCT NOTIFICATIONS ====================

    /**
     * Notify farmer about product published successfully
     */
    public function productPublished(User $farmer, string $productName): void
    {
        $this->toPeternak(
            $farmer,
            'product_published',
            'Produk Dipublikasikan! ✅',
            "Produk {$productName} berhasil dipublikasikan dan dapat dilihat oleh pelanggan.",
            [
                'product_name' => $productName,
                'action' => 'view_product',
            ]
        );
    }

    /**
     * Notify farmer about product disabled by admin
     */
    public function productDisabledByAdmin(User $farmer, string $productName, string $reason): void
    {
        $this->toPeternak(
            $farmer,
            'product_disabled',
            'Produk Dinonaktifkan',
            "Produk {$productName} telah dinonaktifkan oleh admin. Alasan: {$reason}",
            [
                'product_name' => $productName,
                'reason' => $reason,
                'action' => 'view_product',
            ]
        );
    }

    // ==================== ADMIN NOTIFICATIONS ====================

    /**
     * Notify admin about new farmer registration
     */
    public function newFarmerRegistration(User $admin, User $farmer): void
    {
        $this->toPeternak(
            $admin,
            'new_farmer_registration',
            'Peternak Baru! 👨‍🌾',
            "Pengguna {$farmer->name} telah mendaftar sebagai peternak dan menunggu verifikasi.",
            [
                'farmer_id' => $farmer->id,
                'farmer_name' => $farmer->name,
                'action' => 'verify_farmer',
            ]
        );
    }

    /**
     * Notify admin about product awaiting verification
     */
    public function productAwaitingVerification(User $admin, string $productName, int $farmerId): void
    {
        $this->toPeternak(
            $admin,
            'product_awaiting_verification',
            'Produk Menunggu Verifikasi',
            "Produk {$productName} menunggu verifikasi dari admin.",
            [
                'product_name' => $productName,
                'farmer_id' => $farmerId,
                'action' => 'verify_product',
            ]
        );
    }

    /**
     * Notify admin about payment error
     */
    public function paymentError(User $admin, string $orderNumber, string $error): void
    {
        $this->toPeternak(
            $admin,
            'payment_error',
            'Error Pembayaran ⚠️',
            "Terjadi error pada pembayaran pesanan {$orderNumber}: {$error}",
            [
                'order_number' => $orderNumber,
                'error' => $error,
                'action' => 'view_order',
            ]
        );
    }

    /**
     * Notify admin about failed transaction report
     */
    public function transactionFailed(User $admin, string $orderNumber, string $reason): void
    {
        $this->toPeternak(
            $admin,
            'transaction_failed',
            'Transaksi Gagal ❌',
            "Transaksi pesanan {$orderNumber} gagal: {$reason}",
            [
                'order_number' => $orderNumber,
                'reason' => $reason,
                'action' => 'view_order',
            ]
        );
    }
}
