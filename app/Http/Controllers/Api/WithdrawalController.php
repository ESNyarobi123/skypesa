<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    /**
     * List user's withdrawals
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $withdrawals = $user->withdrawals()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ],
        ]);
    }

    /**
     * Get withdrawal info (limits, fees)
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;
        $plan = $subscription?->plan;

        $balance = $user->wallet?->balance ?? 0;
        $minWithdrawal = $plan?->min_withdrawal ?? 5000;
        $withdrawalFee = $plan?->withdrawal_fee_percent ?? 20;

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'min_withdrawal' => $minWithdrawal,
                'withdrawal_fee_percent' => $withdrawalFee,
                'can_withdraw' => $balance >= $minWithdrawal,
                'pending_withdrawals' => $user->withdrawals()->where('status', 'pending')->count(),
                'payment_methods' => [
                    ['id' => 'mpesa', 'name' => 'M-Pesa', 'icon' => 'phone'],
                    ['id' => 'tigopesa', 'name' => 'Tigo Pesa', 'icon' => 'phone'],
                    ['id' => 'airtel', 'name' => 'Airtel Money', 'icon' => 'phone'],
                    ['id' => 'halopesa', 'name' => 'Halo Pesa', 'icon' => 'phone'],
                ],
            ],
        ]);
    }

    /**
     * Create withdrawal request
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100',
            'payment_method' => 'required|in:mpesa,tigopesa,airtel,halopesa',
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $wallet = $user->wallet;
        $amount = $request->amount;
        $subscription = $user->activeSubscription;
        $plan = $subscription?->plan;

        $minWithdrawal = $plan?->min_withdrawal ?? 5000;
        $feePercent = $plan?->withdrawal_fee_percent ?? 20;

        // Validations
        if ($wallet->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Salio haitoshi',
            ], 400);
        }

        if ($amount < $minWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => "Kiwango cha chini ni TZS " . number_format($minWithdrawal),
            ], 400);
        }

        // Check pending withdrawals
        $pendingCount = $user->withdrawals()->where('status', 'pending')->count();
        if ($pendingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Una ombi lingine linalosubiri. Subiri likamilike kwanza.',
            ], 400);
        }

        // Calculate fee
        $fee = ($amount * $feePercent) / 100;
        $netAmount = $amount - $fee;

        // Create withdrawal
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'payment_method' => $request->payment_method,
            'phone_number' => $request->phone_number,
            'status' => 'pending',
        ]);

        // Debit wallet
        $wallet->debit($amount, 'withdrawal', $withdrawal, 'Withdrawal request');

        return response()->json([
            'success' => true,
            'message' => 'Ombi lako limepokelewa. Utapata pesa ndani ya masaa 24-48.',
            'data' => $withdrawal,
        ], 201);
    }

    /**
     * Get single withdrawal
     */
    public function show(Request $request, Withdrawal $withdrawal)
    {
        $user = $request->user();

        if ($withdrawal->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $withdrawal,
        ]);
    }

    /**
     * Cancel pending withdrawal
     */
    public function cancel(Request $request, Withdrawal $withdrawal)
    {
        $user = $request->user();

        if ($withdrawal->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Ombi hili haliwezi kufutwa',
            ], 400);
        }

        // Refund to wallet
        $user->wallet->credit($withdrawal->amount, 'withdrawal_refund', $withdrawal, 'Withdrawal cancelled');

        // Update status
        $withdrawal->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Ombi limefutwa na pesa imerudi kwenye wallet',
        ]);
    }
}
