<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe payment processing
    |
    */

    'secret' => env('STRIPE_SECRET'),
    'public' => env('STRIPE_PUBLIC'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for payments
    |
    */
    'currency' => 'jpy',
    
    /*
    |--------------------------------------------------------------------------
    | Download Grant Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for download grants created after purchase
    |
    */
    'grant_defaults' => [
        'max_downloads' => 5,
        'expires_years' => 2, // null for no expiration, or number of years
    ],
];