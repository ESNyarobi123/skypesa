<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class TaskDistributionService
{
    /**
     * Get tasks with dynamic limits based on user's plan
     * 
     * The total of all task limits will equal the user's daily plan limit
     * This ensures fair distribution based on subscription plan
     */
    public function getTasksForUser(User $user, ?string $provider = null): array
    {
        $planLimit = $user->getDailyTaskLimit();
        
        // Get all available tasks
        $query = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order');
        
        // Filter by provider if specified
        if ($provider && in_array($provider, ['monetag', 'adsterra'])) {
            $query->where('provider', $provider);
        }
        
        $allTasks = $query->get();
        
        // If unlimited plan (VIP), return all tasks with their original limits
        if (is_null($planLimit)) {
            return [
                'tasks' => $allTasks->map(fn($task) => $this->formatTask($task, $user, null)),
                'plan_info' => [
                    'name' => $user->getPlanName(),
                    'daily_limit' => null,
                    'is_unlimited' => true,
                    'tasks_shown' => $allTasks->count(),
                    'total_slots' => null,
                ],
            ];
        }
        
        // Calculate how many tasks to show and their limits
        $distribution = $this->calculateDistribution($allTasks, $planLimit);
        
        // Format tasks with dynamic limits
        $formattedTasks = collect($distribution['tasks'])->map(function ($item) use ($user) {
            return $this->formatTask($item['task'], $user, $item['dynamic_limit']);
        });
        
        return [
            'tasks' => $formattedTasks,
            'plan_info' => [
                'name' => $user->getPlanName(),
                'daily_limit' => $planLimit,
                'is_unlimited' => false,
                'tasks_shown' => $formattedTasks->count(),
                'total_slots' => $distribution['total_slots'],
            ],
        ];
    }
    
    /**
     * Calculate optimal distribution of tasks and limits
     * 
     * Strategy:
     * 1. Determine ideal number of tasks to show (variety matters!)
     * 2. Distribute the plan limit evenly across tasks
     * 3. Handle remainder to ensure total = plan limit exactly
     */
    protected function calculateDistribution(Collection $allTasks, int $planLimit): array
    {
        $taskCount = $allTasks->count();
        
        if ($taskCount === 0) {
            return ['tasks' => [], 'total_slots' => 0];
        }
        
        // Determine optimal number of tasks to show
        // We want variety but not too many - aim for 4-10 tasks depending on plan
        $idealTaskCount = $this->getIdealTaskCount($planLimit);
        $tasksToShow = min($idealTaskCount, $taskCount);
        
        // Calculate base limit per task
        $baseLimit = intdiv($planLimit, $tasksToShow);
        $remainder = $planLimit % $tasksToShow;
        
        // Ensure minimum 1 per task
        if ($baseLimit < 1) {
            $tasksToShow = $planLimit;
            $baseLimit = 1;
            $remainder = 0;
        }
        
        // Take only the tasks we need
        $selectedTasks = $allTasks->take($tasksToShow);
        
        // Distribute limits
        $distribution = [];
        $totalSlots = 0;
        
        foreach ($selectedTasks as $index => $task) {
            // Add 1 extra to first few tasks to handle remainder
            $dynamicLimit = $baseLimit + ($index < $remainder ? 1 : 0);
            
            $distribution[] = [
                'task' => $task,
                'dynamic_limit' => $dynamicLimit,
            ];
            
            $totalSlots += $dynamicLimit;
        }
        
        return [
            'tasks' => $distribution,
            'total_slots' => $totalSlots,
        ];
    }
    
    /**
     * Get ideal number of tasks to show based on plan limit
     * 
     * This balances variety (more tasks) with simplicity (fewer tasks)
     */
    protected function getIdealTaskCount(int $planLimit): int
    {
        // Mapping: plan limit -> ideal task count
        // Goal: Each task should have 4-10 completions ideally
        if ($planLimit <= 20) {
            return 5;  // Free: 5 tasks × 4 = 20
        } elseif ($planLimit <= 40) {
            return 8;  // Starter: 8 tasks × 5 = 40
        } elseif ($planLimit <= 80) {
            return 10; // Silver: 10 tasks × 8 = 80
        } elseif ($planLimit <= 120) {
            return 12; // Gold: 12 tasks × 10 = 120
        } else {
            return 15; // Any higher plan
        }
    }
    
    /**
     * Format task with user-specific data
     */
    protected function formatTask(Task $task, User $user, ?int $dynamicLimit): array
    {
        $completionsToday = $task->userCompletionsToday($user);
        
        // For VIP/unlimited users, effective limit should be null (unlimited)
        $isUnlimited = is_null($user->getDailyTaskLimit());
        $effectiveLimit = $isUnlimited ? null : ($dynamicLimit ?? $task->daily_limit);
        
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'provider' => $task->provider,
            'duration_seconds' => $task->duration_seconds,
            'reward' => $task->getRewardFor($user),
            'is_featured' => $task->is_featured,
            'thumbnail' => $task->thumbnail,
            'icon' => $task->icon,
            // Dynamic limit based on plan (null = unlimited for VIP)
            'daily_limit' => $effectiveLimit,
            'completions_today' => $completionsToday,
            'remaining' => $effectiveLimit ? max(0, $effectiveLimit - $completionsToday) : null,
            'can_complete' => $this->canUserCompleteTask($user, $task, $dynamicLimit),
            'is_unlimited' => $isUnlimited,
            // Original task object for routing
            'task' => $task,
        ];
    }
    
    /**
     * Check if user can complete this task based on dynamic limits
     */
    public function canUserCompleteTask(User $user, Task $task, ?int $dynamicLimit = null): bool
    {
        // VIP/Unlimited users can always complete tasks (no daily limit)
        $planLimit = $user->getDailyTaskLimit();
        if (is_null($planLimit)) {
            // VIP has no restrictions - just check if task is available
            return $task->isAvailable();
        }
        
        // Check if user has reached their overall daily limit
        if (!$user->canCompleteMoreTasks()) {
            return false;
        }
        
        // Check task-specific limit (only for non-VIP users)
        $completionsToday = $task->userCompletionsToday($user);
        $effectiveLimit = $dynamicLimit ?? $task->daily_limit;
        
        if ($effectiveLimit !== null && $completionsToday >= $effectiveLimit) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get dynamic limit for a specific task for a user
     * Used when validating task start/completion
     */
    public function getDynamicLimitForTask(User $user, Task $task): ?int
    {
        $planLimit = $user->getDailyTaskLimit();
        
        // Unlimited plan gets original task limit
        if (is_null($planLimit)) {
            return $task->daily_limit;
        }
        
        // Get all available tasks to calculate distribution
        $allTasks = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();
        
        $distribution = $this->calculateDistribution($allTasks, $planLimit);
        
        // Find this task in the distribution
        foreach ($distribution['tasks'] as $item) {
            if ($item['task']->id === $task->id) {
                return $item['dynamic_limit'];
            }
        }
        
        // Task not in distribution (user exceeded visible tasks)
        return 0;
    }
    
    /**
     * Get provider counts based on user's plan
     */
    public function getProviderCounts(User $user): array
    {
        $result = $this->getTasksForUser($user);
        $tasks = $result['tasks'];
        
        $allCount = 0;
        $monetagCount = 0;
        $adsterraCount = 0;
        
        foreach ($tasks as $task) {
            $slots = $task['daily_limit'] ?? 1;
            $allCount += $slots;
            
            if ($task['provider'] === 'monetag') {
                $monetagCount += $slots;
            } elseif ($task['provider'] === 'adsterra') {
                $adsterraCount += $slots;
            }
        }
        
        return [
            'all' => $allCount,
            'monetag' => $monetagCount,
            'adsterra' => $adsterraCount,
        ];
    }
}
