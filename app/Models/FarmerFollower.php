<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerFollower extends Model
{
    protected $fillable = [
        'farmer_id',
        'follower_id',
        'notify_new_product',
        'notify_promotion',
    ];

    protected $casts = [
        'notify_new_product' => 'boolean',
        'notify_promotion' => 'boolean',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function scopeFollowing($query, int $farmerId)
    {
        return $query->where('farmer_id', $farmerId);
    }

    public function scopeFollowedBy($query, int $followerId)
    {
        return $query->where('follower_id', $followerId);
    }

    public static function isFollowing(int $farmerId, int $followerId): bool
    {
        return self::where('farmer_id', $farmerId)
            ->where('follower_id', $followerId)
            ->exists();
    }

    public static function toggle(int $farmerId, int $followerId): bool
    {
        $existing = self::where('farmer_id', $farmerId)
            ->where('follower_id', $followerId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        self::create([
            'farmer_id' => $farmerId,
            'follower_id' => $followerId,
        ]);
        return true;
    }
}
