<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'session_id',
        'ip_address',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Track a product view.
     */
    public static function track(int $productId): void
    {
        $userId = auth()->check() ? auth()->id() : null;
        $sessionId = session()->getId();
        $ipAddress = request()->ip();

        // Remove any existing view of this product for this user/session
        if ($userId) {
            static::where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();
        } else {
            static::where('session_id', $sessionId)
                ->where('product_id', $productId)
                ->delete();
        }

        // Add new view
        static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'viewed_at' => now(),
        ]);

        // Keep only last 20 views per user/session
        $limit = 20;

        if ($userId) {
            $oldViews = static::where('user_id', $userId)
                ->orderBy('viewed_at', 'desc')
                ->skip($limit)
                ->take(10)
                ->pluck('id');

            if ($oldViews->isNotEmpty()) {
                static::whereIn('id', $oldViews)->delete();
            }
        } else {
            $oldViews = static::where('session_id', $sessionId)
                ->orderBy('viewed_at', 'desc')
                ->skip($limit)
                ->take(10)
                ->pluck('id');

            if ($oldViews->isNotEmpty()) {
                static::whereIn('id', $oldViews)->delete();
            }
        }
    }

    /**
     * Get recently viewed products for a user (or guest by session).
     */
    public static function getRecentProducts(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $userId = auth()->check() ? auth()->id() : null;
        $sessionId = session()->getId();

        $query = static::with(['product.category'])
            ->whereHas('product', fn($q) => $q->whereNotNull('name'))
            ->orderBy('viewed_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Scope for user's views.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for guest's session.
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)->whereNull('user_id');
    }
}
