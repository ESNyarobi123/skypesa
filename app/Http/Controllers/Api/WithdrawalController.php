<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    protected FraudDetectionService $fraudService;

    public function __construct(FraudDetectionService $fraudService)
    {
        $this->fraudService = $fraudService;
    }

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
        $wallet = $user->wallet;
        $plan = $user->getCurrentPlan();

        $balance = $wallet?->getAvailableBalance() ?? 0;
        $pendingWithdrawal = $wallet?->pending_withdrawal ?? 0;
        $minWithdrawal = $plan?->min_withdrawal ?? 5000;
        $withdrawalFee = $plan?->withdrawal_fee_percent ?? 20;

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'pending_withdrawal' => $pendingWithdrawal,
                'min_withdrawal' => $minWithdrawal,
                'withdrawal_fee_percent' => $withdrawalFee,
                'can_withdraw' => $balance >= $minWithdrawal,
                'pending_withdrawals_count' => $user->withdrawals()->where('status', 'pending')->count(),
                'user_name' => $user->name,
                'user_phone' => $user->phone,
                'payment_providers' => [
                    ['id' => 'mpesa', 'name' => 'M-Pesa', 'color' => '#e11d48'],
                    ['id' => 'tigopesa', 'name' => 'Tigo Pesa', 'color' => '#0ea5e9'],
                    ['id' => 'airtelmoney', 'name' => 'Airtel Money', 'color' => '#dc2626'],
                    ['id' => 'halopesa', 'name' => 'Halo Pesa', 'color' => '#f97316'],
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
        $wallet = $user->wallet;
        $plan = $user->getCurrentPlan();

        $minWithdrawal = $plan?->min_withdrawal ?? 5000;
        $feePercent = $plan?->withdrawal_fee_percent ?? 20;
        $availableBalance = $wallet?->getAvailableBalance() ?? 0;

        $validator = Validator::make($request->all(), [
            'amount' => "required|numeric|min:{$minWithdrawal}|max:{$availableBalance}",
            'payment_provider' => 'required|in:mpesa,tigopesa,airtelmoney,halopesa',
            'payment_number' => 'required|string|min:10|max:15',
            'payment_name' => 'required|string|min:3|max:100',
        ], [
            'amount.required' => 'Tafadhali weka kiasi.',
            'amount.min' => "Kiwango cha chini ni TZS " . number_format($minWithdrawal, 0),
            'amount.max' => 'Salio lako halitoshi.',
            'payment_provider.required' => 'Tafadhali chagua mtoa huduma.',
            'payment_provider.in' => 'Mtoa huduma uliochagua haupo.',
            'payment_number.required' => 'Tafadhali weka namba ya simu.',
            'payment_number.min' => 'Namba ya simu ni fupi sana.',
            'payment_name.required' => 'Tafadhali weka jina kamili la mwenye akaunti.',
            'payment_name.min' => 'Jina liwe na angalau herufi 3.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check pending withdrawals
        $pendingCount = $user->withdrawals()->where('status', 'pending')->count();
        if ($pendingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Una ombi lingine linalosubiri. Subiri likamilike kwanza.',
            ], 400);
        }

        $amount = $request->amount;
        $fee = ($amount * $feePercent) / 100;
        $netAmount = $amount - $fee;

        try {
            DB::beginTransaction();

            // Create withdrawal request
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'payment_method' => 'mobile_money',
                'payment_number' => $request->payment_number,
                'payment_name' => $request->payment_name,
                'payment_provider' => $request->payment_provider,
                'status' => 'pending',
            ]);

            // Assess withdrawal risk using FraudDetectionService
            $riskAssessment = $this->fraudService->assessWithdrawalRisk($withdrawal);

            // Determine delay based on risk
            $delayHours = $riskAssessment['delay_hours'];
            $processableAt = now()->addHours($delayHours);

            // Log risk assessment
            Log::channel('fraud')->info('API Withdrawal risk assessment', [
                'user_id' => $user->id,
                'amount' => $amount,
                'risk_score' => $riskAssessment['score'],
                'risk_level' => $riskAssessment['risk_level'],
                'delay_hours' => $delayHours,
                'factors' => $riskAssessment['factors'],
            ]);

            // Update withdrawal with risk data
            $withdrawal->update([
                'processable_at' => $processableAt,
                'delay_hours' => $delayHours,
                'risk_score' => $riskAssessment['score'],
                'risk_factors' => $riskAssessment['factors'],
                'is_frozen' => $riskAssessment['should_freeze'] ?? false,
                'freeze_reason' => ($riskAssessment['should_freeze'] ?? false)
                    ? 'Auto-frozen: High risk score (' . $riskAssessment['score'] . ')'
                    : null,
            ]);

            // Debit wallet and set pending
            $wallet->debit($amount, 'withdrawal', $withdrawal, 'Ombi la kutoa pesa');
            $wallet->increment('pending_withdrawal', $amount);

            // Create fee transaction
            if ($fee > 0) {
                $wallet->transactions()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'reference' => 'FEE' . strtoupper(\Str::random(10)),
                    'type' => 'debit',
                    'category' => 'withdrawal_fee',
                    'amount' => $fee,
                    'balance_before' => $wallet->balance + $fee,
                    'balance_after' => $wallet->balance,
                    'description' => 'Ada ya kutoa pesa',
                    'transactionable_type' => Withdrawal::class,
                    'transactionable_id' => $withdrawal->id,
                ]);
            }

            DB::commit();

            // Build success message with delay info
            if ($delayHours >= 24) {
                $delayText = ceil($delayHours / 24) . ' siku';
            } else {
                $delayText = $delayHours . ' saa';
            }

            $message = 'Ombi lako limepokelewa! Utapata TZS ' . number_format($netAmount, 0);
            if ($delayHours > 0) {
                $message .= " baada ya {$delayText}";
            }
            if ($withdrawal->is_frozen) {
                $message .= ' (Inahitaji ukaguzi wa ziada)';
            }

            // Reload withdrawal with updated data
            $withdrawal->refresh();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $withdrawal->id,
                    'reference' => $withdrawal->reference,
                    'amount' => $withdrawal->amount,
                    'fee' => $withdrawal->fee,
                    'net_amount' => $withdrawal->net_amount,
                    'payment_provider' => $withdrawal->payment_provider,
                    'payment_number' => $withdrawal->payment_number,
                    'payment_name' => $withdrawal->payment_name,
                    'status' => $withdrawal->status,
                    'status_label' => $withdrawal->getStatusLabel(),
                    'delay_hours' => $withdrawal->delay_hours,
                    'processable_at' => $withdrawal->processable_at?->toIso8601String(),
                    'is_frozen' => $withdrawal->is_frozen,
                    'created_at' => $withdrawal->created_at->toIso8601String(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Withdrawal creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena baadaye.',
            ], 500);
        }
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
