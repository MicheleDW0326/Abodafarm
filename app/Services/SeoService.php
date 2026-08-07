<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\FarmProfile;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * SEO Service for generating meta tags and SEO metadata.
 *
 * This service provides a unified way to generate SEO metadata
 * for pages across the application.
 *
 * Dynamic SEO Methods:
 * - forHomepage(): Homepage SEO
 * - forCategory(): Category page SEO
 * - forProduct(): Product page SEO
 * - forFarmer(): Farmer profile SEO
 * - forShop(): Shop/search page SEO
 * - forLocation(): Location page SEO
 */
class SeoService
{
    /**
     * Current SEO data being built.
     */
    protected array $data = [];

    /**
     * Schemas to include.
     */
    protected array $schemas = ['organization', 'website'];

    /**
     * Breadcrumbs for breadcrumb schema.
     */
    protected ?array $breadcrumbs = null;

    /**
     * Product data for product schema.
     */
    protected ?array $productData = null;

    /**
     * Create a new SEO Service instance.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Reset SEO data to defaults.
     */
    public function reset(): self
    {
        $this->data = [
            'title' => config('seo.site_name'),
            'description' => config('seo.site_description'),
            'keywords' => config('seo.site_keywords'),
            'canonical' => $this->getCanonicalUrl(),
            'image' => config('seo.default_image'),
            'type' => config('seo.og_type'),
            'locale' => config('seo.og_locale'),
            'locale_alternate' => config('seo.og_locale_alternate'),
            'robots' => config('seo.default_robots'),
            'author' => config('seo.author'),
            'author_url' => config('seo.author_url'),
            'twitter_card' => config('seo.twitter_card_type'),
            'site_name' => config('seo.site_name'),
            'twitter_account' => config('seo.twitter_account'),
            'theme_color' => config('seo.theme_color'),
        ];

        $this->schemas = ['organization', 'website'];
        $this->breadcrumbs = null;
        $this->productData = null;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic SEO Generation Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Generate SEO data for the homepage.
     */
    public function forHomepage(): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');

        $this->data['title'] = "{$siteName} - Organic Livestock Marketplace";
        $this->data['description'] = config('seo.site_description', 'AbodaFarm - Peternakan organik terpercaya menyediakan sapi, kambing, dan ayam berkualitas.');
        $this->data['keywords'] = $this->generateKeywords(['peternakan organik', 'pasarternak', 'sapi', 'kambing', 'ayam', 'Indonesia']);
        $this->data['canonical'] = $this->baseUrl();
        $this->data['type'] = 'website';

        return $this->toArray();
    }

    /**
     * Generate SEO data for a category page.
     */
    public function forCategory(Category $category, int $productCount = 0): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');
        $categoryName = $category->name ?? 'Kategori';
        $categorySlug = $category->slug ?? Str::slug($categoryName);

        $this->data['title'] = "{$categoryName} - {$siteName}";

        $description = $category->description
            ?? "Jual {$categoryName} berkualitas tinggi. Tersedia {$productCount} produk {$categoryName} dengan harga terbaik di {$siteName}.";
        $this->data['description'] = $this->truncate($description, 150);

        $this->data['keywords'] = $this->generateKeywords([
            $categoryName,
            'jual ' . Str::lower($categoryName),
            'harga ' . Str::lower($categoryName),
            'peternakan',
        ]);

        $this->data['image'] = $category->image_url ?? $this->data['image'];
        $this->data['canonical'] = $this->baseUrl() . "/shop?category={$categorySlug}";
        $this->data['type'] = 'website';

