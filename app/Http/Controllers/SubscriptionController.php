<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentSubscription = $user->activeSubscription;
        
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get();
        
        return view('subscriptions.index', compact('plans', 'currentSubscription'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = auth()->user();
        
        // If free plan, activate immediately
        if ($plan->isFree()) {
            $this->activateSubscription($user, $plan);
            return redirect()->route('subscriptions.index')
                ->with('success', 'Umejiunga na mpango wa ' . $plan->display_name);
        }
        
        // For paid plans, redirect to ZenoPay payment
        return redirect()->route('payments.subscription', $plan);
    }

    public function processPayment(Request $request, SubscriptionPlan $plan)
    {
        // This would integrate with ZenoPay
        // For now, we'll simulate a successful payment
        
        $user = auth()->user();
        
        try {
            DB::beginTransaction();
            
            // Deactivate current subscription
            $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
            
            // Create new subscription
            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'status' => 'active',
                'amount_paid' => $plan->price,
                'payment_reference' => 'TEST_' . strtoupper(\Str::random(10)),
            ]);
            
            // Debit from wallet if has balance
            $wallet = $user->wallet;
            if ($wallet->balance >= $plan->price) {
                $wallet->debit($plan->price, 'subscription', $subscription, 'Malipo ya ' . $plan->display_name);
            }
            
            DB::commit();
            
            return redirect()->route('subscriptions.index')
                ->with('success', 'Hongera! Umejiunga na mpango wa ' . $plan->display_name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Kuna tatizo la malipo. Jaribu tena.');
        }
    }

    protected function activateSubscription($user, $plan)
    {
        // Deactivate current subscriptions
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
        
        // Create new subscription
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
            'status' => 'active',
            'amount_paid' => 0,
        ]);
    }
}
