@extends('layouts.admin')

@section('title', 'User Profile')
@section('page-title', $user->name)
@section('page-subtitle', 'User profile and activity details')

@section('content')
<!-- User Header -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <img src="{{ $user->getAvatarUrl() }}" style="width: 80px; height: 80px; border-radius: 16px; border: 3px solid var(--primary);">
            <div>
                <h2 style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 0.25rem;">{{ $user->name }}</h2>
                <p style="color: var(--text-muted); margin-bottom: 0.5rem;">{{ $user->email }}</p>
                <div style="display: flex; gap: 0.75rem;">
                    <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                        {{ $user->is_active ? 'Active' : 'Suspended' }}
                    </span>
                    @if($user->activeSubscription)
                    <span class="badge" style="background: {{ $user->activeSubscription->plan->badge_color ?? 'var(--primary)' }}20; color: {{ $user->activeSubscription->plan->badge_color ?? 'var(--primary)' }};">
                        {{ $user->activeSubscription->plan->display_name }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                Edit User
            </a>
            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary">
                    <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" style="width: 16px; height: 16px;"></i>
                    {{ $user->is_active ? 'Suspend' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Wallet Balance</div>
        <div class="stat-number">TZS {{ number_format($user->wallet?->balance ?? 0, 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Tasks Completed</div>
        <div class="stat-number">{{ number_format($stats['tasks_completed']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Earned</div>
        <div class="stat-number">TZS {{ number_format($stats['total_earned'], 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Referrals</div>
        <div class="stat-number">{{ $stats['referrals_count'] }}</div>
    </div>
</div>

<!-- Info Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Account Info -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">Account Information</div>
        </div>
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Phone</span>
                <span style="color: white; font-weight: 500;">{{ $user->phone ?? 'Not set' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Referral Code</span>
                <span style="color: var(--primary); font-weight: 600; font-family: monospace;">{{ $user->referral_code }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Referred By</span>
                <span style="color: white;">
                    @if($user->referrer)
                    <a href="{{ route('admin.users.show', $user->referrer) }}">{{ $user->referrer->name }}</a>
                    @else
                    -
                    @endif
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Joined</span>
                <span style="color: white;">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Last Login</span>
                <span style="color: white;">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
            </div>
        </div>
    </div>
    
    <!-- Subscription Info -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">Subscription Details</div>
        </div>
        @if($user->activeSubscription)
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Current Plan</span>
                <span style="color: var(--primary); font-weight: 600;">{{ $user->activeSubscription->plan->display_name }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Daily Limit</span>
                <span style="color: white;">{{ $user->activeSubscription->plan->daily_task_limit ?? 'Unlimited' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Reward/Task</span>
                <span style="color: white;">TZS {{ number_format($user->activeSubscription->plan->reward_per_task, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Started</span>
                <span style="color: white;">{{ $user->activeSubscription->starts_at->format('M d, Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Expires</span>
                <span style="color: {{ $user->activeSubscription->expires_at?->isPast() ? 'var(--error)' : 'white' }};">
                    {{ $user->activeSubscription->expires_at?->format('M d, Y') ?? 'Never' }}
                </span>
            </div>
        </div>
        @else
        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No active subscription</p>
        @endif
    </div>
    
    <!-- Wallet Info -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">Wallet Summary</div>
        </div>
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Balance</span>
                <span style="color: var(--success); font-weight: 700;">TZS {{ number_format($user->wallet?->balance ?? 0, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Total Earned</span>
                <span style="color: white;">TZS {{ number_format($user->wallet?->total_earned ?? 0, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Total Withdrawn</span>
                <span style="color: white;">TZS {{ number_format($user->wallet?->total_withdrawn ?? 0, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Pending</span>
                <span style="color: var(--warning);">TZS {{ number_format($user->wallet?->pending_withdrawal ?? 0, 0) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Tables -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Recent Task Completions -->
    <div class="data-table-container">
        <div class="table-header">
            <div class="table-title">Recent Task Completions</div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Reward</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->taskCompletions as $completion)
                <tr>
                    <td style="color: white;">{{ Str::limit($completion->task->title, 25) }}</td>
                    <td style="color: var(--success); font-weight: 600;">TZS {{ number_format($completion->reward_earned, 0) }}</td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $completion->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No completions yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Recent Transactions -->
    <div class="data-table-container">
        <div class="table-header">
            <div class="table-title">Recent Transactions</div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->transactions as $txn)
                <tr>
                    <td>
                        <span class="status-badge {{ $txn->type === 'credit' ? 'active' : 'inactive' }}">
                            {{ ucfirst($txn->type) }}
                        </span>
                    </td>
                    <td style="color: white;">{{ $txn->getCategoryLabel() }}</td>
                    <td style="color: {{ $txn->type === 'credit' ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                        {{ $txn->type === 'credit' ? '+' : '-' }}TZS {{ number_format($txn->amount, 0) }}
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $txn->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No transactions yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Referrals Section -->
@if($user->referrals->count() > 0)
<div class="data-table-container" style="margin-top: 1.5rem;">
    <div class="table-header">
        <div class="table-title">Referrals ({{ $user->referrals->count() }})</div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @foreach($user->referrals as $referral)
            <tr>
                <td>
                    <div class="user-cell">
                        <img src="{{ $referral->getAvatarUrl() }}" class="user-avatar">
                        <div class="user-name">{{ $referral->name }}</div>
                    </div>
                </td>
                <td style="color: var(--text-muted);">{{ $referral->email }}</td>
                <td>
                    <span class="status-badge {{ $referral->is_active ? 'active' : 'inactive' }}">
                        {{ $referral->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="color: var(--text-muted);">{{ $referral->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
