<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // User stats
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();
        $newUsersToday = User::where('role', 'user')->whereDate('created_at', today())->count();
        $newUsersThisMonth = User::where('role', 'user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Task stats
        $totalTasks = Task::count();
        $activeTasks = Task::where('is_active', true)->count();
        $completionsToday = TaskCompletion::whereDate('created_at', today())->count();
        $completionsThisMonth = TaskCompletion::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Revenue stats
        $totalEarnings = TaskCompletion::where('status', 'completed')->sum('reward_earned');
        $earningsToday = TaskCompletion::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('reward_earned');
        $earningsThisMonth = TaskCompletion::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('reward_earned');
        
        // Withdrawal stats
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingAmount = Withdrawal::where('status', 'pending')->sum('net_amount');
        $paidThisMonth = Withdrawal::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('net_amount');
        
        // Recent activities
        $recentUsers = User::where('role', 'user')
            ->latest()
            ->take(5)
            ->get();
        
        $recentWithdrawals = Withdrawal::with('user')
            ->latest()
            ->take(5)
            ->get();
        
        $recentCompletions = TaskCompletion::with(['user', 'task'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsers', 'activeUsers', 'newUsersToday', 'newUsersThisMonth',
            'totalTasks', 'activeTasks', 'completionsToday', 'completionsThisMonth',
            'totalEarnings', 'earningsToday', 'earningsThisMonth',
            'pendingWithdrawals', 'pendingAmount', 'paidThisMonth',
            'recentUsers', 'recentWithdrawals', 'recentCompletions'
        ));
    }
}
