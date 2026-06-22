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
}
