<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'review_type',
        'product_id',
        'farmer_id',
        'rating',
        'content',
        'title',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Type constants
    public const TYPE_PRODUCT = 'product';
    public const TYPE_FARMER = 'farmer';

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the user (reviewer).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order (verified purchase).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product (for product reviews).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the farmer (for booking reviews).
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    /**
     * Get the photos.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ReviewPhoto::class)->orderBy('sort_order');
    }

    /**
     * Scope: approved reviews only.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: product reviews only.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('review_type', self::TYPE_PRODUCT)
            ->where('product_id', $productId);
    }

    /**
     * Scope: farmer reviews only.
     */
    public function scopeForFarmer($query, int $farmerId)
    {
        return $query->where('review_type', self::TYPE_FARMER)
            ->where('farmer_id', $farmerId);
    }

    /**
     * Get photo URLs accessor.
     */
    public function getPhotoUrlsAttribute(): array
    {
        return $this->photos->map(fn(ReviewPhoto $photo) => $photo->url)->toArray();
    }

    /**
     * Check if this is a verified purchase review.
     */
    public function getIsVerifiedPurchaseAttribute(): bool
    {
        return $this->order && in_array($this->order->status, ['delivered', 'completed']);
    }

    /**
     * Get reviewer name accessor.
     */
    public function getReviewerNameAttribute(): string
    {
        return $this->user?->name ?? 'Anonim';
    }

    /**
     * Get reviewer avatar accessor.
     */
    public function getReviewerAvatarAttribute(): ?string
    {
        if ($this->user?->avatar) {
            return asset('storage/' . $this->user->avatar);
        }
        return null;
    }

    /**
     * Get status label accessor.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get type label accessor.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->review_type) {
            self::TYPE_PRODUCT => 'Produk',
            self::TYPE_FARMER => 'Peternak',
            default => ucfirst($this->review_type),
        };
    }

    /**
     * Check if this review can be edited by user.
     */
    public function canBeEdited(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    /**
     * Check if this is a product review.
     */
    public function isProductReview(): bool
    {
        return $this->review_type === self::TYPE_PRODUCT;
    }

    /**
     * Check if this is a farmer review.
     */
    public function isFarmerReview(): bool
    {
        return $this->review_type === self::TYPE_FARMER;
    }
}
