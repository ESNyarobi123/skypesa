<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * TASK REWARD STRATEGY
         * ====================
         * - Most tasks use plan-based rewards (reward_override = null)
         * - Only special/bonus tasks have small override bonuses
         * - This keeps rewards controlled and profitable
         * 
         * Plan-based rewards:
         * - Bure: TZS 3/task
         * - Starter: TZS 4/task
         * - Silver: TZS 5/task
         * - Gold: TZS 7/task
         * - VIP: TZS 10/task
         */

        // Your ad network links
        $monetagLink = 'https://otieu.com/4/10346170';
        $adsterraLink = 'https://www.effectivegatecpm.com/uk68d3hni?key=2764d550fa2bfd5e4a87fe2790b260f4';

        $tasks = [
            // ============ FEATURED TASKS (Small Bonus) ============
            [
                'title' => '⭐ Ofa Maalum ya Leo',
                'description' => 'Bonus ya kila siku! Tembelea ofa maalum. Sekunde 45.',
                'type' => 'daily_bonus',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 45,
                'daily_limit' => 1, // Once per day
                'reward_override' => null, // Uses plan reward + small bonus handled elsewhere
                'thumbnail' => null,
                'icon' => 'star',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
            ],
            [
                'title' => '🎯 Task ya Premium',
                'description' => 'Kazi bora na malipo mazuri. Sekunde 40.',
                'type' => 'visit_sponsored_link',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 40,
                'daily_limit' => 3,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'zap',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],

            // ============ MONETAG TASKS ============
            [
                'title' => 'Tazama Tangazo',
                'description' => 'Fungua na tazama tangazo. Sekunde 30.',
                'type' => 'view_ad',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 30,
                'daily_limit' => 5,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'play-circle',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Tembelea Wadhamini',
                'description' => 'Tembelea tovuti za wadhamini. Sekunde 35.',
                'type' => 'visit_sponsored_link',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 35,
                'daily_limit' => 5,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'external-link',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Quick View',
                'description' => 'Kazi ya haraka! Sekunde 25 tu.',
                'type' => 'view_ad',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 25,
                'daily_limit' => 6,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'eye',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],

            // ============ ADSTERRA TASKS ============
            [
                'title' => 'Angalia Ofa',
                'description' => 'Tazama ofa maalum. Sekunde 30.',
                'type' => 'view_ad',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 30,
                'daily_limit' => 5,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'gift',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'title' => 'Discover Offers',
                'description' => 'Gundua ofa mpya! Sekunde 40.',
                'type' => 'visit_sponsored_link',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 40,
                'daily_limit' => 4,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'compass',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Explore Content',
                'description' => 'Tazama maudhui mapya. Sekunde 35.',
                'type' => 'view_ad',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 35,
                'daily_limit' => 4,
                'reward_override' => null, // Plan-based
                'thumbnail' => null,
                'icon' => 'layout',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],

            // ============ MORE VARIETY ============
            [
                'title' => 'Daily Click',
                'description' => 'Bonyeza na uangalie! Sekunde 30.',
                'type' => 'view_ad',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 30,
                'daily_limit' => 5,
                'reward_override' => null,
                'thumbnail' => null,
                'icon' => 'mouse-pointer',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
            [
                'title' => 'Watch & Earn',
                'description' => 'Angalia na upate! Sekunde 35.',
                'type' => 'view_ad',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 35,
                'daily_limit' => 4,
                'reward_override' => null,
                'thumbnail' => null,
                'icon' => 'tv',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 9,
            ],
        ];

        foreach ($tasks as $task) {
            Task::updateOrCreate(
                ['title' => $task['title']],
                $task
            );
        }

        // Remove old tasks that no longer exist
        $newTitles = collect($tasks)->pluck('title')->toArray();
        Task::whereNotIn('title', $newTitles)->delete();
    }
}


