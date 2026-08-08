<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLevel extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'color',
        'min_points',
        'max_points',
        'discount_percent',
        'free_shipping_threshold',
        'priority_support',
        'sort_order',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
        'discount_percent' => 'float',
        'free_shipping_threshold' => 'integer',
        'priority_support' => 'integer',
        'sort_order' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function getLevelForPoints(int $points): ?CustomerLevel
    {
        return self::where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->whereNull('max_points')
                    ->orWhere('max_points', '>=', $points);
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }

    public function getNextLevelAttribute(): ?CustomerLevel
    {
        return self::where('min_points', '>', $this->min_points)
            ->orderBy('min_points')
            ->first();
    }

    public function getProgressToNextLevelAttribute(): ?float
    {
        $nextLevel = $this->nextLevel;
        if (!$nextLevel) {
            return null;
        }

        $range = $nextLevel->min_points - $this->min_points;
        $current = $this->max_points ? $range : null;

        if ($current === null) {
            return null;
        }

        return min(100, (($this->max_points ?? 0) / $range) * 100);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
