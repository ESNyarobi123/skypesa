<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daily Goal Model
 * 
 * Defines daily challenges for users.
 * Example: "Complete 15 tasks today, earn TZS 50 bonus"
 */
class DailyGoal extends Model
{
    protected $fillable = [
        'name',
        'description',
        'target_tasks',
        'bonus_amount',
        'icon',
        'color',
        'is_active',
    ];

    protected $casts = [
        'target_tasks' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all completions of this goal
     */
    public function completions(): HasMany
    {
        return $this->hasMany(DailyGoalCompletion::class);
    }

    /**
     * Get the active daily goal
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get user's progress for today
     */
    public function getUserProgress(User $user): array
    {
        // Always get the actual count of tasks completed today for 100% accuracy
        $completed = $user->taskCompletions()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
            
        // Sync the user's progress field and date if needed (for other parts of the system)
        if ($user->daily_goal_progress !== $completed || $user->last_daily_goal_date !== today()->toDateString()) {
            $user->update([
                'daily_goal_progress' => $completed,
                'last_daily_goal_date' => today(),
            ]);
        }

        $target = $this->target_tasks;
        $percentage = $target > 0 ? min(100, ($completed / $target) * 100) : 0;

        return [
            'completed' => $completed,
            'target' => $target,
            'percentage' => round($percentage, 1),
            'remaining' => max(0, $target - $completed),
            'is_complete' => $completed >= $target,
            'is_claimed' => $user->daily_goal_claimed ?? false,
            'bonus_amount' => $this->bonus_amount,
        ];
    }
}
