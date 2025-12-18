<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZenoPay Configuration
    |--------------------------------------------------------------------------
    |
    | ZenoPay Mobile Money Tanzania API configuration for processing
    | subscription payments and deposits.
    |
    */

    'api_key' => env('ZENOPAY_API_KEY', ''),
    
    'base_url' => env('ZENOPAY_BASE_URL', 'https://zenoapi.com'),
    
    'endpoints' => [
        'payment' => '/api/payments/mobile_money_tanzania',
        'status' => '/api/payments/order-status',
    ],
    
    'timeout' => 30,
    
    // Polling configuration
    'polling' => [
        'max_attempts' => 30,  // Maximum polling attempts
        'interval' => 5,      // Seconds between polls
    ],
];
