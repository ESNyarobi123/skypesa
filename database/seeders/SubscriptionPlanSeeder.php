<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * PROFITABLE REWARD MODEL
         * ========================
         * Reality: Ad networks pay ~$0.002-0.005 per view (TZS 5-12)
         * Rule: User reward ≤ 40% of ad revenue
         * 
         * So if ad pays TZS 8, user gets TZS 3-4 max
         * 
         * PROFIT SOURCES:
         * 1. Ad revenue margin (60%+)
         * 2. Subscription fees (main profit!)
         * 3. Withdrawal fees
         * 4. Referral cuts
         */

        $plans = [
            [
                'name' => 'free',
                'display_name' => 'Bure',
                'description' => 'Anza bure na kupata mapato ya msingi. Jaribu SkyEarn!',
                'price' => 0,
                'duration_days' => null, // Forever
                'daily_task_limit' => 20, // More tasks, less reward
                'reward_per_task' => 3, // TZS 3 per task (realistic!)
                'min_withdrawal' => 5000, // Min TZS 5,000 to withdraw
                'withdrawal_fee_percent' => 20, // 20% fee
                'processing_days' => 7, // 7 days to process
                'badge_color' => '#6b7280',
                'icon' => 'gift',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'starter',
                'display_name' => 'Starter',
                'description' => 'Anzisha safari yako ya mapato! Tasks zaidi na fees chini.',
                'price' => 2000, // TZS 2,000 subscription
                'duration_days' => 30,
                'daily_task_limit' => 40,
                'reward_per_task' => 4, // TZS 4 per task
                'min_withdrawal' => 3000,
                'withdrawal_fee_percent' => 15,
                'processing_days' => 5,
                'badge_color' => '#3b82f6',
                'icon' => 'trending-up',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'silver',
                'display_name' => 'Silver',
                'description' => 'Pata mapato zaidi kila siku! Ofa bora kwa wafanya kazi.',
                'price' => 5000, // TZS 5,000 subscription
                'duration_days' => 30,
                'daily_task_limit' => 60,
                'reward_per_task' => 5, // TZS 5 per task
                'min_withdrawal' => 2000,
                'withdrawal_fee_percent' => 10,
                'processing_days' => 3,
                'badge_color' => '#94a3b8',
                'icon' => 'shield',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'gold',
                'display_name' => 'Gold',
                'description' => 'Kwa wanaotaka mapato makubwa! Tasks nyingi na rewards nzuri.',
                'price' => 10000, // TZS 10,000 subscription
                'duration_days' => 30,
                'daily_task_limit' => 100,
                'reward_per_task' => 7, // TZS 7 per task
                'min_withdrawal' => 1500,
                'withdrawal_fee_percent' => 7,
                'processing_days' => 2,
                'badge_color' => '#f59e0b',
                'icon' => 'award',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'vip',
                'display_name' => 'VIP',
                'description' => 'Kiwango cha juu kabisa! Unlimited tasks na withdrawal ya haraka.',
                'price' => 25000, // TZS 25,000 subscription
                'duration_days' => 30,
                'daily_task_limit' => null, // Unlimited (but still capped by available tasks)
                'reward_per_task' => 10, // TZS 10 per task (max!)
                'min_withdrawal' => 1000,
                'withdrawal_fee_percent' => 5,
                'processing_days' => 1, // 1 day
                'badge_color' => '#10b981',
                'icon' => 'crown',
                'sort_order' => 5,
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

        // Remove old plans that don't exist anymore
        $newPlanNames = collect($plans)->pluck('name')->toArray();
        SubscriptionPlan::whereNotIn('name', $newPlanNames)->delete();
    }
}
