<?php

namespace App\Services;

use App\Models\FarmProfile;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Local SEO Service
 *
 * Provides SEO metadata and data for location-based pages.
 */
class LocalSeoService
{
    /**
     * Location types for URL generation.
     */
    public const LOCATION_PROVINCE = 'province';
    public const LOCATION_REGENCY = 'regency';
    public const LOCATION_DISTRICT = 'district';
    public const LOCATION_VILLAGE = 'village';

    /**
     * Category types.
     */
    public const CATEGORY_LIVESTOCK = 'ternak';
    public const CATEGORY_MEAT = 'daging';
    public const CATEGORY_SUPPLY = 'pakan';

    /**
     * Generate SEO data for a province page.
     */
    public function getProvinceSeo(string $province): array
    {
        $provinceName = $this->formatLocationName($province);
        $farmCount = $this->countFarmsByProvince($province);
        $productCount = $this->countProductsByProvince($province);

        return [
            'title' => "Peternak & Ternak {$provinceName} | AbodaFarm",
            'description' => "Temukan {$farmCount} peternak dan {$productCount} produk ternak berkualitas di {$provinceName}. Beli sapi, kambing, dan ayam organik langsung dari peternak lokal.",
            'keywords' => "peternak {$provinceName}, ternak {$provinceName}, sapi {$provinceName}, kambing {$provinceName}, ayam {$provinceName}, AbodaFarm",
            'canonical' => $this->generateUrl(self::LOCATION_PROVINCE, $province),
            'type' => 'website',
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Peternak', 'url' => '/peternak'],
                ['name' => $provinceName],
            ],
        ];
    }

    /**
     * Generate SEO data for a regency page.
     */
    public function getRegencySeo(string $province, string $regency): array
    {
        $provinceName = $this->formatLocationName($province);
        $regencyName = $this->formatLocationName($regency);
        $farmCount = $this->countFarmsByRegency($regency);

        return [
            'title' => "Peternak {$regencyName}, {$provinceName} | AbodaFarm",
            'description' => "Temukan peternak terpercaya di {$regencyName}, {$provinceName}. {$farmCount} peternak dengan produk sapi, kambing, dan ayam berkualitas tinggi.",
            'keywords' => "peternak {$regencyName}, {$provinceName}, ternak berkualitas, AbodaFarm",
            'canonical' => $this->generateUrl(self::LOCATION_REGENCY, $regency, $province),
            'type' => 'website',
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Peternak', 'url' => '/peternak'],
                ['name' => $provinceName, 'url' => "/peternak/{$province}"],
                ['name' => $regencyName],
            ],
        ];
    }

    /**
     * Generate SEO data for a category-location page.
     */
    public function getCategoryLocationSeo(string $category, string $location): array
    {
        $categoryLabel = $this->getCategoryLabel($category);
        $locationName = $this->formatLocationName($location);
        $productCount = $this->countProductsByCategoryAndLocation($category, $location);

        return [
            'title' => "{$categoryLabel} {$locationName} | AbodaFarm",
            'description' => "Jual {$categoryLabel} berkualitas di {$locationName}. {$productCount} produk tersedia dengan harga terbaik dari peternak lokal terpercaya.",
            'keywords' => "{$categoryLabel} {$locationName}, beli {$categoryLabel}, peternak {$locationName}, AbodaFarm",
            'canonical' => $this->generateCategoryUrl($category, $location),
            'type' => 'website',
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => $categoryLabel, 'url' => "/{$category}"],
                ['name' => $locationName],
            ],
        ];
    }

    /**
     * Get nearby farmers for a location.
     */
    public function getNearbyFarmers(string $location, string $type = self::LOCATION_PROVINCE, int $limit = 10): Collection
    {
        return FarmProfile::query()
            ->with('user')
            ->where(function ($query) use ($location, $type) {
                match ($type) {
                    self::LOCATION_PROVINCE => $query->where('province', $location),
                    self::LOCATION_REGENCY => $query->where('regency', $location),
                    self::LOCATION_DISTRICT => $query->where('district', $location),
                    self::LOCATION_VILLAGE => $query->where('village', $location),
                    default => $query->where('province', $location),
                };
            })
            ->whereNotNull('image')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products by location.
     */
    public function getProductsByLocation(string $location, string $type = self::LOCATION_PROVINCE, int $limit = 12): Collection
    {
        $farmIds = FarmProfile::query()
            ->where(function ($query) use ($location, $type) {
                match ($type) {
                    self::LOCATION_PROVINCE => $query->where('province', $location),
                    self::LOCATION_REGENCY => $query->where('regency', $location),
                    self::LOCATION_DISTRICT => $query->where('district', $location),
                    self::LOCATION_VILLAGE => $query->where('village', $location),
                    default => $query->where('province', $location),
                };
            })
            ->pluck('user_id');

        return Product::query()
            ->with(['category', 'owner.farmProfile'])
            ->whereIn('owner_id', $farmIds)
            ->availableWithStatus()
            ->inStock()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular products by location.
     */
    public function getPopularProductsByLocation(string $location, string $type = self::LOCATION_PROVINCE, int $limit = 8): Collection
    {
        $farmIds = FarmProfile::query()
            ->where(function ($query) use ($location, $type) {
                match ($type) {
                    self::LOCATION_PROVINCE => $query->where('province', $location),
                    self::LOCATION_REGENCY => $query->where('regency', $location),
                    self::LOCATION_DISTRICT => $query->where('district', $location),
                    self::LOCATION_VILLAGE => $query->where('village', $location),
                    default => $query->where('province', $location),
                };
            })
            ->pluck('user_id');

        return Product::query()
            ->with(['category', 'owner.farmProfile'])
            ->whereIn('owner_id', $farmIds)
            ->availableWithStatus()
            ->inStock()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest products by location.
     */
    public function getLatestProductsByLocation(string $location, string $type = self::LOCATION_PROVINCE, int $limit = 8): Collection
    {
        $farmIds = FarmProfile::query()
            ->where(function ($query) use ($location, $type) {
                match ($type) {
                    self::LOCATION_PROVINCE => $query->where('province', $location),
                    self::LOCATION_REGENCY => $query->where('regency', $location),
                    self::LOCATION_DISTRICT => $query->where('district', $location),
                    self::LOCATION_VILLAGE => $query->where('village', $location),
                    default => $query->where('province', $location),
                };
            })
            ->pluck('user_id');

        return Product::query()
            ->with(['category', 'owner.farmProfile'])
            ->whereIn('owner_id', $farmIds)
            ->availableWithStatus()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate LocalBusiness schema for a location.
     */
    public function generateLocalBusinessSchema(string $location, string $type = self::LOCATION_PROVINCE): ?array
    {
        $farmers = $this->getNearbyFarmers($location, $type, 20);

        if ($farmers->isEmpty()) {
            return null;
        }

        $locationName = $this->formatLocationName($location);
        $avgRating = $farmers->avg('rating') ?? 0;
        $totalReviews = $farmers->sum('total_reviews');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => "Peternak & Ternak di {$locationName}",
            'description' => "Marketplace peternak dan produk ternak berkualitas di {$locationName}. {$farmers->count()} peternak terpercaya.",
            'url' => config('app.url') . $this->generateUrl($type, $location),
            'areaServed' => [
                '@type' => match ($type) {
                    self::LOCATION_PROVINCE => 'State',
                    self::LOCATION_REGENCY => 'AdministrativeArea',
                    self::LOCATION_DISTRICT => 'AdministrativeArea',
                    self::LOCATION_VILLAGE => 'AdministrativeArea',
                    default => 'Place',
                },
                'name' => $locationName,
            ],
        ];

        if ($type === self::LOCATION_REGENCY || $type === self::LOCATION_DISTRICT) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1),
                'reviewCount' => (string) $totalReviews,
            ];
        }

        return $schema;
    }

    /**
     * Generate FarmerBusiness schema for individual farmers.
     */
    public function generateFarmerSchema(FarmProfile $farm): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $farm->farm_name,
            'description' => $farm->description,
            'image' => $farm->image ? asset('storage/' . $farm->image) : null,
            'telephone' => $farm->phone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $farm->address,
                'addressLocality' => $farm->village,
                'addressRegion' => $farm->district,
                'addressCountry' => 'ID',
            ],
            'geo' => $farm->hasCoordinates() ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $farm->latitude,
                'longitude' => (float) $farm->longitude,
            ] : null,
            'aggregateRating' => $farm->rating ? [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $farm->rating,
                'reviewCount' => (string) ($farm->total_reviews ?? 0),
            ] : null,
            'url' => route('peternak.profile', ['username' => $farm->user?->username ?? $farm->user_id]),
        ];
    }

    /**
     * Generate related location URLs for internal linking.
     */
    public function getRelatedLocations(string $location, string $type = self::LOCATION_PROVINCE): array
    {
        $related = [];

        switch ($type) {
            case self::LOCATION_PROVINCE:
                // Get related provinces (neighboring or similar)
                $regencies = FarmProfile::where('province', $location)
                    ->whereNotNull('regency')
                    ->distinct()
                    ->pluck('regency')
                    ->take(5);

                foreach ($regencies as $regency) {
                    $related[] = [
                        'name' => $this->formatLocationName($regency),
                        'url' => $this->generateUrl(self::LOCATION_REGENCY, $regency, $location),
                        'type' => 'regency',
                    ];
                }
                break;

            case self::LOCATION_REGENCY:
                // Get related regencies in same province
                $province = FarmProfile::where('regency', $location)->value('province');
                if ($province) {
                    $otherRegencies = FarmProfile::where('province', $province)
                        ->where('regency', '!=', $location)
                        ->whereNotNull('regency')
                        ->distinct()
                        ->pluck('regency')
                        ->take(5);

                    foreach ($otherRegencies as $regency) {
                        $related[] = [
                            'name' => $this->formatLocationName($regency),
                            'url' => $this->generateUrl(self::LOCATION_REGENCY, $regency, $province),
                            'type' => 'regency',
                        ];
                    }
                }
                break;
        }

        return $related;
    }

    /**
     * Format location name for display.
     */
    public function formatLocationName(string $location): string
    {
        // Remove common prefixes
        $location = preg_replace('/^(kabupaten|kota|kecamatan|desa|provinsi)\s+/i', '', $location);

        // Capitalize first letter of each word
        return ucwords(strtolower($location));
    }

    /**
     * Generate URL for a location page.
     */
    public function generateUrl(string $type, string $location, ?string $parent = null): string
    {
        $slug = $this->slugify($location);

        return match ($type) {
            self::LOCATION_PROVINCE => "/peternak/{$slug}",
            self::LOCATION_REGENCY => "/peternak/{$this->slugify($parent)}/{$slug}",
            self::LOCATION_DISTRICT => "/peternak/{$this->slugify($parent)}/kecamatan/{$slug}",
            self::LOCATION_VILLAGE => "/peternak/{$this->slugify($parent)}/desa/{$slug}",
            default => "/peternak/{$slug}",
        };
    }

    /**
     * Generate URL for category-location page.
     */
    public function generateCategoryUrl(string $category, string $location): string
    {
        $slug = $this->slugify($location);

        return "/{$category}/{$slug}";
    }

    /**
     * Slugify a location name for URL.
     */
    protected function slugify(string $text): string
    {
        // Remove common prefixes
        $text = preg_replace('/^(kabupaten|kota|kecamatan|desa|provinsi)\s+/i', '', $text);

        // Replace spaces and special chars
        $text = preg_replace('/\s+/', '-', trim($text));
        $text = preg_replace('/[^a-z0-9-]/i', '', strtolower($text));

        return $text;
    }

    /**
     * Get category label.
     */
    protected function getCategoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_LIVESTOCK => 'Ternak',
            self::CATEGORY_MEAT => 'Daging Segar',
            self::CATEGORY_SUPPLY => 'Pakan & Perlengkapan',
            default => ucfirst($category),
        };
    }

    /**
     * Count farms by province.
     */
    protected function countFarmsByProvince(string $province): int
    {
        return FarmProfile::where('province', $province)->count();
    }

    /**
     * Count farms by regency.
     */
    protected function countFarmsByRegency(string $regency): int
    {
        return FarmProfile::where('regency', $regency)->count();
    }

    /**
     * Count products by province.
     */
    protected function countProductsByProvince(string $province): int
    {
        $farmIds = FarmProfile::where('province', $province)->pluck('user_id');
        return Product::whereIn('owner_id', $farmIds)->count();
    }

    /**
     * Count products by category and location.
     */
    protected function countProductsByCategoryAndLocation(string $category, string $location): int
    {
        $categorySlug = $this->slugify($location);

        return Product::query()
            ->whereHas('category', function ($q) use ($categorySlug) {
                match ($category) {
                    self::CATEGORY_LIVESTOCK => $q->whereIn('slug', ['sapi', 'kambing', 'ayam', 'bebek']),
                    self::CATEGORY_MEAT => $q->where('slug', 'like', '%daging%'),
                    default => $q,
                };
            })
            ->whereHas('owner.farmProfile', function ($q) use ($location) {
                $q->where('province', 'like', "%{$location}%")
                  ->orWhere('regency', 'like', "%{$location}%")
                  ->orWhere('district', 'like', "%{$location}%");
            })
            ->count();
    }
}
