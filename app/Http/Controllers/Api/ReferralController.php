<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Get referral info
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $referralLink = url('/register?ref=' . $user->referral_code);
        $referralCount = $user->referrals()->count();
        $activeReferrals = $user->referrals()->whereHas('activeSubscription')->count();

        // Calculate total earnings from referrals
        $referralEarnings = $user->wallet?->transactions()
            ->where('reference_type', 'referral')
            ->sum('amount') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => $referralLink,
                'total_referrals' => $referralCount,
                'active_referrals' => $activeReferrals,
                'total_earnings' => $referralEarnings,
                'share_message' => "Jiunge na SKYpesa na upate pesa kwa kutazama matangazo! Tumia code yangu: {$user->referral_code}. {$referralLink}",
            ],
        ]);
    }

    /**
     * Get referred users
     */
    public function referredUsers(Request $request)
    {
        $user = $request->user();

        $referrals = $user->referrals()
            ->select('id', 'name', 'created_at')
            ->withCount(['taskCompletions as tasks_completed' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->latest()
            ->paginate(20);

        $data = $referrals->getCollection()->map(function ($referral) {
            return [
                'id' => $referral->id,
                'name' => $referral->name,
                'tasks_completed' => $referral->tasks_completed,
                'joined_at' => $referral->created_at->toISOString(),
                'is_active' => $referral->tasks_completed > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'total' => $referrals->total(),
            ],
        ]);
    }

    /**
     * Get referral earnings
     */
    public function earnings(Request $request)
    {
        $user = $request->user();

        $transactions = $user->wallet?->transactions()
            ->where('reference_type', 'referral')
            ->latest()
            ->paginate(20);

        if (!$transactions) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => ['total' => 0],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get referral stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $referralsToday = $user->referrals()->whereDate('created_at', $today)->count();
        $referralsThisWeek = $user->referrals()->where('created_at', '>=', $thisWeek)->count();
        $referralsThisMonth = $user->referrals()->where('created_at', '>=', $thisMonth)->count();
        $totalReferrals = $user->referrals()->count();

        // Earnings
        $earningsToday = $user->wallet?->transactions()
            ->where('reference_type', 'referral')
            ->whereDate('created_at', $today)
            ->sum('amount') ?? 0;

        $earningsThisMonth = $user->wallet?->transactions()
            ->where('reference_type', 'referral')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'referrals' => [
                    'today' => $referralsToday,
                    'this_week' => $referralsThisWeek,
                    'this_month' => $referralsThisMonth,
                    'total' => $totalReferrals,
                ],
                'earnings' => [
                    'today' => $earningsToday,
                    'this_month' => $earningsThisMonth,
                ],
            ],
        ]);
    }
}
