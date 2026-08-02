<?php

namespace App\Models;

use App\Enums\MidtransTransactionStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // =========================================================================
    // BACKWARD COMPATIBLE CONSTANTS (Legacy - use Enums instead)
    // =========================================================================
    public const STATUS_PENDING = 'pending';
    public const STATUS_CAPTURE = 'capture';
    public const STATUS_SETTLEMENT = 'settlement';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_DENY = 'deny';
    public const STATUS_CANCEL = 'cancel';
    public const STATUS_EXPIRE = 'expire';
    public const STATUS_REFUND = 'refund';
    public const STATUS_PARTIAL_REFUND = 'partial_refund';
    public const STATUS_CHARGEBACK = 'challenge';
    public const STATUS_PARTIAL_CHARGEBACK = 'partial_chargeback';
    public const STATUS_PREVENT_CHARGEBACK = 'prevent_chargeback';
    public const STATUS_ACCEPTANCE = 'acceptance';

    // =========================================================================
    // FILLABLE
    // =========================================================================
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

    // =========================================================================
    // CASTS
    // =========================================================================
    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Get transaction status enum.
     */
    public function getTransactionStatusEnumAttribute(): ?MidtransTransactionStatusEnum
    {
        return $this->transaction_status
            ? MidtransTransactionStatusEnum::tryFrom($this->transaction_status)
            : null;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->transactionStatusEnum?->label()
            ?? $this->transaction_status;
    }

    /**
     * Get badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->transactionStatusEnum?->badgeClass()
            ?? 'bg-slate-100 text-slate-700';
    }

    /**
     * Get formatted gross amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_amount, 0, ',', '.');
    }

    // =========================================================================
    // BOOLEAN HELPERS
    // =========================================================================

    /**
     * Check if payment is completed successfully.
     */
    public function isCompleted(): bool
    {
        return $this->transactionStatusEnum?->isSuccess()
            ?? in_array($this->transaction_status, [
                self::STATUS_SETTLEMENT,
                self::STATUS_CAPTURE,
            ]);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->transactionStatusEnum?->isPending()
            ?? in_array($this->transaction_status, [
                self::STATUS_PENDING,
                self::STATUS_PENDING_PAYMENT,
            ]);
    }

    /**
     * Check if payment is failed/cancelled/expired.
     */
    public function isFailed(): bool
    {
        return $this->transactionStatusEnum?->isFailed()
            ?? in_array($this->transaction_status, [
                self::STATUS_DENY,
                self::STATUS_CANCEL,
                self::STATUS_EXPIRE,
            ]);
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->transactionStatusEnum?->isRefund()
            ?? in_array($this->transaction_status, [
                self::STATUS_REFUND,
                self::STATUS_PARTIAL_REFUND,
            ]);
    }

    /**
     * Check if payment has chargeback.
     */
    public function hasChargeback(): bool
    {
        return $this->transactionStatusEnum?->isChargeback()
            ?? in_array($this->transaction_status, [
                self::STATUS_CHARGEBACK,
                self::STATUS_PARTIAL_CHARGEBACK,
                self::STATUS_PREVENT_CHARGEBACK,
            ]);
    }
}
