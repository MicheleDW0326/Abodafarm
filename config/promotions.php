<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Promotion Types
    |--------------------------------------------------------------------------
    |
    | Defines the available promotion types in the system.
    |
    */
    'types' => [
        'percentage' => 'Persentase',
        'fixed' => 'Nominal Tetap',
        'free_shipping' => 'Gratis Ongkir',
    ],

    /*
    |--------------------------------------------------------------------------
    | Promotion Statuses
    |--------------------------------------------------------------------------
    |
    | Defines the available promotion statuses.
    |
    */
    'statuses' => [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'expired' => 'Kedaluwarsa',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default settings for new promotions.
    |
    */
    'defaults' => [
        'usage_per_user' => 1,
        'status' => 'active',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    |
    | Default limits for promotions. Null means unlimited.
    |
    */
    'limits' => [
        'default_usage_limit' => null,
        'default_max_discount' => null,
        'max_percentage' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for promotion codes.
    |
    */
    'code' => [
        'length' => 8,
        'prefix' => 'PROMO',
        'case_sensitive' => false,
    ],
];
