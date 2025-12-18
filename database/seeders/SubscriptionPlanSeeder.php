<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'free',
                'display_name' => 'Free',
                'description' => 'Anza bure na kupata mapato ya msingi',
                'price' => 0,
                'duration_days' => null, // Forever
                'daily_task_limit' => 5,
                'reward_per_task' => 50,
                'min_withdrawal' => 10000,
                'withdrawal_fee_percent' => 20,
                'processing_days' => 7,
                'badge_color' => '#6b7280',
                'icon' => 'gift',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'phase1',
                'display_name' => 'Phase 1',
                'description' => 'Panda ngazi na kupata faida zaidi',
                'price' => 5000,
                'duration_days' => 30,
                'daily_task_limit' => 15,
                'reward_per_task' => 75,
                'min_withdrawal' => 5000,
                'withdrawal_fee_percent' => 10,
                'processing_days' => 3,
                'badge_color' => '#3b82f6',
                'icon' => 'trending-up',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'phase2',
                'display_name' => 'Phase 2',
                'description' => 'Mapato makubwa na muda mfupi wa withdrawal',
                'price' => 15000,
                'duration_days' => 30,
                'daily_task_limit' => 30,
                'reward_per_task' => 100,
                'min_withdrawal' => 3000,
                'withdrawal_fee_percent' => 5,
                'processing_days' => 1,
                'badge_color' => '#8b5cf6',
                'icon' => 'zap',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'premium',
                'display_name' => 'Premium',
                'description' => 'Usimowezekano! Tasks unlimited na malipo ya papo hapo',
                'price' => 30000,
                'duration_days' => 30,
                'daily_task_limit' => null, // Unlimited
                'reward_per_task' => 150,
                'min_withdrawal' => 2000,
                'withdrawal_fee_percent' => 2,
                'processing_days' => 0, // Instant
                'badge_color' => '#10b981',
                'icon' => 'crown',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
