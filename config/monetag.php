<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monetag Configuration
    |--------------------------------------------------------------------------
    |
    | Monetag Publisher configuration for push notifications and smartlinks.
    |
    */

    'domain' => env('MONETAG_DOMAIN', '3nbf4.com'),
    
    'zone_id' => env('MONETAG_ZONE_ID', 10345364),
    
    // Smartlink base URLs (ya ku-generate task URLs)
    'smartlink_base' => env('MONETAG_SMARTLINK_BASE', ''),
    
    // Enable service worker for push notifications
    'enable_push' => env('MONETAG_ENABLE_PUSH', true),
    
    // Enable in-page push (IPN) ads
    'enable_ipn' => env('MONETAG_ENABLE_IPN', true),
];
