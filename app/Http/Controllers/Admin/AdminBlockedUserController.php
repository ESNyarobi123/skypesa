<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserClickFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin Controller for managing blocked users and click flags
 */
class AdminBlockedUserController extends Controller
{
    /**
     * Display list of blocked users and users with flagged clicks
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'user');

        // Filter options
        $filter = $request->get('filter', 'all');
        
        switch ($filter) {
            case 'blocked':
                $query->blocked();
                break;
            case 'flagged':
                $query->withFlaggedClicks()->notBlocked();
                break;
            case 'at_risk':
                // Users close to auto-block threshold (>= 15 flags)
                $threshold = UserClickFlag::getAutoBlockThreshold();
                $query->where('total_flagged_clicks', '>=', max(1, $threshold - 5))
                      ->where('total_flagged_clicks', '<', $threshold)
                      ->notBlocked();
                break;
            case 'auto_blocked':
                $query->blocked()->whereNull('blocked_by');
                break;
            case 'admin_blocked':
                $query->blocked()->whereNotNull('blocked_by');
                break;
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Order by most flagged first, then blocked
        $query->orderByDesc('is_blocked')
              ->orderByDesc('total_flagged_clicks')
              ->orderByDesc('blocked_at');

        $users = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total_blocked' => User::where('role', 'user')->blocked()->count(),
            'auto_blocked' => User::where('role', 'user')->blocked()->whereNull('blocked_by')->count(),
            'admin_blocked' => User::where('role', 'user')->blocked()->whereNotNull('blocked_by')->count(),
            'flagged_users' => User::where('role', 'user')->withFlaggedClicks()->count(),
            'at_risk' => User::where('role', 'user')
                ->where('total_flagged_clicks', '>=', max(1, UserClickFlag::getAutoBlockThreshold() - 5))
                ->where('total_flagged_clicks', '<', UserClickFlag::getAutoBlockThreshold())
                ->notBlocked()
                ->count(),
            'total_flags_today' => UserClickFlag::today()->count(),
            'unreviewed_flags' => UserClickFlag::unreviewed()->count(),
        ];

        return view('admin.blocked-users.index', compact('users', 'stats', 'filter'));
    }

    /**
     * Show details of a specific user's click flags
     */
    public function show(User $user)
    {
        $clickFlags = $user->clickFlags()
            ->with(['task', 'taskCompletion', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = UserClickFlag::getUserStats($user);

        return view('admin.blocked-users.show', compact('user', 'clickFlags', 'stats'));
    }

    /**
     * Block a user
     */
    public function block(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($user->isBlocked()) {
            return back()->with('warning', 'User tayari amezuiwa.');
        }

        $user->blockUser($request->reason, $request->user());

        Log::info('User blocked by admin', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'blocked_by' => $request->user()->id,
            'reason' => $request->reason,
        ]);

        return back()->with('success', "User {$user->name} amezuiwa kikamilifu.");
    }

    /**
     * Unblock a user
     */
    public function unblock(Request $request, User $user)
    {
        if (!$user->isBlocked()) {
            return back()->with('warning', 'User hajauzuiwa.');
        }

        $resetClicks = $request->boolean('reset_clicks', false);
        $user->unblockUser($resetClicks);

        Log::info('User unblocked by admin', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'unblocked_by' => $request->user()->id,
            'clicks_reset' => $resetClicks,
        ]);

        $message = "User {$user->name} amefunguliwa kikamilifu.";
        if ($resetClicks) {
            $message .= " Click counter imeresetiwa.";
        }

        return back()->with('success', $message);
    }

    /**
     * Review a click flag
     */
    public function reviewFlag(Request $request, UserClickFlag $flag)
    {
        $flag->markAsReviewed($request->user());

        return back()->with('success', 'Flag imekaguliwa.');
    }

    /**
     * Bulk review all flags for a user
     */
    public function reviewAllFlags(Request $request, User $user)
    {
        $user->clickFlags()->unreviewed()->update([
            'is_reviewed' => true,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Flags zote za {$user->name} zimekaguliwa.");
    }

    /**
     * Get recent flags (AJAX for notifications)
     */
    public function recentFlags(Request $request)
    {
        $flags = UserClickFlag::with(['user', 'task'])
            ->unreviewed()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'count' => UserClickFlag::unreviewed()->count(),
            'flags' => $flags->map(function ($flag) {
                return [
                    'id' => $flag->id,
                    'user_id' => $flag->user_id,
                    'user_name' => $flag->user->name,
                    'user_email' => $flag->user->email,
                    'task_id' => $flag->task_id,
                    'task_title' => $flag->task?->title ?? 'N/A',
                    'click_count' => $flag->click_count,
                    'created_at' => $flag->created_at->diffForHumans(),
                    'ip_address' => $flag->ip_address,
                ];
            }),
        ]);
    }

    /**
     * Reset user's click count without unblocking
     */
    public function resetClickCount(Request $request, User $user)
    {
        $oldCount = $user->total_flagged_clicks;
        $user->update(['total_flagged_clicks' => 0]);

        Log::info('User click count reset by admin', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'reset_by' => $request->user()->id,
            'old_count' => $oldCount,
        ]);

        return back()->with('success', "Click counter ya {$user->name} imeresetiwa (ilikuwa {$oldCount}).");
    }
}
