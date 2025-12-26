<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TaskCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Get the main leaderboard (Top Earners)
     * GET /api/v1/leaderboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'weekly'); // daily, weekly, monthly, all-time
        $limit = min($request->get('limit', 20), 100);
        
        $cacheKey = "leaderboard_{$period}_{$limit}";
        $cacheTTL = match($period) {
            'daily' => 300,    // 5 minutes
            'weekly' => 900,   // 15 minutes
            'monthly' => 1800, // 30 minutes
            default => 3600,   // 1 hour
        };
        
        $leaderboard = Cache::remember($cacheKey, $cacheTTL, function () use ($period, $limit) {
            $query = TaskCompletion::query()
                ->select('user_id', DB::raw('SUM(reward_earned) as total_earnings'), DB::raw('COUNT(*) as tasks_completed'))
                ->where('status', 'completed')
                ->groupBy('user_id');
            
            // Apply period filter
            switch ($period) {
                case 'daily':
                    $query->whereDate('created_at', today());
                    break;
                case 'weekly':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'monthly':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
                // 'all-time' has no date filter
            }
            
            return $query->orderByDesc('total_earnings')
                ->limit($limit)
                ->get()
                ->map(function ($item, $index) {
                    $user = User::find($item->user_id);
                    return [
                        'rank' => $index + 1,
                        'user' => [
                            'id' => $user?->id,
                            'name' => $user ? $this->maskName($user->name) : 'Unknown User',
                            'avatar' => $user?->avatar_url,
                        ],
                        'total_earnings' => (float) $item->total_earnings,
                        'tasks_completed' => (int) $item->tasks_completed,
                    ];
                });
        });
        
        // Get current user's rank
        $userRank = $this->getUserRank($request->user(), $period);
        
        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'period' => $period,
                'my_rank' => $userRank,
            ],
        ]);
    }
    
    /**
     * Get top referrers leaderboard
     * GET /api/v1/leaderboard/referrers
     */
    public function referrers(Request $request)
    {
        $limit = min($request->get('limit', 20), 100);
        
        $cacheKey = "leaderboard_referrers_{$limit}";
        
        $leaderboard = Cache::remember($cacheKey, 1800, function () use ($limit) {
            return User::select('id', 'name', 'avatar')
                ->withCount('referrals')
                ->having('referrals_count', '>', 0)
                ->orderByDesc('referrals_count')
                ->limit($limit)
                ->get()
                ->map(function ($user, $index) {
                    return [
                        'rank' => $index + 1,
                        'user' => [
                            'id' => $user->id,
                            'name' => $this->maskName($user->name),
                            'avatar' => $user->avatar_url,
                        ],
                        'referral_count' => $user->referrals_count,
                    ];
                });
        });
        
        // Get current user's referral rank
        $user = $request->user();
        $userReferralCount = $user->referrals()->count();
        $userRank = User::withCount('referrals')
            ->having('referrals_count', '>', $userReferralCount)
            ->count() + 1;
        
        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'my_rank' => [
                    'rank' => $userRank,
                    'referral_count' => $userReferralCount,
                ],
            ],
        ]);
    }
    
    /**
     * Get task champions (most tasks completed)
     * GET /api/v1/leaderboard/tasks
     */
    public function taskChampions(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $limit = min($request->get('limit', 20), 100);
        
        $cacheKey = "leaderboard_tasks_{$period}_{$limit}";
        
        $leaderboard = Cache::remember($cacheKey, 900, function () use ($period, $limit) {
            $query = TaskCompletion::query()
                ->select('user_id', DB::raw('COUNT(*) as tasks_completed'))
                ->where('status', 'completed')
                ->groupBy('user_id');
            
            // Apply period filter
            switch ($period) {
                case 'daily':
                    $query->whereDate('created_at', today());
                    break;
                case 'weekly':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'monthly':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
            }
            
            return $query->orderByDesc('tasks_completed')
                ->limit($limit)
                ->get()
                ->map(function ($item, $index) {
                    $user = User::find($item->user_id);
                    return [
                        'rank' => $index + 1,
                        'user' => [
                            'id' => $user?->id,
                            'name' => $user ? $this->maskName($user->name) : 'Unknown User',
                            'avatar' => $user?->avatar_url,
                        ],
                        'tasks_completed' => (int) $item->tasks_completed,
                    ];
                });
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'period' => $period,
            ],
        ]);
    }
    
    /**
     * Get user's rank for a specific period
     */
    private function getUserRank($user, $period): array
    {
        if (!$user) {
            return ['rank' => null, 'total_earnings' => 0, 'tasks_completed' => 0];
        }
        
        $query = TaskCompletion::where('user_id', $user->id)
            ->where('status', 'completed');
        
        switch ($period) {
            case 'daily':
                $query->whereDate('created_at', today());
                break;
            case 'weekly':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'monthly':
                $query->where('created_at', '>=', now()->startOfMonth());
                break;
        }
        
        $userStats = $query->selectRaw('SUM(reward_earned) as total_earnings, COUNT(*) as tasks_completed')->first();
        
        $totalEarnings = (float) ($userStats->total_earnings ?? 0);
        $tasksCompleted = (int) ($userStats->tasks_completed ?? 0);
        
        // Calculate rank
        $rankQuery = TaskCompletion::select('user_id', DB::raw('SUM(reward_earned) as total_earnings'))
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->having('total_earnings', '>', $totalEarnings);
        
        switch ($period) {
            case 'daily':
                $rankQuery->whereDate('created_at', today());
                break;
            case 'weekly':
                $rankQuery->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'monthly':
                $rankQuery->where('created_at', '>=', now()->startOfMonth());
                break;
        }
        
        $rank = $rankQuery->get()->count() + 1;
        
        return [
            'rank' => $rank,
            'total_earnings' => $totalEarnings,
            'tasks_completed' => $tasksCompleted,
        ];
    }
    
    /**
     * Mask user name for privacy (show first name + last initial)
     */
    private function maskName(string $name): string
    {
        $parts = explode(' ', trim($name));
        
        if (count($parts) === 1) {
            return $parts[0];
        }
        
        $firstName = $parts[0];
        $lastInitial = strtoupper(substr($parts[count($parts) - 1], 0, 1));
        
        return "{$firstName} {$lastInitial}.";
    }
}
