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
        // Reset if it's a new day
        if ($user->last_daily_goal_date !== today()->toDateString()) {
            $user->update([
                'last_daily_goal_date' => today(),
                'daily_goal_progress' => 0,
                'daily_goal_claimed' => false,
            ]);
        }

        $completed = $user->daily_goal_progress;
        $target = $this->target_tasks;
        $percentage = min(100, ($completed / $target) * 100);

        return [
            'completed' => $completed,
            'target' => $target,
            'percentage' => round($percentage, 1),
            'remaining' => max(0, $target - $completed),
            'is_complete' => $completed >= $target,
            'is_claimed' => $user->daily_goal_claimed,
            'bonus_amount' => $this->bonus_amount,
        ];
    }
}
