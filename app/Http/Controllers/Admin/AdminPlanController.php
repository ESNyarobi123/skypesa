<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount(['subscriptions' => function($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();
        
        $totalActiveSubscriptions = UserSubscription::where('status', 'active')->count();
        $totalRevenue = UserSubscription::where('status', 'active')
            ->whereNotNull('amount_paid')
            ->sum('amount_paid');
        
        return view('admin.plans.index', compact('plans', 'totalActiveSubscriptions', 'totalRevenue'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:subscription_plans,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'daily_task_limit' => ['nullable', 'integer', 'min:1'],
            'reward_per_task' => ['required', 'numeric', 'min:0'],
            'min_withdrawal' => ['required', 'numeric', 'min:0'],
            'withdrawal_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'processing_days' => ['required', 'integer', 'min:0', 'max:30'],
            'badge_color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        
        SubscriptionPlan::create($validated);
        
        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Subscription plan created successfully!');
    }

    /**
     * Show the form for editing a plan.
     */
    public function edit(SubscriptionPlan $plan)
    {
        $plan->loadCount(['subscriptions' => function($q) {
            $q->where('status', 'active');
        }]);
        
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('subscription_plans')->ignore($plan->id)],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'daily_task_limit' => ['nullable', 'integer', 'min:1'],
            'reward_per_task' => ['required', 'numeric', 'min:0'],
            'min_withdrawal' => ['required', 'numeric', 'min:0'],
            'withdrawal_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'processing_days' => ['required', 'integer', 'min:0', 'max:30'],
            'badge_color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        
        $plan->update($validated);
        
        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Subscription plan updated successfully!');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Don't allow deleting plans with active subscriptions
        $activeCount = $plan->subscriptions()->where('status', 'active')->count();
        
        if ($activeCount > 0) {
            return back()->with('error', "Cannot delete plan with {$activeCount} active subscriptions. Please migrate users first.");
        }
        
        // Don't allow deleting the free plan
        if ($plan->name === 'free') {
            return back()->with('error', 'The free plan cannot be deleted.');
        }
        
        $plan->delete();
        
        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Subscription plan deleted successfully!');
    }

    /**
     * Toggle plan active status.
     */
    public function toggleStatus(SubscriptionPlan $plan)
    {
        // Don't allow deactivating the free plan
        if ($plan->name === 'free' && $plan->is_active) {
            return back()->with('error', 'The free plan cannot be deactivated.');
        }
        
        $plan->update(['is_active' => !$plan->is_active]);
        
        $status = $plan->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Plan {$plan->display_name} has been {$status}.");
    }

    /**
     * Reorder plans.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:subscription_plans,id'],
        ]);
        
        foreach ($request->order as $index => $planId) {
            SubscriptionPlan::where('id', $planId)->update(['sort_order' => $index]);
        }
        
        return response()->json(['success' => true]);
    }
}
