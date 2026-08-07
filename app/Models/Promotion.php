<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_purchase',
        'maximum_discount',
        'usage_limit',
        'usage_per_user',
        'used_count',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast() || $this->status === self::STATUS_EXPIRED;
    }

    public function isStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    public function hasUsagesRemaining(): bool
    {
        if ($this->usage_limit === null) {
            return true;
        }
        return $this->used_count < $this->usage_limit;
    }

    public function getUsageRemainingAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }
        return max(0, $this->usage_limit - $this->used_count);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    public function getDiscountTypes(): array
    {
        return config('promotions.types');
    }

    public function getStatusOptions(): array
    {
        return config('promotions.statuses');
    }
}
