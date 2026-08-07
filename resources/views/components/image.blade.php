{{--
    Optimized Image Component - SEO & Performance Optimized

    Features:
    - Lazy loading for below-fold images (default)
    - Eager loading for hero/above-fold images
    - Async decoding
    - Width and height attributes to prevent CLS
    - Automatic alt text generation
    - Fallback placeholder for missing images
    - WebP support (when available)
    - Responsive sizing support

    Usage:
    <x-image
        src="/images/product.jpg"
        alt="Sapi Limousin Premium"
        width="400"
        height="300"
        class="rounded-lg"
    />

    Hero image (eager loading):
    <x-image
        src="/images/hero.jpg"
        alt="AbodaFarm Hero Banner"
        width="1600"
        height="600"
        loading="eager"
        class="w-full"
    />

    Auto alt text:
    <x-image src="/images/cow.jpg" type="product" />
    <x-image src="/images/avatar.jpg" context="farmer" />
--}}

@props([
    // Image source
    'src' => null,

    // Alt text (auto-generated if not provided)
    'alt' => null,

    // Dimensions (prevents CLS)
    'width' => null,
    'height' => null,

    // CSS classes
    'class' => '',

    // Loading strategy: 'lazy' (default) or 'eager' (hero)
    'loading' => 'lazy',

    // Decoding: 'async' (default)
    'decoding' => 'async',

    // Image type for auto alt text (product, hero, gallery, avatar, farm, etc.)
    'type' => null,

    // Context for auto alt text
    'context' => null,

    // Title attribute (tooltip)
    'title' => null,

    // Responsive sizes
    'sizes' => null,
])

@php
    // Default placeholder
    $defaultFallback = config('seo.default_image', '/images/placeholder.jpg');

    // If no src provided, use fallback
    $imageSrc = $src ?? $defaultFallback;

    // Check if it's an external URL or local
    $isExternal = str_starts_with($imageSrc, 'http://') || str_starts_with($imageSrc, 'https://');
    $baseUrl = rtrim(config('app.url', 'http://abodafarm.test'), '/');

    // Normalize image URL
    if (!$isExternal && $imageSrc && !str_starts_with($imageSrc, '/')) {
        $imageSrc = '/' . $imageSrc;
    }

    // Generate alt text if not provided
    $generatedAlt = '';
    if ($alt) {
        $generatedAlt = $alt;
    } elseif ($type || $context) {
        $generatedAlt = generate_image_alt($type, $context, $imageSrc);
    } else {
        // Generate from source filename
        $generatedAlt = generate_image_alt(null, null, $imageSrc);
    }

    // Ensure alt is never empty
    if (empty(trim($generatedAlt))) {
        $generatedAlt = config('seo.site_name', 'AbodaFarm') . ' Image';
    }

    // Add site name to alt if not already present
    $siteName = config('seo.site_name', 'AbodaFarm');
    if (!str_contains(strtolower($generatedAlt), strtolower($siteName))) {
        $generatedAlt .= ' - ' . $siteName;
    }

    // Build srcset for responsive images
    $srcset = '';
    if ($sizes && $src && !$isExternal) {
        $breakpoints = [
            'sm' => '640',
            'md' => '768',
            'lg' => '1024',
            'xl' => '1280',
        ];

        $srcsetParts = [];
        foreach ($breakpoints as $suffix => $size) {
            $responsiveSrc = generate_responsive_src($imageSrc, $suffix);
            if ($responsiveSrc) {
                $srcsetParts[] = "{$responsiveSrc} {$size}w";
            }
        }
        if (!empty($srcsetParts)) {
            $srcset = implode(', ', $srcsetParts);
        }
    }

    // Default object-cover class
    $defaultClasses = 'object-cover';
    $fullClasses = trim($defaultClasses . ' ' . $class);
@endphp

{{-- Picture element with WebP support --}}
@if($src && !$isExternal && !str_ends_with(strtolower($src), '.webp'))
<picture>
    {{-- WebP source --}}
    <source
        srcset="{{ generate_webp_src($imageSrc) }}"
        type="image/webp"
    >
</picture>
@endif

{{-- Main image --}}
<img
    @if($src || $defaultFallback)
        src="{{ $imageSrc }}"
    @endif
    @if($srcset)
        srcset="{{ $srcset }}"
    @endif
    @if($sizes)
        sizes="{{ $sizes }}"
    @endif
    alt="{{ $generatedAlt }}"
    @if($width)
        width="{{ $width }}"
    @endif
    @if($height)
        height="{{ $height }}"
    @endif
    class="{{ $fullClasses }}"
    loading="{{ $loading }}"
    decoding="{{ $decoding }}"
    @if($title)
        title="{{ $title }}"
    @endif
    onerror="this.onerror=null; this.src='{{ $defaultFallback }}';"
>
