<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    case CART = 'cart';
    case INQUIRY = 'inquiry';

    public function label(): string
    {
        return match($this) {
            self::CART => 'Pembelian',
            self::INQUIRY => 'Booking/Inquiry',
        };
    }

    public function isCart(): bool
    {
        return $this === self::CART;
    }

    public function isInquiry(): bool
    {
        return $this === self::INQUIRY;
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
