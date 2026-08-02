<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    protected $fillable = [
        'code',
        'type',
        'label',
        'label_en',
        'color',
        'sort_order',
        'is_terminal',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_status_id');
    }

    public function orderStatusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class, 'order_status_id');
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Get enum equivalent.
     */
    public function getEnumAttribute(): ?PaymentStatusEnum
    {
        return $this->code ? PaymentStatusEnum::tryFrom($this->code) : null;
    }

    /**
     * Get badge class.
     */
    public function getBadgeClassAttribute(): string
    {
        return $this->enum?->badgeClass()
            ?? "bg-{$this->color}-100 text-{$this->color}-700";
    }

    // =========================================================================
    // STATIC METHODS
    // =========================================================================

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public static function findIdByCode(string $code): ?int
    {
        return static::where('code', $code)->value('id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopePayment($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeTerminal($query)
    {
        return $query->where('is_terminal', true);
    }
}
