<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'platform_name', 'value' => 'SKYpesa', 'type' => 'string', 'group' => 'general', 'description' => 'Platform Name'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'description' => 'Maintenance Mode'],
            
            // Referral
            ['key' => 'referral_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'referral', 'description' => 'Enable Referral System'],
            ['key' => 'referral_bonus_referrer', 'value' => '500', 'type' => 'integer', 'group' => 'referral', 'description' => 'Bonus for the Referrer (TZS)'],
            ['key' => 'referral_bonus_new_user', 'value' => '200', 'type' => 'integer', 'group' => 'referral', 'description' => 'Bonus for the New User (TZS)'],
            ['key' => 'referral_require_task_completion', 'value' => 'true', 'type' => 'boolean', 'group' => 'referral', 'description' => 'Require first task completion for bonus'],
            
            // Withdrawal
            ['key' => 'withdrawal_min_global', 'value' => '10000', 'type' => 'integer', 'group' => 'withdrawal', 'description' => 'Global Minimum Withdrawal (TZS)'],
            ['key' => 'withdrawal_max_daily', 'value' => '100000', 'type' => 'integer', 'group' => 'withdrawal', 'description' => 'Daily Maximum Withdrawal (TZS)'],
            ['key' => 'withdrawal_per_day_limit', 'value' => '1', 'type' => 'integer', 'group' => 'withdrawal', 'description' => 'Withdrawals per day limit'],
            ['key' => 'withdrawal_require_phone_verification', 'value' => 'true', 'type' => 'boolean', 'group' => 'withdrawal', 'description' => 'Require phone verification for withdrawal'],
            ['key' => 'withdrawal_auto_approve', 'value' => 'false', 'type' => 'boolean', 'group' => 'withdrawal', 'description' => 'Auto-approve small withdrawals'],
            ['key' => 'withdrawal_auto_approve_max', 'value' => '5000', 'type' => 'integer', 'group' => 'withdrawal', 'description' => 'Max amount for auto-approval'],
            
            // Task
            ['key' => 'task_default_duration', 'value' => '60', 'type' => 'integer', 'group' => 'task', 'description' => 'Default task duration (seconds)'],
            ['key' => 'task_allow_skip', 'value' => 'false', 'type' => 'boolean', 'group' => 'task', 'description' => 'Allow skipping tasks'],
            
            // Profit
            ['key' => 'ad_revenue_per_view', 'value' => '10', 'type' => 'integer', 'group' => 'profit', 'description' => 'Estimated ad revenue per view (TZS)'],
            ['key' => 'platform_profit_percent', 'value' => '30', 'type' => 'integer', 'group' => 'profit', 'description' => 'Platform profit percentage'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
