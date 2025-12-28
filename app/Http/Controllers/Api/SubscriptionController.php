<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * GET /api/v1/plans (public)
     */
    public function index()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug ?? strtolower($plan->name),
                    'display_name' => $plan->display_name,
                    'description' => $plan->description,
                    'price' => (float) $plan->price,
                    'price_formatted' => 'TZS ' . number_format($plan->price, 0),
                    'duration_days' => $plan->duration_days,
                    'daily_task_limit' => $plan->daily_task_limit,
                    'has_unlimited_tasks' => $plan->hasUnlimitedTasks(),
                    'reward_per_task' => (float) $plan->reward_per_task,
                    'reward_per_task_formatted' => 'TZS ' . number_format($plan->reward_per_task, 0),
                    'min_withdrawal' => (float) $plan->min_withdrawal,
                    'min_withdrawal_formatted' => 'TZS ' . number_format($plan->min_withdrawal, 0),
                    'withdrawal_fee_percent' => (float) $plan->withdrawal_fee_percent,
                    'processing_days' => $plan->processing_days,
                    'features' => $plan->getFormattedFeatures(),
                    'badge_color' => $plan->badge_color,
                    'icon' => $plan->getIconName(),
                    'is_free' => $plan->isFree(),
                    'is_featured' => $plan->is_featured,
                    'daily_earnings_estimate' => $plan->getDailyEarningsEstimate(),
                    'monthly_earnings_estimate' => $plan->getMonthlyEarningsEstimate(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get single plan details
     * GET /api/v1/plans/{slug}
     */
    public function show(string $slug)
    {
        $plan = SubscriptionPlan::where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Mpango haujapatikana.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug ?? strtolower($plan->name),
                'display_name' => $plan->display_name,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'price_formatted' => 'TZS ' . number_format($plan->price, 0),
                'duration_days' => $plan->duration_days,
                'daily_task_limit' => $plan->daily_task_limit,
                'has_unlimited_tasks' => $plan->hasUnlimitedTasks(),
                'reward_per_task' => (float) $plan->reward_per_task,
                'min_withdrawal' => (float) $plan->min_withdrawal,
                'withdrawal_fee_percent' => (float) $plan->withdrawal_fee_percent,
                'processing_days' => $plan->processing_days,
                'features' => $plan->getFormattedFeatures(),
                'badge_color' => $plan->badge_color,
                'icon' => $plan->getIconName(),
                'is_free' => $plan->isFree(),
                'is_featured' => $plan->is_featured,
            ],
        ]);
    }

    /**
     * Get current subscription
     * GET /api/v1/subscriptions/current
     */
    public function current(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            // Return free plan info if no active subscription
            $freePlan = SubscriptionPlan::getFree();
            
            return response()->json([
                'success' => true,
                'data' => null,
                'has_subscription' => false,
                'message' => 'Huna subscription. Unatumia mpango wa bure.',
                'default_plan' => $freePlan ? [
                    'id' => $freePlan->id,
                    'name' => $freePlan->display_name,
                    'slug' => $freePlan->slug ?? 'free',
                ] : null,
            ]);
        }

        $plan = $subscription->plan;
        $daysRemaining = $subscription->daysRemaining();

        return response()->json([
            'success' => true,
            'has_subscription' => true,
            'data' => [
                'id' => $subscription->id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'display_name' => $plan->display_name,
                    'slug' => $plan->slug ?? strtolower($plan->name),
                    'badge_color' => $plan->badge_color,
                    'icon' => $plan->getIconName(),
                ],
                'status' => $subscription->status,
                'status_label' => $this->getStatusLabel($subscription->status),
                'started_at' => $subscription->starts_at?->toIso8601String(),
                'expires_at' => $subscription->expires_at?->toIso8601String(),
                'is_active' => $subscription->isActive(),
                'is_expired' => $subscription->isExpired(),
                'days_remaining' => $daysRemaining,
                'is_expiring_soon' => $daysRemaining !== null && $daysRemaining <= 3,
                'daily_task_limit' => $plan->daily_task_limit,
                'reward_per_task' => (float) $plan->reward_per_task,
            ],
        ]);
    }

    /**
     * Subscribe to plan (for free plans or initiate paid)
     * POST /api/v1/subscriptions/subscribe/{plan}
     */
    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();

        // Check if already has active subscription
        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription && $activeSubscription->plan_id === $plan->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tayari una mpango huu.',
            ], 400);
        }

        // Free plan - activate immediately
        if ($plan->isFree()) {
            DB::beginTransaction();
            try {
                // Expire current subscription if any
                if ($activeSubscription) {
                    $activeSubscription->update([
                        'status' => 'expired',
                        'expires_at' => now(),
                    ]);
                }

                // Create new subscription
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => null, // Free never expires
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Umejiunga na mpango wa bure!',
                    'data' => [
                        'subscription_id' => $subscription->id,
                        'plan_name' => $plan->display_name,
                        'status' => 'active',
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Free subscription failed', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Kuna tatizo. Jaribu tena.',
                ], 500);
            }
        }

        // For paid plans, return payment info
        return response()->json([
            'success' => true,
            'message' => 'Mpango huu unahitaji malipo.',
            'data' => [
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->display_name,
                    'price' => (float) $plan->price,
                    'price_formatted' => 'TZS ' . number_format($plan->price, 0),
                ],
                'payment_required' => true,
                'payment_methods' => [
                    ['id' => 'mobile_money', 'name' => 'Mobile Money (M-Pesa, Tigo, Airtel)'],
                ],
            ],
        ]);
    }

    /**
     * Initiate payment for a plan
     * POST /api/v1/subscriptions/pay/{plan}
     */
    public function initiatePayment(Request $request, SubscriptionPlan $plan)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:10|max:15',
        ], [
            'phone_number.required' => 'Tafadhali weka namba ya simu.',
            'phone_number.min' => 'Namba ya simu ni fupi sana.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($plan->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'Mpango wa bure hauhitaji malipo.',
            ], 400);
        }

        $user = $request->user();
        $orderId = $this->zenoPayService->generateOrderId();

        try {
            // Create pending payment record
            $payment = Payment::create([
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
                    'message' => $result['message'] ?? 'Malipo yameshindwa. Jaribu tena.',
                ], 400);
            }

            Log::info('Payment initiated', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $orderId,
                'amount' => $plan->price,
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Ombi la malipo limetumwa. Angalia simu yako.',
                'data' => [
                    'order_id' => $orderId,
                    'amount' => (float) $plan->price,
                    'amount_formatted' => 'TZS ' . number_format($plan->price, 0),
                    'phone' => $request->phone_number,
                    'plan_name' => $plan->display_name,
                    'status' => 'pending',
                    'demo_mode' => $result['demo_mode'] ?? false,
                    'instructions' => 'Utapokea PUSH notification kwenye simu yako. Ingiza PIN yako kukamilisha malipo.',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo la mtandao. Jaribu tena.',
            ], 500);
        }
    }

    /**
     * Check payment status
     * GET /api/v1/subscriptions/payment-status/{orderId}
     */
    public function paymentStatus(Request $request, string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Malipo hayajapatikana.',
            ], 404);
        }

        // Verify ownership
        if ($payment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa.',
            ], 403);
        }

        // Already completed
        if ($payment->status === 'completed') {
            $subscription = UserSubscription::where('payment_id', $payment->id)->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'completed',
                    'status_label' => 'Imekamilika',
                    'message' => 'Malipo yamekamilika! Mpango wako umeanzishwa.',
                    'subscription' => $subscription ? [
                        'id' => $subscription->id,
                        'plan_name' => $subscription->plan->display_name,
                        'expires_at' => $subscription->expires_at?->toIso8601String(),
                    ] : null,
                ],
            ]);
        }

        // Already failed
        if ($payment->status === 'failed') {
            return response()->json([
                'success' => false,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'failed',
                    'status_label' => 'Imeshindwa',
                    'message' => 'Malipo yameshindwa. Jaribu tena.',
                ],
            ]);
        }

        // Check with ZenoPay
        $status = $this->zenoPayService->checkStatus($orderId);

        if ($status['is_completed']) {
            // Mark as paid and activate subscription
            $this->completePayment($payment, $status);

            $subscription = UserSubscription::where('payment_id', $payment->id)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'completed',
                    'status_label' => 'Imekamilika',
                    'message' => 'Malipo yamekamilika! Mpango wako umeanzishwa.',
                    'subscription' => $subscription ? [
                        'id' => $subscription->id,
                        'plan_name' => $subscription->plan->display_name,
                        'expires_at' => $subscription->expires_at?->toIso8601String(),
                    ] : null,
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
                    'status_label' => 'Imeshindwa',
                    'message' => 'Malipo yameshindwa.',
                ],
            ]);
        }

        // Still pending
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'status' => 'pending',
                'status_label' => 'Inasubiri',
                'message' => 'Inasubiri malipo... Angalia simu yako.',
                'demo_mode' => $status['demo_mode'] ?? false,
                'seconds_remaining' => $status['seconds_remaining'] ?? null,
            ],
        ]);
    }

    /**
     * Get subscription history
     * GET /api/v1/subscriptions/history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(10);

        $formattedSubscriptions = $subscriptions->getCollection()->map(function ($sub) {
            return [
                'id' => $sub->id,
                'plan' => [
                    'id' => $sub->plan->id,
                    'name' => $sub->plan->display_name,
                    'slug' => $sub->plan->slug ?? strtolower($sub->plan->name),
                ],
                'status' => $sub->status,
                'status_label' => $this->getStatusLabel($sub->status),
                'started_at' => $sub->starts_at?->toIso8601String(),
                'expires_at' => $sub->expires_at?->toIso8601String(),
                'amount_paid' => (float) $sub->amount_paid,
                'is_active' => $sub->isActive(),
                'created_at' => $sub->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedSubscriptions,
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    /**
     * Get payment history
     * GET /api/v1/subscriptions/payments
     */
    public function payments(Request $request)
    {
        $user = $request->user();

        $payments = Payment::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->paginate(10);

        $formattedPayments = $payments->getCollection()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'plan' => $payment->plan ? [
                    'id' => $payment->plan->id,
                    'name' => $payment->plan->display_name,
                ] : null,
                'amount' => (float) $payment->amount,
                'amount_formatted' => 'TZS ' . number_format($payment->amount, 0),
                'status' => $payment->status,
                'status_label' => $this->getPaymentStatusLabel($payment->status),
                'phone_number' => $payment->phone_number,
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'created_at' => $payment->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedPayments,
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * ZenoPay webhook callback
     * POST /api/webhooks/zenopay
     */
    public function zenoPayCallback(Request $request)
    {
        Log::info('ZenoPay callback received', $request->all());

        $orderId = $request->input('order_id');
        $status = $request->input('payment_status');

        if (!$orderId) {
            Log::warning('ZenoPay callback missing order_id');
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::warning('ZenoPay callback order not found', ['order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        if ($payment->status === 'completed') {
            Log::info('ZenoPay callback already processed', ['order_id' => $orderId]);
            return response()->json(['status' => 'ok', 'message' => 'Already processed']);
        }

        // Process based on status
        $statusUpper = strtoupper($status ?? '');
        
        if (in_array($statusUpper, ['COMPLETED', 'SUCCESS'])) {
            $this->completePayment($payment, $request->all());
            Log::info('ZenoPay payment completed via callback', ['order_id' => $orderId]);
        } elseif (in_array($statusUpper, ['FAILED', 'CANCELLED', 'EXPIRED'])) {
            $payment->update(['status' => 'failed', 'metadata' => $request->all()]);
            Log::info('ZenoPay payment failed via callback', ['order_id' => $orderId, 'status' => $status]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Helper to complete payment and activate subscription
     */
    protected function completePayment(Payment $payment, array $metadata = []): void
    {
        if ($payment->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($payment, $metadata) {
            // Update payment
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], $metadata),
                'transaction_id' => $metadata['transaction_id'] ?? $metadata['transid'] ?? null,
                'reference' => $metadata['reference'] ?? null,
            ]);

            $plan = $payment->plan;
            $user = $payment->user;

            // Deactivate previous active subscriptions
            $user->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                ]);

            // Create new subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'payment_id' => $payment->id,
                'amount_paid' => $payment->amount,
                'payment_reference' => $payment->order_id,
            ]);

            Log::info('Subscription activated', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
            ]);
        });
    }

    /**
     * Get status label in Swahili
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'active' => 'Hai',
            'expired' => 'Imeisha',
            'cancelled' => 'Imefutwa',
            'pending' => 'Inasubiri',
            default => ucfirst($status),
        };
    }

    /**
     * Get payment status label
     */
    private function getPaymentStatusLabel(string $status): string
    {
        return match($status) {
            'completed' => 'Imekamilika',
            'pending' => 'Inasubiri',
            'failed' => 'Imeshindwa',
            default => ucfirst($status),
        };
    }
}
