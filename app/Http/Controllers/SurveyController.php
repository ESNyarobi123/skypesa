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
     * Display CPX Research Survey Wall (Frame Integration)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get user stats for display
        $stats = $this->cpxService->getUserStats($user);

        // Check if user is VIP
        $plan = $user->getCurrentPlan();
        $isVip = $plan && in_array($plan->name, config('cpx.vip_plans', []));

        // Generate CPX Offerwall URL (Frame Integration method)
        // Parameters as per CPX Research documentation
        $appId = config('cpx.app_id');
        $secureHash = config('cpx.secure_hash');
        $extUserId = $user->id;
        
        // Generate secure hash: md5({unique_user_id}-{app_secure_hash})
        $secureHashMd5 = md5($extUserId . '-' . $secureHash);
        
        // Build CPX Research iframe URL
        $cpxWallUrl = "https://offers.cpx-research.com/index.php?" . http_build_query([
            'app_id' => $appId,
            'ext_user_id' => $extUserId,
            'secure_hash' => $secureHashMd5,
            'username' => $user->name,
            'email' => $user->email,
            'subid_1' => '', // Optional: can add tracking info
            'subid_2' => '', // Optional: can add more tracking info
        ]);

        return view('surveys.index', compact(
            'stats',
            'isVip',
            'cpxWallUrl'
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
