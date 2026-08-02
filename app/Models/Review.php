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
        if ($this->user?->avatar) {
            return asset('storage/' . $this->user->avatar);
        }
        return null;
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
}
