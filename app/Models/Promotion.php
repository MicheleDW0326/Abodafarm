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
        'code',
        'title',
        'description',
        'type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isValid(): bool
    {
        $now = Carbon::now();
        $hasStarted = !$this->starts_at || $this->starts_at <= $now;
        $notExpired = !$this->expires_at || $this->expires_at >= $now;
        return $this->is_active && $hasStarted && $notExpired;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isStarted(): bool
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    public function hasUsagesRemaining(): bool
    {
        if ($this->usage_limit === null) {
            return true;
        }
        return $this->usage_count < $this->usage_limit;
    }

    public function getUsageRemainingAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }
        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
