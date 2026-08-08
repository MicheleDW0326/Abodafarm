<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLike extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isLiked(int $reviewId, int $userId): bool
    {
        return self::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->exists();
    }

    public static function toggle(int $reviewId, int $userId): bool
    {
        $existing = self::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        self::create([
            'review_id' => $reviewId,
            'user_id' => $userId,
        ]);
        return true;
    }

    public static function getLikeCount(int $reviewId): int
    {
        return self::where('review_id', $reviewId)->count();
    }
}
