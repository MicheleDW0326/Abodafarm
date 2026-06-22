<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeternakNotification extends Model
{
    use HasFactory;

    protected $table = 'peternak_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'new_order' => 'fa-solid fa-shopping-cart text-blue-500',
            'payment_verified' => 'fa-solid fa-check-circle text-green-500',
            'livestock_sold' => 'fa-solid fa-hand-holding-dollar text-emerald-500',
            'review' => 'fa-solid fa-star text-yellow-500',
            default => 'fa-solid fa-bell text-gray-500',
        };
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
