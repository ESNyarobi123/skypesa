<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TaskLockService
{
    /**
     * Check if user has an active (locked) task
     */
    public function hasActiveTask(User $user): bool
    {
        return TaskCompletion::where('user_id', $user->id)
            ->where('is_locked', true)
            ->where('status', 'in_progress')
            ->exists();
    }

    /**
     * Get the active/locked task for a user
     */
    public function getActiveTask(User $user): ?TaskCompletion
    {
        return TaskCompletion::where('user_id', $user->id)
            ->where('is_locked', true)
            ->where('status', 'in_progress')
            ->with('task')
            ->first();
    }

    /**
     * Get remaining time for user's active task (in seconds)
     */
    public function getRemainingTime(User $user): int
    {
        $activeTask = $this->getActiveTask($user);
        
        if (!$activeTask || !$activeTask->started_at) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($activeTask->started_at);
        $remaining = $activeTask->required_duration - $elapsed;
        
        return max(0, $remaining);
    }

    /**
     * Start a new task (lock it)
     */
    public function startTask(User $user, Task $task): array
    {
        // Check if user already has an active task
        if ($this->hasActiveTask($user)) {
            $activeTask = $this->getActiveTask($user);
            $remaining = $this->getRemainingTime($user);
            
            return [
                'success' => false,
                'message' => 'Una kazi inayoendelea! Subiri sekunde ' . $remaining . ' zimalizike.',
                'remaining_time' => $remaining,
                'active_task' => $activeTask->task,
                'lock_token' => $activeTask->lock_token,
            ];
        }

        // Generate unique lock token
        $lockToken = Str::random(64);

        // Create task completion record with lock
        $completion = TaskCompletion::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status' => 'in_progress',
            'is_locked' => true,
            'lock_token' => $lockToken,
            'started_at' => now(),
            'required_duration' => $task->duration_seconds,
            'reward_earned' => 0,
            'duration_spent' => 0,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info('Task started', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'lock_token' => $lockToken,
            'duration' => $task->duration_seconds,
        ]);

        return [
            'success' => true,
            'message' => 'Kazi imeanza!',
            'lock_token' => $lockToken,
            'completion_id' => $completion->id,
            'duration' => $task->duration_seconds,
            'started_at' => $completion->started_at,
        ];
    }

    /**
     * Validate and complete a task
     */
    public function completeTask(User $user, Task $task, string $lockToken): array
    {
        // Find the locked task
        $completion = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('lock_token', $lockToken)
            ->where('is_locked', true)
            ->where('status', 'in_progress')
            ->first();

        if (!$completion) {
            return [
                'success' => false,
                'message' => 'Kazi hii haitambuliki au imekwisha.',
                'error_code' => 'INVALID_TASK',
            ];
        }

        // Calculate actual time spent (use absolute value to handle timezone issues)
        $startedAt = $completion->started_at;
        $now = now();
        
        // Get the absolute difference in seconds
        $actualDuration = abs($now->timestamp - $startedAt->timestamp);
        
        // Log for debugging
        Log::info('Task completion attempt', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => $startedAt->toDateTimeString(),
            'now' => $now->toDateTimeString(),
            'actual_duration' => $actualDuration,
            'required_duration' => $completion->required_duration,
        ]);
        
        // Allow 5 seconds tolerance for network delays
        $minimumTime = max(0, $completion->required_duration - 5);
        
        if ($actualDuration < $minimumTime) {
            $remaining = $minimumTime - $actualDuration;
            return [
                'success' => false,
                'message' => 'Bado sekunde ' . ceil($remaining) . ' zimebaki. Subiri timer ikamilike.',
                'error_code' => 'TIME_NOT_COMPLETE',
                'remaining' => ceil($remaining),
            ];
        }

        // Mark as completed
        $completion->update([
            'status' => 'completed',
            'is_locked' => false,
            'completed_at' => now(),
            'duration_spent' => $actualDuration,
            'reward_earned' => $task->getRewardFor($user),
        ]);

        Log::info('Task completed', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'duration' => $actualDuration,
            'reward' => $completion->reward_earned,
        ]);

        return [
            'success' => true,
            'message' => 'Kazi imekamilika!',
            'completion' => $completion,
            'reward' => $completion->reward_earned,
            'duration' => $actualDuration,
        ];
    }

    /**
     * Cancel/abandon a task (release lock)
     */
    public function cancelTask(User $user, ?string $lockToken = null): bool
    {
        $query = TaskCompletion::where('user_id', $user->id)
            ->where('is_locked', true)
            ->where('status', 'in_progress');

        if ($lockToken) {
            $query->where('lock_token', $lockToken);
        }

        return $query->update([
            'status' => 'abandoned',
            'is_locked' => false,
            'completed_at' => now(),
        ]) > 0;
    }

    /**
     * Clean up expired locks (tasks started but never completed)
     * Should be run periodically via scheduler
     */
    public function cleanupExpiredLocks(int $maxAgeMinutes = 30): int
    {
        return TaskCompletion::where('is_locked', true)
            ->where('status', 'in_progress')
            ->where('started_at', '<', now()->subMinutes($maxAgeMinutes))
            ->update([
                'status' => 'expired',
                'is_locked' => false,
                'completed_at' => now(),
            ]);
    }

    /**
     * Get user's task activity summary
     */
    public function getActivitySummary(User $user): array
    {
        $activeTask = $this->getActiveTask($user);
        
        return [
            'has_active_task' => $activeTask !== null,
            'active_task' => $activeTask ? [
                'task' => $activeTask->task,
                'started_at' => $activeTask->started_at,
                'remaining_seconds' => $this->getRemainingTime($user),
                'lock_token' => $activeTask->lock_token,
            ] : null,
            'completed_today' => $user->tasksCompletedToday(),
            'remaining_today' => $user->remainingTasksToday(),
        ];
    }
}
