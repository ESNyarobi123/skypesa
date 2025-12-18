<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $tasks = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();
        
        return view('tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
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
        
        return view('tasks.show', compact('task'));
    }

    public function complete(Request $request, Task $task)
    {
        $user = auth()->user();
        
        // Validate
        if (!$task->canUserComplete($user) || !$user->canCompleteMoreTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Hauwezi kukamilisha kazi hii.',
            ], 403);
        }
        
        // Get reward amount
        $reward = $task->getRewardFor($user);
        
        try {
            DB::beginTransaction();
            
            // Create task completion record
            $completion = TaskCompletion::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'reward_earned' => $reward,
                'duration_spent' => $request->input('duration', $task->duration_seconds),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'completed',
            ]);
            
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
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena.',
            ], 500);
        }
    }
}
