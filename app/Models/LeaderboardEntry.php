<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Leaderboard Entry Model
 * 
 * Stores leaderboard rankings (weekly/monthly)
 */
class LeaderboardEntry extends Model
{
    protected $fillable = [
        'user_id',
        'period',
        'period_start',
        'period_end',
        'rank',
        'tasks_completed',
        'earnings',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'rank' => 'integer',
        'tasks_completed' => 'integer',
        'earnings' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get weekly leaderboard
     */
    public static function getWeeklyLeaderboard(int $limit = 10): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // Get top earners this week directly from transactions
        $topEarners = TaskCompletion::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('user_id, COUNT(*) as tasks_count, SUM(reward_earned) as total_earnings')
            ->groupBy('user_id')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get();

        $leaderboard = [];
        $rank = 1;

        foreach ($topEarners as $entry) {
            if ($entry->user) {
                $leaderboard[] = [
                    'rank' => $rank,
                    'user' => $entry->user,
                    'tasks_completed' => $entry->tasks_count,
                    'earnings' => $entry->total_earnings,
                    'avatar_initial' => strtoupper(substr($entry->user->name, 0, 1)),
                ];
                $rank++;
            }
        }

        return $leaderboard;
    }

    /**
     * Get monthly leaderboard
     */
    public static function getMonthlyLeaderboard(int $limit = 10): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $topEarners = TaskCompletion::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('user_id, COUNT(*) as tasks_count, SUM(reward_earned) as total_earnings')
            ->groupBy('user_id')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get();

        $leaderboard = [];
        $rank = 1;

        foreach ($topEarners as $entry) {
            if ($entry->user) {
                $leaderboard[] = [
                    'rank' => $rank,
                    'user' => $entry->user,
                    'tasks_completed' => $entry->tasks_count,
                    'earnings' => $entry->total_earnings,
                    'avatar_initial' => strtoupper(substr($entry->user->name, 0, 1)),
                ];
                $rank++;
            }
        }

        return $leaderboard;
    }

    /**
     * Get user's rank this week
     */
    public static function getUserWeeklyRank(User $user): ?int
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $userEarnings = TaskCompletion::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->sum('reward_earned');

        if ($userEarnings == 0) {
            return null;
        }

        // Count how many users have more earnings
        $rank = TaskCompletion::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('user_id, SUM(reward_earned) as total')
            ->groupBy('user_id')
            ->having('total', '>', $userEarnings)
            ->count();

        return $rank + 1;
    }
}
