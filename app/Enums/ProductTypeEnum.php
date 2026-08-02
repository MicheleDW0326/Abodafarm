<?php

namespace App\Enums;

enum ProductTypeEnum: string
{
    case LIVE = 'live';
    case FRESH = 'fresh';
    case SUPPLY = 'supply';

    public function label(): string
    {
        return match($this) {
            self::LIVE => 'Ternak Hidup',
            self::FRESH => 'Daging Segar',
            self::SUPPLY => 'Pakan & Perlengkapan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LIVE => 'green',
            self::FRESH => 'red',
            self::SUPPLY => 'blue',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
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
