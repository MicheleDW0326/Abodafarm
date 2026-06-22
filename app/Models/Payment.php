<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // Transaction status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CAPTURE = 'capture';
    const STATUS_SETTLEMENT = 'settlement';
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_DENY = 'deny';
    const STATUS_CANCEL = 'cancel';
    const STATUS_EXPIRE = 'expire';
    const STATUS_REFUND = 'refund';
    const STATUS_PARTIAL_REFUND = 'partial_refund';
    const STATUS_CHARGEBACK = 'challenge';
    const STATUS_PARTIAL_CHARGEBACK = 'partial_chargeback';
    const STATUS_PREVENT_CHARGEBACK = 'prevent_chargeback';
    const STATUS_ACCEPTANCE = 'acceptance';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'fraud_status',
        'snap_token',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if payment is completed successfully.
     */
    public function isCompleted(): bool
    {
        return in_array($this->transaction_status, [
            self::STATUS_SETTLEMENT,
            self::STATUS_CAPTURE,
        ]);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->transaction_status === self::STATUS_PENDING ||
               $this->transaction_status === self::STATUS_PENDING_PAYMENT;
    }

    /**
     * Check if payment is failed/cancelled/expired.
     */
    public function isFailed(): bool
    {
        return in_array($this->transaction_status, [
            self::STATUS_DENY,
            self::STATUS_CANCEL,
            self::STATUS_EXPIRE,
        ]);
    }

    /**
     * Get formatted gross amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_amount, 0, ',', '.');
    }
}