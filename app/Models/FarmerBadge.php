<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_type',
        'name',
        'description',
        'icon',
        'color',
        'is_active',
        'earned_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'earned_at' => 'datetime',
    ];

    public const TYPE_VERIFIED = 'verified';
    public const TYPE_TOP_SELLER = 'top_seller';
    public const TYPE_FAST_RESPONSE = 'fast_response';
    public const TYPE_TRUSTED_FARMER = 'trusted_farmer';
    public const TYPE_ORGANIC_CERTIFIED = 'organic_certified';
    public const TYPE_EXCELLENT_RATING = 'excellent_rating';

    public static array $badgeTypes = [
        self::TYPE_VERIFIED => [
            'name' => 'Terverifikasi',
            'description' => 'Peternak yang telah diverifikasi oleh AbodaFarm',
            'icon' => 'fa-solid fa-circle-check',
            'color' => '#3B82F6',
        ],
        self::TYPE_TOP_SELLER => [
            'name' => 'Top Seller',
            'description' => 'Peternak dengan penjualan terbaik',
            'icon' => 'fa-solid fa-trophy',
            'color' => '#F59E0B',
        ],
        self::TYPE_FAST_RESPONSE => [
            'name' => 'Respon Cepat',
            'description' => 'Peternak dengan waktu respons kurang dari 1 jam',
            'icon' => 'fa-solid fa-bolt',
            'color' => '#10B981',
        ],
        self::TYPE_TRUSTED_FARMER => [
            'name' => 'Peternak Terpercaya',
            'description' => 'Peternak dengan trust score di atas 90',
            'icon' => 'fa-solid fa-shield-check',
            'color' => '#6366F1',
        ],
        self::TYPE_ORGANIC_CERTIFIED => [
            'name' => 'Sertifikat Organik',
            'description' => 'Peternak dengan sertifikasi organik',
            'icon' => 'fa-solid fa-leaf',
            'color' => '#22C55E',
        ],
        self::TYPE_EXCELLENT_RATING => [
            'name' => 'Rating Excellent',
            'description' => 'Peternak dengan rating rata-rata di atas 4.8',
            'icon' => 'fa-solid fa-star',
            'color' => '#EAB308',
        ],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getBadgeInfo(string $type): array
    {
        return self::$badgeTypes[$type] ?? [
            'name' => ucfirst(str_replace('_', ' ', $type)),
            'description' => '',
            'icon' => 'fa-solid fa-medal',
            'color' => '#6B7280',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('badge_type', $type);
    }
}
