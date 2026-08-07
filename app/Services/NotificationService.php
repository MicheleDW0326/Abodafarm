<?php

namespace App\Services;

use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\PeternakNotification;
use App\Models\StaffNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Unified Notification Service
 * Handles database notifications, FCM push notifications, and email notifications
 */
class NotificationService
{
    private FcmService $fcmService;

    // Channel constants
    public const CHANNEL_DATABASE = 'database';
    public const CHANNEL_FCM = 'fcm';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_ALL = 'all';

    // Duplicate prevention cooldown in minutes
    public const DUPLICATE_COOLDOWN = 5;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    // ==================== STAFF NOTIFICATIONS ====================

    /**
     * Check duplicate for staff notifications.
     */
    public function isStaffDuplicate(int $userId, string $type, string $referenceId): bool
    {
        $cutoff = now()->subMinutes(self::DUPLICATE_COOLDOWN);

        return StaffNotification::where('user_id', $userId)
            ->where('type', $type)
            ->whereJsonContains('data', ['reference_id' => $referenceId])
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Send notification to a staff member.
     *
     * @param string $channel 'all', 'database', 'fcm', or 'email'
     */
    public function toStaff(
        User $staff,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $channel = self::CHANNEL_DATABASE
    ): void {
        // Check for duplicate
        $referenceId = $data['reference_id'] ?? $data['order_number'] ?? uniqid();
        if ($this->isStaffDuplicate($staff->id, $type, $referenceId)) {
            Log::debug("Skipping duplicate staff notification", [
                'staff_id' => $staff->id,
                'type' => $type,
                'reference_id' => $referenceId,
            ]);
            return;
        }

        // Create database notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_DATABASE) {
            StaffNotification::create([
                'user_id' => $staff->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => array_merge($data, ['reference_id' => $referenceId]),
                'is_read' => false,
            ]);
        }

        // Send FCM push notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_FCM) {
            try {
                $this->fcmService->sendToUser($staff, $title, $message, array_merge($data, [
                    'type' => $type,
                    'source' => 'staff_notification',
                ]));
            } catch (\Exception $e) {
                Log::error('FCM push failed for staff notification', [
                    'staff_id' => $staff->id,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify staff about new order assignment.
     */
    public function orderAssignedToStaff(User $staff, Order $order, User $assignedBy): void
    {
        $this->toStaff(
            $staff,
            StaffNotification::TYPE_ORDER_ASSIGNED,
            'Pesanan Ditugaskan! 📋',
            "Pesanan {$order->order_number} ({$order->customer_name}) telah ditugaskan kepada Anda oleh {$assignedBy->name}.",
            [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'assigned_by_id' => $assignedBy->id,
                'assigned_by_name' => $assignedBy->name,
                'customer_name' => $order->customer_name,
                'action' => 'view_order',
                'url' => route('staff.orders.detail', $order->id),
            ]
        );
    }

    /**
     * Check if notification is a duplicate (within cooldown period).
     */
    public function isDuplicate(int $userId, string $type, string $referenceId): bool
    {
        $cutoff = now()->subMinutes(self::DUPLICATE_COOLDOWN);

        return CustomerNotification::where('user_id', $userId)
            ->where('type', $type)
            ->whereJsonContains('data', ['reference_id' => $referenceId])
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Check duplicate for farmer notifications.
     */
    public function isFarmerDuplicate(int $userId, string $type, string $referenceId): bool
    {
        $cutoff = now()->subMinutes(self::DUPLICATE_COOLDOWN);

        return PeternakNotification::where('user_id', $userId)
            ->where('type', $type)
            ->whereJsonContains('data', ['reference_id' => $referenceId])
            ->where('created_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Send notification to a farmer (peternak)
     *
     * @param string $channel 'all', 'database', 'fcm', or 'email'
     */
    public function toPeternak(
        User $farmer,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $channel = self::CHANNEL_ALL
    ): void {
        // Check for duplicate (skip for 'new_booking' type as it's always unique)
        $referenceId = $data['reference_id'] ?? $data['order_number'] ?? uniqid();
        if ($type !== 'new_booking' && $this->isFarmerDuplicate($farmer->id, $type, $referenceId)) {
            Log::debug("Skipping duplicate farmer notification", [
                'farmer_id' => $farmer->id,
                'type' => $type,
                'reference_id' => $referenceId,
            ]);
            return;
        }

        // Create database notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_DATABASE) {
            PeternakNotification::create([
                'user_id' => $farmer->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => array_merge($data, ['reference_id' => $referenceId]),
                'is_read' => false,
            ]);
        }

        // Send FCM push notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_FCM) {
            try {
                $this->fcmService->sendToUser($farmer, $title, $message, array_merge($data, [
                    'type' => $type,
                    'source' => 'peternak_notification',
                ]));
            } catch (\Exception $e) {
                Log::error('FCM push failed for peternak notification', [
                    'farmer_id' => $farmer->id,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send notification to a customer (pelanggan)
     *
     * @param string $channel 'all', 'database', 'fcm', or 'email'
     */
    public function toCustomer(
        User $customer,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $channel = self::CHANNEL_ALL
    ): void {
        // Check for duplicate
        $referenceId = $data['reference_id'] ?? $data['order_number'] ?? uniqid();
        if ($this->isDuplicate($customer->id, $type, $referenceId)) {
            Log::debug("Skipping duplicate customer notification", [
                'customer_id' => $customer->id,
                'type' => $type,
                'reference_id' => $referenceId,
            ]);
            return;
        }

        // Create database notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_DATABASE) {
            CustomerNotification::create([
                'user_id' => $customer->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => array_merge($data, ['reference_id' => $referenceId]),
                'is_read' => false,
            ]);
        }

        // Send FCM push notification
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_FCM) {
            try {
                $this->fcmService->sendToUser($customer, $title, $message, array_merge($data, [
                    'type' => $type,
                    'source' => 'customer_notification',
                ]));
            } catch (\Exception $e) {
                Log::error('FCM push failed for customer notification', [
                    'customer_id' => $customer->id,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send email notification (only for important events)
        if ($channel === self::CHANNEL_ALL || $channel === self::CHANNEL_EMAIL) {
            $this->sendEmailIfNeeded($customer, $type, $title, $message, $data);
        }
    }

    /**
     * Send email notification for important events.
     */
    protected function sendEmailIfNeeded(User $user, string $type, string $title, string $message, array $data): void
    {
        // Only send email for these important notification types
        $emailTriggers = [
            'booking_approved',
            'booking_rejected',
            'payment_success',
            'order_delivered',
            'payment_failed',
        ];

        if (!in_array($type, $emailTriggers)) {
            return;
        }

        // Check if user has email
        if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            $mailable = new \App\Mail\OrderNotificationMail(
                type: $type,
                title: $title,
                message: $message,
                user: $user,
                data: $data
            );

            \Illuminate\Support\Facades\Mail::to($user->email)->queue($mailable);

            Log::info("Email notification queued", [
                'user_id' => $user->id,
                'type' => $type,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue email notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send order status change notification based on new status workflow.
     */
    public function notifyOrderStatusChange(Order $order, ?string $notes = null): void
    {
        $user = $order->user;
        if (!$user) {
            return;
        }

        $status = $order->status;
        $isBooking = $order->isInquiry();

        // Get payment, booking, or shipment status from FK columns
        $paymentStatus = $order->paymentStatusRelation?->code;
        $bookingStatus = $order->bookingStatusRelation?->code;
        $shipmentStatus = $order->shipmentStatusRelation?->code;

        // Route to appropriate handler
        if ($paymentStatus && !in_array($paymentStatus, ['pending'])) {
            $this->notifyPaymentStatusChange($order, $paymentStatus);
        } elseif ($bookingStatus && !in_array($bookingStatus, ['pending'])) {
            $this->notifyBookingStatusChange($order, $bookingStatus);
        } elseif ($shipmentStatus) {
            $this->notifyShipmentStatusChange($order, $shipmentStatus);
        } else {
            // Legacy fallback - use legacy status
            $this->notifyLegacyStatusChange($order, $status);
        }
    }

    /**
     * Notify customer about payment status change.
     */
    protected function notifyPaymentStatusChange(Order $order, string $paymentStatus): void
    {
        $user = $order->user;
        if (!$user) return;

        $orderNumber = $order->order_number;

        match ($paymentStatus) {
            'paid' => $this->paymentSuccess(
                $user,
                $orderNumber,
                'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                $order->total_items
            ),
            'payment_failed' => $this->paymentFailed($user, $orderNumber),
            'payment_refunded' => $this->toCustomer(
                $user,
                'payment_refunded',
                'Pembayaran Direfund',
                "Pembayaran untuk pesanan {$orderNumber} telah dikembalikan.",
                ['order_number' => $orderNumber, 'action' => 'view_order']
            ),
            default => null,
        };
    }

    /**
     * Notify customer about booking status change.
     */
    protected function notifyBookingStatusChange(Order $order, string $bookingStatus): void
    {
        $user = $order->user;
        if (!$user) return;

        $orderNumber = $order->order_number;
        $productName = $order->items->first()?->product_name ?? 'Produk';

        match ($bookingStatus) {
            'approved' => $this->bookingApproved($user, $productName, $orderNumber),
            'rejected' => $this->bookingRejected($user, $productName, $orderNumber),
            default => null,
        };
    }

    /**
     * Notify customer about shipment status change.
     */
    protected function notifyShipmentStatusChange(Order $order, string $shipmentStatus): void
    {
        $user = $order->user;
        if (!$user) return;

        $orderNumber = $order->order_number;

        match ($shipmentStatus) {
            'processing' => $this->orderProcessing($user, $orderNumber),
            'shipped' => $this->orderShipped($user, $orderNumber),
            'delivered' => $this->orderDelivered($user, $orderNumber),
            'completed' => $this->toCustomer(
                $user,
                'order_completed',
                'Pesanan Selesai',
                "Terima kasih! Pesanan {$orderNumber} telah selesai. Semoga puas dengan layanannya!",
                ['order_number' => $orderNumber, 'action' => 'view_order']
            ),
            default => null,
        };
    }

    /**
     * Handle legacy status changes (fallback).
     */
    protected function notifyLegacyStatusChange(Order $order, string $status): void
    {
        $user = $order->user;
        if (!$user) return;

        $orderNumber = $order->order_number;

        // Use existing methods based on status
        match ($status) {
            'pending' => $this->paymentPending($user, $orderNumber),
            'paid' => $this->paymentSuccess($user, $orderNumber, 'Rp ' . number_format($order->total_amount, 0, ',', '.'), $order->total_items),
            'processing' => $this->orderProcessing($user, $orderNumber),
            'shipped' => $this->orderShipped($user, $orderNumber),
            'delivered' => $this->orderDelivered($user, $orderNumber),
            'cancelled' => $this->toCustomer(
                $user,
                'order_cancelled',
                'Pesanan Dibatalkan',
                "Pesanan {$orderNumber} telah dibatalkan.",
                ['order_number' => $orderNumber, 'action' => 'view_order']
            ),
            default => null,
        };
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

    /**
     * Notify staff that an order has been completed by customer
     */
    public function orderCompleted(User $staff, string $orderNumber): void
    {
        $this->toStaff(
            $staff,
            'order_completed_customer',
            'Pesanan Diselesaikan! 🎉',
            "Pesanan {$orderNumber} telah dikonfirmasi selesai oleh pelanggan.",
            [
                'order_number' => $orderNumber,
                'action' => 'view_order',
            ]
        );
    }

    /**
     * Notify admin that an order has been completed
     * Note: This broadcasts via event. AdminNotification model should be created for full DB notification.
     */
    public function orderCompletedAdmin(string $orderNumber, string $completedBy): void
    {
        // Broadcast via event for now
        // Full implementation would require AdminNotification model
        Log::info("Order completed notification", [
            'order_number' => $orderNumber,
            'completed_by' => $completedBy,
            'type' => 'admin',
        ]);
    }

    /**
     * Notify customer that their order was automatically completed
     */
    public function orderAutoCompleted(User $customer, string $orderNumber): void
    {
        $this->toCustomer(
            $customer,
            'order_auto_completed',
            'Pesanan Otomatis Selesai 🤖',
            "Pesanan {$orderNumber} telah otomatis diselesaikan oleh sistem setelah 3 hari.",
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
