<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCompletion;
use App\Services\TaskLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected TaskLockService $lockService;

    public function __construct(TaskLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $provider = $request->query('provider');
        
        $query = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order');
        
        // Filter by provider if specified
        if ($provider && in_array($provider, ['monetag', 'adsterra'])) {
            $query->where('provider', $provider);
        }
        
        $tasks = $query->get();

        // Get user's active task status
        $activitySummary = $this->lockService->getActivitySummary($user);
        
        // Get task counts per provider
        $providerCounts = [
            'all' => Task::available()->count(),
            'monetag' => Task::available()->where('provider', 'monetag')->count(),
            'adsterra' => Task::available()->where('provider', 'adsterra')->count(),
        ];
        
        return view('tasks.index', compact('tasks', 'activitySummary', 'provider', 'providerCounts'));
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
        // Check if user has an active task - they MUST complete it first
        if ($this->lockService->hasActiveTask($user)) {
            $activeTask = $this->lockService->getActiveTask($user);
            $remaining = $this->lockService->getRemainingTime($user);
            $remaining = max(0, min($remaining, $activeTask->task->duration_seconds ?? 60));
            
            // Always redirect to the active task - no exceptions
            // User must complete current task before starting another
            if ($activeTask->task_id !== $task->id) {
                return redirect()->route('tasks.show', $activeTask->task)
                    ->with('warning', 'Kamilisha kazi inayoendelea kwanza! Sekunde ' . $remaining . ' zimebaki.');
            }
            
            // If they're viewing the same task they already started, show it with the existing lock
            // This allows them to continue without restarting
            return view('tasks.show', [
                'task' => $task,
                'lockToken' => $activeTask->lock_token,
                'remaining' => $remaining,
                'isActive' => true,
            ]);
        }
        
        if (!$task->isAvailable()) {
            return redirect()->route('tasks.index')
                ->with('error', 'Kazi hii haipatikani kwa sasa.');
        }
        
        if (!$task->canUserComplete($user)) {
            return redirect()->route('tasks.index')
                ->with('error', 'Umeshakamilisha kazi hii mara nyingi leo.');
        }
        
        if (!$user->canCompleteMoreTasks()) {
            return redirect()->route('tasks.index')
                ->with('error', 'Umefika limit ya tasks za leo. Upgrade mpango wako!');
        }
        
        return view('tasks.show', [
            'task' => $task,
            'lockToken' => null,
            'remaining' => $task->duration_seconds,
            'isActive' => false,
        ]);
    }

    /**
     * Start a task (create lock)
     */
    public function start(Request $request, Task $task)
    {
        $user = auth()->user();
        
        // Validate user can start this task
        if (!$task->canUserComplete($user) || !$user->canCompleteMoreTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Hauwezi kuanza kazi hii.',
                'error_code' => 'LIMIT_REACHED',
            ], 403);
        }
        
        $result = $this->lockService->startTask($user, $task);
        
        if (!$result['success']) {
            return response()->json($result, 423); // 423 Locked
        }
        
        return response()->json($result);
    }

    /**
     * Check task status (is timer complete?)
     */
    public function status(Request $request, Task $task)
    {
        $user = auth()->user();
        
        $request->validate([
            'lock_token' => 'required|string|size:64',
        ]);
        
        $activeTask = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('lock_token', $request->lock_token)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$activeTask) {
            return response()->json([
                'success' => false,
                'message' => 'Kazi haitambuliki.',
                'can_complete' => false,
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
                'can_complete' => false,
                'expired' => true,
            ], 410); // 410 Gone
        }
        
        $remaining = max(0, min($activeTask->required_duration - $elapsed, $activeTask->required_duration));
        $canComplete = $remaining <= 0;
        
        return response()->json([
            'success' => true,
            'elapsed' => $elapsed,
            'remaining' => $remaining,
            'required' => $activeTask->required_duration,
            'can_complete' => $canComplete,
            'started_at' => $activeTask->started_at->toISOString(),
        ]);
    }

    /**
     * Complete a task
     */
    public function complete(Request $request, Task $task)
    {
        $user = auth()->user();
        
        $request->validate([
            'lock_token' => 'required|string|size:64',
        ]);
        
        // Use lock service to validate and complete
        $result = $this->lockService->completeTask($user, $task, $request->lock_token);
        
        if (!$result['success']) {
            $statusCode = $result['error_code'] === 'TIME_NOT_COMPLETE' ? 425 : 400;
            return response()->json($result, $statusCode);
        }
        
        try {
            DB::beginTransaction();
            
            $completion = $result['completion'];
            $reward = $result['reward'];
            
            // Increment task completions count
            $task->increment('completions_count');
            
            // Credit user wallet
            $user->wallet->credit(
                $reward,
                'task_reward',
                $completion,
                'Malipo ya task: ' . $task->title
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Hongera! Umepata TZS ' . number_format($reward, 0),
                'reward' => $reward,
                'new_balance' => $user->wallet->fresh()->balance,
                'duration' => $result['duration'],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Revert completion status
            $result['completion']->update([
                'status' => 'in_progress',
                'is_locked' => true,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena.',
            ], 500);
        }
    }

    /**
     * Cancel/abandon a task
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();
        $lockToken = $request->input('lock_token');
        
        $cancelled = $this->lockService->cancelTask($user, $lockToken);
        
        return response()->json([
            'success' => $cancelled,
            'message' => $cancelled ? 'Kazi imesitishwa.' : 'Hakuna kazi ya kusitisha.',
        ]);
    }

    /**
     * Get user's current task activity status (API)
     */
    public function activityStatus()
    {
        $user = auth()->user();
        $summary = $this->lockService->getActivitySummary($user);
        
        return response()->json($summary);
    }
}
