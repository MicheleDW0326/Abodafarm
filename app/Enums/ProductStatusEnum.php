<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case AVAILABLE = 'available';
    case PRE_ORDER = 'pre_order';
    case IN_CARE = 'in_care';
    case SOLD = 'sold';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Siap Jual',
            self::PRE_ORDER => 'Pre Order',
            self::IN_CARE => 'Dalam Perawatan',
            self::SOLD => 'Terjual',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::AVAILABLE => 'green',
            self::PRE_ORDER => 'amber',
            self::IN_CARE => 'blue',
            self::SOLD => 'gray',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public function isSaleable(): bool
    {
        return match($this) {
            self::AVAILABLE, self::PRE_ORDER => true,
            default => false,
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
        ], self::cases());
    }
}
