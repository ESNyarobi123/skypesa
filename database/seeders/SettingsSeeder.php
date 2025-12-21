<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'platform_name',
                'value' => 'SKYpesa',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Platform display name',
            ],
            [
                'key' => 'platform_currency',
                'value' => 'TZS',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default currency code',
            ],
            [
                'key' => 'platform_timezone',
                'value' => 'Africa/Dar_es_Salaam',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable maintenance mode',
            ],

            // Referral Settings
            [
                'key' => 'referral_bonus_referrer',
                'value' => '500',
                'type' => 'integer',
                'group' => 'referral',
                'description' => 'Bonus for the user who referred (TZS)',
            ],
            [
                'key' => 'referral_bonus_new_user',
                'value' => '200',
                'type' => 'integer',
                'group' => 'referral',
                'description' => 'Bonus for the new user (TZS)',
            ],
            [
                'key' => 'referral_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'referral',
                'description' => 'Enable referral program',
            ],
            [
                'key' => 'referral_require_task_completion',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'referral',
                'description' => 'Require new user to complete a task before bonus',
            ],

            // Withdrawal Settings
            [
                'key' => 'withdrawal_min_global',
                'value' => '5000',
                'type' => 'integer',
                'group' => 'withdrawal',
                'description' => 'Minimum withdrawal amount globally (TZS)',
            ],
            [
                'key' => 'withdrawal_max_daily',
                'value' => '500000',
                'type' => 'integer',
                'group' => 'withdrawal',
                'description' => 'Maximum withdrawal per day (TZS)',
            ],
            [
                'key' => 'withdrawal_per_day_limit',
                'value' => '3',
                'type' => 'integer',
                'group' => 'withdrawal',
                'description' => 'Number of withdrawals allowed per day',
            ],
            [
                'key' => 'withdrawal_require_phone_verification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'withdrawal',
                'description' => 'Require phone verification for withdrawals',
            ],
            [
                'key' => 'withdrawal_auto_approve',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'withdrawal',
                'description' => 'Auto-approve withdrawals',
            ],
            [
                'key' => 'withdrawal_auto_approve_max',
                'value' => '10000',
                'type' => 'integer',
                'group' => 'withdrawal',
                'description' => 'Max amount for auto-approval (TZS)',
            ],

            // Task Settings
            [
                'key' => 'task_default_duration',
                'value' => '30',
                'type' => 'integer',
                'group' => 'task',
                'description' => 'Default task duration in seconds',
            ],
            [
                'key' => 'task_allow_skip',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'task',
                'description' => 'Allow users to skip tasks',
            ],

            // Profit Settings (for admin analytics)
            [
                'key' => 'ad_revenue_per_view',
                'value' => '8',
                'type' => 'integer',
                'group' => 'profit',
                'description' => 'Average ad revenue per view (TZS)',
            ],
            [
                'key' => 'platform_profit_percent',
                'value' => '60',
                'type' => 'integer',
                'group' => 'profit',
                'description' => 'Platform profit percentage from ads',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
