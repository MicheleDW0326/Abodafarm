<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ShipmentStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    // =========================================================================
    // BACKWARD COMPATIBLE CONSTANTS (Legacy - use Enums instead)
    // =========================================================================
    public const TYPE_CART = 'cart';
    public const TYPE_INQUIRY = 'inquiry';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // =========================================================================
    // FILLABLE ATTRIBUTES
    // =========================================================================
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
        'order_type',
        'preferred_delivery_date',
        'pickup_location',
        'admin_notes',
        'tracking_number',
        'shipping_carrier',
        'shipped_at',
        'delivered_at',
        // New FK columns
        'payment_status_id',
        'booking_status_id',
        'shipment_status_id',
    ];

    // =========================================================================
    // CASTS
    // =========================================================================
    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'preferred_delivery_date' => 'date',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'total_amount' => 'decimal:0',
            'total_items' => 'integer',
            // Enum casts for new FK columns
            'payment_status_id' => 'integer',
            'booking_status_id' => 'integer',
            'shipment_status_id' => 'integer',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'asc');
    }

    // New FK-based relationships
    public function paymentStatusRelation(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'payment_status_id');
    }

    public function bookingStatusRelation(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

    public function shipmentStatusRelation(): BelongsTo
    {
        return $this->belongsTo(ShipmentStatus::class, 'shipment_status_id');
    }

    // =========================================================================
    // COMPUTED PROPERTIES - NEW STATUS ACCESSORS
    // =========================================================================

    /**
     * Get payment status enum value.
     */
    public function getPaymentStatusEnumAttribute(): ?PaymentStatusEnum
    {
        $code = $this->paymentStatusRelation?->code;
        return $code ? PaymentStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get booking status enum value.
     */
    public function getBookingStatusEnumAttribute(): ?BookingStatusEnum
    {
        $code = $this->bookingStatusRelation?->code;
        return $code ? BookingStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get shipment status enum value.
     */
    public function getShipmentStatusEnumAttribute(): ?ShipmentStatusEnum
    {
        $code = $this->shipmentStatusRelation?->code;
        return $code ? ShipmentStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get effective payment status label.
     */
    public function getPaymentStatusLabelAttribute(): ?string
    {
        return $this->paymentStatusEnum?->label();
    }

    /**
     * Get effective booking status label.
     */
    public function getBookingStatusLabelAttribute(): ?string
    {
        return $this->bookingStatusEnum?->label();
    }

    /**
     * Get effective shipment status label.
     */
    public function getShipmentStatusLabelAttribute(): ?string
    {
        return $this->shipmentStatusEnum?->label();
    }

    /**
     * Get payment status badge class.
     */
    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return $this->paymentStatusEnum?->badgeClass() ?? 'bg-slate-100 text-slate-700';
    }

    /**
     * Get shipment status badge class.
     */
    public function getShipmentStatusBadgeClassAttribute(): string
    {
        return $this->shipmentStatusEnum?->badgeClass() ?? 'bg-slate-100 text-slate-700';
    }

    // =========================================================================
    // COMPUTED PROPERTIES - LEGACY BACKWARD COMPATIBILITY
    // =========================================================================

    /**
     * Get effective status (derived from new FK columns).
     * Used for backward compatibility.
     */
    public function getEffectiveStatusAttribute(): ?string
    {
        // For backward compatibility - derive from new FK columns
        if ($this->order_type === self::TYPE_CART) {
            return $this->shipmentStatusRelation?->code
                ?? $this->paymentStatusRelation?->code
                ?? self::STATUS_PENDING;
        }

        // For bookings
        return $this->shipmentStatusRelation?->code
            ?? $this->bookingStatusRelation?->code
            ?? self::STATUS_PENDING;
    }

    /**
     * Get the primary status label for display.
     * Prioritizes: shipment > booking > payment
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->shipmentStatusLabel
            ?? $this->bookingStatusLabel
            ?? $this->paymentStatusLabel
            ?? $this->getStatusLabelFromCode($this->status);
    }

    /**
     * Get status badge class (legacy accessor).
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->booking_status_badge_class;
    }

    /**
     * Get booking status badge class.
     */
    public function getBookingStatusBadgeClassAttribute(): string
    {
        return $this->bookingStatusEnum?->badgeClass() ?? 'bg-amber-100 text-amber-700';
    }

    /**
     * Get order type label.
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return OrderTypeEnum::tryFrom($this->order_type)?->label()
            ?? match($this->order_type) {
                'cart' => 'Pembelian',
                'inquiry' => 'Booking/Inquiry',
                default => $this->order_type,
            };
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
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
                'is_current' => $log->to_status === ($this->effective_status ?? $this->status),
                'is_terminal' => $log->isTerminalStatus(),
            ];
        })->toArray();
    }

    // =========================================================================
    // BOOLEAN HELPERS
    // =========================================================================

    public function isCart(): bool
    {
        return $this->order_type === self::TYPE_CART;
    }

    public function isInquiry(): bool
    {
        return $this->order_type === self::TYPE_INQUIRY;
    }

    public function isBooking(): bool
    {
        return $this->isInquiry();
    }

    public function isCompleted(): bool
    {
        $status = $this->effective_status ?? $this->status;
        return in_array($status, [self::STATUS_DELIVERED, self::STATUS_COMPLETED]);
    }

    public function isTerminalStatus(): bool
    {
        $status = $this->effective_status ?? $this->status;
        return in_array($status, [self::STATUS_COMPLETED, self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    public function canBeCancelledByUser(): bool
    {
        $status = $this->effective_status ?? $this->status;
        return $status === self::STATUS_PENDING;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeCart($query)
    {
        return $query->where('order_type', self::TYPE_CART);
    }

    public function scopeInquiry($query)
    {
        return $query->where('order_type', self::TYPE_INQUIRY);
    }

    public function scopeBooking($query)
    {
        return $query->where('order_type', self::TYPE_INQUIRY);
    }

    public function scopeFreshOrders($query)
    {
        return $query->where('order_type', self::TYPE_CART);
    }

    public function scopePendingBooking($query)
    {
        return $query->where('order_type', self::TYPE_INQUIRY)
                     ->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_REJECTED]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_DELIVERED, self::STATUS_COMPLETED]);
    }

    public function scopeUserBookings($query, int $userId)
    {
        return $query->where('user_id', $userId)->where('order_type', self::TYPE_INQUIRY);
    }

    public function scopeUserOrders($query, int $userId)
    {
        return $query->where('user_id', $userId)->where('order_type', self::TYPE_CART);
    }

    public function scopeWithOwnedProducts($query, int $userId)
    {
        return $query->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
    }

    // New FK-based scopes
    public function scopeByPaymentStatus($query, string $statusCode)
    {
        return $query->whereHas('paymentStatusRelation', fn($q) => $q->where('code', $statusCode));
    }

    public function scopeByBookingStatus($query, string $statusCode)
    {
        return $query->whereHas('bookingStatusRelation', fn($q) => $q->where('code', $statusCode));
    }

    public function scopeByShipmentStatus($query, string $statusCode)
    {
        return $query->whereHas('shipmentStatusRelation', fn($q) => $q->where('code', $statusCode));
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================

    public static function generateBookingCode(): string
    {
        return 'BK-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }

    /**
     * Get status label from status code (for backward compatibility).
     */
    public static function getStatusLabel(string $status): string
    {
        return self::getStatusLabelFromCode($status);
    }

    protected static function getStatusLabelFromCode(?string $status): string
    {
        return match($status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Diterima',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => $status ? ucfirst($status) : 'Unknown',
        };
    }

    /**
     * Get all valid statuses (legacy).
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    // =========================================================================
    // REVIEW HELPER METHODS
    // =========================================================================

    public function hasCompletedPurchaseForProduct(int $productId): bool
    {
        return self::where('id', $this->id)
            ->whereIn('status', [self::STATUS_DELIVERED, self::STATUS_COMPLETED])
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->exists();
    }

    public function hasExistingReviewForProduct(int $productId): bool
    {
        return Review::where('order_id', $this->id)
            ->where('product_id', $productId)
            ->exists();
    }

    public function hasExistingReviewForFarmer(int $farmerId): bool
    {
        return Review::where('order_id', $this->id)
            ->where('farmer_id', $farmerId)
            ->exists();
    }

    public function canReviewProduct(int $userId, int $productId): bool
    {
        if (!$this->isCompleted()) return false;
        if ($this->user_id !== $userId) return false;
        if (!$this->hasCompletedPurchaseForProduct($productId)) return false;
        if ($this->hasExistingReviewForProduct($productId)) return false;

        return true;
    }

    public function canReviewFarmer(int $userId, int $farmerId): bool
    {
        if ($this->status !== self::STATUS_COMPLETED) return false;
        if (!$this->isInquiry()) return false;
        if ($this->user_id !== $userId) return false;

        $hasFarmerProduct = $this->items()
            ->whereHas('product', fn($q) => $q->where('owner_id', $farmerId))
            ->exists();

        if (!$hasFarmerProduct) return false;
        if ($this->hasExistingReviewForFarmer($farmerId)) return false;

        return true;
    }

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

    public function getReviewableProducts(): Collection
    {
        if (!$this->isCompleted()) {
            return collect();
        }

        return $this->items
            ->filter(fn($item) => $item->product_id && !$this->hasExistingReviewForProduct($item->product_id))
            ->map(fn($item) => $item->product)
            ->filter();
    }

    // =========================================================================
    // STATUS LOG METHODS
    // =========================================================================

    public function hasStatusLogs(): bool
    {
        return $this->statusLogs()->exists();
    }

    public function getLastStatusLog(): ?OrderStatusLog
    {
        return $this->statusLogs()->latest('created_at')->first();
    }
}
