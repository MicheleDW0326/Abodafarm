<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    // =========================================================================
    // BACKWARD COMPATIBLE CONSTANTS (Legacy - use Enums instead)
    // =========================================================================
    public const TYPE_LIVE = 'live';
    public const TYPE_FRESH = 'fresh';
    public const TYPE_SUPPLY = 'supply';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_PRE_ORDER = 'pre_order';
    public const STATUS_IN_CARE = 'in_care';
    public const STATUS_SOLD = 'sold';

    // =========================================================================
    // FILLABLE
    // =========================================================================
    protected $fillable = [
        'category_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'discount_percent',
        'image',
        'images',
        'type',
        'status',
        'stock',
        'unit',
        // Livestock info
        'weight',
        'age',
        'sex',
        'origin',
        'condition',
        'breed',
        'barn_location',
        // Fresh meat info
        'weight_per_unit',
        'is_featured',
        // Supply product info
        'specifications',
        'how_to_use',
        'shipping_info',
    ];

    // =========================================================================
    // CASTS
    // =========================================================================
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'stock' => 'integer',
        'weight' => 'decimal:2',
        'weight_per_unit' => 'decimal:2',
        'images' => 'array',
        'is_featured' => 'boolean',
    ];

    // =========================================================================
    // BOOT
    // =========================================================================
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function stocks(): HasMany
    {
        return $this->inventoryLogs();
    }

    public function careRecords(): HasMany
    {
        return $this->hasMany(CareRecord::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('review_type', 'product');
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', Review::STATUS_APPROVED);
    }

    public function favoritedByUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'favoritable', 'favorites');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'favoritable_id')
            ->where('favoritable_type', Product::class);
    }

    // =========================================================================
    // COMPUTED PROPERTIES - ENUMS
    // =========================================================================

    /**
     * Get product type enum.
     */
    public function getTypeEnumAttribute(): ?ProductTypeEnum
    {
        return $this->type ? ProductTypeEnum::tryFrom($this->type) : null;
    }

    /**
     * Get product status enum.
     */
    public function getStatusEnumAttribute(): ?ProductStatusEnum
    {
        return $this->status ? ProductStatusEnum::tryFrom($this->status) : null;
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->typeEnum?->label()
            ?? match($this->type) {
                self::TYPE_LIVE => 'Ternak Hidup',
                self::TYPE_FRESH => 'Daging Segar',
                self::TYPE_SUPPLY => 'Pakan & Perlengkapan',
                default => $this->type,
            };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->statusEnum?->label()
            ?? match($this->status) {
                self::STATUS_AVAILABLE => 'Siap Jual',
                self::STATUS_PRE_ORDER => 'Pre Order',
                self::STATUS_IN_CARE => 'Dalam Perawatan',
                self::STATUS_SOLD => 'Terjual',
                default => $this->status,
            };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->statusEnum?->badgeClass()
            ?? 'bg-slate-100 text-slate-700';
    }

    /**
     * Get sex label.
     */
    public function getSexLabelAttribute(): string
    {
        return match($this->sex) {
            'male' => 'Jantan',
            'female' => 'Betina',
            'mixed' => 'Campuran',
            default => '-',
        };
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get current price (considering discount).
     */
    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->has_discount ? $this->discount_price : $this->price);
    }

    /**
     * Get formatted current price.
     */
    public function getFormattedCurrentPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->current_price, 0, ',', '.');
    }

    /**
     * Get formatted discount price.
     */
    public function getFormattedDiscountPriceAttribute(): ?string
    {
        if (!$this->has_discount) {
            return null;
        }
        return 'Rp ' . number_format($this->discount_price, 0, ',', '.');
    }

    /**
     * Check if has discount.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price !== null
            && (float) $this->discount_price > 0
            && (float) $this->discount_price < (float) $this->price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->has_discount || (float) $this->price <= 0) {
            return 0;
        }
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock <= 5) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    /**
     * Get stock status label.
     */
    public function getStockStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Stok Habis',
            'low_stock' => 'Stok Terbatas',
            'in_stock' => 'Tersedia',
            default => 'Tersedia',
        };
    }

    /**
     * Get stock status badge class.
     */
    public function getStockStatusBadgeClassAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'bg-red-100 text-red-700',
            'low_stock' => 'bg-amber-100 text-amber-700',
            'in_stock' => 'bg-green-100 text-green-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    /**
     * Get cart key.
     */
    public function getCartKeyAttribute(): string
    {
        return 'product_' . $this->id;
    }

    /**
     * Get image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Get rating distribution.
     */
    public function getRatingDistributionAttribute(): array
    {
        return [
            5 => $this->rating_5_count ?? 0,
            4 => $this->rating_4_count ?? 0,
            3 => $this->rating_3_count ?? 0,
            2 => $this->rating_2_count ?? 0,
            1 => $this->rating_1_count ?? 0,
        ];
    }

    /**
     * Get formatted rating.
     */
    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->rating ?? 0, 1);
    }

    // =========================================================================
    // BOOLEAN HELPERS
    // =========================================================================

    public function isLive(): bool
    {
        return $this->type === self::TYPE_LIVE;
    }

    public function isFresh(): bool
    {
        return $this->type === self::TYPE_FRESH;
    }

    public function isSupply(): bool
    {
        return $this->type === self::TYPE_SUPPLY;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isSaleable(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_PRE_ORDER]);
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function hasReviews(): bool
    {
        return ($this->total_reviews ?? 0) > 0;
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeLive($query)
    {
        return $query->where('type', self::TYPE_LIVE);
    }

    public function scopeFreshType($query)
    {
        return $query->where('type', self::TYPE_FRESH);
    }

    public function scopeSupply($query)
    {
        return $query->where('type', self::TYPE_SUPPLY);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeAvailableWithStatus($query)
    {
        return $query->whereIn('status', [self::STATUS_AVAILABLE, self::STATUS_PRE_ORDER]);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOwned($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeOwnedBy($query, $user)
    {
        $userId = is_object($user) ? $user->id : (is_numeric($user) ? (int) $user : null);
        return $query->where('owner_id', $userId);
    }
}
