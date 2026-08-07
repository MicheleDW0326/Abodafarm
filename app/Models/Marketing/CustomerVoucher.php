<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CustomerVoucher extends Model
{
    use HasFactory;

    protected $table = 'customer_vouchers';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'voucher_code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'maximum_discount',
        'minimum_order',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'status',
        'is_auto_applied',
        'metadata',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_auto_applied' => 'boolean',
        'metadata' => 'array',
    ];

    public const DISCOUNT_PERCENTAGE = 'percentage';
    public const DISCOUNT_FIXED = 'fixed';
    public const DISCOUNT_FREE_SHIPPING = 'free_shipping';
    public const DISCOUNT_CASHBACK = 'cashback';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_USED = 'used';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAvailable($query)
    {
        $now = Carbon::now();
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeExpiringSoon($query, int $days = 3)
    {
        $now = Carbon::now();
        $future = $now->copy()->addDays($days);
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereBetween('valid_until', [$now, $future]);
    }

    public function isValid(): bool
    {
        $now = Carbon::now();
        return $this->status === self::STATUS_ACTIVE
            && $this->valid_from->lte($now)
            && $this->valid_until->gte($now)
            && $this->used_count < $this->usage_limit;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || $this->valid_until->isPast();
    }

    public function hasUsagesRemaining(): bool
    {
        return $this->used_count < $this->usage_limit;
    }

    public function canUse(): bool
    {
        return $this->isValid() && $this->hasUsagesRemaining();
    }

    public function calculateDiscount(float $subtotal, float $shipping = 0): float
    {
        if (!$this->canUse()) {
            return 0;
        }

        if ($this->minimum_order && $subtotal < (float) $this->minimum_order) {
            return 0;
        }

        $discount = 0;

        switch ($this->discount_type) {
            case self::DISCOUNT_PERCENTAGE:
                $discount = $subtotal * ((float) $this->discount_value / 100);
                if ($this->maximum_discount) {
                    $discount = min($discount, (float) $this->maximum_discount);
                }
                break;

            case self::DISCOUNT_FIXED:
                $discount = min((float) $this->discount_value, $subtotal);
                break;

            case self::DISCOUNT_FREE_SHIPPING:
                $discount = $shipping;
                break;

            case self::DISCOUNT_CASHBACK:
                $discount = min((float) $this->discount_value, $subtotal);
                break;
        }

        return round($discount, 0);
    }

    public function markAsUsed(): void
    {
        $this->increment('used_count');

        if ($this->used_count >= $this->usage_limit) {
            $this->update(['status' => self::STATUS_USED]);
        }
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    public static function generateCode(string $prefix = 'VCH'): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = $prefix . '-';

        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        if (static::where('voucher_code', $code)->exists()) {
            return static::generateCode($prefix);
        }

        return $code;
    }

    public function getFormattedDiscountAttribute(): string
    {
        return match($this->discount_type) {
            self::DISCOUNT_PERCENTAGE => $this->discount_value . '%',
            self::DISCOUNT_FREE_SHIPPING => 'Gratis Ongkir',
            self::DISCOUNT_CASHBACK => 'Cashback ' . $this->discount_value . '%',
            default => 'Rp ' . number_format($this->discount_value, 0, ',', '.'),
        };
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return (int) now()->diffInDays($this->valid_until, false);
    }

    public static function getDiscountTypes(): array
    {
        return [
            self::DISCOUNT_PERCENTAGE => 'Persentase',
            self::DISCOUNT_FIXED => 'Nominal Tetap',
            self::DISCOUNT_FREE_SHIPPING => 'Gratis Ongkir',
            self::DISCOUNT_CASHBACK => 'Cashback',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_USED => 'Terpakai',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_REVOKED => 'Dibatalkan',
        ];
    }
}
