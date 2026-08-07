<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Search Engine Verification --}}
    <x-webmaster-verification />

    {{-- SEO Meta Tags with Structured Data (Public pages) --}}
    @php
        // Support multiple SEO data sources:
        // 1. $seo array from SeoService (forHomepage, forProduct, etc.)
        // 2. $seoData array from LocalSeoService/legacy components
        // 3. Individual variables ($seoTitle, $seoDescription, etc.)

        $seoTitle = $seo['title'] ?? $seoData['title'] ?? $seoTitle ?? $title ?? null;
        $seoDescription = $seo['description'] ?? $seoData['description'] ?? $seoDescription ?? null;
        $seoKeywords = $seo['keywords'] ?? $seoData['keywords'] ?? $seoKeywords ?? null;
        $seoImage = $seo['image'] ?? $seoData['image'] ?? $seoImage ?? null;
        $seoCanonical = $seo['url'] ?? $seoData['url'] ?? $seoData['canonical'] ?? $seoCanonical ?? null;
        $seoType = $seo['type'] ?? $seoData['type'] ?? $seoType ?? 'website';
        $seoSchema = $seo['schemas'] ?? $seoData['schemas'] ?? $seoSchema ?? ['organization', 'website'];
        $seoProductData = $seo['product_data'] ?? $productData ?? null;
        $seoBreadcrumbs = $seo['breadcrumbs'] ?? $seoData['breadcrumbs'] ?? $breadcrumbs ?? null;
    @endphp
    <x-seo.meta
        :title="$seoTitle"
        :description="$seoDescription"
        :keywords="$seoKeywords"
        :image="$seoImage"
        :url="$seoCanonical"
        :type="$seoType"
        :schema="$seoSchema"
        :productData="$seoProductData"
        :breadcrumbs="$seoBreadcrumbs"
        robots="index, follow"
    />

    {{-- Favicon --}}
    <link rel="icon" href="/icons/IconTitle.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/IconTitle.png">

    {{-- DNS Prefetch for faster connections --}}
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://maps.googleapis.com">

    {{-- Preconnect to critical third-party domains --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://maps.googleapis.com" crossorigin>

    {{-- Font Awesome Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- Preload critical font with display=swap for FOIT prevention --}}
    <link
        rel="preload"
        href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap"
        as="style"
        onload="this.onload=null;this.rel='stylesheet'"
    >
    <noscript>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap">
    </noscript>

    {{-- Preload logo for faster LCP --}}
    <link rel="preload" href="/icons/MainLogo.png" as="image" type="image/png">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- SweetAlert2 - deferred loading --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    {{-- Google Maps - lazy loaded --}}
    @if(config('googlemaps.api_key'))
        <script>
            // Lazy load Google Maps only when needed
            window.loadGoogleMaps = function() {
                if (!window.googleMapsLoaded) {
                    const script = document.createElement('script');
                    script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&libraries=places&loading=async';
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                    window.googleMapsLoaded = true;
                }
            };
        </script>
    @endif

    @livewireStyles

    {{-- Analytics & Tracking Scripts --}}
    <x-analytics />

    {{-- Inline critical CSS for above-the-fold content --}}
    <style>
        /* Critical CSS for above-the-fold */
        body { margin: 0; font-family: 'Outfit', system-ui, sans-serif; }
        .font-\[Outfit\] { font-family: 'Outfit', system-ui, sans-serif; }
    </style>
</head>

<body class="font-['Outfit'] antialiased" x-data="{ appLoaded: false }" x-init="setTimeout(() => appLoaded = true, 100)">
    {{-- Google Tag Manager (noscript) --}}
    <x-analytics-body-start />

    <!-- Loading Overlay -->
    <x-loading-overlay />

    <!-- Splash Screen - lazy loaded -->
    @livewire('splash-screen')

    {{ $slot }}

    <!-- Help Center Modal - lazy loaded via Alpine -->
    <x-help-center />

    {{-- Defer non-critical scripts --}}
    <script>
        // Defer Livewire scripts
        window.addEventListener('load', function() {
            // Dynamic import for non-critical functionality
            setTimeout(function() {
                // Any additional deferred initialization
            }, 100);
        });
    </script>

    @livewireScripts
</body>

</html>
