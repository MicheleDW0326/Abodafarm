<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    /**
     * Get the parent favoritable model.
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a product is favorited by a user.
     */
    public static function isFavorited(int $userId, string $type, int $id): bool
    {
        return static::where('user_id', $userId)
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->exists();
    }

    /**
     * Toggle favorite status.
     */
    public static function toggle(int $userId, string $type, int $id): bool
    {
        $exists = static::where('user_id', $userId)
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->exists();

        if ($exists) {
            static::where('user_id', $userId)
                ->where('favoritable_type', $type)
                ->where('favoritable_id', $id)
                ->delete();
            return false;
        }

        static::create([
            'user_id' => $userId,
            'favoritable_type' => $type,
            'favoritable_id' => $id,
        ]);
        return true;
    }

    /**
     * Scope for user's favorites.
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
        return $query->where('favoritable_type', $type);
    }
}
