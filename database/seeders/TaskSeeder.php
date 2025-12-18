<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'title' => 'Tazama Tangazo la Bidhaa',
                'description' => 'Tazama tangazo hili kwa sekunde 30 kupata malipo',
                'type' => 'view_ad',
                'url' => 'https://example.monetag.com/smartlink1',
                'provider' => 'monetag',
                'duration_seconds' => 30,
                'daily_limit' => 3,
                'thumbnail' => null,
                'icon' => 'play-circle',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Tazama Video Fupi',
                'description' => 'Angalia video hii fupi ya sekunde 45',
                'type' => 'view_ad',
                'url' => 'https://example.monetag.com/smartlink2',
                'provider' => 'monetag',
                'duration_seconds' => 45,
                'daily_limit' => 2,
                'thumbnail' => null,
                'icon' => 'video',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Shiriki Link',
                'description' => 'Shiriki link hii na marafiki wako',
                'type' => 'share_link',
                'url' => 'https://example.adsterra.com/direct1',
                'provider' => 'adsterra',
                'duration_seconds' => 10,
                'daily_limit' => 5,
                'thumbnail' => null,
                'icon' => 'share-2',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Tazama Ofa Maalum',
                'description' => 'Angalia ofa maalum ya siku kwa sekunde 60',
                'type' => 'view_ad',
                'url' => 'https://example.monetag.com/smartlink3',
                'provider' => 'monetag',
                'duration_seconds' => 60,
                'daily_limit' => 1,
                'reward_override' => 100, // Special reward
                'thumbnail' => null,
                'icon' => 'star',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
            ],
        ];

        foreach ($tasks as $task) {
            Task::updateOrCreate(
                ['title' => $task['title']],
                $task
            );
        }
    }
}
