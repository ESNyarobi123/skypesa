<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        
        // Chart data - Last 7 days
        $chartLabels = [];
        $chartEarnings = [];
        $chartCompletions = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D');
            
            $chartEarnings[] = TaskCompletion::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('reward_earned');
            
            $chartCompletions[] = TaskCompletion::whereDate('created_at', $date)->count();
        }
        
        // Subscription distribution
        $subscriptionDistribution = SubscriptionPlan::withCount(['subscriptions' => function($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function($plan) {
                $colors = [
                    'free' => '#71717a',
                    'basic' => '#3b82f6',
                    'standard' => '#8b5cf6',
                    'premium' => '#f59e0b',
                    'vip' => '#10b981',
                ];
                return [
                    'name' => $plan->display_name,
                    'count' => $plan->subscriptions_count,
                    'color' => $colors[$plan->name] ?? '#10b981',
                ];
            });
        
        // Referral stats
        $referredUsersCount = User::whereNotNull('referred_by')->count();
        $activeReferrersCount = User::has('referrals')->count();
        $referralConversionRate = $totalUsers > 0 
            ? round(($referredUsersCount / $totalUsers) * 100, 1) 
            : 0;
        $referralBonusesPaid = Transaction::where('category', 'referral_bonus')->sum('amount');
        
        return view('admin.dashboard-new', compact(
            'totalUsers', 'activeUsers', 'newUsersToday', 'newUsersThisMonth',
            'totalTasks', 'activeTasks', 'completionsToday', 'completionsThisMonth',
            'totalEarnings', 'earningsToday', 'earningsThisMonth',
            'pendingWithdrawals', 'pendingAmount', 'paidThisMonth',
            'recentUsers', 'recentWithdrawals', 'recentCompletions',
            'chartLabels', 'chartEarnings', 'chartCompletions',
            'subscriptionDistribution',
            'referredUsersCount', 'activeReferrersCount', 'referralConversionRate', 'referralBonusesPaid'
        ));
    }
    
    /**
     * Analytics page with detailed trends
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 30);
        
        // Daily user registrations
        $userGrowth = User::where('role', 'user')
            ->where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');
        
        // Daily task completions
        $taskCompletionTrend = TaskCompletion::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(reward_earned) as earnings')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Top performing tasks
        $topTasks = Task::withCount(['completions' => function($q) use ($period) {
                $q->where('created_at', '>=', now()->subDays($period));
            }])
            ->orderByDesc('completions_count')
            ->take(10)
            ->get();
        
        // Top earning users
        $topEarners = User::withSum(['taskCompletions as total_earned' => function($q) use ($period) {
                $q->where('status', 'completed')
                    ->where('created_at', '>=', now()->subDays($period));
            }], 'reward_earned')
            ->where('role', 'user')
            ->orderByDesc('total_earned')
            ->take(10)
            ->get();
        
        // Peak activity hours
        $peakHours = TaskCompletion::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');
        
        return view('admin.analytics', compact(
            'period', 'userGrowth', 'taskCompletionTrend', 'topTasks', 'topEarners', 'peakHours'
        ));
    }
    
    /**
     * Real-time system stats for AJAX refresh
     */
    public function liveStats()
    {
        return response()->json([
            'users_online' => User::where('last_login_at', '>=', now()->subMinutes(5))->count(),
            'tasks_this_hour' => TaskCompletion::where('created_at', '>=', now()->subHour())->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'earnings_today' => TaskCompletion::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('reward_earned'),
        ]);
    }
    
    /**
     * Referral program overview
     */
    public function referrals()
    {
        // Top referrers
        $topReferrers = User::withCount('referrals')
            ->where('role', 'user')
            ->having('referrals_count', '>', 0)
            ->orderByDesc('referrals_count')
            ->take(20)
            ->get();
        
        // Referral chain visualization
        $referralStats = [
            'total_referrals' => User::whereNotNull('referred_by')->count(),
            'referral_rate' => round((User::whereNotNull('referred_by')->count() / max(User::count(), 1)) * 100, 1),
            'avg_referrals_per_user' => round(User::whereNotNull('referred_by')->count() / max(User::has('referrals')->count(), 1), 1),
            'referral_bonuses' => Transaction::where('category', 'referral_bonus')->sum('amount'),
        ];
        
        // Monthly referral trend
        $monthlyReferrals = User::whereNotNull('referred_by')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return view('admin.referrals', compact('topReferrers', 'referralStats', 'monthlyReferrals'));
    }
    
    /**
     * Transaction history
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with('user')->latest();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $transactions = $query->paginate(50);
        
        $statistics = [
            'total_credits' => Transaction::where('type', 'credit')->sum('amount'),
            'total_debits' => Transaction::where('type', 'debit')->sum('amount'),
            'transactions_today' => Transaction::whereDate('created_at', today())->count(),
        ];
        
        return view('admin.transactions', compact('transactions', 'statistics'));
    }
    
    /**
     * System settings
     */
    public function settings()
    {
        return view('admin.settings');
    }
}
