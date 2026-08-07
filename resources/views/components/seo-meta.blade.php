{{--
    SEO Meta Component

    A reusable Blade component for generating SEO meta tags.
    Uses the SeoService to generate complete Open Graph, Twitter Card,
    and standard meta tags.

    Usage:
    <x-seo-meta
        title="Page Title"
        description="Page description"
        image="/images/page-image.jpg"
    />

    Or pass an array:
    <x-seo-meta :data="['title' => 'Page', 'description' => 'Desc']" />

    For dynamic pages (e.g., product detail), pass the full data:
    <x-seo-meta
        title="{{ $product->name }}"
        description="{{ $product->excerpt }}"
        image="{{ $product->image_url }}"
        canonical="{{ route('shop.product', $product->slug) }}"
        type="product"
    />
--}}

@props([
    // Basic SEO
    'title' => null,
    'description' => null,
    'keywords' => null,
    'canonical' => null,

    // Open Graph
    'image' => null,
    'type' => 'website',

    // Twitter Card
    'twitterCard' => null,

    // Control
    'robots' => null,
    'author' => null,

    // Full data array override
    'data' => null,
])

@php
    // Initialize the SEO service
    $seo = app(\App\Services\SeoService::class)->reset();

    // Apply data from props or data array
    if ($data && is_array($data)) {
        $seo->set($data);
    } else {
        // Apply individual properties
        if ($title) {
            $seo->title($title);
        }

        if ($description) {
            $seo->description($description);
        }

        if ($keywords) {
            $seo->keywords($keywords);
        }

        if ($canonical) {
            $seo->canonical($canonical);
        }

        if ($image) {
            $seo->image($image);
        }

        if ($type !== 'website') {
            $seo->type($type);
        }

        if ($twitterCard) {
            $seo->twitterCard($twitterCard);
        }

        if ($robots) {
            $seo->robots($robots);
        }

        if ($author) {
            $seo->author($author);
        }
    }

    $seoData = $seo->toArray();
@endphp

{{-- Title --}}
<title>{{ $seoData['seo_title'] }}</title>

{{-- Standard Meta Tags --}}
<meta name="description" content="{{ $seoData['meta_description'] }}">
<meta name="keywords" content="{{ $seoData['meta_keywords'] }}">
<meta name="author" content="{{ $seoData['meta_author'] }}">
<meta name="robots" content="{{ $seoData['meta_robots'] }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $seoData['canonical_url'] }}">

{{-- Open Graph Tags --}}
<meta property="og:title" content="{{ $seoData['og_title'] }}">
<meta property="og:description" content="{{ $seoData['og_description'] }}">
<meta property="og:image" content="{{ $seoData['og_image'] }}">
<meta property="og:url" content="{{ $seoData['og_url'] }}">
<meta property="og:type" content="{{ $seoData['og_type'] }}">
<meta property="og:locale" content="{{ $seoData['og_locale'] }}">

@if(!empty($seoData['og_locale_alternate']))
    <meta property="og:locale:alternate" content="{{ $seoData['og_locale_alternate'] }}">
@endif

<meta property="og:site_name" content="{{ $seoData['og_site_name'] }}">

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="{{ $seoData['twitter_card'] }}">
<meta name="twitter:title" content="{{ $seoData['twitter_title'] }}">
<meta name="twitter:description" content="{{ $seoData['twitter_description'] }}">
<meta name="twitter:image" content="{{ $seoData['twitter_image'] }}">

@if(!empty($seoData['twitter_site']))
    <meta name="twitter:site" content="{{ $seoData['twitter_site'] }}">
@endif

{{-- Theme Color --}}
@if(!empty($seoData['theme_color']))
    <meta name="theme-color" content="{{ $seoData['theme_color'] }}">
@endif
