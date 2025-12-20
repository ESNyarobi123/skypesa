<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
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

        // TODO: Integrate with payment gateway (ZenoPay)
        $orderId = 'SKY' . time() . $user->id;

        // For now, return mock response
        return response()->json([
            'success' => true,
            'message' => 'Ombi la malipo limetumwa. Fuata maelekezo kwenye simu yako.',
            'data' => [
                'order_id' => $orderId,
                'amount' => $plan->price,
                'phone' => $request->phone_number,
                'status' => 'pending',
                'check_status_url' => route('api.subscriptions.payment-status', $orderId),
            ],
        ]);
    }

    /**
     * Check payment status
     */
    public function paymentStatus(Request $request, $orderId)
    {
        // TODO: Check with payment gateway

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'status' => 'pending', // pending, completed, failed
                'message' => 'Inasubiri malipo',
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

        // TODO: Process payment callback
        // 1. Validate signature
        // 2. Find order
        // 3. Activate subscription
        // 4. Send notification

        return response()->json(['status' => 'ok']);
    }
}
