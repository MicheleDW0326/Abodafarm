<?php

namespace App\Enums;

enum ShipmentStatusEnum: string
{
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PROCESSING => 'Sedang Diproses',
            self::SHIPPED => 'Dalam Pengiriman',
            self::DELIVERED => 'Telah Diterima',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PROCESSING => 'blue',
            self::SHIPPED => 'purple',
            self::DELIVERED => 'emerald',
            self::COMPLETED => 'green',
            self::CANCELLED => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PROCESSING => 'fa-box',
            self::SHIPPED => 'fa-truck',
            self::DELIVERED => 'fa-home',
            self::COMPLETED => 'fa-check-double',
            self::CANCELLED => 'fa-ban',
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
        return $this === self::COMPLETED || $this === self::CANCELLED;
    }

    public function isActive(): bool
    {
        return $this !== self::CANCELLED;
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
}
