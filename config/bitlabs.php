<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BitLabs Survey Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for BitLabs Survey Offerwall Integration
    | Provider: Prodege, LLC
    |
    */

    // API Credentials
    'api_token' => env('BITLABS_API_TOKEN', ''),
    'secret_key' => env('BITLABS_SECRET_KEY', ''),
    's2s_key' => env('BITLABS_S2S_KEY', ''),

    // Offerwall URL
    'offerwall_url' => 'https://web.bitlabs.ai',

    // API Base URL
    'api_url' => 'https://api.bitlabs.ai/v1',
    
    // Cache settings (seconds)
    'cache_ttl' => 120,

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
    'usd_to_tzs' => env('BITLABS_USD_TO_TZS', 2500),

    // Callback/Postback security
    'callback_secret' => env('BITLABS_CALLBACK_SECRET', ''),
    
    // IP Whitelist for callback (empty = allow all)
    // BitLabs IPs can be added here for extra security
    'allowed_ips' => array_filter(explode(',', env('BITLABS_ALLOWED_IPS', ''))),

    // Enable/Disable
    'enabled' => env('BITLABS_ENABLED', true),

    // Demo mode for testing
    'demo_mode' => env('BITLABS_DEMO_MODE', false),
];
