<?php

namespace App\Services;

use App\Enums\ShipmentStatusEnum;
use App\Models\Delivery;
use App\Models\DeliveryTimeline;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DeliveryStatusNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    protected const CACHE_TTL = 3600;

    /**
     * Create delivery record for an order.
     */
    public function createDelivery(Order $order, ?int $createdBy = null): Delivery
    {
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'delivery_code' => Delivery::generateDeliveryCode(),
            'status' => Delivery::STATUS_PREPARING,
            'estimated_delivery' => now()->addDays(2),
        ]);

        $delivery->addTimelineEntry(
            Delivery::STATUS_PREPARING,
            'Delivery dibuat',
            $createdBy,
            $createdBy ? 'admin' : 'system'
        );

        return $delivery;
    }

    /**
     * Get or create delivery for order.
     */
    public function getOrCreateDelivery(Order $order, ?int $createdBy = null): Delivery
    {
        return $order->delivery ?? $this->createDelivery($order, $createdBy);
    }

    /**
     * Update delivery status with notifications.
     */
    public function updateStatus(
        Delivery $delivery,
        string $newStatus,
        ?int $updatedBy = null,
        ?string $notes = null,
        string $updatedByType = 'admin'
    ): Delivery {
        $previousStatus = $delivery->status;

        $delivery->update([
            'previous_status' => $previousStatus,
            'status' => $newStatus,
        ]);

        // Add timeline entry
        $delivery->addTimelineEntry($newStatus, $notes, $updatedBy, $updatedByType);

        // Update timestamps based on status
        $this->updateTimestamps($delivery, $newStatus);

        // Update related order status
        $this->syncOrderStatus($delivery->order, $newStatus);

        // Send notification to customer
        $this->notifyCustomer($delivery, $newStatus);

        return $delivery->fresh();
    }

    protected function updateTimestamps(Delivery $delivery, string $status): void
    {
        $updates = [];

        switch ($status) {
            case Delivery::STATUS_OUT_FOR_DELIVERY:
                $updates['shipped_at'] = now();
                break;
            case Delivery::STATUS_DELIVERED:
                $updates['delivered_at'] = now();
                break;
        }

        if (!empty($updates)) {
            $delivery->update($updates);
        }
    }

    protected function syncOrderStatus(Order $order, string $deliveryStatus): void
    {
        $orderStatus = match ($deliveryStatus) {
            Delivery::STATUS_PREPARING => Order::STATUS_PROCESSING,
            Delivery::STATUS_READY_FOR_DELIVERY => Order::STATUS_PROCESSING,
            Delivery::STATUS_ASSIGNED => Order::STATUS_PROCESSING,
            Delivery::STATUS_OUT_FOR_DELIVERY => Order::STATUS_SHIPPED,
            Delivery::STATUS_DELIVERED => Order::STATUS_DELIVERED,
            Delivery::STATUS_COMPLETED => Order::STATUS_COMPLETED,
            Delivery::STATUS_FAILED, Delivery::STATUS_RETURNED => Order::STATUS_PROCESSING,
            default => $order->status,
        };

        $order->update(['status' => $orderStatus]);
    }

    protected function notifyCustomer(Delivery $delivery, string $status): void
    {
        try {
            $order = $delivery->order;
            $user = $order->user;

            if (!$user) {
                return;
            }

            $statusEnum = ShipmentStatusEnum::tryFrom($status);
            $message = $this->getNotificationMessage($status);

            $user->notify(new DeliveryStatusNotification($delivery, $statusEnum?->label() ?? $status, $message));
        } catch (\Exception $e) {
            Log::error('Failed to send delivery notification', [
                'delivery_id' => $delivery->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getNotificationMessage(string $status): string
    {
        return match ($status) {
            Delivery::STATUS_PREPARING => 'Pesanan Anda sedang disiapkan.',
            Delivery::STATUS_READY_FOR_DELIVERY => 'Pesanan Anda siap untuk dikirim.',
            Delivery::STATUS_ASSIGNED => 'Kurir telah ditugaskan untuk pesanan Anda.',
            Delivery::STATUS_OUT_FOR_DELIVERY => 'Pesanan sedang dalam perjalanan.',
            Delivery::STATUS_DELIVERED => 'Pesanan telah diterima!',
            Delivery::STATUS_COMPLETED => 'Pesanan selesai. Terima kasih!',
            Delivery::STATUS_FAILED => 'Pengiriman gagal. Kami akan menghubungi Anda.',
            Delivery::STATUS_RETURNED => 'Pesanan dikembalikan ke pengirim.',
            default => 'Update status pengiriman: ' . $status,
        };
    }

    /**
     * Assign staff to delivery.
     */
    public function assignStaff(Delivery $delivery, int $staffId, ?int $assignedBy = null): Delivery
    {
        if ($delivery->assigned_staff_id && $delivery->assigned_staff_id !== $staffId) {
            throw new \Exception('Delivery already assigned to another staff');
        }

        $delivery->update([
            'assigned_staff_id' => $staffId,
            'assigned_by' => $assignedBy,
        ]);

        $delivery->addTimelineEntry(
            Delivery::STATUS_ASSIGNED,
            "Ditugaskan ke staff: " . User::find($staffId)?->name,
            $assignedBy,
            'admin'
        );

        return $delivery->fresh();
    }

    /**
     * Staff accepts delivery.
     */
    public function acceptDelivery(Delivery $delivery, int $staffId): Delivery
    {
        if ($delivery->assigned_staff_id !== $staffId) {
            throw new \Exception('Delivery not assigned to this staff');
        }

        if ($delivery->status !== Delivery::STATUS_ASSIGNED && $delivery->status !== Delivery::STATUS_READY_FOR_DELIVERY) {
            throw new \Exception('Delivery cannot be accepted in current status');
        }

        return $this->updateStatus($delivery, Delivery::STATUS_ASSIGNED, $staffId, 'Staff menerima pesanan', 'staff');
    }

    /**
     * Staff starts delivery.
     */
    public function startDelivery(Delivery $delivery, int $staffId, ?string $trackingNumber = null): Delivery
    {
        if ($delivery->assigned_staff_id !== $staffId) {
            throw new \Exception('Delivery not assigned to this staff');
        }

        $updates = ['tracking_number' => $trackingNumber];

        $delivery->update($updates);

        return $this->updateStatus($delivery, Delivery::STATUS_OUT_FOR_DELIVERY, $staffId, 'Pengiriman dimulai', 'staff');
    }

    /**
     * Complete delivery with proof.
     */
    public function completeDelivery(
        Delivery $delivery,
        int $staffId,
        array $proofData = []
    ): Delivery {
        if ($delivery->assigned_staff_id !== $staffId) {
            throw new \Exception('Delivery not assigned to this staff');
        }

        $proofUpdates = array_filter([
            'proof_photo' => $proofData['proof_photo'] ?? null,
            'customer_signature' => $proofData['customer_signature'] ?? null,
            'delivery_notes' => $proofData['notes'] ?? null,
            'receiver_name' => $proofData['receiver_name'] ?? null,
            'latitude' => $proofData['latitude'] ?? null,
            'longitude' => $proofData['longitude'] ?? null,
        ]);

        if (!empty($proofUpdates)) {
            $delivery->update($proofUpdates);
        }

        return $this->updateStatus($delivery, Delivery::STATUS_DELIVERED, $staffId, 'Pesanan diterima', 'staff');
    }

    /**
     * Mark delivery as failed.
     */
    public function markDeliveryFailed(
        Delivery $delivery,
        int $staffId,
        string $reason,
        ?array $location = null
    ): Delivery {
        if ($delivery->assigned_staff_id !== $staffId) {
            throw new \Exception('Delivery not assigned to this staff');
        }

        $delivery->increment('attempt_count');
        $delivery->update([
            'last_attempt_at' => now(),
            'failure_reason' => $reason,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
        ]);

        $delivery->addTimelineEntry(
            Delivery::STATUS_FAILED,
            "Gagal: {$reason}",
            $staffId,
            'staff'
        );

        // Update order status back to processing
        $delivery->order->update(['status' => Order::STATUS_PROCESSING]);

        // Notify customer
        $this->notifyCustomer($delivery, Delivery::STATUS_FAILED);

        return $delivery->fresh();
    }

    /**
     * Get staff workload stats.
     */
    public function getStaffWorkload(int $staffId): array
    {
        return Cache::remember("staff_workload_{$staffId}", self::CACHE_TTL, function () use ($staffId) {
            $delivery = Delivery::forStaff($staffId);

            return [
                'active' => (clone $delivery)->active()->count(),
                'assigned' => (clone $delivery)->where('status', Delivery::STATUS_ASSIGNED)->count(),
                'out_for_delivery' => (clone $delivery)->where('status', Delivery::STATUS_OUT_FOR_DELIVERY)->count(),
                'completed_today' => (clone $delivery)
                    ->where('status', Delivery::STATUS_DELIVERED)
                    ->whereDate('delivered_at', today())
                    ->count(),
                'failed_today' => (clone $delivery)
                    ->where('status', Delivery::STATUS_FAILED)
                    ->whereDate('last_attempt_at', today())
                    ->count(),
            ];
        });
    }

    /**
     * Get delivery stats for admin dashboard.
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('delivery_dashboard_stats', self::CACHE_TTL, function () {
            $delivery = Delivery::query();

            return [
                'total_active' => (clone $delivery)->active()->count(),
                'preparing' => (clone $delivery)->where('status', Delivery::STATUS_PREPARING)->count(),
                'ready_for_delivery' => (clone $delivery)->where('status', Delivery::STATUS_READY_FOR_DELIVERY)->count(),
                'assigned' => (clone $delivery)->where('status', Delivery::STATUS_ASSIGNED)->count(),
                'out_for_delivery' => (clone $delivery)->where('status', Delivery::STATUS_OUT_FOR_DELIVERY)->count(),
                'delivered_today' => (clone $delivery)->whereDate('delivered_at', today())->count(),
                'failed_today' => (clone $delivery)->whereDate('last_attempt_at', today())->where('status', Delivery::STATUS_FAILED)->count(),
                'unassigned' => (clone $delivery)->where('status', Delivery::STATUS_READY_FOR_DELIVERY)->whereNull('assigned_staff_id')->count(),
            ];
        });
    }

    /**
     * Clear cache for a delivery or staff.
     */
    public function clearCache(?int $staffId = null): void
    {
        if ($staffId) {
            Cache::forget("staff_workload_{$staffId}");
        }
        Cache::forget('delivery_dashboard_stats');
    }

    /**
     * Get delivery with all relations (eager loaded).
     */
    public function getDeliveryWithRelations(int $deliveryId): ?Delivery
    {
        return Delivery::with(['order.user', 'assignedStaff', 'timeline.creator'])
            ->find($deliveryId);
    }

    /**
     * Get available staff for assignment.
     */
    public function getAvailableStaff(): array
    {
        return User::role('staf')
            ->with('farmProfile')
            ->get()
            ->map(function ($staff) {
                $workload = $this->getStaffWorkload($staff->id);
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'avatar' => $staff->farmProfile?->image ? asset('storage/' . $staff->farmProfile->image) : null,
                    'active_deliveries' => $workload['active'],
                ];
            })
            ->toArray();
    }
}
