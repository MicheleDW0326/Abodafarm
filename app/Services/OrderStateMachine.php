<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    /**
     * Valid transitions for CART orders (Fresh Meat).
     */
    private const CART_TRANSITIONS = [
        'pending' => ['paid', 'cancelled'],
        'paid' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['completed'],
        'completed' => [],      // Terminal state
        'cancelled' => [],      // Terminal state
    ];

    /**
     * Valid transitions for INQUIRY orders (Booking).
     */
    private const INQUIRY_TRANSITIONS = [
        'pending' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['completed', 'cancelled'],
        'completed' => [],       // Terminal state
        'rejected' => [],       // Terminal state
        'cancelled' => [],      // Terminal state
        // Additional statuses for bookings
        'paid' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['completed'],
    ];

    /**
     * Check if a transition is valid.
     */
    public static function canTransition(Order $order, string $toStatus): bool
    {
        $transitions = $order->isCart()
            ? self::CART_TRANSITIONS
            : self::INQUIRY_TRANSITIONS;

        $validTransitions = $transitions[$order->status] ?? [];

        return in_array($toStatus, $validTransitions);
    }

    /**
     * Get valid next statuses for current order.
     */
    public static function getValidNextStatuses(Order $order): array
    {
        $transitions = $order->isCart()
            ? self::CART_TRANSITIONS
            : self::INQUIRY_TRANSITIONS;

        return $transitions[$order->status] ?? [];
    }

    /**
     * Check if current status is terminal (no further transitions).
     */
    public static function isTerminalStatus(Order $order): bool
    {
        $transitions = $order->isCart()
            ? self::CART_TRANSITIONS
            : self::INQUIRY_TRANSITIONS;

        return empty($transitions[$order->status] ?? []);
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
                // Update order status
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

            return [
                'success' => true,
                'message' => "Status berhasil diubah ke '{$toStatus}'",
                'order' => $result->fresh(['statusLogs']),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ];
        }
    }

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
            ['status' => 'completed', 'label' => 'Selesai', 'icon' => 'check-double'],
        ];
    }

    /**
     * Get the current progress percentage.
     */
    public static function getProgressPercentage(Order $order): int
    {
        $flow = self::getStatusFlow($order);
        $currentIndex = array_search($order->status, array_column($flow, 'status'));

        if ($currentIndex === false) {
            // Handle edge cases like cancelled, rejected
            if (in_array($order->status, ['cancelled', 'rejected'])) {
                return 50; // Stopped halfway
            }
            return 0;
        }

        $totalSteps = count($flow);
        return (int) (($currentIndex + 1) / $totalSteps * 100);
    }
}
