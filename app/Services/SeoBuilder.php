<?php

namespace App\Services;

/**
 * SEO Builder Class
 *
 * Provides methods to generate SEO metadata and JSON-LD structured data.
 * Used by the seo:meta Blade component.
 */
class SeoBuilder
{
    /**
     * Base URL for the application.
     */
    protected string $baseUrl;

    /**
     * Current page URL.
     */
    protected string $currentUrl;

    /**
     * SEO data.
     */
    protected array $data = [];

    /**
     * Schemas to include.
     */
    protected array $schemas = [];

    /**
     * Product data for product schema.
     */
    protected ?array $productData = null;

    /**
     * Article data for article schema.
     */
    protected ?array $articleData = null;

    /**
     * Breadcrumbs for breadcrumb schema.
     */
    protected ?array $breadcrumbs = null;

    /**
     * Create a new SeoBuilder instance.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(config('seo.canonical_base_url', config('app.url', 'https://abodafarm.test')), '/');
        $this->currentUrl = request()->fullUrl();
        $this->initializeDefaults();
    }

    /**
     * Initialize with default values.
     */
    protected function initializeDefaults(): void
    {
        $this->data = [
            'title' => config('seo.site_name', 'AbodaFarm'),
            'description' => config('seo.site_description', ''),
            'keywords' => config('seo.site_keywords', ''),
            'image' => $this->baseUrl . config('seo.default_image', '/images/og-default.jpg'),
            'type' => 'website',
            'author' => config('seo.author', 'AbodaFarm'),
            'robots' => config('seo.default_robots', 'index, follow'),
        ];
    }

