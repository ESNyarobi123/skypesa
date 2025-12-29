<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get user profile
     * GET /api/v1/user/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['wallet', 'activeSubscription.plan']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->getAvatarUrl(),
                'avatar_path' => $user->avatar,
                'role' => $user->role,
                'referral_code' => $user->referral_code,
                'is_verified' => $user->is_verified,
                'wallet' => [
                    'balance' => (float) ($user->wallet?->balance ?? 0),
                    'balance_formatted' => 'TZS ' . number_format($user->wallet?->balance ?? 0, 0),
                    'total_earned' => (float) ($user->wallet?->total_earned ?? 0),
                    'total_withdrawn' => (float) ($user->wallet?->total_withdrawn ?? 0),
                    'pending_withdrawal' => (float) ($user->wallet?->pending_withdrawal ?? 0),
                ],
                'subscription' => $user->activeSubscription ? [
                    'id' => $user->activeSubscription->id,
                    'plan' => [
                        'id' => $user->activeSubscription->plan->id,
                        'name' => $user->activeSubscription->plan->name,
                        'display_name' => $user->activeSubscription->plan->display_name,
                        'slug' => $user->activeSubscription->plan->slug ?? strtolower($user->activeSubscription->plan->name),
                    ],
                    'status' => $user->activeSubscription->status,
                    'started_at' => $user->activeSubscription->starts_at?->toIso8601String(),
                    'expires_at' => $user->activeSubscription->expires_at?->toIso8601String(),
                    'days_remaining' => $user->activeSubscription->daysRemaining(),
                ] : null,
                'stats' => [
                    'tasks_completed_today' => $user->tasksCompletedToday(),
                    'daily_task_limit' => $user->getDailyTaskLimit(),
                    'remaining_tasks_today' => $user->remainingTasksToday(),
                    'reward_per_task' => (float) $user->getRewardPerTask(),
                    'total_tasks_completed' => $user->taskCompletions()->where('status', 'completed')->count(),
                    'referrals_count' => $user->referrals()->count(),
                ],
                'created_at' => $user->created_at->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update profile (name, phone)
     * PUT /api/v1/user/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:3|max:100',
            'phone' => 'sometimes|required|string|min:10|max:15|unique:users,phone,' . $user->id,
        ], [
            'name.required' => 'Tafadhali weka jina lako.',
            'name.min' => 'Jina liwe na angalau herufi 3.',
            'phone.required' => 'Tafadhali weka namba ya simu.',
            'phone.unique' => 'Namba hii ya simu tayari inatumika.',
            'phone.min' => 'Namba ya simu ni fupi sana.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Maelezo yako yamebadilishwa!',
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Update avatar
     * POST /api/v1/user/avatar
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'avatar.required' => 'Tafadhali chagua picha.',
            'avatar.image' => 'Faili lazima iwe picha.',
            'avatar.mimes' => 'Picha iwe ya aina: jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'Picha isizidi 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Create avatars directory if not exists
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }

            // Store new avatar with simple path: avatars/user_id_timestamp.ext
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $filename = $user->id . '_' . time() . '.' . $extension;
            $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');

            $user->update(['avatar' => $path]);

            Log::info('Avatar updated', ['user_id' => $user->id, 'path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Picha imebadilishwa!',
                'data' => [
                    'avatar' => $user->getAvatarUrl(),
                    'avatar_path' => $path,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Avatar upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo la kupakia picha. Jaribu tena.',
            ], 500);
        }
    }

    /**
     * Remove avatar
     * DELETE /api/v1/user/avatar
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Picha imeondolewa!',
            'data' => [
                'avatar' => $user->getAvatarUrl(),
            ],
        ]);
    }

    /**
     * Change password
     * PUT /api/v1/user/password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Tafadhali weka password ya sasa.',
            'password.required' => 'Tafadhali weka password mpya.',
            'password.min' => 'Password mpya iwe na angalau herufi 6.',
            'password.confirmed' => 'Password mpya hazilingani.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password ya sasa si sahihi.',
                'errors' => ['current_password' => ['Password ya sasa si sahihi.']],
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password imebadilishwa!',
        ]);
    }

    /**
     * Get dashboard stats
     * GET /api/v1/user/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $user->load(['wallet', 'activeSubscription.plan']);

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Calculate earnings
        $earningsToday = $user->taskCompletions()
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('reward_earned');

        $earningsThisWeek = $user->taskCompletions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisWeek)
            ->sum('reward_earned');

        $earningsThisMonth = $user->taskCompletions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('reward_earned');

        return response()->json([
            'success' => true,
            'data' => [
                'wallet_balance' => (float) ($user->wallet?->balance ?? 0),
                'wallet_balance_formatted' => 'TZS ' . number_format($user->wallet?->balance ?? 0, 0),
                'tasks_today' => $user->tasksCompletedToday(),
                'tasks_limit' => $user->getDailyTaskLimit(),
                'tasks_remaining' => $user->remainingTasksToday(),
                'can_do_more_tasks' => $user->canCompleteMoreTasks(),
                'reward_per_task' => (float) $user->getRewardPerTask(),
                'earnings' => [
                    'today' => (float) $earningsToday,
                    'today_formatted' => 'TZS ' . number_format($earningsToday, 0),
                    'this_week' => (float) $earningsThisWeek,
                    'this_month' => (float) $earningsThisMonth,
                ],
                'subscription' => [
                    'name' => $user->activeSubscription?->plan?->display_name ?? 'Free',
                    'slug' => $user->activeSubscription?->plan?->slug ?? 'free',
                    'days_remaining' => $user->activeSubscription?->daysRemaining(),
                    'is_expiring_soon' => $user->activeSubscription?->daysRemaining() !== null && $user->activeSubscription->daysRemaining() <= 3,
                ],
                'referral_count' => $user->referrals()->count(),
                'referral_code' => $user->referral_code,
            ],
        ]);
    }

    /**
     * Get activity summary
     * GET /api/v1/user/activity
     */
    public function activity(Request $request)
    {
        $user = $request->user();

        $recentTasks = $user->taskCompletions()
            ->with('task')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($completion) {
                return [
                    'id' => $completion->id,
                    'task' => $completion->task->title ?? 'Task',
                    'task_type' => $completion->task->type ?? 'unknown',
                    'reward' => (float) $completion->reward_earned,
                    'reward_formatted' => 'TZS ' . number_format($completion->reward_earned, 0),
                    'completed_at' => $completion->created_at->toIso8601String(),
                    'completed_at_human' => $completion->created_at->diffForHumans(),
                ];
            });

        $recentTransactions = $user->wallet?->transactions()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'amount_formatted' => ($transaction->type === 'debit' ? '-' : '+') . 'TZS ' . number_format(abs($transaction->amount), 0),
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'created_at_human' => $transaction->created_at->diffForHumans(),
                ];
            }) ?? collect();

        return response()->json([
            'success' => true,
            'data' => [
                'recent_tasks' => $recentTasks,
                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }

    /**
     * Delete account
     * DELETE /api/v1/user/account
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
        ], [
            'password.required' => 'Tafadhali weka password yako.',
            'confirmation.required' => 'Tafadhali andika DELETE kuthibitisha.',
            'confirmation.in' => 'Andika DELETE (herufi kubwa) kuthibitisha.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password si sahihi.',
            ], 400);
        }

        // Delete tokens
        $user->tokens()->delete();

        // Deactivate account
        $user->update(['is_active' => false]);

        Log::info('Account deactivated via API', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Akaunti imefutwa. Pole sana kuondoka!',
        ]);
    }

    /**
     * Update FCM token
     * POST /api/v1/user/fcm-token
     */
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|in:android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type ?? 'android',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token imehifadhiwa.',
        ]);
    }

    /**
     * Get user settings/preferences
     * GET /api/v1/user/settings
     */
    public function settings(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'locale' => $user->locale ?? 'sw',
                'notifications_enabled' => true,
                'email_notifications' => true,
            ],
        ]);
    }

    /**
     * Update user settings
     * PUT /api/v1/user/settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'sometimes|in:sw,en',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if ($request->has('locale')) {
            $user->update(['locale' => $request->locale]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mipangilio imehifadhiwa.',
        ]);
    }
}
