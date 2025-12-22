<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LinkPool;
use App\Models\PoolLink;

class LinkPoolSeeder extends Seeder
{
    /**
     * Seed the link pools with sample data.
     */
    public function run(): void
    {
        // Create SkyBoost™ pool
        $skyboost = LinkPool::create([
            'name' => 'SkyBoost™',
            'slug' => 'skyboost',
            'description' => 'High-value boost tasks with premium rewards. Complete these to maximize your earnings!',
            'icon' => 'rocket',
            'color' => '#FF6B35',
            'reward_amount' => 10,
            'duration_seconds' => 30,
            'daily_user_limit' => 5,
            'cooldown_seconds' => 180,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create SkyLinks™ pool
        $skylinks = LinkPool::create([
            'name' => 'SkyLinks™',
            'slug' => 'skylinks',
            'description' => 'Regular ad viewing tasks. Quick and easy way to earn daily rewards.',
            'icon' => 'link',
            'color' => '#10B981',
            'reward_amount' => 5,
            'duration_seconds' => 30,
            'daily_user_limit' => 10,
            'cooldown_seconds' => 120,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Add sample links to SkyBoost™
        $skyboostLinks = [
            ['name' => 'Adsterra Premium 1', 'provider' => 'adsterra', 'url' => 'https://www.profitablecpmrate.com/example1'],
            ['name' => 'Monetag Boost 1', 'provider' => 'monetag', 'url' => 'https://monetag.com/example1'],
            ['name' => 'Adsterra Premium 2', 'provider' => 'adsterra', 'url' => 'https://www.profitablecpmrate.com/example2'],
        ];

        foreach ($skyboostLinks as $linkData) {
            PoolLink::create([
                'link_pool_id' => $skyboost->id,
                'name' => $linkData['name'],
                'url' => $linkData['url'],
                'provider' => $linkData['provider'],
                'is_active' => true,
                'weight' => 1,
            ]);
        }

        // Add sample links to SkyLinks™
        $skylinksLinks = [
            ['name' => 'Adsterra Standard 1', 'provider' => 'adsterra', 'url' => 'https://www.profitablecpmrate.com/standard1'],
            ['name' => 'Monetag Direct 1', 'provider' => 'monetag', 'url' => 'https://monetag.com/direct1'],
            ['name' => 'Adsterra Standard 2', 'provider' => 'adsterra', 'url' => 'https://www.profitablecpmrate.com/standard2'],
            ['name' => 'Monetag Direct 2', 'provider' => 'monetag', 'url' => 'https://monetag.com/direct2'],
            ['name' => 'PropellerAds Link', 'provider' => 'propellerads', 'url' => 'https://propellerads.com/example1'],
        ];

        foreach ($skylinksLinks as $linkData) {
            PoolLink::create([
                'link_pool_id' => $skylinks->id,
                'name' => $linkData['name'],
                'url' => $linkData['url'],
                'provider' => $linkData['provider'],
                'is_active' => true,
                'weight' => 1,
            ]);
        }

        $this->command->info('Created 2 Link Pools with sample links!');
    }
}