    /**
     * Set the page title.
     */
    public function title(?string $title): self
    {
        if ($title) {
            $this->data['title'] = "{$title} - " . config('seo.site_name', 'AbodaFarm');
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
     * Set keywords (string or array).
     */
    public function keywords(mixed $keywords): self
    {
        if (is_array($keywords)) {
            $this->data['keywords'] = implode(', ', array_filter($keywords));
        } elseif (is_string($keywords)) {
            $this->data['keywords'] = $keywords;
        }
        return $this;
    }

    /**
     * Set the page image.
     */
    public function image(?string $image): self
    {
        if ($image) {
            $this->data['image'] = $this->normalizeUrl($image);
        }
        return $this;
    }

    /**
     * Set the URL.
     */
    public function url(?string $url): self
    {
        if ($url) {
            $this->currentUrl = $url;
        }
        return $this;
    }

    /**
     * Set the page type.
     */
    public function type(string $type): self
    {
        $this->data['type'] = $type;
        return $this;
    }

    /**
     * Set the author.
     */
    public function author(?string $author): self
    {
        if ($author) {
            $this->data['author'] = $author;
        }
        return $this;
    }

    /**
     * Set robots directive.
     */
    public function robots(?string $robots): self
    {
        if ($robots) {
            $this->data['robots'] = $robots;
        }
        return $this;
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
     * Add organization schema.
     */
    public function organization(): self
    {
        $this->schemas[] = 'organization';
        return $this;
    }

    /**
     * Add website schema.
     */
    public function website(): self
    {
        $this->schemas[] = 'website';
        return $this;
    }

    /**
     * Set product data for product schema.
     */
    public function product(array $productData): self
    {
        $this->productData = $productData;
        $this->data['type'] = 'product';
        if (!in_array('product', $this->schemas)) {
            $this->schemas[] = 'product';
        }
        return $this;
    }

    /**
     * Set article data for article schema.
     */
    public function article(array $articleData): self
    {
        $this->articleData = $articleData;
        $this->data['type'] = 'article';
        if (!in_array('article', $this->schemas)) {
            $this->schemas[] = 'article';
        }
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
     * Mark as public page.
     */
    public function public(): self
    {
        $this->data['robots'] = 'index, follow';
        return $this;
    }

    /**
     * Mark as private page (dashboard).
     */
    public function private(): self
    {
        $this->data['robots'] = 'noindex, nofollow';
        return $this;
    }

    /**
     * Build and return all SEO data.
     */
    public function build(): array
    {
        // If no schemas specified, default to organization
        if (empty($this->schemas)) {
            $this->schemas = ['organization'];
        }

        return [
            'title' => $this->data['title'],
            'description' => $this->data['description'],
            'keywords' => $this->data['keywords'],
            'image' => $this->data['image'],
            'url' => $this->currentUrl,
            'type' => $this->data['type'],
            'author' => $this->data['author'],
            'robots' => $this->data['robots'],
            'schemas' => $this->schemas,
            'product_data' => $this->productData,
            'article_data' => $this->articleData,
            'breadcrumbs' => $this->breadcrumbs,
        ];
    }

    /**
     * Get JSON-LD schemas as HTML strings.
     */
    public function getJsonLd(): array
    {
        $schemas = [];

        // Always include website schema for search
        if (in_array('website', $this->schemas) || count($this->schemas) === 0) {
            $schemas[] = $this->buildWebSiteSchema();
        }

        // Organization / LocalBusiness schema
        if (in_array('organization', $this->schemas)) {
            $schemas[] = $this->buildOrganizationSchema();
        }

        // Breadcrumb schema
        if ($this->breadcrumbs) {
            $schemas[] = $this->buildBreadcrumbSchema();
        }

        // Product schema
        if (in_array('product', $this->schemas) && $this->productData) {
            $schemas[] = $this->buildProductSchema();
        }

        // Article schema
        if (in_array('article', $this->schemas) && $this->articleData) {
            $schemas[] = $this->buildArticleSchema();
        }

        return $schemas;
    }

    /**
     * Build WebSite schema.
     */
    protected function buildWebSiteSchema(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => $this->baseUrl,
            'description' => config('seo.site_description'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->baseUrl . '/shop?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build Organization/LocalBusiness schema.
     */
    protected function buildOrganizationSchema(): string
    {
        $sameAs = array_filter([
            config('seo.facebook_url'),
            config('seo.instagram_url'),
            config('seo.youtube_url'),
            config('seo.twitter_account') ? 'https://twitter.com/' . ltrim(config('seo.twitter_account'), '@') : null,
        ]);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => $this->baseUrl . '/#organization',
            'name' => config('seo.site_name'),
            'description' => config('seo.site_description'),
            'url' => $this->baseUrl,
            'logo' => $this->baseUrl . config('seo.default_logo', '/icons/IconTitle.png'),
            'image' => $this->data['image'],
            'telephone' => config('seo.site_phone'),
            'email' => config('seo.site_email'),
            'priceRange' => '$$',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => config('seo.address_locality'),
                'addressRegion' => config('seo.address_region'),
                'addressCountry' => config('seo.address_country'),
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => config('seo.latitude'),
                'longitude' => config('seo.longitude'),
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'opens' => '00:00',
                'closes' => '23:59',
            ],
        ];

        if (!empty($sameAs)) {
            $schema['sameAs'] = array_values($sameAs);
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build Breadcrumb schema.
     */
    protected function buildBreadcrumbSchema(): string
    {
        $items = [];
        foreach ($this->breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => isset($crumb['url']) && $crumb['url']
                    ? $this->normalizeUrl($crumb['url'])
                    : null,
            ];
        }

        // Remove null items
        foreach ($items as &$item) {
            if ($item['item'] === null) {
                unset($item['item']);
            }
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values($items),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build Product schema.
     */
    protected function buildProductSchema(): string
    {
        $product = $this->productData;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'] ?? '',
            'description' => $product['description'] ?? $this->data['description'],
            'image' => $product['image'] ?? $this->data['image'],
            'sku' => $product['sku'] ?? '',
            'mpn' => $product['sku'] ?? '',
        ];

        if (!empty($product['brand'])) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product['brand'],
            ];
        }

        if (!empty($product['category'])) {
            $schema['category'] = $product['category'];
        }

        // Build offers
        $availability = $product['availability'] ?? 'https://schema.org/InStock';
        if (is_bool($product['in_stock'] ?? null)) {
            $availability = ($product['in_stock'])
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock';
        }

        $schema['offers'] = [
            '@type' => 'Offer',
            'url' => $this->currentUrl,
            'priceCurrency' => $product['currency'] ?? 'IDR',
            'price' => $product['price'] ?? '0',
            'availability' => $availability,
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => [
                '@type' => 'Organization',
                'name' => $product['seller']['name'] ?? config('seo.site_name'),
            ],
        ];

        // Add rating if available
        if (!empty($product['rating'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product['rating']['value'] ?? '5',
                'reviewCount' => $product['rating']['count'] ?? '1',
            ];
        }

        // Add weight if available
        if (!empty($product['weight'])) {
            $schema['weight'] = [
                '@type' => 'QuantitativeValue',
                'value' => $product['weight']['value'] ?? '',
                'unitCode' => $product['weight']['unit'] ?? 'KG',
            ];
        }

        // Add additional properties
        if (!empty($product['age'])) {
            $schema['additionalProperty'][] = [
                '@type' => 'PropertyValue',
                'name' => 'Age',
                'value' => $product['age'],
            ];
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build Article schema.
     */
    protected function buildArticleSchema(): string
    {
        $article = $this->articleData;

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'] ?? $this->data['title'],
            'description' => $article['description'] ?? $this->data['description'],
            'image' => $this->data['image'],
            'author' => [
                '@type' => 'Person',
                'name' => $article['author'] ?? $this->data['author'],
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('seo.site_name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->baseUrl . config('seo.default_logo', '/icons/IconTitle.png'),
                ],
            ],
            'datePublished' => $article['datePublished'] ?? now()->toIso8601String(),
            'dateModified' => $article['dateModified'] ?? now()->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->currentUrl,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Normalize URL to absolute.
     */
    protected function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        if (str_starts_with($url, '/')) {
            return $this->baseUrl . $url;
        }
        return $this->baseUrl . '/' . $url;
    }
}
