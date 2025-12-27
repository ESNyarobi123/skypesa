<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\UserClickFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * API Controller for Click/Tap Fraud Detection
 * 
 * This controller handles reports from mobile app when user
 * clicks/taps on the webview screen (especially "verify if human" area)
 */
class ClickFlagController extends Controller
{
    /**
     * Report a suspicious click/tap on webview
     * 
     * Called by mobile app when user taps on screen during ad display
     * POST /api/v1/tasks/{task}/report-click
     */
    public function reportClick(Request $request, Task $task)
    {
        $user = $request->user();

        // Check if user is already blocked
        if ($user->isBlocked()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akaunti yako imezuiwa. Wasiliana na admin kupitia WhatsApp.',
                'is_blocked' => true,
                'blocking_info' => $user->getBlockingInfo(),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'click_count' => 'required|integer|min:1',
            'task_completion_id' => 'nullable|integer',
            'click_coordinates' => 'nullable|array',
            'click_coordinates.*.x' => 'nullable|numeric',
            'click_coordinates.*.y' => 'nullable|numeric',
            'device_info' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find task completion if provided
        $completion = null;
        if ($request->task_completion_id) {
            $completion = TaskCompletion::where('id', $request->task_completion_id)
                ->where('user_id', $user->id)
                ->first();
        }

        // Record the click flag
        $flag = UserClickFlag::recordClick(
            user: $user,
            task: $task,
            completion: $completion,
            clickCount: $request->click_count,
            data: [
                'ip_address' => $request->ip(),
                'device_info' => $request->device_info ?? $request->userAgent(),
                'click_coordinates' => $request->click_coordinates,
                'notes' => $request->notes,
            ]
        );

        // Refresh user to get updated data
        $user->refresh();

        // Check if user was auto-blocked
        if ($user->isBlocked()) {
            return response()->json([
                'status' => 'blocked',
                'message' => 'Akaunti yako imezuiwa kwa sababu ya shughuli za tuhuma. Wasiliana na admin kupitia WhatsApp ili kuomba kufunguliwa.',
                'is_blocked' => true,
                'blocking_info' => $user->getBlockingInfo(),
            ], 403);
        }

        return response()->json([
            'status' => 'recorded',
            'message' => 'Click recorded for review',
            'flag_id' => $flag->id,
            'total_flagged_clicks' => $user->total_flagged_clicks,
            'threshold' => UserClickFlag::AUTO_BLOCK_THRESHOLD,
            'remaining_before_block' => max(0, UserClickFlag::AUTO_BLOCK_THRESHOLD - $user->total_flagged_clicks),
        ]);
    }

    /**
     * Get user's click flag status
     * 
     * GET /api/v1/user/click-status
     */
    public function getStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'is_blocked' => $user->isBlocked(),
            'blocking_info' => $user->getBlockingInfo(),
            'click_stats' => UserClickFlag::getUserStats($user),
        ]);
    }

    /**
     * Get blocked user info (for showing blocked page)
     * 
     * GET /api/v1/user/blocked-info
     */
    public function getBlockedInfo(Request $request)
    {
        $user = $request->user();

        if (!$user->isBlocked()) {
            return response()->json([
                'status' => 'success',
                'is_blocked' => false,
                'message' => 'Akaunti yako haijazuiwa.',
            ]);
        }

        // Get admin WhatsApp number from admin settings
        $adminWhatsApp = Setting::get('whatsapp_support_number', '255700000000');
        // Format WhatsApp number (ensure it has country code)
        $adminWhatsApp = ltrim($adminWhatsApp, '+');

        return response()->json([
            'status' => 'blocked',
            'is_blocked' => true,
            'blocking_info' => $user->getBlockingInfo(),
            'support' => [
                'whatsapp' => $adminWhatsApp,
                'whatsapp_url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $adminWhatsApp),
                'message' => 'Habari Admin, naomba msaada. Akaunti yangu imezuiwa. Jina: ' . $user->name . ', Email: ' . $user->email,
            ],
            'instructions' => [
                'sw' => 'Akaunti yako imezuiwa kwa sababu ya shughuli za tuhuma. Tafadhali wasiliana na admin kupitia WhatsApp ili kuomba kufunguliwa.',
                'en' => 'Your account has been blocked due to suspicious activity. Please contact admin via WhatsApp to request unblocking.',
            ],
        ]);
    }
}
