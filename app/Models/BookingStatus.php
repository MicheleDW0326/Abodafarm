<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingStatus extends Model
{
    protected $fillable = [
        'code',
        'label',
        'label_en',
        'color',
        'icon',
        'sort_order',
        'requires_action',
        'is_cancelled',
    ];

    protected $casts = [
        'requires_action' => 'boolean',
        'is_cancelled' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'booking_status_id');
    }

    public function orderStatusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class, 'booking_status_id');
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Get enum equivalent.
     */
    public function getEnumAttribute(): ?BookingStatusEnum
    {
        return $this->code ? BookingStatusEnum::tryFrom($this->code) : null;
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

    public function scopeRequiresAction(Builder $query): Builder
    {
        return $query->where('requires_action', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_cancelled', false);
    }
}
