<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'campaign_type',
        'description',
        'status',
        'start_date',
        'end_date',
        'priority',
        'auto_generate_voucher',
        'voucher_quantity',
        'max_claim',
        'budget_limit',
        'budget_used',
        'total_vouchers_generated',
        'total_vouchers_claimed',
        'total_vouchers_used',
        'total_discount_given',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget_limit' => 'decimal:2',
        'budget_used' => 'decimal:2',
        'total_discount_given' => 'decimal:2',
        'auto_generate_voucher' => 'boolean',
    ];

    public const TYPE_BIRTHDAY = 'birthday';
    public const TYPE_NEW_CUSTOMER = 'new_customer';
    public const TYPE_LOYAL_CUSTOMER = 'loyal_customer';
    public const TYPE_WEEKEND_SALE = 'weekend_sale';
    public const TYPE_FLASH_SALE = 'flash_sale';
    public const TYPE_SEASONAL = 'seasonal';
    public const TYPE_FREE_SHIPPING = 'free_shipping';
    public const TYPE_CASHBACK = 'cashback';
    public const TYPE_CART_ABANDONMENT = 'cart_abandonment';
    public const TYPE_REVIEW_REWARD = 'review_reward';
    public const TYPE_MANUAL = 'manual';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ENDED = 'ended';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CampaignRule::class, 'campaign_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(CustomerVoucher::class, 'campaign_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('campaign_type', $type);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_SCHEDULED])
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isValid(): bool
    {
        $now = Carbon::now();
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SCHEDULED])
            && $this->start_date->lte($now)
            && $this->end_date->gte($now);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function hasBudgetRemaining(): bool
    {
        if ($this->budget_limit === null) {
            return true;
        }
        return $this->budget_used < $this->budget_limit;
    }

    public function incrementClaimed(): void
    {
        $this->increment('total_vouchers_claimed');
    }

    public function incrementUsed(): void
    {
        $this->increment('total_vouchers_used');
    }

    public function addDiscountUsed(float $amount): void
    {
        $this->increment('total_discount_given', $amount);
        $this->increment('budget_used', $amount);
    }

    public static function generateSlug(string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->campaign_type) {
            self::TYPE_BIRTHDAY => 'Ulang Tahun',
            self::TYPE_NEW_CUSTOMER => 'Pelanggan Baru',
            self::TYPE_LOYAL_CUSTOMER => 'Pelanggan Setia',
            self::TYPE_WEEKEND_SALE => 'Diskon Akhir Pekan',
            self::TYPE_FLASH_SALE => 'Flash Sale',
            self::TYPE_SEASONAL => 'Musiman',
            self::TYPE_FREE_SHIPPING => 'Gratis Ongkir',
            self::TYPE_CASHBACK => 'Cashback',
            self::TYPE_CART_ABANDONMENT => 'Keranjang Tertinggal',
            self::TYPE_REVIEW_REWARD => 'Hadiah Ulasan',
            self::TYPE_MANUAL => 'Manual',
            default => ucfirst($this->campaign_type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_SCHEDULED => 'Dijadwalkan',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PAUSED => 'Dijeda',
            self::STATUS_ENDED => 'Berakhir',
            default => ucfirst($this->status),
        };
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->total_vouchers_claimed === 0) {
            return 0;
        }
        return round(($this->total_vouchers_used / $this->total_vouchers_claimed) * 100, 2);
    }

    public static function getCampaignTypes(): array
    {
        return [
            self::TYPE_BIRTHDAY => 'Ulang Tahun',
            self::TYPE_NEW_CUSTOMER => 'Pelanggan Baru',
            self::TYPE_LOYAL_CUSTOMER => 'Pelanggan Setia',
            self::TYPE_WEEKEND_SALE => 'Diskon Akhir Pekan',
            self::TYPE_FLASH_SALE => 'Flash Sale',
            self::TYPE_SEASONAL => 'Musiman',
            self::TYPE_FREE_SHIPPING => 'Gratis Ongkir',
            self::TYPE_CASHBACK => 'Cashback',
            self::TYPE_CART_ABANDONMENT => 'Keranjang Tertinggal',
            self::TYPE_REVIEW_REWARD => 'Hadiah Ulasan',
            self::TYPE_MANUAL => 'Manual',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_SCHEDULED => 'Dijadwalkan',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PAUSED => 'Dijeda',
            self::STATUS_ENDED => 'Berakhir',
        ];
    }
}
