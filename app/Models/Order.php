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
}
