<?php

namespace App\Models;

use App\Enums\ShipmentStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentStatus extends Model
{
    protected $fillable = [
        'code',
        'label',
        'label_en',
        'color',
        'icon',
        'sort_order',
        'tracking_url_template',
        'customer_notified',
    ];

    protected $casts = [
        'customer_notified' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipment_status_id');
    }

    public function orderStatusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class, 'shipment_status_id');
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Get enum equivalent.
     */
    public function getEnumAttribute(): ?ShipmentStatusEnum
    {
        return $this->code ? ShipmentStatusEnum::tryFrom($this->code) : null;
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('code', '!=', 'cancelled');
    }
}
