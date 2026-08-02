<?php

namespace App\Services;

use App\Enums\BookingStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ShipmentStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\BookingStatus;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\ShipmentStatus;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    // =========================================================================
    // CART ORDER TRANSITIONS (Fresh Meat / Pembelian)
    // =========================================================================
    // Payment flow: pending -> paid -> fulfillment: processing -> shipped -> delivered -> completed
    // Cancelled: can happen from pending, paid, processing

    /**
     * Get valid next statuses for CART orders.
     */
    public static function getCartValidNextStatuses(Order $order): array
    {
        $paymentStatus = $order->paymentStatusRelation?->code;
        $shipmentStatus = $order->shipmentStatusRelation?->code;

        // Determine current stage
        if ($paymentStatus === 'paid') {
            // Payment completed, check fulfillment status
            return match($shipmentStatus) {
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => ['completed'],
                'completed', 'cancelled' => [], // Terminal
                default => ['processing'], // From paid to processing
            };
        }

        // Payment not completed yet
        if (in_array($shipmentStatus, ['completed', 'cancelled'])) {
            return []; // Terminal
        }

        // Payment pending
        return ['paid', 'cancelled'];
    }

    /**
     * Get valid next statuses for INQUIRY orders (Bookings).
     */
    public static function getInquiryValidNextStatuses(Order $order): array
    {
        $bookingStatus = $order->bookingStatusRelation?->code;
        $shipmentStatus = $order->shipmentStatusRelation?->code;

        // Booking approval stage
        if (in_array($bookingStatus, ['pending', null])) {
            return ['approved', 'rejected', 'cancelled'];
        }

        // If approved, check fulfillment
        if ($bookingStatus === 'approved') {
            if (in_array($shipmentStatus, ['completed', 'cancelled'])) {
                return [];
            }

            return match($shipmentStatus) {
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => ['completed'],
                default => ['processing'], // Start fulfillment
            };
        }

        // Rejected or other terminal
        return [];
    }

    // =========================================================================
    // CORE METHODS
    // =========================================================================

    /**
     * Check if a transition is valid.
     */
    public static function canTransition(Order $order, string $toStatus): bool
    {
        $validStatuses = self::getValidNextStatuses($order);
        return in_array($toStatus, $validStatuses);
    }

    /**
     * Get valid next statuses for current order.
     */
    public static function getValidNextStatuses(Order $order): array
    {
        return $order->isCart()
            ? self::getCartValidNextStatuses($order)
            : self::getInquiryValidNextStatuses($order);
    }

    /**
     * Check if current status is terminal (no further transitions).
     */
    public static function isTerminalStatus(Order $order): bool
    {
        return empty(self::getValidNextStatuses($order));
    }

    /**
     * Perform a transition with validation.
     *
     * @return array{success: bool, message: string, order?: Order}
     */
    public static function transition(
        Order $order,
        string $toStatus,
        string $triggeredByType = OrderStatusLog::TRIGGER_SYSTEM,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        $fromStatus = $order->status;

        // Check if transition is valid
        if (!self::canTransition($order, $toStatus)) {
            $validStatuses = self::getValidNextStatuses($order);
            $validStatusesStr = empty($validStatuses)
                ? 'tidak ada transisi yang valid'
                : implode(', ', $validStatuses);

            return [
                'success' => false,
                'message' => "Tidak dapat mengubah status dari '{$fromStatus}' ke '{$toStatus}'. Status yang valid: {$validStatusesStr}",
            ];
        }

        // Perform transition in transaction
        try {
            $result = DB::transaction(function () use ($order, $fromStatus, $toStatus, $triggeredByType, $triggeredById, $notes) {
                // Update order using new FK columns
                self::updateStatusColumns($order, $toStatus);

                // Also update legacy status for backward compatibility
                $order->update(['status' => $toStatus]);

                // Log the status change
                OrderStatusLog::log(
                    $order,
                    $fromStatus,
                    $toStatus,
                    $triggeredByType,
                    $triggeredById,
                    $notes
                );

                return $order;
            });

            // Dispatch event after successful transition
            event(new OrderStatusChanged(
                $result->fresh(),
                $fromStatus,
                $toStatus,
                $triggeredByType,
                $triggeredById,
                $notes
            ));

            return [
                'success' => true,
                'message' => "Status berhasil diubah ke '{$toStatus}'",
                'order' => $result->fresh(['statusLogs.triggeredBy']),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update the correct FK columns based on status type.
     */
    protected static function updateStatusColumns(Order $order, string $toStatus): void
    {
        // Determine which FK column to update
        if (in_array($toStatus, ['pending', 'paid', 'payment_failed', 'payment_refunded'])) {
            // Payment status
            $statusId = OrderStatus::findIdByCode($toStatus);
            if ($statusId) {
                $order->payment_status_id = $statusId;
                $order->saveQuietly(); // Save without events
            }
        } elseif (in_array($toStatus, ['pending', 'approved', 'rejected'])) {
            // Booking status
            $statusId = BookingStatus::findIdByCode($toStatus);
            if ($statusId) {
                $order->booking_status_id = $statusId;
                $order->saveQuietly();
            }
        } elseif (in_array($toStatus, ['processing', 'shipped', 'delivered', 'completed', 'cancelled'])) {
            // Shipment/Fulfillment status
            $statusId = ShipmentStatus::findIdByCode($toStatus);
            if ($statusId) {
                $order->shipment_status_id = $statusId;
                $order->saveQuietly();
            }
        }
    }

    // =========================================================================
    // SPECIFIC TRANSITIONS
    // =========================================================================

    /**
     * Transition to paid status (for Midtrans payment).
     */
    public static function transitionToPaid(
        Order $order,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_PAID,
            OrderStatusLog::TRIGGER_WEBHOOK,
            $triggeredById,
            $notes ?? 'Pembayaran berhasil'
        );
    }

    /**
     * Transition to approved status (for booking).
     */
    public static function transitionToApproved(
        Order $order,
        int $triggeredById,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_APPROVED,
            OrderStatusLog::TRIGGER_ADMIN,
            $triggeredById,
            $notes ?? 'Booking disetujui'
        );
    }

    /**
     * Transition to rejected status (for booking).
     */
    public static function transitionToRejected(
        Order $order,
        int $triggeredById,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_REJECTED,
            OrderStatusLog::TRIGGER_ADMIN,
            $triggeredById,
            $notes ?? 'Booking ditolak'
        );
    }

    /**
     * Transition to cancelled status.
     */
    public static function transitionToCancelled(
        Order $order,
        string $triggeredByType = OrderStatusLog::TRIGGER_CUSTOMER,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_CANCELLED,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Dibatalkan'
        );
    }

    /**
     * Transition to processing status.
     */
    public static function transitionToProcessing(
        Order $order,
        string $triggeredByType = OrderStatusLog::TRIGGER_ADMIN,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_PROCESSING,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Pesanan sedang diproses'
        );
    }

    /**
     * Transition to shipped status.
     */
    public static function transitionToShipped(
        Order $order,
        string $triggeredByType = OrderStatusLog::TRIGGER_ADMIN,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_SHIPPED,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Pesanan telah dikirim'
        );
    }

    /**
     * Transition to delivered status.
     */
    public static function transitionToDelivered(
        Order $order,
        string $triggeredByType = OrderStatusLog::TRIGGER_SYSTEM,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_DELIVERED,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Pesanan telah diterima'
        );
    }

    /**
     * Transition to completed status.
     */
    public static function transitionToCompleted(
        Order $order,
        string $triggeredByType = OrderStatusLog::TRIGGER_SYSTEM,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        return self::transition(
            $order,
            Order::STATUS_COMPLETED,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Pesanan selesai'
        );
    }

    // =========================================================================
    // NEW FK-BASED TRANSITION METHODS
    // =========================================================================

    /**
     * Set payment status directly using FK.
     */
    public static function setPaymentStatus(
        Order $order,
        PaymentStatusEnum $status,
        string $triggeredByType = OrderStatusLog::TRIGGER_WEBHOOK,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        $fromStatus = $order->status;
        $statusId = OrderStatus::findIdByCode($status->value);

        if (!$statusId) {
            return [
                'success' => false,
                'message' => "Payment status '{$status->value}' not found in order_statuses table",
            ];
        }

        try {
            DB::transaction(function () use ($order, $statusId, $status, $fromStatus, $triggeredByType, $triggeredById, $notes) {
                $order->update(['payment_status_id' => $statusId]);
                $order->update(['status' => $status->value]); // Legacy

                OrderStatusLog::log(
                    $order,
                    $fromStatus,
                    $status->value,
                    $triggeredByType,
                    $triggeredById,
                    $notes ?? $status->label()
                );
            });

            // Dispatch event for notification
            event(new OrderStatusChanged(
                $order->fresh(),
                $fromStatus,
                $status->value,
                $triggeredByType,
                $triggeredById,
                $notes ?? $status->label()
            ));

            return [
                'success' => true,
                'message' => "Payment status changed to '{$status->value}'",
                'order' => $order->fresh(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Set booking status directly using FK.
     */
    public static function setBookingStatus(
        Order $order,
        BookingStatusEnum $status,
        string $triggeredByType = OrderStatusLog::TRIGGER_ADMIN,
        int $triggeredById = 0,
        ?string $notes = null
    ): array {
        $fromStatus = $order->status;
        $statusId = BookingStatus::findIdByCode($status->value);

        if (!$statusId) {
            return [
                'success' => false,
                'message' => "Booking status '{$status->value}' not found in booking_statuses table",
            ];
        }

        try {
            DB::transaction(function () use ($order, $statusId, $status, $fromStatus, $triggeredByType, $triggeredById, $notes) {
                $order->update(['booking_status_id' => $statusId]);
                $order->update(['status' => $status->value]); // Legacy

                OrderStatusLog::log(
                    $order,
                    $fromStatus,
                    $status->value,
                    $triggeredByType,
                    $triggeredById ?: null,
                    $notes ?? $status->label()
                );
            });

            // Dispatch event for notification
            event(new OrderStatusChanged(
                $order->fresh(),
                $fromStatus,
                $status->value,
                $triggeredByType,
                $triggeredById ?: null,
                $notes ?? $status->label()
            ));

            return [
                'success' => true,
                'message' => "Booking status changed to '{$status->value}'",
                'order' => $order->fresh(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update booking status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Set shipment status directly using FK.
     */
    public static function setShipmentStatus(
        Order $order,
        ShipmentStatusEnum $status,
        string $triggeredByType = OrderStatusLog::TRIGGER_ADMIN,
        ?int $triggeredById = null,
        ?string $notes = null
    ): array {
        $fromStatus = $order->status;
        $statusId = ShipmentStatus::findIdByCode($status->value);

        if (!$statusId) {
            return [
                'success' => false,
                'message' => "Shipment status '{$status->value}' not found in shipment_statuses table",
            ];
        }

        try {
            DB::transaction(function () use ($order, $statusId, $status, $fromStatus, $triggeredByType, $triggeredById, $notes) {
                $order->update(['shipment_status_id' => $statusId]);
                $order->update(['status' => $status->value]); // Legacy

                OrderStatusLog::log(
                    $order,
                    $fromStatus,
                    $status->value,
                    $triggeredByType,
                    $triggeredById,
                    $notes ?? $status->label()
                );
            });

            // Dispatch event for notification
            event(new OrderStatusChanged(
                $order->fresh(),
                $fromStatus,
                $status->value,
                $triggeredByType,
                $triggeredById,
                $notes ?? $status->label()
            ));

            return [
                'success' => true,
                'message' => "Shipment status changed to '{$status->value}'",
                'order' => $order->fresh(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update shipment status: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // DISPLAY METHODS
    // =========================================================================

    /**
     * Get the status flow for display purposes.
     */
    public static function getStatusFlow(Order $order): array
    {
        if ($order->isCart()) {
            return [
                ['status' => 'pending', 'label' => 'Menunggu Pembayaran', 'icon' => 'clock'],
                ['status' => 'paid', 'label' => 'Sudah Dibayar', 'icon' => 'check-circle'],
                ['status' => 'processing', 'label' => 'Sedang Diproses', 'icon' => 'cog'],
                ['status' => 'shipped', 'label' => 'Sedang Dikirim', 'icon' => 'truck'],
                ['status' => 'delivered', 'label' => 'Sudah Diterima', 'icon' => 'package'],
                ['status' => 'completed', 'label' => 'Selesai', 'icon' => 'check-double'],
            ];
        }

        // Inquiry/Booking flow
        return [
            ['status' => 'pending', 'label' => 'Menunggu Review', 'icon' => 'clock'],
            ['status' => 'approved', 'label' => 'Disetujui', 'icon' => 'check-circle'],
            ['status' => 'processing', 'label' => 'Sedang Diproses', 'icon' => 'cog'],
            ['status' => 'shipped', 'label' => 'Sedang Dikirim', 'icon' => 'truck'],
            ['status' => 'delivered', 'label' => 'Sudah Diterima', 'icon' => 'package'],
            ['status' => 'completed', 'label' => 'Selesai', 'icon' => 'check-double'],
        ];
    }

    /**
     * Get the current progress percentage.
     */
    public static function getProgressPercentage(Order $order): int
    {
        $flow = self::getStatusFlow($order);
        $currentStatus = $order->effective_status ?? $order->status;
        $currentIndex = array_search($currentStatus, array_column($flow, 'status'));

        if ($currentIndex === false) {
            // Handle edge cases like cancelled, rejected
            if (in_array($currentStatus, ['cancelled', 'rejected'])) {
                return 50;
            }
            return 0;
        }

        $totalSteps = count($flow);
        return (int) (($currentIndex + 1) / $totalSteps * 100);
    }
}
