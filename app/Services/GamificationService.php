<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyGoal;
use App\Models\DailyGoalCompletion;
use App\Models\LeaderboardEntry;
use App\Models\TaskCompletion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Gamification Service
 * 
 * Handles:
 * - Welcome Bonus (first task x10)
 * - Daily Goals/Missions
 * - Leaderboard calculations
 */
class GamificationService
{
    // Welcome bonus multiplier for first task
    const WELCOME_BONUS_MULTIPLIER = 10;
    
    // Signup bonus amount (TZS)
    const SIGNUP_BONUS_AMOUNT = 50;

    /**
     * Check if user is eligible for welcome bonus (first task)
     */
    public function isEligibleForWelcomeBonus(User $user): bool
    {
        return !$user->first_task_completed;
    }

    /**
     * Get welcome bonus multiplier (10x for first task)
     */
    public function getWelcomeBonusMultiplier(User $user): int
    {
        if ($this->isEligibleForWelcomeBonus($user)) {
            return self::WELCOME_BONUS_MULTIPLIER;
        }
        return 1;
    }

    /**
     * Mark first task as completed
     */
    public function markFirstTaskCompleted(User $user): void
    {
        if (!$user->first_task_completed) {
            $user->update([
                'first_task_completed' => true,
                'first_task_at' => now(),
            ]);

            Log::info('Welcome bonus applied - First task completed', [
                'user_id' => $user->id,
                'multiplier' => self::WELCOME_BONUS_MULTIPLIER,
            ]);
        }
    }

    /**
     * Award signup bonus to new user
     */
    public function awardSignupBonus(User $user): bool
    {
        if ($user->received_welcome_bonus) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Credit wallet
            $user->wallet->credit(
                self::SIGNUP_BONUS_AMOUNT,
                'bonus',
                null,
                '🎁 Karibu SKYpesa! Bonus ya usajili.'
            );

            $user->update(['received_welcome_bonus' => true]);

            DB::commit();

            Log::info('Signup bonus awarded', [
                'user_id' => $user->id,
                'amount' => self::SIGNUP_BONUS_AMOUNT,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to award signup bonus', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Increment daily goal progress
     */
    public function incrementDailyProgress(User $user): void
    {
        // Reset if new day
        if ($user->last_daily_goal_date !== today()->toDateString()) {
            $user->update([
                'last_daily_goal_date' => today(),
                'daily_goal_progress' => 1,
                'daily_goal_claimed' => false,
            ]);
        } else {
            $user->increment('daily_goal_progress');
        }
    }

    /**
     * Get daily goal data for user
     */
    public function getDailyGoalData(User $user): ?array
    {
        $goal = DailyGoal::getActive();
        
        if (!$goal) {
            return null;
        }

        return $goal->getUserProgress($user);
    }

    /**
     * Claim daily goal bonus
     */
    public function claimDailyGoalBonus(User $user): array
    {
        $goal = DailyGoal::getActive();
        
        if (!$goal) {
            return ['success' => false, 'message' => 'Hakuna goal ya leo.'];
        }

        $progress = $goal->getUserProgress($user);

        if (!$progress['is_complete']) {
            return [
                'success' => false, 
                'message' => 'Bado hujakamilisha goal. Fanya tasks ' . $progress['remaining'] . ' zaidi!'
            ];
        }

        if ($progress['is_claimed']) {
            return ['success' => false, 'message' => 'Umeshachukua bonus hii!'];
        }

        try {
            DB::beginTransaction();

            // Credit wallet
            $user->wallet->credit(
                $goal->bonus_amount,
                'bonus',
                null,
                '🎯 Daily Goal Bonus! Umekamilisha ' . $goal->target_tasks . ' tasks.'
            );

            // Mark as claimed
            $user->update(['daily_goal_claimed' => true]);

            // Record completion
            DailyGoalCompletion::create([
                'user_id' => $user->id,
                'daily_goal_id' => $goal->id,
                'completed_date' => today(),
                'tasks_completed' => $progress['completed'],
                'bonus_earned' => $goal->bonus_amount,
            ]);

            DB::commit();

            Log::info('Daily goal bonus claimed', [
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'bonus' => $goal->bonus_amount,
            ]);

            return [
                'success' => true,
                'message' => '🎉 Hongera! Umepata TZS ' . number_format($goal->bonus_amount) . ' bonus!',
                'bonus_amount' => $goal->bonus_amount,
                'new_balance' => $user->wallet->fresh()->balance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to claim daily goal', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Kuna tatizo. Jaribu tena.'];
        }
    }

    /**
     * Get leaderboard data
     */
    public function getLeaderboard(string $period = 'weekly', int $limit = 10): array
    {
        if ($period === 'monthly') {
            return LeaderboardEntry::getMonthlyLeaderboard($limit);
        }
        return LeaderboardEntry::getWeeklyLeaderboard($limit);
    }

    /**
     * Get user's current rank
     */
    public function getUserRank(User $user, string $period = 'weekly'): ?int
    {
        return LeaderboardEntry::getUserWeeklyRank($user);
    }

    /**
     * Get user's weekly stats
     */
    public function getUserWeeklyStats(User $user): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $stats = TaskCompletion::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('COUNT(*) as tasks, COALESCE(SUM(reward_earned), 0) as earnings')
            ->first();

        return [
            'tasks_completed' => $stats->tasks ?? 0,
            'earnings' => $stats->earnings ?? 0,
            'rank' => $this->getUserRank($user) ?? '-',
        ];
    }
}
