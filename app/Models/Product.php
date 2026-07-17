<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

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
        'type', // 'live', 'fresh', or 'supply'
        'status', // 'available', 'pre_order', 'in_care', 'sold'
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

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'stock' => 'integer',
        'weight' => 'decimal:2',
        'weight_per_unit' => 'decimal:2',
        'images' => 'array',
        'is_featured' => 'boolean',
        'type' => 'string',
        'status' => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

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

    // Alias for backward compatibility
    public function stocks(): HasMany
    {
        return $this->inventoryLogs();
    }

    public function careRecords(): HasMany
    {
        return $this->hasMany(CareRecord::class);
    }

    public function scopeLive($query)
    {
        return $query->where('type', 'live');
    }

    public function scopeFreshType($query)
    {
        return $query->where('type', 'fresh');
    }

    public function scopeSupply($query)
    {
        return $query->where('type', 'supply');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeAvailableWithStatus($query)
    {
        return $query->whereIn('status', ['available', 'pre_order']);
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

    public function isLive(): bool
    {
        return $this->type === 'live';
    }

    public function isFresh(): bool
    {
        return $this->type === 'fresh';
    }

    public function isSupply(): bool
    {
        return $this->type === 'supply';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function getSexLabelAttribute(): string
    {
        return match($this->sex) {
            'male' => 'Jantan',
            'female' => 'Betina',
            'mixed' => 'Campuran',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Siap Jual',
            'pre_order' => 'Pre Order',
            'in_care' => 'Dalam Perawatan',
            'sold' => 'Terjual',
            default => $this->status,
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->has_discount ? $this->discount_price : $this->price);
    }

    public function getFormattedCurrentPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->current_price, 0, ',', '.');
    }

    public function getFormattedDiscountPriceAttribute(): ?string
    {
        if (!$this->has_discount) {
            return null;
        }
        return 'Rp ' . number_format($this->discount_price, 0, ',', '.');
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price !== null && (float)$this->discount_price > 0 && (float)$this->discount_price < (float)$this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->has_discount || (float)$this->price <= 0) {
            return 0;
        }
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock <= 5) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Stok Habis',
            'low_stock' => 'Stok Terbatas',
            'in_stock' => 'Tersedia',
            default => 'Tersedia',
        };
    }

    public function getCartKeyAttribute(): string
    {
        return 'product_' . $this->id;
    }

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
     * Get users who favorited this product.
     */
    public function favoritedByUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'favoritable', 'favorites');
    }

    /**
     * Check if this product is favorited by a user.
     */
    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    /**
     * Get favorites for this product.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'favoritable_id')
            ->where('favoritable_type', Product::class);
    }

    /**
     * Get all reviews for this product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('review_type', 'product');
    }

    /**
     * Get approved reviews for this product.
     */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', Review::STATUS_APPROVED);
    }

    /**
     * Get rating distribution attribute.
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
     * Get formatted rating (e.g., "4.5").
     */
    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->rating ?? 0, 1);
    }

    /**
     * Check if product has reviews.
     */
    public function hasReviews(): bool
    {
        return ($this->total_reviews ?? 0) > 0;
    }
}
