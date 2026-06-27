<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWeb($query)
    {
        return $query->where('device_type', 'web');
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public static function updateOrCreateToken(int $userId, string $token, string $deviceType = 'web', ?string $deviceName = null): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'token' => $token,
            ],
            [
                'device_type' => $deviceType,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }
}
