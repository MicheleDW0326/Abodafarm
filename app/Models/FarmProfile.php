<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'description',
        'address',
        'phone',
        'village',
        'district',
        'regency',
        'province',
        'image',
        'rating',
        'total_reviews',
        'is_verified',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
        'total_reviews' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->village,
            $this->district,
            $this->regency,
            $this->province,
        ]);

        return implode(', ', $parts);
    }

    public function getStarRatingAttribute(): string
    {
        $fullStars = floor($this->rating);
        $hasHalfStar = $this->rating - $fullStars >= 0.5;

        return str_repeat('⭐', $fullStars) . ($hasHalfStar ? '⭐' : '');
    }
}
