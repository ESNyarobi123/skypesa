<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Daily Goal Completion Model
 * 
 * Tracks when users complete daily goals (history)
 */
class DailyGoalCompletion extends Model
{
    protected $fillable = [
        'user_id',
        'daily_goal_id',
        'completed_date',
        'tasks_completed',
        'bonus_earned',
    ];

    protected $casts = [
        'completed_date' => 'date',
        'tasks_completed' => 'integer',
        'bonus_earned' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyGoal(): BelongsTo
    {
        return $this->belongsTo(DailyGoal::class);
    }
}
