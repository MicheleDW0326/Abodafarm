<?php

namespace App\Enums;

enum ShipmentStatusEnum: string
{
    // Pre-delivery statuses
    case PENDING = 'pending';
    case PAID = 'paid';
    case PREPARING = 'preparing';
    case READY_FOR_DELIVERY = 'ready_for_delivery';

    // Delivery statuses
    case ASSIGNED = 'assigned';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';

    // Terminal statuses
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case DELIVERY_FAILED = 'delivery_failed';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PAID => 'Lunas',
            self::PREPARING => 'Sedang Disiapkan',
            self::READY_FOR_DELIVERY => 'Siap Dikirim',
            self::ASSIGNED => 'Kurir Ditugaskan',
            self::OUT_FOR_DELIVERY => 'Sedang Dikirim',
            self::DELIVERED => 'Telah Diterima',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::DELIVERY_FAILED => 'Pengiriman Gagal',
            self::RETURNED => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::PAID => 'teal',
            self::PREPARING => 'blue',
            self::READY_FOR_DELIVERY => 'cyan',
            self::ASSIGNED => 'indigo',
            self::OUT_FOR_DELIVERY => 'purple',
            self::DELIVERED => 'emerald',
            self::COMPLETED => 'green',
            self::CANCELLED => 'slate',
            self::DELIVERY_FAILED => 'red',
            self::RETURNED => 'orange',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'fa-clock',
            self::PAID => 'fa-credit-card',
            self::PREPARING => 'fa-boxes-packing',
            self::READY_FOR_DELIVERY => 'fa-truck-fast',
            self::ASSIGNED => 'fa-user-check',
            self::OUT_FOR_DELIVERY => 'fa-truck-moving',
            self::DELIVERED => 'fa-home',
            self::COMPLETED => 'fa-check-double',
            self::CANCELLED => 'fa-ban',
            self::DELIVERY_FAILED => 'fa-triangle-exclamation',
            self::RETURNED => 'fa-rotate-left',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function customerNotified(): bool
    {
        return true;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::CANCELLED,
            self::DELIVERY_FAILED,
            self::RETURNED,
        ]);
    }

    public function isActive(): bool
    {
        return !$this->isTerminal();
    }

    public function isDeliveryActive(): bool
    {
        return in_array($this, [
            self::ASSIGNED,
            self::OUT_FOR_DELIVERY,
            self::PREPARING,
            self::READY_FOR_DELIVERY,
        ]);
    }

    public function requiresStaff(): bool
    {
        return in_array($this, [
            self::ASSIGNED,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
        ]);
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'icon' => $case->icon(),
        ], self::cases());
    }

    public static function deliveryStatuses(): array
    {
        return array_filter(self::cases(), fn($case) => $case->requiresStaff());
    }
}
