<?php

/**
 * SEO Configuration for AbodaFarm
 *
 * This file contains all SEO-related settings that can be customized
 * without modifying application code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Site Information
    |--------------------------------------------------------------------------
    */

    'site_name' => env('SEO_SITE_NAME', 'AbodaFarm'),

    'site_description' => env('SEO_SITE_DESCRIPTION', 'AbodaFarm - Marketplace peternakan terpercaya di Sulawesi Utara. Jual beli sapi, kambing, dan ayam berkualitas dengan jaminan kesehatan. Tersedia untuk Aqiqah dan Qurban.'),

    'site_keywords' => env('SEO_SITE_KEYWORDS', 'peternakan sulawesi utara, jual sapi, jual kambing, ayam berkualitas, aqiqah, qurban, peternak manado, beli ternak online, livestock marketplace, organik'),

    /*
    |--------------------------------------------------------------------------
    | Contact Information (for Structured Data)
    |--------------------------------------------------------------------------
    */

    'site_phone' => env('SEO_SITE_PHONE', '+62-xxx-xxxx-xxxx'),

    'site_email' => env('SEO_SITE_EMAIL', 'info@abodafarm.test'),

    /*
    |--------------------------------------------------------------------------
    | Address Information (for LocalBusiness Schema)
    |--------------------------------------------------------------------------
    */

    'address_locality' => env('SEO_ADDRESS_LOCALITY', 'Manado'),

    'address_region' => env('SEO_ADDRESS_REGION', 'Sulawesi Utara'),

    'address_country' => env('SEO_ADDRESS_COUNTRY', 'ID'),

    'address_street' => env('SEO_ADDRESS_STREET', ''),

    'postal_code' => env('SEO_POSTAL_CODE', ''),

    /*
    |--------------------------------------------------------------------------
    | Geo Coordinates (for LocalBusiness Schema)
    |--------------------------------------------------------------------------
    */

    'latitude' => env('SEO_LATITUDE', '1.4748'),

    'longitude' => env('SEO_LONGITUDE', '124.8424'),

    /*
    |--------------------------------------------------------------------------
    | Default Media
    |--------------------------------------------------------------------------
    */

    'default_image' => env('SEO_DEFAULT_IMAGE', '/images/og-default.svg'),

    'default_logo' => env('SEO_DEFAULT_LOGO', '/icons/IconTitle.png'),

    /*
    |--------------------------------------------------------------------------
    | Canonical URL Configuration
    |--------------------------------------------------------------------------
    */

    'canonical_base_url' => env('APP_URL', 'https://abodafarm.test'),

    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    */

    'twitter_account' => env('SEO_TWITTER_ACCOUNT', '@abodafarm'),

    'facebook_url' => env('SEO_FACEBOOK_URL', 'https://facebook.com/abodafarm'),

    'instagram_url' => env('SEO_INSTAGRAM_URL', 'https://instagram.com/abodafarm'),

    'youtube_url' => env('SEO_YOUTUBE_URL', 'https://youtube.com/@abodafarm'),

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    */

    'theme_color' => env('SEO_THEME_COLOR', '#1A7FA8'),

    'theme_color_light' => env('SEO_THEME_COLOR_LIGHT', '#E6F4F9'),

    /*
    |--------------------------------------------------------------------------
    | Default Robots Settings
    |--------------------------------------------------------------------------
    |
    | Default robots meta tag for public pages.
    | Options: 'index, follow', 'noindex, nofollow', 'index, nofollow', etc.
    |
    */

    'default_robots' => 'index, follow',

    'admin_robots' => 'noindex, nofollow',

    /*
    |--------------------------------------------------------------------------
    | Open Graph Defaults
    |--------------------------------------------------------------------------
    */

    'og_locale' => 'id_ID',

    'og_locale_alternate' => 'en_US',

    'og_type' => 'website',

    /*
    |--------------------------------------------------------------------------
    | Twitter Card Defaults
    |--------------------------------------------------------------------------
    */

    'twitter_card_type' => 'summary_large_image',

    /*
    |--------------------------------------------------------------------------
    | Author Information
    |--------------------------------------------------------------------------
    */

    'author' => env('SEO_AUTHOR', 'AbodaFarm'),

    'author_url' => env('SEO_AUTHOR_URL', 'https://abodafarm.test'),

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */

    'app_name' => env('APP_NAME', 'AbodaFarm'),

    'app_url' => env('APP_URL', 'https://abodafarm.test'),

];
