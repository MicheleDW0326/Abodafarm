<?php

/**
 * Performance Configuration for Core Web Vitals Optimization
 *
 * This file contains settings for optimizing LCP, CLS, FCP, INP, and TTFB.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Critical Resources to Preload
    |--------------------------------------------------------------------------
    |
    | Define resources that should be preloaded for faster LCP.
    |
    */

    'preload' => [
        // Hero image for homepage
        // 'hero_image' => '/images/hero-default.jpg',

        // Logo
        'logo' => '/icons/MainLogo.png',

        // Critical fonts
        'font_main' => 'https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap',
    ],

    /*
    |--------------------------------------------------------------------------
    | DNS Prefetch Domains
    |--------------------------------------------------------------------------
    |
    | Define external domains to prefetch DNS for faster connections.
    |
    */

    'dns_prefetch' => [
        'https://fonts.bunny.net',
        'https://cdnjs.cloudflare.com',
        'https://maps.googleapis.com',
        'https://maps.gstatic.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preconnect Domains
    |--------------------------------------------------------------------------
    |
    | Define domains for early connection establishment.
    |
    */

    'preconnect' => [
        'https://fonts.bunny.net',
        'https://cdnjs.cloudflare.com',
        'https://maps.googleapis.com',
        'https://maps.gstatic.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lazy Load Components
    |--------------------------------------------------------------------------
    |
    | Components that should be lazily loaded.
    |
    */

    'lazy_components' => [
        'help-center',
        'notification-dropdown',
        'cart-drawer',
        'smart-search',
    ],

    /*
    |--------------------------------------------------------------------------
    | Defer Non-Critical Scripts
    |--------------------------------------------------------------------------
    |
    | Scripts that can be deferred for faster initial render.
    |
    */

    'defer_scripts' => [
        // Add non-critical JS here
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Control Headers (seconds)
    |--------------------------------------------------------------------------
    |
    | Cache duration for different asset types.
    |
    */

    'cache' => [
        // Immutable assets (hashed filenames)
        'immutable' => 31536000, // 1 year

        // HTML pages (no cache for dynamic content)
        'html' => 0,

        // Static assets
        'assets' => 86400, // 1 day

        // Images
        'images' => 604800, // 1 week

        // Fonts
        'fonts' => 2592000, // 30 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression Settings
    |--------------------------------------------------------------------------
    */

    'compression' => [
        'enabled' => true,
        'level' => 6, // 0-9, higher = more compression
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Performance
    |--------------------------------------------------------------------------
    */

    'livewire' => [
        // Disable automatic asset cleanup (faster)
        'asset_cleanup' => false,

        // Use defer mode by default
        'defer' => true,

        // Disable persist across page loads for smaller payload
        'persist' => false,
    ],

];
