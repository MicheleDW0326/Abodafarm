<?php

/**
 * SEO Helper Functions
 *
 * Provides convenient functions for generating SEO metadata.
 */

use App\Services\SeoService;
use App\Services\BreadcrumbService;
use App\Models\Category;
use App\Models\Product;
use App\Models\FarmProfile;

/**
 * Get or create the SEO service instance.
 */
function seo(): SeoService
{
    static $instance = null;

    if ($instance === null) {
        $instance = new SeoService();
    }

    return $instance;
}

/**
 * Generate SEO meta tags as HTML string.
 *
 * @param array|null $data Optional SEO data to override defaults
 * @return string HTML meta tags
 */
function seo_tags(?array $data = null): string
{
    $service = seo();

    if ($data) {
        $service->set($data);
    }

    $d = $service->toArray();

    $tags = [];

    // Title
    $tags[] = '<title>' . e($d['title']) . '</title>';

    // Meta tags
    $tags[] = '<meta name="description" content="' . e($d['description']) . '">';
    $tags[] = '<meta name="keywords" content="' . e($d['keywords']) . '">';
    $tags[] = '<meta name="author" content="' . e($d['author']) . '">';
    $tags[] = '<meta name="robots" content="' . e($d['robots']) . '">';

    // Canonical
    $tags[] = '<link rel="canonical" href="' . e($d['url']) . '">';

    // Open Graph
    $tags[] = '<meta property="og:title" content="' . e($d['og_title']) . '">';
    $tags[] = '<meta property="og:description" content="' . e($d['og_description']) . '">';
    $tags[] = '<meta property="og:image" content="' . e($d['og_image']) . '">';
    $tags[] = '<meta property="og:url" content="' . e($d['og_url']) . '">';
    $tags[] = '<meta property="og:type" content="' . e($d['og_type']) . '">';
    $tags[] = '<meta property="og:locale" content="' . e($d['og_locale']) . '">';

    if (!empty($d['og_locale_alternate'])) {
        $tags[] = '<meta property="og:locale:alternate" content="' . e($d['og_locale_alternate']) . '">';
    }

    $tags[] = '<meta property="og:site_name" content="' . e($d['og_site_name']) . '">';

    // Twitter Card
    $tags[] = '<meta name="twitter:card" content="' . e($d['twitter_card']) . '">';
    $tags[] = '<meta name="twitter:title" content="' . e($d['twitter_title']) . '">';
    $tags[] = '<meta name="twitter:description" content="' . e($d['twitter_description']) . '">';
    $tags[] = '<meta name="twitter:image" content="' . e($d['og_image']) . '">';

    if (!empty($d['twitter_site'])) {
        $tags[] = '<meta name="twitter:site" content="' . e($d['twitter_site']) . '">';
    }

    // Theme Color
    if (!empty($d['theme_color'])) {
        $tags[] = '<meta name="theme-color" content="' . e($d['theme_color']) . '">';
    }

    return implode("\n", $tags);
}

/**
 * Set SEO data and return the service.
 */
function set_seo(array $data): SeoService
{
    return seo()->set($data);
}

/**
 * Generate SEO data for homepage.
 */
function seo_homepage(): array
{
    return seo()->forHomepage();
}

/**
 * Generate SEO data for a category.
 */
function seo_category(Category $category, int $productCount = 0): array
{
    return seo()->forCategory($category, $productCount);
}

/**
 * Generate SEO data for a product.
 */
function seo_product(Product $product): array
{
    return seo()->forProduct($product);
}

/**
 * Generate SEO data for a farmer profile.
 */
function seo_farmer(FarmProfile $farm, int $productCount = 0): array
{
    return seo()->forFarmer($farm, $productCount);
}

/**
 * Generate SEO data for shop/search page.
 */
function seo_shop(?string $search = null, ?Category $category = null): array
{
    return seo()->forShop($search, $category);
}

/**
 * Generate SEO data for a location page.
 */
function seo_location(string $location, string $type = 'province', array $data = []): array
{
    return seo()->forLocation($location, $type, $data);
}

/*
|--------------------------------------------------------------------------
| Breadcrumb Helper Functions
|--------------------------------------------------------------------------
*/

/**
 * Get breadcrumb service instance.
 */
function breadcrumbs(): BreadcrumbService
{
    return app(BreadcrumbService::class);
}

/**
 * Generate breadcrumbs for current route.
 *
 * @param array|null $items Custom breadcrumb items
 * @return array
 */
function get_breadcrumbs(?array $items = null): array
{
    return breadcrumbs()->generate($items);
}

/**
 * Generate breadcrumbs as array.
 */
function breadcrumb_items(?array $items = null): array
{
    $service = breadcrumbs();

    if ($items) {
        // Add custom items
        foreach ($items as $item) {
            $service->addItem(
                $item['name'] ?? '',
                $item['url'] ?? null,
                $item['icon'] ?? null
            );
        }
    }

    return $service->getItems();
}

/**
 * Generate JSON-LD for breadcrumbs.
 */
function breadcrumb_jsonld(?array $items = null): ?string
{
    $service = breadcrumbs();

    if ($items) {
        foreach ($items as $item) {
            $service->addItem(
                $item['name'] ?? '',
                $item['url'] ?? null,
                $item['icon'] ?? null
            );
        }
    }

    return $service->toJsonLd();
}

/**
 * Set breadcrumb parameter label.
 */
function set_breadcrumb_label(string $param, string $label): void
{
    breadcrumbs()->setParameterLabel($param, $label);
}
