<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    // Type constants
    const TYPE_ORDER_STATUS = 'order_status';
    const TYPE_BOOKING_APPROVED = 'booking_approved';
    const TYPE_BOOKING_REJECTED = 'booking_rejected';
    const TYPE_ORDER_SHIPPED = 'order_shipped';
    const TYPE_ORDER_DELIVERED = 'order_delivered';
    const TYPE_PROMO = 'promo';
    const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for user's notifications.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get notification icon based on type.
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ORDER_STATUS, self::TYPE_ORDER_SHIPPED => 'heroicon-o-truck',
            self::TYPE_BOOKING_APPROVED => 'heroicon-o-check-circle',
            self::TYPE_BOOKING_REJECTED => 'heroicon-o-x-circle',
            self::TYPE_ORDER_DELIVERED => 'heroicon-o-package',
            self::TYPE_PROMO => 'heroicon-o-gift',
            default => 'heroicon-o-bell',
        };
    }

    /**
     * Get notification color based on type.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_BOOKING_APPROVED, self::TYPE_ORDER_DELIVERED => 'text-emerald-600 bg-emerald-50',
            self::TYPE_BOOKING_REJECTED => 'text-red-600 bg-red-50',
            self::TYPE_ORDER_SHIPPED => 'text-purple-600 bg-purple-50',
            self::TYPE_PROMO => 'text-amber-600 bg-amber-50',
            default => 'text-sky-600 bg-sky-50',
        };
    }

    /**
     * Create a notification for order status change.
     */
    public static function forOrderStatus(int $userId, string $orderNumber, string $status, string $message): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_ORDER_STATUS,
            'title' => "Pesanan #{$orderNumber}",
            'message' => $message,
            'data' => [
                'order_number' => $orderNumber,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Create a notification for booking approval.
     */
    public static function forBookingApproved(int $userId, string $bookingCode, string $message): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_APPROVED,
            'title' => "Booking {$bookingCode} Disetujui",
            'message' => $message,
            'data' => [
                'booking_code' => $bookingCode,
            ],
        ]);
    }

    /**
     * Create a notification for booking rejection.
     */
    public static function forBookingRejected(int $userId, string $bookingCode, string $message): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_REJECTED,
            'title' => "Booking {$bookingCode} Ditolak",
            'message' => $message,
            'data' => [
                'booking_code' => $bookingCode,
            ],
        ]);
    }
}
