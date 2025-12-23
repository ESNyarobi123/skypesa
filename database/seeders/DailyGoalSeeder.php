<?php

namespace Database\Seeders;

use App\Models\DailyGoal;
use Illuminate\Database\Seeder;

class DailyGoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DailyGoal::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Mkakamavu wa Leo',
                'description' => 'Kamilisha tasks 15 leo na upate bonus maalum!',
                'target_tasks' => 15,
                'bonus_amount' => 50,
                'icon' => 'target',
                'color' => '#10B981',
                'is_active' => true,
            ]
        );
    }
}
