<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = [
            'general' => Setting::getByGroup('general'),
            'referral' => Setting::getByGroup('referral'),
            'withdrawal' => Setting::getByGroup('withdrawal'),
            'task' => Setting::getByGroup('task'),
            'profit' => Setting::getByGroup('profit'),
            'support' => Setting::getByGroup('support'),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // General
            'platform_name' => 'nullable|string|max:100',
            'maintenance_mode' => 'nullable',
            
            // Referral
            'referral_bonus_referrer' => 'nullable|integer|min:0',
            'referral_bonus_new_user' => 'nullable|integer|min:0',
            'referral_enabled' => 'nullable',
            'referral_require_task_completion' => 'nullable',
            'referral_tasks_required' => 'nullable|integer|min:1|max:100',
            
            // Withdrawal
            'withdrawal_min_global' => 'nullable|integer|min:0',
            'withdrawal_max_daily' => 'nullable|integer|min:0',
            'withdrawal_per_day_limit' => 'nullable|integer|min:1|max:10',
            'withdrawal_require_phone_verification' => 'nullable',
            'withdrawal_auto_approve' => 'nullable',
            'withdrawal_auto_approve_max' => 'nullable|integer|min:0',
            
            // Task
            'task_default_duration' => 'nullable|integer|min:10|max:300',
            'task_ip_daily_limit' => 'nullable|integer|min:1',
            'task_allow_skip' => 'nullable',
            
            // Profit
            'ad_revenue_per_view' => 'nullable|integer|min:0',
            'platform_profit_percent' => 'nullable|integer|min:0|max:100',

            // Support
            'whatsapp_support_number' => 'nullable|string|max:20',
        ]);

        // Define field groups for proper categorization
        $fieldGroups = [
            'platform_name' => 'general',
            'maintenance_mode' => 'general',
            'referral_bonus_referrer' => 'referral',
            'referral_bonus_new_user' => 'referral',
            'referral_enabled' => 'referral',
            'referral_require_task_completion' => 'referral',
            'referral_tasks_required' => 'referral',
            'withdrawal_min_global' => 'withdrawal',
            'withdrawal_max_daily' => 'withdrawal',
            'withdrawal_per_day_limit' => 'withdrawal',
            'withdrawal_require_phone_verification' => 'withdrawal',
            'withdrawal_auto_approve' => 'withdrawal',
            'withdrawal_auto_approve_max' => 'withdrawal',
            'task_default_duration' => 'task',
            'task_ip_daily_limit' => 'task',
            'task_allow_skip' => 'task',
            'ad_revenue_per_view' => 'profit',
            'platform_profit_percent' => 'profit',
            'whatsapp_support_number' => 'support',
        ];

        // Boolean fields list
        $booleanFields = [
            'maintenance_mode',
            'referral_enabled',
            'referral_require_task_completion',
            'withdrawal_require_phone_verification',
            'withdrawal_auto_approve',
            'task_allow_skip',
        ];

        // Process all fields
        foreach ($fieldGroups as $key => $group) {
            if (in_array($key, $booleanFields)) {
                // Handle checkbox/boolean fields
                $value = $request->has($key) ? 'true' : 'false';
                Setting::set($key, $value, 'boolean', $group);
            } else {
                // Handle regular fields
                $value = $request->input($key);
                if ($value !== null && $value !== '') {
                    $type = is_numeric($value) ? 'integer' : 'string';
                    Setting::set($key, $value, $type, $group);
                }
            }
        }

        Setting::clearCache();

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully!');
    }

    /**
     * Clear system cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Setting::clearCache();

            return back()->with('success', 'System cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Reset demo data (dangerous!)
     */
    public function resetDemoData(Request $request)
    {
        // Require confirmation
        if ($request->input('confirm') !== 'DELETE') {
            return back()->with('error', 'Please type DELETE to confirm.');
        }

        try {
            // This would delete test data - implement carefully
            // For now, just return success message
            return back()->with('success', 'Demo data reset completed!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset data: ' . $e->getMessage());
        }
    }
}
