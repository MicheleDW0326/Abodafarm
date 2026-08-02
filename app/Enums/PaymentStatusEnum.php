<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PAYMENT_FAILED = 'payment_failed';
    case PAYMENT_REFUNDED = 'payment_refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PAID => 'Lunas',
            self::PAYMENT_FAILED => 'Pembayaran Gagal',
            self::PAYMENT_REFUNDED => 'Dana Dikembalikan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'amber',
            self::PAID => 'green',
            self::PAYMENT_FAILED => 'red',
            self::PAYMENT_REFUNDED => 'gray',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function isTerminal(): bool
    {
        return match($this) {
            self::PAYMENT_FAILED, self::PAYMENT_REFUNDED => true,
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
