<?php

namespace App\Http\Controllers;

use App\Services\BitLabsService;
use App\Models\SurveyCompletion;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected $bitLabsService;

    public function __construct(BitLabsService $bitLabsService)
    {
        $this->bitLabsService = $bitLabsService;
    }

    /**
     * Display BitLabs Survey Wall (Frame Integration)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get user stats for display
        $stats = $this->bitLabsService->getUserStats($user);

        // Check if user is VIP
        $plan = $user->getCurrentPlan();
        $isVip = $plan && in_array(strtolower($plan->name), config('bitlabs.vip_plans', []));

        // Generate BitLabs Offerwall URL (Frame Integration method)
        $apiToken = config('bitlabs.api_token');
        
        // Build BitLabs iframe URL
        $bitlabsWallUrl = "https://web.bitlabs.ai?" . http_build_query([
            'token' => $apiToken,
            'uid' => $user->id,
            'username' => $user->name,
            'email' => $user->email,
            'country' => 'TZ',
        ]);

        return view('surveys.index', compact(
            'stats',
            'isVip',
            'bitlabsWallUrl'
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

        $stats = $this->bitLabsService->getUserStats($user);

        return view('surveys.history', compact('completions', 'stats'));
    }

    /**
     * Demo complete (for testing)
     */
    public function demoComplete(Request $request, string $id)
    {
        if (!config('bitlabs.demo_mode')) {
            return redirect()->back()->with('error', 'Demo mode imezimwa');
        }

        $user = auth()->user();
        $result = $this->bitLabsService->processDemoCompletion($user, $id);

        if ($result['success']) {
            return redirect()->route('surveys.index')
                ->with('success', 'Survey imekamilika! TZS imewekwa kwenye wallet yako.');
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
