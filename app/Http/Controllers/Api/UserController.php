<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get user profile
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
                'role' => $user->role,
                'referral_code' => $user->referral_code,
                'is_verified' => $user->is_verified,
                'wallet' => [
                    'balance' => $user->wallet?->balance ?? 0,
                    'total_earned' => $user->wallet?->total_earned ?? 0,
                    'total_withdrawn' => $user->wallet?->total_withdrawn ?? 0,
                ],
                'subscription' => $user->activeSubscription ? [
                    'id' => $user->activeSubscription->id,
                    'plan' => [
                        'id' => $user->activeSubscription->plan->id,
                        'name' => $user->activeSubscription->plan->name,
                        'slug' => $user->activeSubscription->plan->slug,
                    ],
                    'status' => $user->activeSubscription->status,
                    'started_at' => $user->activeSubscription->started_at?->toISOString(),
                    'expires_at' => $user->activeSubscription->expires_at?->toISOString(),
                ] : null,
                'stats' => [
                    'tasks_completed_today' => $user->tasksCompletedToday(),
                    'daily_task_limit' => $user->getDailyTaskLimit(),
                    'remaining_tasks_today' => $user->remainingTasksToday(),
                    'reward_per_task' => $user->getRewardPerTask(),
                ],
                'created_at' => $user->created_at->toISOString(),
                'last_login_at' => $user->last_login_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'phone']));

        return response()->json([
            'success' => true,
            'message' => 'Maelezo yamebadilishwa',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Delete old avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Picha imebadilishwa',
            'data' => [
                'avatar' => $user->getAvatarUrl(),
            ],
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password ya sasa si sahihi',
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password imebadilishwa',
        ]);
    }

    /**
     * Get dashboard stats
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
                'wallet_balance' => $user->wallet?->balance ?? 0,
                'tasks_today' => $user->tasksCompletedToday(),
                'tasks_limit' => $user->getDailyTaskLimit(),
                'tasks_remaining' => $user->remainingTasksToday(),
                'reward_per_task' => $user->getRewardPerTask(),
                'earnings' => [
                    'today' => $earningsToday,
                    'this_week' => $earningsThisWeek,
                    'this_month' => $earningsThisMonth,
                ],
                'subscription' => $user->activeSubscription?->plan?->name ?? 'Free',
                'referral_count' => $user->referrals()->count(),
            ],
        ]);
    }

    /**
     * Get activity summary
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
                    'task' => $completion->task->title,
                    'reward' => $completion->reward_earned,
                    'completed_at' => $completion->created_at->toISOString(),
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
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->toISOString(),
                ];
            }) ?? [];

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
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password si sahihi',
            ], 400);
        }

        // Delete tokens
        $user->tokens()->delete();

        // Soft delete or deactivate
        $user->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Akaunti imefutwa',
        ]);
    }

    /**
     * Update FCM token
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
            'message' => 'FCM token updated',
        ]);
    }
}
