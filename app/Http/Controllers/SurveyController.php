<?php

namespace App\Http\Controllers;

use App\Services\CpxResearchService;
use App\Models\SurveyCompletion;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected $cpxService;

    public function __construct(CpxResearchService $cpxService)
    {
        $this->cpxService = $cpxService;
    }

    /**
     * Display available surveys for user
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get surveys from CPX
        $result = $this->cpxService->getSurveys(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        $surveys = $result['surveys'] ?? [];
        $stats = $this->cpxService->getUserStats($user);

        // Categorize surveys
        $shortSurveys = collect($surveys)->where('type', 'short')->values();
        $mediumSurveys = collect($surveys)->where('type', 'medium')->values();
        $longSurveys = collect($surveys)->where('type', 'long')->values();

        // Check if user is VIP
        $plan = $user->getCurrentPlan();
        $isVip = $plan && in_array($plan->name, config('cpx.vip_plans', []));

        return view('surveys.index', compact(
            'surveys',
            'shortSurveys',
            'mediumSurveys',
            'longSurveys',
            'stats',
            'isVip',
            'result'
        ));
    }

    /**
     * Show survey history
     */
    public function history()
    {
        $user = auth()->user();

        $completions = SurveyCompletion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = $this->cpxService->getUserStats($user);

        return view('surveys.history', compact('completions', 'stats'));
    }

    /**
     * Demo complete (for testing)
     */
    public function demoComplete(Request $request, string $id)
    {
        if (!config('cpx.demo_mode')) {
            return redirect()->back()->with('error', 'Demo mode imezimwa');
        }

        $user = auth()->user();
        $result = $this->cpxService->processDemoCompletion($user, $id);

        if ($result['success']) {
            return redirect()->route('surveys.index')
                ->with('success', 'Survey imekamilika! TZS imewekwa kwenye wallet yako.');
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
