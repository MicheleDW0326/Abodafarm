<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\FarmProfile;
use App\Models\Article;
use Illuminate\Support\Facades\URL;

/**
 * Sitemap Service
 *
 * Generates XML sitemap for search engines.
 */
class SitemapService
{
    /**
     * Base URL for the sitemap.
     */
    protected string $baseUrl;

    /**
     * All sitemap items.
     */
    protected array $items = [];

    /**
     * URLs to exclude.
     */
    protected array $excludedPatterns = [
        // Authentication
        'login',
        'register',
        'password/*',
        'forgot-password',
        'reset-password',

        // Dashboard/Admin
        'admin/*',
        'staff/*',
        'peternak/*',
        'pelanggan/*',
        'dashboard',
        'settings/*',

        // Checkout
        'checkout/*',
        'checkout',

        // API
        'api/*',
        'sanctum/*',

        // Authenticated routes
        'logout',
        'verify-email',
        'email/*',
    ];

    /**
     * Priority levels.
     */
    protected array $priorities = [
        'home' => '1.0',
        'shop' => '0.9',
        'product' => '0.8',
        'category' => '0.8',
        'farmer' => '0.7',
        'static' => '0.7',
        'article' => '0.6',
        'location' => '0.6',
    ];

    /**
     * Change frequencies.
     */
    protected array $changeFrequencies = [
        'home' => 'daily',
        'shop' => 'daily',
        'product' => 'weekly',
        'category' => 'weekly',
        'farmer' => 'weekly',
        'static' => 'monthly',
        'article' => 'weekly',
        'location' => 'weekly',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url', 'https://abodafarm.test'), '/');
    }

    /**
     * Generate the full sitemap.
     */
    public function generate(): string
    {
        $this->items = [];

        // Add static pages
        $this->addStaticPages();

        // Add categories
        $this->addCategories();

        // Add products
        $this->addProducts();

        // Add farm profiles
        $this->addFarmProfiles();

        // Add articles (if exists)
        $this->addArticles();

        // Add location pages
        $this->addLocationPages();

        return $this->buildXml();
    }

    /**
     * Add static pages.
     */
    protected function addStaticPages(): void
    {
        $staticPages = [
            '/' => ['priority' => '1.0', 'changefreq' => 'daily'],
            '/shop' => ['priority' => '0.9', 'changefreq' => 'daily'],
            '/tentang-kami' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            '/kontak' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            '/galeri' => ['priority' => '0.6', 'changefreq' => 'weekly'],
        ];

        foreach ($staticPages as $path => $data) {
            $this->addItem($path, $data['priority'], $data['changefreq'], now());
        }
    }

    /**
     * Add categories.
     */
    protected function addCategories(): void
    {
        $categories = Category::active()
            ->where('is_active', true)
            ->get();

        foreach ($categories as $category) {
            $slug = $category->slug ?? str()->slug($category->name);
            $this->addItem(
                "/shop?category={$slug}",
                '0.8',
                'weekly',
                $category->updated_at ?? now()
            );
        }
    }

    /**
     * Add products.
     */
    protected function addProducts(): void
    {
        $products = Product::availableWithStatus()
            ->select(['slug', 'updated_at', 'created_at'])
            ->get();

        foreach ($products as $product) {
            if ($product->slug) {
                $this->addItem(
                    "/shop/{$product->slug}",
                    '0.8',
                    'weekly',
                    $product->updated_at ?? $product->created_at ?? now()
                );
            }
        }
    }

    /**
     * Add farm profiles.
     */
    protected function addFarmProfiles(): void
    {
        $profiles = FarmProfile::whereNotNull('user_id')
            ->select(['user_id', 'updated_at'])
            ->get();

        foreach ($profiles as $profile) {
            $this->addItem(
                "/peternak/{$profile->user_id}",
                '0.7',
                'weekly',
                $profile->updated_at ?? now()
            );
        }
    }

