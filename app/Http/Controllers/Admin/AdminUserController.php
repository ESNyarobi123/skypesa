<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');
        
        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('referral_code', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Filter by subscription
        if ($request->has('plan') && $request->plan) {
            $query->whereHas('activeSubscription', function($q) use ($request) {
                $q->where('plan_id', $request->plan);
            });
        }
        
        // Filter by date
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        
        $allowedSorts = ['name', 'email', 'created_at', 'last_login_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->latest();
        }
        
        $users = $query->with(['wallet', 'activeSubscription.plan'])
            ->withCount(['taskCompletions', 'referrals'])
            ->paginate(25);
        
        // Stats
        $stats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('is_active', true)->count(),
            'new_today' => User::where('role', 'user')->whereDate('created_at', today())->count(),
            'new_this_month' => User::where('role', 'user')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        
        return view('admin.users.index', compact('users', 'stats', 'plans'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        
        return view('admin.users.create', compact('plans'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'is_active' => ['boolean'],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->has('is_active'),
            'role' => 'user',
        ]);
        
        // Set initial balance if provided
        if (isset($validated['initial_balance']) && $validated['initial_balance'] > 0) {
            $user->wallet->credit(
                $validated['initial_balance'],
                'bonus',
                $user,
                'Initial balance from admin'
            );
        }
        
        // Override the default free subscription with the selected plan
        if ($validated['plan_id']) {
            $plan = SubscriptionPlan::find($validated['plan_id']);
            
            // Deactivate any existing subscriptions
            $user->subscriptions()->update(['status' => 'cancelled']);
            
            // Create the new subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'expires_at' => $plan->price > 0 ? now()->addDays($plan->duration_days) : null,
                'status' => 'active',
            ]);
        }
        
        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully!");
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load([
            'wallet',
            'subscriptions.plan',
            'activeSubscription.plan',
            'taskCompletions' => function($q) {
                $q->with('task')->latest()->take(20);
            },
            'transactions' => function($q) {
                $q->latest()->take(20);
            },
            'withdrawals' => function($q) {
                $q->latest()->take(10);
            },
            'referrals' => function($q) {
                $q->latest()->take(10);
            },
            'referrer',
        ]);
        
        $stats = [
            'total_earned' => $user->taskCompletions()->where('status', 'completed')->sum('reward_earned'),
            'tasks_completed' => $user->taskCompletions()->count(),
            'withdrawals_count' => $user->withdrawals()->count(),
            'withdrawals_total' => $user->withdrawals()->where('status', 'paid')->sum('net_amount'),
            'referrals_count' => $user->referrals()->count(),
        ];
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $user->load(['wallet', 'activeSubscription.plan']);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        
        return view('admin.users.edit', compact('user', 'plans'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'is_active' => ['boolean'],
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);
        
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }
        
        // Update subscription if changed
        $currentPlanId = $user->activeSubscription?->plan_id;
        if ($validated['plan_id'] != $currentPlanId) {
            $plan = SubscriptionPlan::find($validated['plan_id']);
            
            // Deactivate current subscription
            $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);
            
            // Create new subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'expires_at' => $plan->price > 0 ? now()->addDays($plan->duration_days) : null,
                'status' => 'active',
            ]);
        }
        
        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Don't allow deleting admin users
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot delete admin users.');
        }
        
        // Check for financial dependencies
        $pendingWithdrawals = $user->withdrawals()->whereIn('status', ['pending', 'approved'])->count();
        if ($pendingWithdrawals > 0) {
            return back()->with('error', "Cannot delete user with {$pendingWithdrawals} pending withdrawal(s).");
        }
        
        $userName = $user->name;
        $user->delete();
        
        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$userName} has been deleted.");
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot modify admin status.');
        }
        
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'suspended';
        
        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Adjust user wallet balance.
     */
    public function adjustBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:credit,debit'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        
        $wallet = $user->wallet;
        
        if ($validated['type'] === 'credit') {
            $wallet->credit(
                $validated['amount'],
                'adjustment',
                $user,
                $validated['reason']
            );
            $message = "TZS " . number_format($validated['amount']) . " credited to {$user->name}'s wallet.";
        } else {
            if ($wallet->balance < $validated['amount']) {
                return back()->with('error', 'Insufficient balance for debit.');
            }
            
            $wallet->debit(
                $validated['amount'],
                'adjustment',
                $user,
                $validated['reason']
            );
            $message = "TZS " . number_format($validated['amount']) . " debited from {$user->name}'s wallet.";
        }
        
        return back()->with('success', $message);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);
        
        $user->update(['password' => Hash::make($validated['password'])]);
        
        return back()->with('success', "Password reset for {$user->name}.");
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $users = User::where('role', 'user')
            ->with(['wallet', 'activeSubscription.plan'])
            ->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_' . now()->format('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Plan', 'Balance', 
                'Status', 'Referral Code', 'Referred By', 'Created At'
            ]);
            
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->activeSubscription?->plan?->display_name ?? 'None',
                    $user->wallet?->balance ?? 0,
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->referral_code,
                    $user->referrer?->name ?? '-',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
