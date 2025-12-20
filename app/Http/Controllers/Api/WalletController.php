<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get wallet info
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = $user->wallet()->create(['balance' => 0]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'total_earned' => $wallet->total_earned ?? 0,
                'total_withdrawn' => $wallet->total_withdrawn ?? 0,
                'pending_withdrawals' => $user->withdrawals()->where('status', 'pending')->sum('amount'),
            ],
        ]);
    }

    /**
     * Get transactions
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => ['total' => 0],
            ]);
        }

        $type = $request->input('type'); // credit, debit, all
        $query = $wallet->transactions()->latest();

        if ($type === 'credit') {
            $query->where('type', 'credit');
        } elseif ($type === 'debit') {
            $query->where('type', 'debit');
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get single transaction
     */
    public function showTransaction(Request $request, $transactionId)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $transaction = $wallet->transactions()->find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Get earnings summary
     */
    public function earnings(Request $request)
    {
        $user = $request->user();

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $earningsToday = $user->taskCompletions()
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('reward_earned');

        $earningsThisWeek = $user->taskCompletions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisWeek)
            ->sum('reward_earned');

        $earningsThisMonth = $user->taskCompletions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('reward_earned');

        $totalEarnings = $user->taskCompletions()
            ->where('status', 'completed')
            ->sum('reward_earned');

        // Daily breakdown for chart (last 7 days)
        $dailyEarnings = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $amount = $user->taskCompletions()
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('reward_earned');
            
            $dailyEarnings[] = [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'amount' => (float) $amount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $earningsToday,
                'this_week' => $earningsThisWeek,
                'this_month' => $earningsThisMonth,
                'total' => $totalEarnings,
                'daily_breakdown' => $dailyEarnings,
            ],
        ]);
    }
}
