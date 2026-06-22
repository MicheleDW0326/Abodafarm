<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'image',
        'images',
        'type', // 'live' or 'fresh'
        'status', // 'available', 'pre_order', 'in_care', 'sold'
        'stock',
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
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
}
