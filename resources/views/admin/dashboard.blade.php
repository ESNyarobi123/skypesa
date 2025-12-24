@extends('layouts.admin')

@section('title', __('messages.admin.dashboard'))
@section('page-title', __('messages.admin.dashboard'))
@section('page-subtitle', __('messages.admin.dashboard_subtitle'))

@section('content')
<!-- Quick Stats -->
<div class="grid grid-4 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($totalUsers) }}</div>
        <div class="stat-label">{{ __('messages.admin.total_users') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($completionsToday) }}</div>
        <div class="stat-label">{{ __('messages.admin.tasks_today') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">TZS {{ number_format($earningsToday, 0) }}</div>
        <div class="stat-label">{{ __('messages.admin.earnings_today') }}</div>
    </div>
    <div class="stat-card" style="border-color: var(--warning);">
        <div class="stat-value" style="color: var(--warning);">{{ $pendingWithdrawals }}</div>
        <div class="stat-label">{{ __('messages.admin.pending_withdrawals') }}</div>
    </div>
</div>

<!-- Main Stats Cards -->
<div class="grid grid-3 mb-8">
    <!-- Users Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>{{ __('messages.admin.users') }}</h4>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                {{ __('messages.admin.view_all') }}
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.active') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ number_format($activeUsers) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.new_today') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $newUsersToday }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.this_month') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $newUsersThisMonth }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.total') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($totalUsers) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Tasks Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>{{ __('messages.admin.tasks') }}</h4>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-secondary">
                {{ __('messages.admin.manage') }}
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.active_tasks') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $activeTasks }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.total_tasks') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $totalTasks }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.completions_today') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($completionsToday) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.this_month') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($completionsThisMonth) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Withdrawals Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>{{ __('messages.admin.payments') }}</h4>
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-secondary">
                {{ __('messages.admin.view_all') }}
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.pending') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">{{ $pendingWithdrawals }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.pending_amount') }}</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--warning);">TZS {{ number_format($pendingAmount, 0) }}</div>
            </div>
            <div style="grid-column: span 2;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.admin.paid_this_month') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">TZS {{ number_format($paidThisMonth, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Overview -->
<div class="card mb-8" style="padding: var(--space-6); background: var(--gradient-primary);">
    <div style="position: relative; z-index: 10;">
        <h4 style="color: white; margin-bottom: var(--space-4);">{{ __('messages.admin.earnings_overview') }}</h4>
        <div class="grid grid-3" style="gap: var(--space-8);">
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">{{ __('messages.admin.earnings_today') }}</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($earningsToday, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">{{ __('messages.admin.monthly_earnings') }}</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($earningsThisMonth, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">{{ __('messages.admin.lifetime') }}</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($totalEarnings, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="gap: var(--space-6);">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-body" style="padding-bottom: 0;">
            <h4>{{ __('messages.admin.new_users') }}</h4>
        </div>
        <table class="table">
            <tbody>
                @forelse($recentUsers as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->getAvatarUrl() }}" style="width: 36px; height: 36px; border-radius: 50%;">
                            <div>
                                <div style="font-weight: 500;">{{ $user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right; color: var(--text-muted);">
                        {{ $user->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                        {{ __('messages.admin.no_new_users') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pending Withdrawals -->
    <div class="card">
        <div class="card-body" style="padding-bottom: 0;">
            <h4>{{ __('messages.admin.recent_requests') }}</h4>
        </div>
        <table class="table">
            <tbody>
                @forelse($recentWithdrawals as $withdrawal)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $withdrawal->user->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $withdrawal->payment_number }}</div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $colors = [
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'paid' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                        @endphp
                        <span class="badge {{ $colors[$withdrawal->status] ?? '' }}">
                            {{ $withdrawal->getStatusLabel() }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        TZS {{ number_format($withdrawal->net_amount, 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                        {{ __('messages.admin.no_requests') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Task Completions -->
<div class="card mt-8">
    <div class="card-body" style="padding-bottom: 0;">
        <h4>{{ __('messages.admin.recent_completions') }}</h4>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('messages.admin.user') }}</th>
                <th>{{ __('messages.admin.task') }}</th>
                <th>{{ __('messages.admin.payment') }}</th>
                <th>{{ __('messages.admin.time') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentCompletions as $completion)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $completion->user->getAvatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%;">
                        <span>{{ $completion->user->name }}</span>
                    </div>
                </td>
                <td>{{ $completion->task->title }}</td>
                <td style="font-weight: 600; color: var(--success);">TZS {{ number_format($completion->reward_earned, 0) }}</td>
                <td style="color: var(--text-muted);">{{ $completion->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                    {{ __('messages.admin.no_completions') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
