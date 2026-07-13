<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'cover_image',
        'logo',
        'rating',
        'total_reviews',
        'is_verified',
        'google_place_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
        'total_reviews' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(FarmGallery::class)->orderBy('sort_order');
    }

    public function getProfileCompletionAttribute(): int
    {
        $score = 0;
        $total = 14;

        // Basic info (6 points)
        if ($this->farm_name) $score += 1;
        if ($this->description) $score += 1;
        if ($this->phone) $score += 1;
        if ($this->address) $score += 1;
        if ($this->village && $this->district) $score += 1;
        if ($this->province) $score += 1;

        // Images (5 points)
        if ($this->image) $score += 2;
        if ($this->cover_image) $score += 1;
        if ($this->logo) $score += 1;
        if ($this->galleries()->count() > 0) $score += 1;

        // Location (3 points)
        if ($this->latitude && $this->longitude) $score += 2;
        if ($this->google_place_id) $score += 1;

        return (int) round(($score / $total) * 100);
    }

    public function getMissingFieldsAttribute(): array
    {
        $missing = [];

        if (!$this->farm_name) $missing[] = 'Nama peternakan';
        if (!$this->description) $missing[] = 'Deskripsi';
        if (!$this->phone) $missing[] = 'Nomor telepon';
        if (!$this->address) $missing[] = 'Alamat lengkap';
        if (!$this->village || !$this->district) $missing[] = 'Desa/Kecamatan';
        if (!$this->province) $missing[] = 'Provinsi';
        if (!$this->image) $missing[] = 'Foto profil';
        if (!$this->cover_image) $missing[] = 'Cover foto';
        if (!$this->logo) $missing[] = 'Logo';
        if ($this->galleries()->count() === 0) $missing[] = 'Galeri foto';
        if (!$this->latitude || !$this->longitude) $missing[] = 'Lokasi di peta';
        if (!$this->google_place_id) $missing[] = 'Alamat Google Maps';

        return $missing;
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

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function hasCoverImage(): bool
    {
        return !empty($this->cover_image);
    }

    public function hasLogo(): bool
    {
        return !empty($this->logo);
    }

    public function hasMainImage(): bool
    {
        return !empty($this->image);
    }

    public function getCoordinatesArray(): ?array
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    public function getMapUrl(): ?string
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function toggleVerified(): void
    {
        $this->update(['is_verified' => !$this->is_verified]);
    }

    public function getStatsAttribute(): array
    {
        $user = $this->user;

        return [
            // Note: Removed is_active filter as products table doesn't have this column
            'active_products' => $user?->products()->count() ?? 0,
            'total_products' => $user?->products()->count() ?? 0,
            'completed_bookings' => $user?->orders()
                ->whereIn('status', ['completed', 'delivered'])
                ->where('order_type', 'inquiry')
                ->count() ?? 0,
            'total_bookings' => $user?->orders()
                ->where('order_type', 'inquiry')
                ->count() ?? 0,
            'rating' => $this->rating ?? 0,
            'total_reviews' => $this->total_reviews ?? 0,
            'galleries_count' => $this->galleries()->count(),
        ];
    }

    public function getPublicProfileDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'farm_name' => $this->farm_name,
            'description' => $this->description,
            'phone' => $this->phone,
            'location' => $this->full_address,
            'is_verified' => $this->is_verified,
            'rating' => $this->rating,
            'total_reviews' => $this->total_reviews,
            'stats' => $this->stats,
            'images' => [
                'cover' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
                'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
                'main' => $this->image ? asset('storage/' . $this->image) : null,
            ],
            'galleries' => $this->galleries()->limit(6)->get()->map(fn($g) => [
                'id' => $g->id,
                'url' => asset('storage/' . $g->image),
            ])->toArray(),
            'map_url' => $this->map_url,
            'profile_completion' => $this->profile_completion,
        ];
    }
}
