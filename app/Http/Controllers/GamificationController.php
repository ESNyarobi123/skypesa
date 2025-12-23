<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    protected GamificationService $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    /**
     * Leaderboard page
     */
    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $user = auth()->user();

        $leaderboard = $this->gamification->getLeaderboard($period);
        $userStats = $this->gamification->getUserWeeklyStats($user);
        
        return view('leaderboard.index', compact('leaderboard', 'userStats', 'period'));
    }

    /**
     * Get daily goal data (API)
     */
    public function getDailyGoal()
    {
        $user = auth()->user();
        $goalData = $this->gamification->getDailyGoalData($user);

        if (!$goalData) {
            return response()->json(['success' => false, 'message' => 'No active goal']);
        }

        return response()->json([
            'success' => true,
            'goal' => $goalData,
        ]);
    }

    /**
     * Claim daily goal bonus
     */
    public function claimDailyGoal()
    {
        $user = auth()->user();
        $result = $this->gamification->claimDailyGoalBonus($user);

        return response()->json($result);
    }

    /**
     * Get leaderboard data (API)
     */
    public function getLeaderboardData(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $leaderboard = $this->gamification->getLeaderboard($period);

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
        ]);
    }
}
