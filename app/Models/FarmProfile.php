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
        // Trust & Reputation
        'trust_score',
        'response_rate',
        'response_time_hours',
        'cancellation_rate',
        // Statistics
        'products_sold',
        'livestock_sold',
        'total_revenue',
        'completed_bookings',
        'completed_orders',
        'repeat_customers',
        // Badges
        'badge_top_seller',
        'badge_fast_response',
        'badge_trusted_farmer',
        'badge_organic_certified',
        'badge_excellent_rating',
        // Followers
        'followers_count',
        'last_response_at',
        'badges_updated_at',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
        'total_reviews' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        // Trust fields
        'trust_score' => 'decimal:2',
        'response_rate' => 'decimal:2',
        'response_time_hours' => 'integer',
        'cancellation_rate' => 'decimal:2',
        'products_sold' => 'integer',
        'livestock_sold' => 'integer',
        'total_revenue' => 'decimal:2',
        'completed_bookings' => 'integer',
        'completed_orders' => 'integer',
        'repeat_customers' => 'integer',
        // Badge fields
        'badge_top_seller' => 'boolean',
        'badge_fast_response' => 'boolean',
        'badge_trusted_farmer' => 'boolean',
        'badge_organic_certified' => 'boolean',
        'badge_excellent_rating' => 'boolean',
        // Followers
        'followers_count' => 'integer',
        'last_response_at' => 'datetime',
        'badges_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(FarmGallery::class)->orderBy('sort_order');
    }

    public function badges(): HasMany
    {
        return $this->hasMany(FarmerBadge::class, 'user_id', 'user_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(FarmerFollower::class, 'farmer_id', 'user_id');
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

    // =========================================================================
    // TRUST & REPUTATION HELPERS
    // =========================================================================

    /**
     * Get trust score label.
     */
    public function getTrustScoreLabelAttribute(): string
    {
        $score = $this->trust_score ?? 50;
        if ($score >= 90) return 'Sangat Baik';
        if ($score >= 75) return 'Baik';
        if ($score >= 50) return 'Cukup';
        if ($score >= 25) return 'Kurang';
        return 'Buruk';
    }

    /**
     * Get trust score color.
     */
    public function getTrustScoreColorAttribute(): string
    {
        $score = $this->trust_score ?? 50;
        if ($score >= 90) return 'emerald';
        if ($score >= 75) return 'blue';
        if ($score >= 50) return 'amber';
        return 'red';
    }

    /**
     * Get all active badges.
     */
    public function getActiveBadgesAttribute(): array
    {
        $badges = [];

        if ($this->is_verified) {
            $badges[] = [
                'type' => 'verified',
                'name' => 'Terverifikasi',
                'icon' => 'fa-solid fa-circle-check',
                'color' => '#3B82F6',
            ];
        }

        if ($this->badge_top_seller) {
            $badges[] = [
                'type' => 'top_seller',
                'name' => 'Top Seller',
                'icon' => 'fa-solid fa-trophy',
                'color' => '#F59E0B',
            ];
        }

        if ($this->badge_fast_response) {
            $badges[] = [
                'type' => 'fast_response',
                'name' => 'Respon Cepat',
                'icon' => 'fa-solid fa-bolt',
                'color' => '#10B981',
            ];
        }

        if ($this->badge_trusted_farmer) {
            $badges[] = [
                'type' => 'trusted_farmer',
                'name' => 'Peternak Terpercaya',
                'icon' => 'fa-solid fa-shield-check',
                'color' => '#6366F1',
            ];
        }

        if ($this->badge_organic_certified) {
            $badges[] = [
                'type' => 'organic_certified',
                'name' => 'Sertifikat Organik',
                'icon' => 'fa-solid fa-leaf',
                'color' => '#22C55E',
            ];
        }

        if ($this->badge_excellent_rating) {
            $badges[] = [
                'type' => 'excellent_rating',
                'name' => 'Rating Excellent',
                'icon' => 'fa-solid fa-star',
                'color' => '#EAB308',
            ];
        }

        return $badges;
    }

    /**
     * Check if farmer has specific badge.
     */
    public function hasBadge(string $type): bool
    {
        return match($type) {
            'verified' => $this->is_verified,
            'top_seller' => $this->badge_top_seller,
            'fast_response' => $this->badge_fast_response,
            'trusted_farmer' => $this->badge_trusted_farmer,
            'organic_certified' => $this->badge_organic_certified,
            'excellent_rating' => $this->badge_excellent_rating,
            default => false,
        };
    }

    /**
     * Get formatted response time.
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        $hours = $this->response_time_hours ?? 0;
        if ($hours === 0) return 'Baru';
        if ($hours < 1) return '< 1 jam';
        if ($hours < 24) return "{$hours} jam";
        $days = floor($hours / 24);
        return "{$days} hari";
    }

    /**
     * Get formatted revenue.
     */
    public function getFormattedRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_revenue ?? 0, 0, ',', '.');
    }

    public function getStatsAttribute(): array
    {
        $user = $this->user;

        return [
            // Products
            'active_products' => $user?->products()->count() ?? 0,
            'total_products' => $user?->products()->count() ?? 0,
            'products_sold' => $this->products_sold ?? 0,
            'livestock_sold' => $this->livestock_sold ?? 0,
            // Orders & Bookings
            'completed_bookings' => $this->completed_bookings ?? 0,
            'completed_orders' => $this->completed_orders ?? 0,
            'total_bookings' => $user?->orders()
                ->where('order_type', 'inquiry')
                ->count() ?? 0,
            'cancellation_rate' => $this->cancellation_rate ?? 0,
            // Reviews & Rating
            'rating' => $this->rating ?? 0,
            'total_reviews' => $this->total_reviews ?? 0,
            // Trust
            'trust_score' => $this->trust_score ?? 50,
            'trust_score_label' => $this->trust_score_label,
            'response_rate' => $this->response_rate ?? 0,
            'response_time' => $this->formatted_response_time,
            // Revenue
            'total_revenue' => $this->total_revenue ?? 0,
            'formatted_revenue' => $this->formatted_revenue,
            // Customers
            'repeat_customers' => $this->repeat_customers ?? 0,
            'followers_count' => $this->followers_count ?? 0,
            // Badges
            'badges' => $this->active_badges,
            // Other
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
