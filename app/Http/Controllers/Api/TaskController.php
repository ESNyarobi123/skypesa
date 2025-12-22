<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected TaskLockService $lockService;

    public function __construct(TaskLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * List available tasks
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($task) use ($user) {
                return $this->taskResource($task, $user);
            });

        $activitySummary = $this->lockService->getActivitySummary($user);

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks,
                'activity' => $activitySummary,
                'stats' => [
                    'completed_today' => $user->tasksCompletedToday(),
                    'daily_limit' => $user->getDailyTaskLimit(),
                    'remaining_today' => $user->remainingTasksToday(),
                    'reward_per_task' => $user->getRewardPerTask(),
                ],
            ],
        ]);
    }

    /**
     * Get single task
     */
    public function show(Request $request, Task $task)
    {
        $user = $request->user();

        if (!$task->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Kazi hii haipatikani kwa sasa',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->taskResource($task, $user),
        ]);
    }

    /**
     * Start task (lock)
     */
    public function start(Request $request, Task $task)
    {
        $user = $request->user();

        // Validate
        if (!$task->canUserComplete($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Umeshakamilisha kazi hii mara nyingi leo',
                'error_code' => 'TASK_LIMIT_REACHED',
            ], 403);
        }

        if (!$user->canCompleteMoreTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Umefika limit ya tasks za leo. Upgrade mpango wako!',
                'error_code' => 'DAILY_LIMIT_REACHED',
            ], 403);
        }

        $result = $this->lockService->startTask($user, $task);

        if (!$result['success']) {
            return response()->json($result, 423);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kazi imeanza!',
            'data' => [
                'lock_token' => $result['lock_token'],
                'duration' => $result['duration'],
                'started_at' => $result['started_at']->toISOString(),
                'task_url' => $task->url,
            ],
        ]);
    }

    /**
     * Check task status
     */
    public function status(Request $request, Task $task)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'lock_token' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $activeTask = \App\Models\TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('lock_token', $request->lock_token)
            ->where('status', 'in_progress')
            ->first();

        if (!$activeTask) {
            return response()->json([
                'success' => false,
                'message' => 'Kazi haitambuliki',
            ], 404);
        }

        // Use absolute timestamp difference to avoid timezone issues
        $elapsed = abs(now()->timestamp - $activeTask->started_at->timestamp);
        
        // Maximum task age is 10 minutes - if older, reset the task
        $maxTaskAge = config('directlinks.max_task_age', 600);
        if ($elapsed > $maxTaskAge) {
            // Task has expired - cancel it
            $this->lockService->cancelTask($user, $activeTask->lock_token);
            return response()->json([
                'success' => false,
                'message' => 'Kazi hii imekwisha muda wake. Anza upya.',
                'expired' => true,
            ], 410);
        }
        
        // Ensure remaining is within valid bounds
        $remaining = max(0, min($activeTask->required_duration - $elapsed, $activeTask->required_duration));

        return response()->json([
            'success' => true,
            'data' => [
                'elapsed' => $elapsed,
                'remaining' => $remaining,
                'required' => $activeTask->required_duration,
                'can_complete' => $remaining <= 0,
                'started_at' => $activeTask->started_at->toISOString(),
            ],
        ]);
    }

    /**
     * Complete task
     */
    public function complete(Request $request, Task $task)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'lock_token' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->lockService->completeTask($user, $task, $request->lock_token);

        if (!$result['success']) {
            $statusCode = $result['error_code'] === 'TIME_NOT_COMPLETE' ? 425 : 400;
            return response()->json($result, $statusCode);
        }

        // Credit wallet
        $completion = $result['completion'];
        $reward = $result['reward'];

        $task->increment('completions_count');
        
        $user->wallet->credit(
            $reward,
            'task_reward',
            $completion,
            'Malipo ya task: ' . $task->title
        );

        return response()->json([
            'success' => true,
            'message' => 'Hongera! Umepata TZS ' . number_format($reward, 0),
            'data' => [
                'reward' => $reward,
                'new_balance' => $user->wallet->fresh()->balance,
                'duration_spent' => $result['duration'],
            ],
        ]);
    }

    /**
     * Cancel task
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $lockToken = $request->input('lock_token');

        $cancelled = $this->lockService->cancelTask($user, $lockToken);

        return response()->json([
            'success' => $cancelled,
            'message' => $cancelled ? 'Kazi imesitishwa' : 'Hakuna kazi ya kusitisha',
        ]);
    }

    /**
     * Get active task
     */
    public function activeTask(Request $request)
    {
        $user = $request->user();
        $summary = $this->lockService->getActivitySummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get task history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $completions = $user->taskCompletions()
            ->with('task')
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $completions->items(),
            'meta' => [
                'current_page' => $completions->currentPage(),
                'last_page' => $completions->lastPage(),
                'per_page' => $completions->perPage(),
                'total' => $completions->total(),
            ],
        ]);
    }

    /**
     * Adsterra postback
     */
    public function adsterraPostback(Request $request)
    {
        // Handle Adsterra conversion postback
        \Log::info('Adsterra postback', $request->all());

        return response()->json(['status' => 'ok']);
    }

    /**
     * Monetag postback
     */
    public function monetagPostback(Request $request)
    {
        // Handle Monetag conversion postback
        \Log::info('Monetag postback', $request->all());

        return response()->json(['status' => 'ok']);
    }

    /**
     * Format task resource
     */
    protected function taskResource(Task $task, $user)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'provider' => $task->provider,
            'url' => $task->url,
            'duration_seconds' => $task->duration_seconds,
            'reward' => $task->getRewardFor($user),
            'daily_limit' => $task->daily_limit,
            'completions_today' => $task->userCompletionsToday($user),
            'can_complete' => $task->canUserComplete($user) && $user->canCompleteMoreTasks(),
            'is_featured' => $task->is_featured,
            'thumbnail' => $task->thumbnail,
            'icon' => $task->icon,
        ];
    }
}
