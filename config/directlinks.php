<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Direct Links Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Monetag Direct Links and Adsterra Smartlink.
    | These are "incentivized click" tasks - no postback verification.
    | 
    | IMPORTANT: Keep rewards SMALL to avoid abuse!
    | Recommend: TZS 3-10 per task, strict daily limits.
    |
    */

    // Adsterra Smartlink (publisher)
    // Get this from: Adsterra Dashboard → Smartlink → Copy URL
    'adsterra' => [
        'smartlink' => env('ADSTERRA_SMARTLINK', ''),
        'enabled' => !empty(env('ADSTERRA_SMARTLINK')),
    ],

    // Monetag Direct Links
    // Get these from: Monetag Dashboard → Direct Links → Copy each URL
    'monetag' => [
        'immortal' => env('MONETAG_DIRECTLINK_IMMORTAL', ''),
        'glad' => env('MONETAG_DIRECTLINK_GLAD', ''),
        // Add more direct links as needed
        // 'another_slug' => env('MONETAG_DIRECTLINK_ANOTHER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Fraud Settings (CRITICAL)
    |--------------------------------------------------------------------------
    |
    | Direct Links are highly susceptible to abuse. Keep these strict!
    |
    */
    
    // Default reward per task (keep small!)
    'default_reward' => env('TASK_DEFAULT_REWARD', 5), // TZS 5
    
    // Maximum tasks per user per day
    'daily_limit' => env('TASK_DAILY_LIMIT', 10),
    
    // Maximum tasks from same IP per day
    'ip_daily_limit' => env('TASK_IP_DAILY_LIMIT', 15),
    
    // Minimum seconds between task starts
    'cooldown_seconds' => env('TASK_COOLDOWN_SECONDS', 120),
    
    // Minimum viewing time required (in seconds)
    'min_duration' => 30,
    
    // Maximum time to complete after starting (in minutes)
    'max_completion_window' => 10,
    
    // Maximum task age in seconds (tasks older than this will be auto-cancelled)
    // This prevents astronomical countdown values when timestamps are corrupted
    'max_task_age' => env('TASK_MAX_AGE', 600), // 10 minutes

    /*
    |--------------------------------------------------------------------------
    | Task Display Settings
    |--------------------------------------------------------------------------
    */
    
    // Task titles (user-friendly)
    'titles' => [
        'monetag_immortal' => 'Tazama Tangazo la Bidhaa',
        'monetag_glad' => 'Tazama Tangazo Maalum',
        'adsterra' => 'Tazama Ofa Mpya',
    ],
    
    // Task descriptions
    'descriptions' => [
        'monetag_immortal' => 'Bofya na utazame kwa sekunde 30 ili upate malipo.',
        'monetag_glad' => 'Bofya na utazame kwa sekunde 30 ili upate malipo.',
        'adsterra' => 'Bofya na utazame tangazo kwa sekunde 30 ili upate malipo.',
    ],
];