    /**
     * Add articles.
     */
    protected function addArticles(): void
    {
        // Check if Article model exists
        if (!class_exists(\App\Models\Article::class)) {
            return;
        }

        try {
            $articles = \App\Models\Article::published()
                ->select(['slug', 'updated_at', 'published_at'])
                ->get();

            foreach ($articles as $article) {
                if ($article->slug) {
                    $this->addItem(
                        "/artikel/{$article->slug}",
                        '0.6',
                        'weekly',
                        $article->published_at ?? $article->updated_at ?? now()
                    );
                }
            }
        } catch (\Exception $e) {
            // Article model or table doesn't exist
        }
    }

    /**
     * Add location pages.
     */
    protected function addLocationPages(): void
    {
        // Get unique provinces from farm profiles
        $provinces = FarmProfile::whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->pluck('province');

        foreach ($provinces as $province) {
            $slug = str()->slug($province);
            $this->addItem(
                "/peternak/{$slug}",
                '0.6',
                'weekly',
                now()
            );

            // Add regencies
            $regencies = FarmProfile::where('province', $province)
                ->whereNotNull('regency')
                ->where('regency', '!=', '')
                ->distinct()
                ->pluck('regency');

            foreach ($regencies as $regency) {
                $regencySlug = str()->slug($regency);
                $this->addItem(
                    "/peternak/{$slug}/{$regencySlug}",
                    '0.5',
                    'weekly',
                    now()
                );
            }
        }

        // Add category location pages
        $locationCategories = ['ternak', 'daging', 'pakan'];
        foreach ($locationCategories as $category) {
            $this->addItem(
                "/{$category}",
                '0.6',
                'weekly',
                now()
            );
        }
    }

    /**
     * Add item to sitemap.
     */
    public function addItem(string $loc, string $priority = '0.5', string $changefreq = 'weekly', $lastmod = null): void
    {
        // Skip if matches excluded pattern
        if ($this->isExcluded($loc)) {
            return;
        }

        // Skip if already added (no duplicates)
        $normalizedLoc = $this->normalizeLoc($loc);
        if (isset($this->items[$normalizedLoc])) {
            return;
        }

        $this->items[$normalizedLoc] = [
            'loc' => $this->baseUrl . $loc,
            'lastmod' => $lastmod ? $this->formatDate($lastmod) : null,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * Check if URL matches excluded pattern.
     */
    protected function isExcluded(string $loc): bool
    {
        $loc = ltrim($loc, '/');

        foreach ($this->excludedPatterns as $pattern) {
            $pattern = rtrim($pattern, '*');
            if (str_starts_with($loc, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize URL for deduplication.
     */
    protected function normalizeLoc(string $loc): string
    {
        // Remove trailing slash except for root
        if ($loc !== '/' && str_ends_with($loc, '/')) {
            $loc = rtrim($loc, '/');
        }

        return $loc;
    }

    /**
     * Format date for sitemap.
     */
    protected function formatDate($date): string
    {
        if ($date instanceof \Carbon\Carbon) {
            return $date->toIso8601String();
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('c');
        }

        return now()->format('c');
    }

    /**
     * Build XML.
     */
    protected function buildXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->items as $item) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($item['loc']) . '</loc>' . "\n";

            if ($item['lastmod']) {
                $xml .= '    <lastmod>' . $item['lastmod'] . '</lastmod>' . "\n";
            }

            $xml .= '    <changefreq>' . $item['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $item['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Get items count.
     */
    public function getItemsCount(): int
    {
        return count($this->items);
    }

    /**
     * Get items array.
     */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    /**
     * Generate sitemap index for multiple sitemaps.
     */
    public function generateIndex(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($sitemap['loc']) . '</loc>' . "\n";

            if (isset($sitemap['lastmod'])) {
                $xml .= '    <lastmod>' . $sitemap['lastmod'] . '</lastmod>' . "\n";
            }

            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Get the base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
