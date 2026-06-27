<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    // Order type constants
    const TYPE_CART = 'cart';
    const TYPE_INQUIRY = 'inquiry';

    // Order status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'total_items',
        'total_amount',
        'status',
        'placed_at',
        'order_type', // 'cart' or 'inquiry'
        'preferred_delivery_date',
        'pickup_location',
        'admin_notes',
        // Shipping tracking
        'tracking_number',
        'shipping_carrier',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'preferred_delivery_date' => 'date',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'total_amount' => 'decimal:0',
            'total_items' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment for the order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the status logs for the order.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get timeline data for UI display.
     */
    public function getTimelineDataAttribute(): array
    {
        return $this->statusLogs->map(function (OrderStatusLog $log) {
            return [
                'id' => $log->id,
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'status_label' => $log->status_change_label,
                'triggered_by_type' => $log->triggered_by_type,
                'triggered_by_name' => $log->triggered_by_name,
                'notes' => $log->notes,
                'timestamp' => $log->created_at?->toIso8601String(),
                'formatted_date' => $log->created_at?->format('d M Y'),
                'formatted_time' => $log->created_at?->format('H:i'),
                'is_current' => $log->to_status === $this->status,
                'is_terminal' => $log->isTerminalStatus(),
            ];
        })->toArray();
    }

    public function scopeWithOwnedProducts($query, int $userId)
    {
        return $query->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
    }

    public function scopeCart($query)
    {
        return $query->where('order_type', 'cart');
    }

    public function scopeInquiry($query)
    {
        return $query->where('order_type', 'inquiry');
    }

    public function isCart(): bool
    {
        return $this->order_type === 'cart';
    }

    public function isInquiry(): bool
    {
        return $this->order_type === 'inquiry';
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return match($this->order_type) {
            'cart' => 'Pembelian',
            'inquiry' => 'Inquiry/Booking',
            default => $this->order_type,
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // Booking status label
    public function getBookingStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'shipped' => 'Dikirim',
            'delivered' => 'Diterima',
            'paid' => 'Lunas',
            default => ucfirst($this->status),
        };
    }

    // Status badge color
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-amber-100 text-amber-700',
            'processing' => 'bg-blue-100 text-blue-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-600',
            'shipped' => 'bg-purple-100 text-purple-700',
            'delivered' => 'bg-emerald-100 text-emerald-700',
            'paid' => 'bg-green-100 text-green-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    // Check if order is a booking
    public function isBooking(): bool
    {
        return $this->order_type === self::TYPE_INQUIRY;
    }

    // Booking actions
    public function approve(): bool
    {
        return $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function reject(): bool
    {
        return $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function complete(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Get all valid statuses.
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * Get status label.
     */
    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Diterima',
            self::STATUS_PAID => 'Lunas',
            default => ucfirst($status),
        };
    }

    /**
     * Get status badge class.
     */
    public static function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-700',
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-700',
            self::STATUS_APPROVED => 'bg-green-100 text-green-700',
            self::STATUS_REJECTED => 'bg-red-100 text-red-700',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-700',
            self::STATUS_CANCELLED => 'bg-slate-100 text-slate-600',
            self::STATUS_SHIPPED => 'bg-purple-100 text-purple-700',
            self::STATUS_DELIVERED => 'bg-emerald-100 text-emerald-700',
            self::STATUS_PAID => 'bg-green-100 text-green-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    /**
     * Check if order has any status logs.
     */
    public function hasStatusLogs(): bool
    {
        return $this->statusLogs()->exists();
    }

    /**
     * Get the last status log.
     */
    public function getLastStatusLog(): ?OrderStatusLog
    {
        return $this->statusLogs()->latest('created_at')->first();
    }

    /**
     * Check if current status is terminal.
     */
    public function isTerminalStatus(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if user can cancel this order.
     */
    public function canBeCancelledByUser(): bool
    {
        // User can cancel if:
        // 1. Order is pending (not paid yet)
        // 2. Not already cancelled/completed/rejected
        return $this->status === self::STATUS_PENDING;
    }

    // Scope for booking orders
    public function scopeBooking($query)
    {
        return $query->where('order_type', self::TYPE_INQUIRY);
    }

    // Scope for pending booking
    public function scopePendingBooking($query)
    {
        return $query->where('order_type', self::TYPE_INQUIRY)
                     ->where('status', self::STATUS_PENDING);
    }

    // Generate booking code
    public static function generateBookingCode(): string
    {
        return 'BK-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }

    // Scope for cart-type orders (fresh meat)
    public function scopeFreshOrders($query)
    {
        return $query->where('order_type', self::TYPE_CART);
    }

    // Scope for active orders (not cancelled, not completed)
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'completed', 'rejected']);
    }

    // Scope for completed orders
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'completed']);
    }

    // Scope for user's bookings
    public function scopeUserBookings($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('order_type', self::TYPE_INQUIRY);
    }

    // Scope for user's orders
    public function scopeUserOrders($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('order_type', self::TYPE_CART);
    }

    // Review Helper Methods

    /**
     * Check if order is completed and can be reviewed.
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_COMPLETED]);
    }

    /**
     * Check if user has completed purchase for a specific product.
     */
    public function hasCompletedPurchaseForProduct(int $productId): bool
    {
        return self::where('id', $this->id)
            ->whereIn('status', [self::STATUS_DELIVERED, self::STATUS_COMPLETED])
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->exists();
    }

    /**
     * Check if user already has a review for this product in this order.
     */
    public function hasExistingReviewForProduct(int $productId): bool
    {
        return Review::where('order_id', $this->id)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Check if user already has a review for this farmer in this booking.
     */
    public function hasExistingReviewForFarmer(int $farmerId): bool
    {
        return Review::where('order_id', $this->id)
            ->where('farmer_id', $farmerId)
            ->exists();
    }

    /**
     * Check if user can review a specific product in this order.
     */
    public function canReviewProduct(int $userId, int $productId): bool
    {
        // Must be completed
        if (!$this->isCompleted()) {
            return false;
        }

        // Must own the order
        if ($this->user_id !== $userId) {
            return false;
        }

        // Must have purchased this product
        if (!$this->hasCompletedPurchaseForProduct($productId)) {
            return false;
        }

        // Must not have already reviewed
        if ($this->hasExistingReviewForProduct($productId)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can review a specific farmer for this booking.
     */
    public function canReviewFarmer(int $userId, int $farmerId): bool
    {
        // Must be completed booking
        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        // Must be an inquiry/booking
        if (!$this->isInquiry()) {
            return false;
        }

        // Must own the booking
        if ($this->user_id !== $userId) {
            return false;
        }

        // Must have booked from this farmer
        $hasFarmerProduct = $this->items()
            ->whereHas('product', fn($q) => $q->where('owner_id', $farmerId))
            ->exists();

        if (!$hasFarmerProduct) {
            return false;
        }

        // Must not have already reviewed this farmer
        if ($this->hasExistingReviewForFarmer($farmerId)) {
            return false;
        }

        return true;
    }

    /**
     * Get the farmer IDs associated with this order's products.
     */
    public function getFarmerIds(): array
    {
        return $this->items()
            ->whereHas('product')
            ->with('product.owner')
            ->get()
            ->pluck('product.owner_id')
            ->filter()
            ->unique()
            ->toArray();
    }

    /**
     * Get products that can be reviewed in this order.
     */
    public function getReviewableProducts(): Collection
    {
        if (!$this->isCompleted()) {
            return collect();
        }

        return $this->items
            ->filter(function ($item) {
                return $item->product_id && !$this->hasExistingReviewForProduct($item->product_id);
            })
            ->map(fn($item) => $item->product)
            ->filter();
    }
}
