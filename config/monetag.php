<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monetag Configuration
    |--------------------------------------------------------------------------
    |
    | Monetag Publisher configuration for push notifications and smartlinks.
    |
    | NOTE: Web-based ads (Push, In-Page Push, Smartlink) do NOT have postback.
    | Postbacks are only for SDK-based Rewarded Interstitial/Popup (mobile apps).
    | For web, payment is timer-based using ymid for click tracking.
    |
    */

    'domain' => env('MONETAG_DOMAIN', '3nbf4.com'),
    
    'zone_id' => env('MONETAG_ZONE_ID', 10345364),
    
    // Smartlink base URL - copy from Monetag dashboard Smartlink section
    'smartlink_base' => env('MONETAG_SMARTLINK_BASE', ''),
    
    // Enable service worker for push notifications
    'enable_push' => env('MONETAG_ENABLE_PUSH', true),
    
    // Enable in-page push (IPN) ads
    'enable_ipn' => env('MONETAG_ENABLE_IPN', true),
];


