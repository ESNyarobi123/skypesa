<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Transaction;
use App\Services\TaskDistributionService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected TaskDistributionService $distributionService;

    public function __construct(TaskDistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Get available tasks with dynamic limits using TaskDistributionService
        $result = $this->distributionService->getTasksForUser($user);
        
        // Take only first 6 tasks for dashboard display
        $tasks = collect($result['tasks'])->take(6);
        $planInfo = $result['plan_info'];
        
        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('dashboard', compact('tasks', 'planInfo', 'recentTransactions'));
    }
}