        $this->schemas = ['organization', 'website'];
        $this->breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Toko', 'url' => '/shop'],
            ['name' => $categoryName],
        ];

        return $this->toArray();
    }

    /**
     * Generate SEO data for a product page.
     */
    public function forProduct(Product $product): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');

        // Generate product title: "{Product Name} | Rp {Price} | AbodaFarm"
        $price = $product->formatted_current_price ?? 'Rp ' . number_format($product->current_price ?? $product->price, 0, ',', '.');
        $this->data['title'] = "{$product->name} | {$price} | {$siteName}";

        // Generate description from product data
        $this->data['description'] = $this->generateProductDescription($product);

        // Generate keywords: product name, category, farm, location
        $keywords = [$product->name];
        if ($product->category?->name) {
            $keywords[] = $product->category->name;
        }
        if ($product->owner?->farmProfile?->farm_name) {
            $keywords[] = $product->owner->farmProfile->farm_name;
        }
        if ($product->origin) {
            $keywords[] = $product->origin;
        }
        $this->data['keywords'] = $this->generateKeywords($keywords);

        // Set image
        $this->data['image'] = $product->image_url ?? $this->data['image'];
        $this->data['canonical'] = $this->baseUrl() . "/shop/{$product->slug}";
        $this->data['type'] = 'product';

        // Set schemas
        $this->schemas = ['organization', 'website', 'product'];

        // Generate product schema data
        $this->productData = $this->generateProductSchemaData($product);

        // Set breadcrumbs
        $categorySlug = $product->category?->slug ?? '';
        $this->breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Toko', 'url' => '/shop'],
            ['name' => $product->category?->name ?? 'Produk', 'url' => $categorySlug ? "/shop?category={$categorySlug}" : '/shop'],
            ['name' => $product->name],
        ];

        return $this->toArray();
    }

    /**
     * Generate SEO data for a farmer profile page.
     */
    public function forFarmer(FarmProfile $farm, int $productCount = 0): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');
        $farmName = $farm->farm_name ?? 'Peternakan';
        $farmSlug = Str::slug($farmName);
        $userId = $farm->user_id ?? $farm->user?->id ?? 'unknown';

        // Generate title: "{Farm Name} - Organic Farm | AbodaFarm"
        $this->data['title'] = "{$farmName} - Organic Farm | {$siteName}";

        // Generate description
        $description = $farm->description
            ?? "Kunjungi profil {$farmName}. Peternakan organik terpercaya dengan {$productCount} produk berkualitas di {$farm->full_address}.";
        $this->data['description'] = $this->truncate($description, 150);

        // Generate keywords
        $keywords = [$farmName, 'peternakan organik', 'peternak terpercaya'];
        if ($farm->province) {
            $keywords[] = $farm->province;
        }
        if ($farm->regency) {
            $keywords[] = $farm->regency;
        }
        if ($farm->district) {
            $keywords[] = $farm->district;
        }
        $this->data['keywords'] = $this->generateKeywords($keywords);

        // Set image
        $image = $farm->cover_image
            ? asset('storage/' . $farm->cover_image)
            : ($farm->image ? asset('storage/' . $farm->image) : $this->data['image']);
        $this->data['image'] = $image;
        $this->data['canonical'] = $this->baseUrl() . "/peternak/{$userId}";
        $this->data['type'] = 'website';

        // Set schemas
        $this->schemas = ['organization', 'website', 'localbusiness'];

        // Set breadcrumbs
        $this->breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Peternak', 'url' => '/peternak'],
            ['name' => $farmName],
        ];

        return $this->toArray();
    }

    /**
     * Generate SEO data for shop/search page.
     */
    public function forShop(?string $search = null, ?Category $category = null): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');

        if ($search) {
            $this->data['title'] = "Hasil Pencarian '{$search}' - {$siteName}";
            $this->data['description'] = "Cari produk terkait '{$search}'. Temukan berbagai pakan dan perlengkapan ternak berkualitas di {$siteName}.";
            $this->data['keywords'] = $this->generateKeywords(['pakanternak', 'perlengkapanternak', $search]);
        } elseif ($category) {
            $this->data['title'] = "{$category->name} - {$siteName}";
            $this->data['description'] = $category->description ?? "{$category->name} berkualitas dengan harga terbaik.";
            $this->data['keywords'] = $this->generateKeywords([$category->name, 'tokopeternakan']);
            $this->data['image'] = $category->image_url ?? $this->data['image'];
        } else {
            $this->data['title'] = "Toko Pakan & Perlengkapan - {$siteName}";
            $this->data['description'] = "Toko online untuk pakan dan perlengkapan peternakan. Dapatkan produk berkualitas dengan harga terbaik.";
            $this->data['keywords'] = $this->generateKeywords(['tokopeternakan', 'pakanternak', 'perlengkapanternak']);
        }

        $this->data['canonical'] = $this->buildShopUrl($search, $category);
        $this->data['type'] = 'website';

        $this->schemas = ['organization', 'website'];
        $this->breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Toko'],
        ];

        if ($category) {
            $this->breadcrumbs[1] = ['name' => 'Toko', 'url' => '/shop'];
            $this->breadcrumbs[] = ['name' => $category->name];
        }

        if ($search) {
            $this->breadcrumbs[] = ['name' => "Pencarian: {$search}"];
        }

        return $this->toArray();
    }

    /**
     * Generate SEO data for a location page.
     */
    public function forLocation(string $location, string $type = 'province', array $data = []): array
    {
        $this->reset();
        $siteName = config('seo.site_name', 'AbodaFarm');
        $province = $data['province'] ?? $location;
        $farmers = $data['farmers'] ?? 0;
        $products = $data['products'] ?? 0;

        // Title: "Peternak & Ternak di {Location} | AbodaFarm"
        $this->data['title'] = "Peternak & Ternak di {$location} | {$siteName}";

        // Description
        $this->data['description'] = "Temukan {$farmers} peternak terpercaya dengan {$products} produk ternak berkualitas di {$location}.";
        $this->data['description'] = $this->truncate($this->data['description'], 150);

        // Keywords
        $this->data['keywords'] = $this->generateKeywords([
            'peternak ' . $location,
            'ternak ' . $location,
            'jualternak ' . $location,
            $province,
            'peternakan organik',
        ]);

        $this->data['canonical'] = $this->buildLocationUrl($province, $type);
        $this->data['type'] = 'website';

        $this->schemas = ['organization', 'website', 'localbusiness'];
        $this->breadcrumbs = $this->buildLocationBreadcrumbs($province, $location, $type);

        return $this->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Generate product description from product data.
     */
    protected function generateProductDescription(Product $product): string
    {
        $parts = [];

        // Base description
        $description = $product->description ?? $product->excerpt ?? '';
        if ($description) {
            $parts[] = $description;
        }

        // Add category
        if ($product->category) {
            $parts[] = $product->category->name;
        }

        // Add weight if available
        if ($product->weight) {
            $parts[] = $product->weight . ' kg';
        }

        // Add origin if available
        if ($product->origin) {
            $parts[] = 'dari ' . $product->origin;
        }

        // Build description
        $description = implode(' - ', array_filter($parts));

        return $this->truncate($description, 150);
    }

    /**
     * Generate product schema data for JSON-LD.
     */
    protected function generateProductSchemaData(Product $product): array
    {
        $inStock = $product->stock > 0 && $product->status === 'available';
        $price = $product->current_price ?? $product->price;
        $sellerName = $product->owner?->farmProfile?->farm_name ?? config('seo.site_name', 'AbodaFarm');

        $data = [
            'name' => $product->name,
            'description' => $product->description ?? $product->excerpt ?? '',
            'image' => $product->image_url ?? $this->data['image'],
            'sku' => $product->sku ?? (string) $product->id,
            'brand' => $sellerName,
            'category' => $product->category?->name ?? '',
            'price' => (string) $price,
            'currency' => 'IDR',
            'in_stock' => $inStock,
            'seller' => ['name' => $sellerName],
        ];

        // Add weight if available
        if ($product->weight) {
            $data['weight'] = [
                'value' => (string) $product->weight,
                'unit' => 'KG',
            ];
        }

        // Add rating if available
        if ($product->rating && $product->rating > 0) {
            $data['rating'] = [
                'value' => (string) $product->rating,
                'count' => (string) ($product->total_reviews ?? 1),
            ];
        }

        // Add availability
        $data['availability'] = $inStock
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';

        return $data;
    }

    /**
     * Generate keywords from array.
     */
    protected function generateKeywords(array $keywords): string
    {
        // Filter and clean keywords
        $keywords = array_filter($keywords, fn($k) => !empty(trim((string) $k)));

        // Add default keywords
        $defaultKeywords = ['AbodaFarm', 'peternakan', 'ternak organik'];
        $keywords = array_merge($keywords, $defaultKeywords);

        // Remove duplicates
        $keywords = array_unique($keywords);

        return implode(', ', $keywords);
    }

    /**
     * Truncate text to specified length.
     */
    protected function truncate(string $text, int $length = 150): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        // Try to cut at a word boundary
        $truncated = mb_substr($text, 0, $length);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > $length - 20) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated . '...';
    }

    /**
     * Get base URL.
     */
    public function baseUrl(): string
    {
        return rtrim(config('seo.canonical_base_url', config('app.url', 'https://abodafarm.test')), '/');
    }

    /**
     * Build shop URL with query parameters.
     */
    protected function buildShopUrl(?string $search, ?Category $category): string
    {
        $params = [];
        $base = $this->baseUrl();

        if ($search) {
            $params['q'] = $search;
        }

        if ($category) {
            $params['category'] = $category->slug;
        }

        if (empty($params)) {
            return $base . '/shop';
        }

        return $base . '/shop?' . http_build_query($params);
    }

    /**
     * Build location URL.
     */
    protected function buildLocationUrl(string $province, string $type): string
    {
        $slug = Str::slug($province);
        $base = $this->baseUrl();

        return match ($type) {
            'province' => $base . "/peternak/{$slug}",
            'regency' => $base . "/peternak/{$slug}",
            'district' => $base . "/ternak/{$slug}",
            default => $base . "/peternak/{$slug}",
        };
    }

    /**
     * Build location breadcrumbs.
     */
    protected function buildLocationBreadcrumbs(string $province, string $location, string $type): array
    {
        $breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Peternak', 'url' => '/peternak'],
        ];

        $provinceSlug = Str::slug($province);

        switch ($type) {
            case 'province':
                $breadcrumbs[] = ['name' => ucwords(str_replace('-', ' ', $province))];
                break;

            case 'regency':
                $breadcrumbs[] = ['name' => ucwords(str_replace('-', ' ', $province)), 'url' => "/peternak/{$provinceSlug}"];
                $breadcrumbs[] = ['name' => ucwords(str_replace('-', ' ', $location))];
                break;

            case 'district':
                $breadcrumbs[] = ['name' => ucwords(str_replace('-', ' ', $province)), 'url' => "/peternak/{$provinceSlug}"];
                $breadcrumbs[] = ['name' => ucwords(str_replace('-', ' ', $location))];
                break;
        }

        return $breadcrumbs;
    }

    /**
     * Set schemas to include.
     */
    public function schemas(array $schemas): self
    {
        $this->schemas = $schemas;
        return $this;
    }

    /**
     * Set breadcrumbs for breadcrumb schema.
     */
    public function breadcrumbs(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

    /**
     * Set product data for product schema.
     */
    public function productData(array $data): self
    {
        $this->productData = $data;
        return $this;
    }

    /**
     * Set the page title.
     *
     * @param string|null $title
     * @param bool $appendSiteName Whether to append site name to title
     */
    public function title(?string $title, bool $appendSiteName = true): self
    {
        if ($title) {
            $this->data['title'] = $appendSiteName
                ? "{$title} - " . config('seo.site_name')
                : $title;
        }

        return $this;
    }

    /**
     * Set the meta description.
     */
    public function description(?string $description): self
    {
        if ($description) {
            $this->data['description'] = $description;
        }

        return $this;
    }

    /**
     * Set meta keywords.
     */
    public function keywords(?string $keywords): self
    {
        if ($keywords) {
            $this->data['keywords'] = $keywords;
        }

        return $this;
    }

    /**
     * Add keywords to existing ones.
     */
    public function addKeywords(array $keywords): self
    {
        $existing = array_filter(array_map('trim', explode(',', $this->data['keywords'])));
        $new = array_filter(array_map('trim', $keywords));
        $this->data['keywords'] = implode(', ', array_unique(array_merge($existing, $new)));

        return $this;
    }

    /**
     * Set the canonical URL.
     */
    public function canonical(?string $url): self
    {
        if ($url) {
            $this->data['canonical'] = $url;
        }

        return $this;
    }

    /**
     * Set the Open Graph image.
     */
    public function image(?string $image): self
    {
        if ($image) {
            $this->data['image'] = $this->normalizeUrl($image);
        }

        return $this;
    }

    /**
     * Set the Open Graph type.
     */
    public function type(string $type): self
    {
        $this->data['type'] = $type;

        return $this;
    }

    /**
     * Set the Open Graph locale.
     */
    public function locale(string $locale, ?string $alternate = null): self
    {
        $this->data['locale'] = $locale;
        if ($alternate) {
            $this->data['locale_alternate'] = $alternate;
        }

        return $this;
    }

    /**
     * Set the robots meta tag.
     */
    public function robots(string $robots): self
    {
        $this->data['robots'] = $robots;

        return $this;
    }

    /**
     * Mark page as public (index, follow).
     */
    public function publicPage(): self
    {
        $this->data['robots'] = config('seo.default_robots');

        return $this;
    }

    /**
     * Mark page as private (noindex, nofollow) - for dashboards.
     */
    public function privatePage(): self
    {
        $this->data['robots'] = config('seo.admin_robots');

        return $this;
    }

    /**
     * Set the author name.
     */
    public function author(string $author): self
    {
        $this->data['author'] = $author;

        return $this;
    }

    /**
     * Set Twitter card type.
     */
    public function twitterCard(string $type): self
    {
        $this->data['twitter_card'] = $type;

        return $this;
    }

    /**
     * Set the site name for Open Graph.
     */
    public function siteName(string $name): self
    {
        $this->data['site_name'] = $name;

        return $this;
    }

    /**
     * Set multiple SEO properties at once.
     *
     * @param array{
     *     title?: string|null,
     *     description?: string|null,
     *     keywords?: string|null,
     *     canonical?: string|null,
     *     image?: string|null,
     *     type?: string,
     *     robots?: string,
     *     author?: string,
     *     twitter_card?: string
     * } $properties
     */
    public function set(array $properties): self
    {
        foreach ($properties as $key => $value) {
            if ($value === null) {
                continue;
            }

            match ($key) {
                'title' => $this->title($value),
                'description' => $this->description($value),
                'keywords' => $this->keywords($value),
                'canonical' => $this->canonical($value),
                'image' => $this->image($value),
                'type' => $this->type($value),
                'robots' => $this->robots($value),
                'author' => $this->author($value),
                'twitter_card' => $this->twitterCard($value),
                default => $this->data[$key] = $value,
            };
        }

        return $this;
    }

    /**
     * Get the current SEO data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the full page title (with site name).
     */
    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * Get meta description.
     */
    public function getDescription(): string
    {
        return $this->data['description'];
    }

    /**
     * Get meta keywords.
     */
    public function getKeywords(): string
    {
        return $this->data['keywords'];
    }

    /**
     * Get canonical URL.
     */
    public function getCanonical(): string
    {
        return $this->data['canonical'];
    }

    /**
     * Get Open Graph image URL.
     */
    public function getImage(): string
    {
        return $this->data['image'];
    }

    /**
     * Get Open Graph type.
     */
    public function getType(): string
    {
        return $this->data['type'];
    }

    /**
     * Get robots directive.
     */
    public function getRobots(): string
    {
        return $this->data['robots'];
    }

    /**
     * Get all meta tags as an array for Blade.
     */
    public function toArray(): array
    {
        return [
            // Title
            'title' => $this->data['title'],

            // Meta tags
            'description' => $this->data['description'],
            'keywords' => $this->data['keywords'],
            'author' => $this->data['author'],
            'robots' => $this->data['robots'],

            // Canonical
            'url' => $this->data['canonical'],

            // Image
            'image' => $this->data['image'],

            // Open Graph
            'og_title' => $this->data['title'],
            'og_description' => $this->data['description'],
            'og_image' => $this->data['image'],
            'og_url' => $this->data['canonical'],
            'og_type' => $this->data['type'],
            'og_locale' => $this->data['locale'],
            'og_locale_alternate' => $this->data['locale_alternate'],
            'og_site_name' => $this->data['site_name'],

            // Twitter
            'twitter_card' => $this->data['twitter_card'],
            'twitter_title' => $this->data['title'],
            'twitter_description' => $this->data['description'],
            'twitter_image' => $this->data['image'],
            'twitter_site' => $this->data['twitter_account'],

            // Theme
            'theme_color' => $this->data['theme_color'],

            // Schemas
            'schemas' => $this->schemas,
            'breadcrumbs' => $this->breadcrumbs,
            'product_data' => $this->productData,
        ];
    }

    /**
     * Get the SEO builder for JSON-LD generation.
     */
    public function getBuilder(): SeoBuilder
    {
        $builder = new SeoBuilder();

        $builder->title($this->data['title'])
            ->description($this->data['description'])
            ->keywords($this->data['keywords'])
            ->image($this->data['image'])
            ->url($this->data['canonical'])
            ->type($this->data['type'])
            ->author($this->data['author'])
            ->robots($this->data['robots'])
            ->schemas($this->schemas);

        if ($this->breadcrumbs) {
            $builder->breadcrumbs($this->breadcrumbs);
        }

        if ($this->productData) {
            $builder->product($this->productData);
        }

        return $builder;
    }

    /**
     * Generate canonical URL from current request.
     */
    protected function getCanonicalUrl(): string
    {
        $base = rtrim(config('seo.canonical_base_url'), '/');
        $path = Request::path();

        return $path === '/' ? $base : "{$base}/{$path}";
    }

    /**
     * Normalize a URL to be absolute.
     */
    protected function normalizeUrl(string $url): string
    {
        // If it's already an absolute URL, return as-is
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        // If it starts with a slash, prepend the base URL
        if (str_starts_with($url, '/')) {
            return rtrim(config('seo.canonical_base_url'), '/') . $url;
        }

        // Otherwise, prepend base URL with slash
        return rtrim(config('seo.canonical_base_url'), '/') . '/' . $url;
    }
}
