<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ZenoPayService;

class SubscriptionController extends Controller
{
    protected ZenoPayService $zenoPayService;

    public function __construct(ZenoPayService $zenoPayService)
    {
        $this->zenoPayService = $zenoPayService;
    }

    /**
     * List all plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => $plan->price,
                    'duration_days' => $plan->duration_days,
                    'daily_task_limit' => $plan->daily_task_limit,
                    'reward_per_task' => $plan->reward_per_task,
                    'min_withdrawal' => $plan->min_withdrawal,
                    'withdrawal_fee_percent' => $plan->withdrawal_fee_percent,
                    'features' => $plan->features,
                    'is_popular' => $plan->slug === 'silver',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get current subscription
     */
    public function current(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active subscription',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subscription->id,
                'plan' => [
                    'id' => $subscription->plan->id,
                    'name' => $subscription->plan->name,
                    'slug' => $subscription->plan->slug,
                ],
                'status' => $subscription->status,
                'started_at' => $subscription->started_at?->toISOString(),
                'expires_at' => $subscription->expires_at?->toISOString(),
                'is_expired' => $subscription->expires_at && $subscription->expires_at->isPast(),
                'days_remaining' => $subscription->expires_at ? max(0, now()->diffInDays($subscription->expires_at, false)) : null,
            ],
        ]);
    }

    /**
     * Subscribe to plan
     */
    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();

        // Free plan
        if ($plan->price <= 0) {
            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Umejiunga na mpango wa bure!',
                'data' => $subscription,
            ]);
        }

        // For paid plans, return payment info
        return response()->json([
            'success' => true,
            'message' => 'Proceed to payment',
            'data' => [
                'plan' => $plan,
                'payment_required' => true,
                'payment_url' => route('api.subscriptions.pay', $plan->id),
            ],
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request, SubscriptionPlan $plan)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $orderId = $this->zenoPayService->generateOrderId();
        
        // Create pending payment record
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'order_id' => $orderId,
            'amount' => $plan->price,
            'phone_number' => $request->phone_number,
            'status' => 'pending',
            'provider' => 'zenopay',
        ]);

        // Initiate payment with ZenoPay
        $result = $this->zenoPayService->initiatePayment(
            $user->name,
            $user->email,
            $request->phone_number,
            $plan->price,
            $orderId
        );

        if (!$result['success']) {
            $payment->update(['status' => 'failed', 'metadata' => $result]);
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Malipo yameshindwa',
                'error' => $result['error'] ?? null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'order_id' => $orderId,
                'amount' => $plan->price,
                'phone' => $request->phone_number,
                'status' => 'pending',
                'check_status_url' => route('api.subscriptions.payment-status', $orderId),
                'demo_mode' => $result['demo_mode'] ?? false,
            ],
        ]);
    }

    /**
     * Check payment status
     */
    public function paymentStatus(Request $request, $orderId)
    {
        $payment = \App\Models\Payment::where('order_id', $orderId)->first();
        
        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'completed',
                    'message' => 'Malipo yamekamilika',
                ],
            ]);
        }

        // Check with ZenoPay
        $status = $this->zenoPayService->checkStatus($orderId);
        
        if ($status['is_completed']) {
            // Mark as paid and activate subscription
            $this->completePayment($payment, $status);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'completed',
                    'message' => 'Malipo yamekamilika! Mpango wako umeanzishwa.',
                ],
            ]);
        }
        
        if ($status['is_failed']) {
            $payment->update(['status' => 'failed', 'metadata' => $status]);
            return response()->json([
                'success' => false,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'failed',
                    'message' => 'Malipo yameshindwa.',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'status' => 'pending',
                'message' => 'Inasubiri malipo...',
            ],
        ]);
    }

    /**
     * Get subscription history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $subscriptions->items(),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    /**
     * ZenoPay webhook callback
     */
    public function zenoPayCallback(Request $request)
    {
        \Log::info('ZenoPay callback', $request->all());

        $orderId = $request->input('order_id');
        $status = $request->input('payment_status'); // Adjust based on actual ZenoPay payload

        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        $payment = \App\Models\Payment::where('order_id', $orderId)->first();
        
        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['status' => 'ok', 'message' => 'Already processed']);
        }

        // Verify status
        // In a real scenario, we should verify signature or call checkStatus to be sure
        // For now, we trust the callback if it says success/completed
        
        if (in_array(strtoupper($status), ['COMPLETED', 'SUCCESS'])) {
            $this->completePayment($payment, $request->all());
        } else if (in_array(strtoupper($status), ['FAILED', 'CANCELLED'])) {
            $payment->update(['status' => 'failed', 'metadata' => $request->all()]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Helper to complete payment and activate subscription
     */
    protected function completePayment(\App\Models\Payment $payment, array $metadata = [])
    {
        if ($payment->status === 'completed') return;

        \DB::transaction(function () use ($payment, $metadata) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], $metadata),
                'transaction_id' => $metadata['transaction_id'] ?? null,
                'reference' => $metadata['reference'] ?? null,
            ]);

            // Activate Subscription
            $plan = $payment->plan;
            $user = $payment->user;
            
            // Deactivate previous active subscriptions
            $user->subscriptions()->where('status', 'active')->update(['status' => 'expired', 'expires_at' => now()]);

            // Create new subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'payment_id' => $payment->id,
            ]);
            
            // Send notification
            // $user->notify(new SubscriptionActivated($plan));
        });
    }
}
