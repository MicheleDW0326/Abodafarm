<?php

namespace App\Models;

use App\Enums\ReviewStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Review extends Model
{
    // =========================================================================
    // BACKWARD COMPATIBLE CONSTANTS (Legacy - use Enums instead)
    // =========================================================================
    public const TYPE_PRODUCT = 'product';
    public const TYPE_FARMER = 'farmer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // =========================================================================
    // FILLABLE
    // =========================================================================
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
        'is_visible',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    // =========================================================================
    // CASTS
    // =========================================================================
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_visible' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReviewPhoto::class)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ReviewVideo::class)->orderBy('sort_order');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Get status enum.
     */
    public function getStatusEnumAttribute(): ?ReviewStatusEnum
    {
        return $this->status ? ReviewStatusEnum::tryFrom($this->status) : null;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->statusEnum?->label()
            ?? match($this->status) {
                self::STATUS_PENDING => 'Menunggu',
                self::STATUS_APPROVED => 'Disetujui',
                self::STATUS_REJECTED => 'Ditolak',
                default => ucfirst($this->status ?? ''),
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
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->review_type) {
            self::TYPE_PRODUCT => 'Produk',
            self::TYPE_FARMER => 'Peternak',
            default => ucfirst($this->review_type ?? ''),
        };
    }

    /**
     * Get photo URLs.
     */
    public function getPhotoUrlsAttribute(): array
    {
        return $this->photos->map(fn(ReviewPhoto $photo) => $photo->url)->toArray();
    }

    /**
     * Check if verified purchase.
     */
    public function getIsVerifiedPurchaseAttribute(): bool
    {
        return $this->order && in_array($this->order->status, ['delivered', 'completed']);
    }

    /**
     * Get reviewer name.
     */
    public function getReviewerNameAttribute(): string
    {
        return $this->user?->name ?? 'Anonim';
    }

    /**
     * Get reviewer avatar.
     */
    public function getReviewerAvatarAttribute(): ?string
    {
        if ($this->user?->farmProfile?->image) {
            return asset('storage/' . $this->user->farmProfile->image);
        }
        return null;
    }

    /**
     * Get like count.
     */
    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Check if liked by user.
     */
    public function isLikedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Get report count.
     */
    public function getReportCountAttribute(): int
    {
        return $this->reports()->count();
    }

    /**
     * Get media (photos + videos) count.
     */
    public function getMediaCountAttribute(): int
    {
        return $this->photos()->count() + $this->videos()->count();
    }

    // =========================================================================
    // BOOLEAN HELPERS
    // =========================================================================

    public function isProductReview(): bool
    {
        return $this->review_type === self::TYPE_PRODUCT;
    }

    public function isFarmerReview(): bool
    {
        return $this->review_type === self::TYPE_FARMER;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeEdited(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    public function canBeDeleted(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    public function hasMedia(): bool
    {
        return $this->media_count > 0;
    }

    public function hasPhotos(): bool
    {
        return $this->photos()->count() > 0;
    }

    public function hasVideos(): bool
    {
        return $this->videos()->count() > 0;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('review_type', self::TYPE_PRODUCT)
            ->where('product_id', $productId);
    }

    public function scopeForFarmer($query, int $farmerId)
    {
        return $query->where('review_type', self::TYPE_FARMER)
            ->where('farmer_id', $farmerId);
    }

    public function scopeWithMedia($query)
    {
        return $query->whereHas('photos')->orWhereHas('videos');
    }

    public function scopeOrderedByLikes($query)
    {
        return $query->withCount('likes')->orderByDesc('likes_count');
    }
}
