<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyCompletion;
use App\Models\User;
use App\Services\CpxResearchService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected $cpxService;

    public function __construct(CpxResearchService $cpxService)
    {
        $this->cpxService = $cpxService;
    }

    /**
     * Survey dashboard and completions list
     */
    public function index(Request $request)
    {
        $query = SurveyCompletion::with('user')->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('survey_type', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $completions = $query->paginate(20);

        // Stats
        $stats = [
            'total_completions' => SurveyCompletion::count(),
            'today_completions' => SurveyCompletion::whereDate('created_at', today())->count(),
            'total_credited' => SurveyCompletion::where('status', 'credited')->sum('user_reward'),
            'today_credited' => SurveyCompletion::where('status', 'credited')->whereDate('created_at', today())->sum('user_reward'),
            'cpx_earnings' => SurveyCompletion::where('status', 'credited')->sum('cpx_payout'),
            'pending_count' => SurveyCompletion::where('status', 'pending')->count(),
            'by_type' => [
                'short' => SurveyCompletion::where('survey_type', 'short')->where('status', 'credited')->count(),
                'medium' => SurveyCompletion::where('survey_type', 'medium')->where('status', 'credited')->count(),
                'long' => SurveyCompletion::where('survey_type', 'long')->where('status', 'credited')->count(),
            ],
        ];

        // Configuration status
        $config = [
            'enabled' => config('cpx.enabled'),
            'demo_mode' => config('cpx.demo_mode'),
            'is_configured' => $this->cpxService->isConfigured(),
            'postback_url' => route('api.webhooks.cpx'),
        ];

        return view('admin.surveys.index', compact('completions', 'stats', 'config'));
    }

    /**
     * Show completion details
     */
    public function show(SurveyCompletion $completion)
    {
        $completion->load('user');
        return view('admin.surveys.show', compact('completion'));
    }

    /**
     * Survey settings page
     */
    public function settings()
    {
        $config = [
            'enabled' => config('cpx.enabled'),
            'demo_mode' => config('cpx.demo_mode'),
            'app_id' => config('cpx.app_id'),
            'daily_limit' => config('cpx.daily_limit_per_user'),
            'rewards' => config('cpx.rewards'),
            'postback_url' => route('api.webhooks.cpx'),
        ];

        return view('admin.surveys.settings', compact('config'));
    }

    /**
     * Manually credit a completion
     */
    public function credit(SurveyCompletion $completion)
    {
        if ($completion->status === 'credited') {
            return back()->with('error', 'Survey hii tayari imelipwa');
        }

        $user = $completion->user;
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = $user->wallet()->create(['balance' => 0]);
        }

        // Credit wallet
        $wallet->increment('balance', $completion->user_reward);

        // Create transaction
        \App\Models\Transaction::create([
            'user_id' => $user->id,
            'type' => 'survey_reward',
            'amount' => $completion->user_reward,
            'balance_after' => $wallet->balance,
            'description' => "Survey {$completion->getTypeLabel()} - #{$completion->survey_id}",
            'reference_type' => SurveyCompletion::class,
            'reference_id' => $completion->id,
            'status' => 'completed',
        ]);

        // Update status
        $completion->update([
            'status' => 'credited',
            'credited_at' => now(),
        ]);

        return back()->with('success', 'Survey imelipwa kikamilifu');
    }

    /**
     * Reject a completion
     */
    public function reject(Request $request, SurveyCompletion $completion)
    {
        if (!in_array($completion->status, ['pending', 'completed'])) {
            return back()->with('error', 'Status haiwezi kubadilishwa');
        }

        $completion->update(['status' => 'rejected']);

        return back()->with('success', 'Survey imekataliwa');
    }

    /**
     * Reverse a credited completion
     */
    public function reverse(SurveyCompletion $completion)
    {
        if ($completion->status !== 'credited') {
            return back()->with('error', 'Tu credited surveys zinaweza reversed');
        }

        $user = $completion->user;
        $wallet = $user->wallet;

        if ($wallet && $wallet->balance >= $completion->user_reward) {
            $wallet->decrement('balance', $completion->user_reward);

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'survey_reversal',
                'amount' => -$completion->user_reward,
                'balance_after' => $wallet->balance,
                'description' => "Survey Reversal - #{$completion->survey_id}",
                'reference_type' => SurveyCompletion::class,
                'reference_id' => $completion->id,
                'status' => 'completed',
            ]);

            $completion->update(['status' => 'reversed']);

            return back()->with('success', 'Survey imerudishwa');
        }

        return back()->with('error', 'Balance haitoshi kwa reversal');
    }

    /**
     * Get survey analytics
     */
    public function analytics(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Daily completions
        $dailyCompletions = SurveyCompletion::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(user_reward) as total_reward')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'credited')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // By type distribution
        $typeDistribution = SurveyCompletion::selectRaw('survey_type, COUNT(*) as count')
            ->where('status', 'credited')
            ->groupBy('survey_type')
            ->get();

        // Top earners
        $topEarners = User::selectRaw('users.*, SUM(survey_completions.user_reward) as total_earned, COUNT(survey_completions.id) as survey_count')
            ->join('survey_completions', 'users.id', '=', 'survey_completions.user_id')
            ->where('survey_completions.status', 'credited')
            ->groupBy('users.id')
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        return view('admin.surveys.analytics', compact('dailyCompletions', 'typeDistribution', 'topEarners', 'days'));
    }
}
