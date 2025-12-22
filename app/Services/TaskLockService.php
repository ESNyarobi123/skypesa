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

        // Use absolute timestamp difference to avoid timezone issues
        $startedTimestamp = $activeTask->started_at->timestamp;
        $nowTimestamp = now()->timestamp;
        $elapsed = abs($nowTimestamp - $startedTimestamp);
        
        // Maximum task age is 10 minutes (600 seconds)
        // If task is older than this, it should be reset
        $maxTaskAge = config('directlinks.max_task_age', 600);
        
        if ($elapsed > $maxTaskAge) {
            // Task has expired, auto-cancel it
            $this->cancelTask($user, $activeTask->lock_token);
            return 0;
        }
        
        $remaining = $activeTask->required_duration - $elapsed;
        
        // Ensure remaining is within valid range
        // Can't be negative, and can't be more than required_duration
        return max(0, min($remaining, $activeTask->required_duration));
    }

    /**
     * Start a new task (lock it)
     */
    public function startTask(User $user, Task $task): array
    {
        $ip = request()->ip();
        
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
                'error_code' => 'HAS_ACTIVE_TASK',
            ];
        }

        // Check cooldown (time since last completion)
        $cooldownSeconds = $task->cooldown_seconds ?? config('directlinks.cooldown_seconds', 120);
        $lastCompletion = TaskCompletion::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastCompletion && $cooldownSeconds > 0) {
            $cooldownEnds = $lastCompletion->created_at->addSeconds($cooldownSeconds);
            if (now()->lt($cooldownEnds)) {
                // Use absolute timestamp difference
                $remaining = abs($cooldownEnds->timestamp - now()->timestamp);
                // Ensure remaining is reasonable (max 10 minutes)
                $remaining = min($remaining, 600);
                return [
                    'success' => false,
                    'message' => "Subiri sekunde {$remaining} kabla ya kazi nyingine.",
                    'remaining_time' => $remaining,
                    'error_code' => 'COOLDOWN_ACTIVE',
                ];
            }
        }

        // Check IP daily limit
        $ipDailyLimit = $task->ip_daily_limit ?? config('directlinks.ip_daily_limit', 15);
        if ($ipDailyLimit > 0) {
            $ipTodayCount = TaskCompletion::where('ip_address', $ip)
                ->whereDate('created_at', today())
                ->count();
            
            if ($ipTodayCount >= $ipDailyLimit) {
                Log::warning('IP limit reached', [
                    'ip' => $ip,
                    'user_id' => $user->id,
                    'count' => $ipTodayCount,
                    'limit' => $ipDailyLimit,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Kikomo cha kazi kimefikiwa kwa IP hii. Jaribu kesho.',
                    'error_code' => 'IP_LIMIT_REACHED',
                ];
            }
        }

        // Check task-specific IP limit
        if ($task->ip_daily_limit && $task->ip_daily_limit > 0) {
            $taskIpCount = TaskCompletion::where('ip_address', $ip)
                ->where('task_id', $task->id)
                ->whereDate('created_at', today())
                ->count();
            
            if ($taskIpCount >= $task->ip_daily_limit) {
                return [
                    'success' => false,
                    'message' => 'Umekamilisha kazi hii mara nyingi leo kwa IP hii.',
                    'error_code' => 'TASK_IP_LIMIT_REACHED',
                ];
            }
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
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'provider' => $task->provider,
        ]);

        Log::info('Task started', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'lock_token' => $lockToken,
            'duration' => $task->duration_seconds,
            'ip' => $ip,
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
