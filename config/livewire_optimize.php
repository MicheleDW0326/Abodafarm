<?php

/**
 * Livewire Performance Optimization Configuration
 *
 * Settings to optimize Livewire rendering for Core Web Vitals.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Component Optimization
    |--------------------------------------------------------------------------
    */

    // Disable asset cleanup for faster loading
    'disable_asset_cleanup' => true,

    // Use compressed Blade templates
    'compress_blade' => true,

    // Disable back button cache for better TTFB
    'back_button_cache' => false,

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    // Default pagination method
    'pagination' => [
        // Use simple pagination by default for faster page loads
        'method' => 'simple', // 'simple' | 'cursor' | 'paginator'
    ],

    /*
    |--------------------------------------------------------------------------
    | Lazy Loading Components
    |--------------------------------------------------------------------------
    */

    // Components that should always be lazy loaded
    'lazy_components' => [
        // 'help-center',
        // 'notification-dropdown',
    ],

    /*
    |--------------------------------------------------------------------------
    | JavaScript Optimizations
    |--------------------------------------------------------------------------
    */

    'javascript' => [
        // Use CDN for faster loading
        'cdn' => true,

        // Concatenate scripts (if using custom scripts)
        'concat' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Update Debouncing
    |--------------------------------------------------------------------------
    */

    // Debounce updates to reduce INP
    'update_debounce' => [
        'enabled' => true,
        'delay' => 300, // milliseconds - increased to prevent 404 errors from rapid updates
    ],

    /*
    |--------------------------------------------------------------------------
    | Tour Configuration
    |--------------------------------------------------------------------------
    */

    // Disable tours for better performance
    'tour_disabled' => true,

];
