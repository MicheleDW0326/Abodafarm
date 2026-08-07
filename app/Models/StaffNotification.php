<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffNotification extends Model
{
    use HasFactory;

    protected $table = 'staff_notifications';

    // Type constants
    const TYPE_ORDER_ASSIGNED = 'order_assigned';
    const TYPE_ORDER_UNASSIGNED = 'order_unassigned';
    const TYPE_ORDER_UPDATED = 'order_updated';
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
            self::TYPE_ORDER_ASSIGNED => 'fa-solid fa-user-check',
            self::TYPE_ORDER_UNASSIGNED => 'fa-solid fa-user-xmark',
            self::TYPE_ORDER_UPDATED => 'fa-solid fa-clipboard-list',
            default => 'fa-solid fa-bell',
        };
    }

    /**
     * Get notification color based on type.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ORDER_ASSIGNED => 'text-emerald-600 bg-emerald-50',
            self::TYPE_ORDER_UNASSIGNED => 'text-amber-600 bg-amber-50',
            self::TYPE_ORDER_UPDATED => 'text-blue-600 bg-blue-50',
            default => 'text-slate-600 bg-slate-50',
        };
    }

    /**
     * Create a notification for order assignment.
     */
    public static function forOrderAssigned(
        int $userId,
        string $orderNumber,
        string $assignedBy,
        string $customerName
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_ORDER_ASSIGNED,
            'title' => "Pesanan Ditugaskan",
            'message' => "Pesanan {$orderNumber} ({$customerName}) telah ditugaskan kepada Anda oleh {$assignedBy}.",
            'data' => [
                'order_number' => $orderNumber,
                'assigned_by' => $assignedBy,
                'customer_name' => $customerName,
            ],
        ]);
    }

    /**
     * Create a notification for order unassignment.
     */
    public static function forOrderUnassigned(
        int $userId,
        string $orderNumber,
        string $customerName
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_ORDER_UNASSIGNED,
            'title' => "Penugasan Dibatalkan",
            'message' => "Pesanan {$orderNumber} ({$customerName}) tidak lagi ditugaskan kepada Anda.",
            'data' => [
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
            ],
        ]);
    }
}
