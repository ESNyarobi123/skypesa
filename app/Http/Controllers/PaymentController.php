<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\ZenoPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    protected ZenoPayService $zenoPay;

    public function __construct(ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    /**
     * Show payment page for subscription
     */
    public function subscriptionPayment(SubscriptionPlan $plan)
    {
        if ($plan->isFree()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Mpango wa Free hauhitaji malipo.');
        }

        $user = auth()->user();
        
        return view('payments.subscription', compact('plan', 'user'));
    }

    /**
     * Initiate subscription payment
     */
    public function initiateSubscriptionPayment(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ], [
            'phone.required' => 'Tafadhali weka namba ya simu.',
        ]);

        $user = auth()->user();
        $orderId = $this->zenoPay->generateOrderId();
        
        // Store pending payment info in cache
        Cache::put("payment_{$orderId}", [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'phone' => $request->phone,
            'type' => 'subscription',
            'created_at' => now()->toDateTimeString(),
        ], 3600); // 1 hour expiry

        // Initiate payment with ZenoPay
        $result = $this->zenoPay->initiatePayment(
            buyerName: $user->name,
            buyerEmail: $user->email,
            buyerPhone: $request->phone,
            amount: $plan->price,
            orderId: $orderId
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Utapokea PUSH kwenye simu yako. Lipia na usubiri.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Kuna tatizo. Jaribu tena.',
        ], 400);
    }

    /**
     * Check payment status (for polling)
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = $request->order_id;
        
        // Get cached payment info
        $paymentInfo = Cache::get("payment_{$orderId}");
        
        if (!$paymentInfo) {
            return response()->json([
                'success' => false,
                'status' => 'NOT_FOUND',
                'message' => 'Order haipo au muda umekwisha.',
            ], 404);
        }

        // Check status with ZenoPay
        $status = $this->zenoPay->checkStatus($orderId);

        if ($status['is_completed']) {
            // Process the payment
            $processed = $this->processCompletedPayment($orderId, $paymentInfo, $status);
            
            if ($processed['success']) {
                // Clear cache
                Cache::forget("payment_{$orderId}");
                
                return response()->json([
                    'success' => true,
                    'status' => 'COMPLETED',
                    'message' => 'Malipo yamekamilika! Umejiunga na mpango.',
                    'redirect' => route('subscriptions.index'),
                ]);
            }
        }

        if ($status['is_failed']) {
            Cache::forget("payment_{$orderId}");
            
            return response()->json([
                'success' => false,
                'status' => $status['status'],
                'message' => 'Malipo yameshindwa au yameghairiwa.',
            ]);
        }

        // Still pending
        return response()->json([
            'success' => true,
            'status' => 'PENDING',
            'message' => 'Inasubiri malipo...',
        ]);
    }

    /**
     * Process completed payment
     */
    protected function processCompletedPayment(string $orderId, array $paymentInfo, array $statusData): array
    {
        $type = $paymentInfo['type'] ?? 'unknown';

        try {
            DB::beginTransaction();

            if ($type === 'subscription') {
                $this->processSubscriptionPayment($paymentInfo, $orderId, $statusData);
            } elseif ($type === 'deposit') {
                $this->processDepositPayment($paymentInfo, $orderId, $statusData);
            }

            DB::commit();

            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Payment processing error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process subscription payment
     */
    protected function processSubscriptionPayment(array $paymentInfo, string $orderId, array $statusData): void
    {
        $userId = $paymentInfo['user_id'];
        $planId = $paymentInfo['plan_id'];
        $amount = $paymentInfo['amount'];

        $user = \App\Models\User::findOrFail($userId);
        $plan = SubscriptionPlan::findOrFail($planId);

        // Deactivate current subscription
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        // Create new subscription
        $subscription = $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
            'status' => 'active',
            'amount_paid' => $amount,
            'payment_reference' => $orderId,
            'payment_method' => 'zenopay',
        ]);

        // Create transaction record
        $user->wallet->transactions()->create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'reference' => 'SUB' . strtoupper(\Str::random(10)),
            'type' => 'debit',
            'category' => 'subscription',
            'amount' => $amount,
            'balance_before' => $user->wallet->balance,
            'balance_after' => $user->wallet->balance,
            'description' => 'Malipo ya ' . $plan->display_name,
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'metadata' => [
                'zenopay_order_id' => $orderId,
                'zenopay_transaction_id' => $statusData['transaction_id'] ?? null,
            ],
        ]);
    }

    /**
     * Process deposit payment (top-up wallet)
     */
    protected function processDepositPayment(array $paymentInfo, string $orderId, array $statusData): void
    {
        $userId = $paymentInfo['user_id'];
        $amount = $paymentInfo['amount'];

        $user = \App\Models\User::findOrFail($userId);

        // Credit wallet
        $user->wallet->credit(
            $amount,
            'deposit',
            null,
            'Deposit via ZenoPay',
            [
                'zenopay_order_id' => $orderId,
                'zenopay_transaction_id' => $statusData['transaction_id'] ?? null,
            ]
        );
    }

    /**
     * Show deposit page
     */
    public function depositPage()
    {
        $user = auth()->user();
        return view('payments.deposit', compact('user'));
    }

    /**
     * Initiate deposit payment
     */
    public function initiateDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|max:1000000',
            'phone' => 'required|string|min:10|max:15',
        ], [
            'amount.required' => 'Tafadhali weka kiasi.',
            'amount.min' => 'Kiasi cha chini ni TZS 500.',
            'phone.required' => 'Tafadhali weka namba ya simu.',
        ]);

        $user = auth()->user();
        $orderId = $this->zenoPay->generateOrderId();
        
        // Store pending payment info in cache
        Cache::put("payment_{$orderId}", [
            'user_id' => $user->id,
            'amount' => $request->amount,
            'phone' => $request->phone,
            'type' => 'deposit',
            'created_at' => now()->toDateTimeString(),
        ], 3600);

        // Initiate payment with ZenoPay
        $result = $this->zenoPay->initiatePayment(
            buyerName: $user->name,
            buyerEmail: $user->email,
            buyerPhone: $request->phone,
            amount: $request->amount,
            orderId: $orderId
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Utapokea PUSH kwenye simu yako. Lipia na usubiri.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Kuna tatizo. Jaribu tena.',
        ], 400);
    }
}
