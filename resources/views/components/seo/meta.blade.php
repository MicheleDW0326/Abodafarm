{{--
    SEO Meta Component - Complete SEO Solution for AbodaFarm

    A comprehensive, reusable Blade component for generating:
    - Standard meta tags (title, description, keywords, author)
    - Open Graph tags for social sharing
    - Twitter Card tags
    - Canonical URL
    - JSON-LD Structured Data (Organization, LocalBusiness, WebSite, Product, Article, Breadcrumb)

    Usage:
    <x-seo.meta
        title="Page Title"
        description="Page description"
        :keywords="['keyword1', 'keyword2']"
        image="/images/page.jpg"
        type="website"
    />

    With schema:
    <x-seo.meta
        title="Product Name"
        :schema="['organization', 'product']"
        :productData="$productArray"
    />

    With breadcrumbs:
    <x-seo.meta
        title="Page"
        :breadcrumbs="[
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Category', 'url' => '/category'],
            ['name' => 'Current']
        ]"
    />
--}}

@props([
    // Basic SEO
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'author' => null,

    // Robots
    'robots' => null,

    // Structured Data
    'schema' => null,
    'productData' => null,
    'articleData' => null,
    'breadcrumbs' => null,
])

@php
    // Use SeoBuilder for structured data generation
    $builder = app(\App\Services\SeoBuilder::class);

    // Configure the builder based on props
    if ($title) {
        $builder->title($title);
    }
    if ($description) {
        $builder->description($description);
    }
    if ($keywords) {
        $builder->keywords($keywords);
    }
    if ($image) {
        $builder->image($image);
    }
    if ($url) {
        $builder->url($url);
    }
    if ($author) {
        $builder->author($author);
    }

    // Set type
    $builder->type($type);

    // Set robots
    if ($robots) {
        $builder->robots($robots);
    }

    // Set schemas
    $schemas = is_array($schema) ? $schema : ($schema ? [$schema] : ['organization']);
    $builder->schemas($schemas);

    // Set product data
    if ($productData && is_array($productData)) {
        $builder->product($productData);
    }

    // Set article data
    if ($articleData && is_array($articleData)) {
        $builder->article($articleData);
    }

    // Set breadcrumbs
    if ($breadcrumbs && is_array($breadcrumbs)) {
        $builder->breadcrumbs($breadcrumbs);
    }

    // Build SEO data
    $seoData = $builder->build();
    $jsonLdSchemas = $builder->getJsonLd();

    // Get defaults
    $baseUrl = rtrim(config('seo.canonical_base_url', config('app.url')), '/');
    $siteName = config('seo.site_name', 'AbodaFarm');
    $twitterAccount = config('seo.twitter_account', '');

    // Normalize image URL
    $seoImage = $seoData['image'];
    if ($seoImage && !str_starts_with($seoImage, 'http')) {
        $seoImage = $baseUrl . $seoImage;
    }
@endphp

{{-- Title --}}
<title>{{ $seoData['title'] }}</title>

{{-- Standard Meta Tags --}}
<meta name="description" content="{{ $seoData['description'] }}">
@if($seoData['keywords'])
<meta name="keywords" content="{{ $seoData['keywords'] }}">
@endif
@if($seoData['author'])
<meta name="author" content="{{ $seoData['author'] }}">
@endif
<meta name="robots" content="{{ $seoData['robots'] }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $seoData['url'] }}">

{{-- Open Graph Tags --}}
<meta property="og:title" content="{{ $seoData['title'] }}">
<meta property="og:description" content="{{ $seoData['description'] }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:url" content="{{ $seoData['url'] }}">
<meta property="og:type" content="{{ $seoData['type'] }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ config('seo.og_locale', 'id_ID') }}">
@if(config('seo.og_locale_alternate'))
<meta property="og:locale:alternate" content="{{ config('seo.og_locale_alternate') }}">
@endif

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoData['title'] }}">
<meta name="twitter:description" content="{{ $seoData['description'] }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@if($twitterAccount)
<meta name="twitter:site" content="{{ $twitterAccount }}">
@endif

{{-- Theme Color --}}
@if(config('seo.theme_color'))
<meta name="theme-color" content="{{ config('seo.theme_color') }}">
@endif

{{-- JSON-LD Structured Data --}}
@foreach($jsonLdSchemas as $jsonLd)
<script type="application/ld+json">
{!! $jsonLd !!}
</script>
@endforeach
