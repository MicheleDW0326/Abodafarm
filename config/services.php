<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Search Console & Webmaster Verification
    |--------------------------------------------------------------------------
    */
    'google' => [
        // Google Search Console verification meta tag content
        'site_verification' => env('GOOGLE_SITE_VERIFICATION'),

        // Google Analytics 4 Measurement ID (G-XXXXXXXXXX)
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),

        // Google Tag Manager Container ID (GTM-XXXXXXX)
        'tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
    ],

    'bing' => [
        // Bing Webmaster Center verification code
        'site_verification' => env('BING_SITE_VERIFICATION'),
    ],

    'yandex' => [
        // Yandex Webmaster verification code
        'site_verification' => env('YANDEX_SITE_VERIFICATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Tracking
    |--------------------------------------------------------------------------
    */
    'clarity' => [
        // Microsoft Clarity Project ID
        'project_id' => env('CLARITY_PROJECT_ID'),
    ],

    'facebook' => [
        // Facebook Pixel ID
        'pixel_id' => env('FACEBOOK_PIXEL_ID'),

        // Enable automatic PageView tracking
        'pixel_events_enabled' => env('FACEBOOK_PIXEL_EVENTS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    */
    'midtrans' => [

        'server_key' => env('MIDTRANS_SERVER_KEY'),

        'client_key' => env('MIDTRANS_CLIENT_KEY'),

        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

        'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

        'is_3ds' => env('MIDTRANS_IS_3DS', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    */
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID'),
        'api_url' => 'https://fcm.googleapis.com/fcm/send',
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];