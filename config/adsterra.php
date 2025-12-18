<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Adsterra Configuration
    |--------------------------------------------------------------------------
    |
    | Adsterra Publisher API configuration for fetching placements
    | and direct links for task monetization.
    |
    */

    'api_key' => env('ADSTERRA_API_KEY', ''),
    
    'base_url' => env('ADSTERRA_BASE_URL', 'https://api3.adsterratools.com'),
    
    'format' => 'json',
    
    'timeout' => 30,
];
