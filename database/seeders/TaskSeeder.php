<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Your ad network links
        $monetagLink = 'https://otieu.com/4/10346170';
        $adsterraLink = 'https://www.effectivegatecpm.com/uk68d3hni?key=2764d550fa2bfd5e4a87fe2790b260f4';

        $tasks = [
            // ============ FEATURED TASKS (Higher Rewards) ============
            [
                'title' => '⭐ Ofa Maalum ya Leo',
                'description' => 'Tembelea ofa maalum na upate bonus! Subiri sekunde 45.',
                'type' => 'daily_bonus',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 45,
                'daily_limit' => 1, // Once per day for bonus
                'reward_override' => 200,
                'thumbnail' => null,
                'icon' => 'star',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
            ],
            [
                'title' => '🎯 Task ya Premium',
                'description' => 'Kazi ya premium na malipo mazuri! Sekunde 40.',
                'type' => 'visit_sponsored_link',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 40,
                'daily_limit' => 3,
                'reward_override' => 150,
                'thumbnail' => null,
                'icon' => 'zap',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],

            // ============ MONETAG TASKS ============
            [
                'title' => 'Tazama Tangazo',
                'description' => 'Fungua na tazama tangazo hili kwa sekunde 30.',
                'type' => 'view_ad',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 30,
                'daily_limit' => 5,
                'reward_override' => 100,
                'thumbnail' => null,
                'icon' => 'play-circle',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Tembelea Wadhamini',
                'description' => 'Tembelea tovuti za wadhamini wetu. Sekunde 35.',
                'type' => 'visit_sponsored_link',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 35,
                'daily_limit' => 4,
                'reward_override' => 120,
                'thumbnail' => null,
                'icon' => 'external-link',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],

            // ============ ADSTERRA TASKS ============
            [
                'title' => 'Angalia Ofa Maalum',
                'description' => 'Tazama ofa maalum na upate malipo! Sekunde 30.',
                'type' => 'view_ad',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 30,
                'daily_limit' => 5,
                'reward_override' => 100,
                'thumbnail' => null,
                'icon' => 'gift',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'Discover Offers',
                'description' => 'Gundua ofa mpya za kuvutia! Sekunde 40.',
                'type' => 'visit_sponsored_link',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 40,
                'daily_limit' => 3,
                'reward_override' => 130,
                'thumbnail' => null,
                'icon' => 'compass',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],

            // ============ MIXED TASKS ============
            [
                'title' => 'Quick View',
                'description' => 'Kazi ya haraka! Sekunde 25 tu.',
                'type' => 'view_ad',
                'url' => $monetagLink,
                'provider' => 'monetag',
                'duration_seconds' => 25,
                'daily_limit' => 6,
                'reward_override' => 80,
                'thumbnail' => null,
                'icon' => 'eye',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Explore Content',
                'description' => 'Tazama maudhui mapya! Sekunde 35.',
                'type' => 'view_ad',
                'url' => $adsterraLink,
                'provider' => 'adsterra',
                'duration_seconds' => 35,
                'daily_limit' => 4,
                'reward_override' => 110,
                'thumbnail' => null,
                'icon' => 'layout',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
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

