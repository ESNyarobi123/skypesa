<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CPX Research Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CPX Research Survey Integration
    |
    */

    // API Credentials
    'app_id' => env('CPX_APP_ID', ''),
    'secure_hash' => env('CPX_SECURE_HASH', ''),

    // API Endpoints
    'api_url' => 'https://live-api.cpx-research.com/api/get-surveys.php',
    
    // Cache settings (seconds)
    'cache_ttl' => 120, // CPX recommends max 120 seconds

    // Survey limits
    'default_limit' => 12,
    'daily_limit_per_user' => 20,

    // Reward structure (in TZS)
    'rewards' => [
        'short' => [
            'min_loi' => 1,
            'max_loi' => 7,
            'reward' => 200,
            'label' => 'Short Survey (5-7 min)',
            'vip_only' => false,
        ],
        'medium' => [
            'min_loi' => 8,
            'max_loi' => 14,
            'reward' => 300,
            'label' => 'Medium Survey (8-12 min)',
            'vip_only' => false,
        ],
        'long' => [
            'min_loi' => 15,
            'max_loi' => 999,
            'reward' => 500,
            'label' => 'Long Survey (15+ min)',
            'vip_only' => true, // Only VIP/Premium users
        ],
    ],

    // VIP plan names that can access long surveys
    'vip_plans' => ['diamond', 'vip', 'premium', 'gold'],

    // VIP bonus percentages per plan
    'vip_bonuses' => [
        'diamond' => 25, // 25% bonus on each survey
        'vip' => 20,     // 20% bonus
        'premium' => 15, // 15% bonus
        'gold' => 10,    // 10% bonus
        'silver' => 5,   // 5% bonus
    ],

    // Currency conversion rate
    'usd_to_tzs' => env('CPX_USD_TO_TZS', 2500),

    // Postback security
    'postback_secret' => env('CPX_POSTBACK_SECRET', ''),
    
    // IP Whitelist for postback (empty = allow all)
    // CPX Research IPs can be added here for extra security
    'allowed_ips' => array_filter(explode(',', env('CPX_ALLOWED_IPS', ''))),

    // Enable/Disable
    'enabled' => env('CPX_ENABLED', true),

    // Demo mode for testing
    'demo_mode' => env('CPX_DEMO_MODE', false),
];

