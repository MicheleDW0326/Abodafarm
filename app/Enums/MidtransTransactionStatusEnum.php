<?php

namespace App\Enums;

enum MidtransTransactionStatusEnum: string
{
    case PENDING = 'pending';
    case CAPTURE = 'capture';
    case SETTLEMENT = 'settlement';
    case PENDING_PAYMENT = 'pending_payment';
    case DENY = 'deny';
    case CANCEL = 'cancel';
    case EXPIRE = 'expire';
    case REFUND = 'refund';
    case PARTIAL_REFUND = 'partial_refund';
    case CHARGEBACK = 'challenge';
    case PARTIAL_CHARGEBACK = 'partial_chargeback';
    case PREVENT_CHARGEBACK = 'prevent_chargeback';
    case ACCEPTANCE = 'acceptance';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::CAPTURE => 'Kartu Ditangkap',
            self::SETTLEMENT => 'Pembayaran Berhasil',
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::DENY => 'Pembayaran Ditolak',
            self::CANCEL => 'Pembayaran Dibatalkan',
            self::EXPIRE => 'Pembayaran Kadaluarsa',
            self::REFUND => 'Dana Dikembalikan',
            self::PARTIAL_REFUND => 'Dana Dikembalikan Sebagian',
            self::CHARGEBACK => 'Chargeback',
            self::PARTIAL_CHARGEBACK => 'Chargeback Sebagian',
            self::PREVENT_CHARGEBACK => 'Chargeback Dicegah',
            self::ACCEPTANCE => 'Diterima',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING, self::PENDING_PAYMENT => 'amber',
            self::CAPTURE, self::SETTLEMENT => 'green',
            self::DENY, self::CANCEL, self::EXPIRE, self::CHARGEBACK, self::PARTIAL_CHARGEBACK => 'red',
            self::REFUND, self::PARTIAL_REFUND => 'orange',
            self::PREVENT_CHARGEBACK => 'yellow',
            self::ACCEPTANCE => 'blue',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function isSuccess(): bool
    {
        return match($this) {
            self::CAPTURE, self::SETTLEMENT, self::ACCEPTANCE => true,
            default => false,
        };
    }

    public function isPending(): bool
    {
        return match($this) {
            self::PENDING, self::PENDING_PAYMENT => true,
            default => false,
        };
    }

    public function isFailed(): bool
    {
        return match($this) {
            self::DENY, self::CANCEL, self::EXPIRE => true,
            default => false,
        };
    }

    public function isRefund(): bool
    {
        return match($this) {
            self::REFUND, self::PARTIAL_REFUND => true,
            default => false,
        };
    }

    public function isChargeback(): bool
    {
        return match($this) {
            self::CHARGEBACK, self::PARTIAL_CHARGEBACK, self::PREVENT_CHARGEBACK => true,
            default => false,
        };
    }
}
