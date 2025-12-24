@extends('layouts.app')

@section('title', __('messages.dashboard.title'))
@section('page-title', __('messages.dashboard.title'))
@section('page-subtitle', __('messages.common.welcome_back') . ' ' . auth()->user()->name . '!')

@push('styles')
<style>
    /* Dashboard responsive styles */
    @media (max-width: 768px) {
        .dashboard-stats {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            gap: var(--space-3);
            padding-bottom: var(--space-2);
        }
        
        .dashboard-stats::-webkit-scrollbar {
            display: none;
        }
        
        .dashboard-stats .stat-card {
            flex: 0 0 calc(50% - var(--space-2));
            scroll-snap-align: start;
        }
        
        .dashboard-quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-2);
        }
        
        .dashboard-quick-actions .btn {
            padding: var(--space-3);
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 480px) {
        .dashboard-stats .stat-card {
            flex: 0 0 70%;
        }
    }
</style>
@endpush

@section('content')
<!-- ==========================================
     GAMIFICATION WIDGETS
     ========================================== -->
     
<!-- Welcome Bonus Widget (for new users) -->
@include('components.welcome-bonus-widget')

<!-- Daily Goal Widget -->
@include('components.daily-goal-widget')

<!-- Wallet Overview -->
<div class="grid grid-3 mb-8">
    <!-- Balance Card -->
    <div class="wallet-card">
        <div style="position: relative; z-index: 10;">
            <div class="wallet-label">{{ __('messages.wallet.current_balance') }}</div>
            <div class="wallet-balance">TZS {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</div>
            <div class="flex gap-4 mt-4">
                <a href="{{ route('withdrawals.create') }}" class="btn w-full-mobile" style="background: rgba(255,255,255,0.2); color: white; backdrop-filter: blur(10px);">
                    <i data-lucide="send"></i>
                    {{ __('messages.wallet.withdraw') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Today's Earnings -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; min-width: 50px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="trending-up" style="color: var(--primary);"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 0.875rem; color: var(--text-muted);">{{ __('messages.dashboard.todays_earnings') }}</div>
                <div style="font-size: 1.25rem; font-weight: 700;">TZS {{ number_format(auth()->user()->earningsToday(), 0) }}</div>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex justify-between" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: var(--space-2);">
                <span>{{ __('messages.tasks.tasks_remaining') }}</span>
                <span>{{ auth()->user()->remainingTasksToday() ?? '∞' }} / {{ auth()->user()->getDailyTaskLimit() ?? '∞' }}</span>
            </div>
            @php
                $limit = auth()->user()->getDailyTaskLimit();
                $completed = auth()->user()->tasksCompletedToday();
                $percentage = $limit ? min(100, ($completed / $limit) * 100) : 0;
            @endphp
            <div class="progress">
                <div class="progress-bar" style="width: {{ $percentage }}%;"></div>
            </div>
        </div>
    </div>
    
    <!-- Current Plan -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; min-width: 50px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="crown" style="color: var(--primary);"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 0.875rem; color: var(--text-muted);">{{ __('messages.dashboard.current_plan') }}</div>
                <div style="font-size: 1.25rem; font-weight: 700;">{{ auth()->user()->getPlanName() }}</div>
            </div>
        </div>
        @php
            $subscription = auth()->user()->activeSubscription;
            $daysRemaining = $subscription?->daysRemaining();
        @endphp
        @if($daysRemaining !== null)
        <div class="mt-4">
            <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.25rem;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                <span>{{ __('messages.subscriptions.expires_in') }} {{ $daysRemaining }} {{ __('messages.time.days') }}</span>
            </div>
        </div>
        @endif
        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary btn-sm mt-4" style="width: 100%;">
            <i data-lucide="arrow-up-circle"></i>
            {{ __('messages.subscriptions.upgrade') }}
        </a>
    </div>
</div>


<!-- Quick Stats -->
<div class="grid grid-4 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ auth()->user()->tasksCompletedToday() }}</div>
        <div class="stat-label">{{ __('messages.dashboard.tasks_today') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">TZS {{ number_format(auth()->user()->earningsThisMonth(), 0) }}</div>
        <div class="stat-label">{{ __('messages.dashboard.monthly_earnings') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">TZS {{ number_format(auth()->user()->wallet?->total_withdrawn ?? 0, 0) }}</div>
        <div class="stat-label">{{ __('messages.wallet.total_withdrawn') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ auth()->user()->referrals()->count() }}</div>
        <div class="stat-label">{{ __('messages.referrals.title') }}</div>
    </div>
</div>

<!-- Available Tasks -->
<div class="flex justify-between items-center mb-4">
    <h3>{{ __('messages.tasks.available') }}</h3>
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
        {{ __('messages.common.view') }} {{ __('messages.common.all') }}
        <i data-lucide="arrow-right"></i>
    </a>
</div>

<div class="grid grid-3 mb-8">
    @forelse($tasks ?? [] as $task)
    <div class="task-card">
        <div class="task-card-header flex justify-between items-center">
            <span class="task-reward">
                <i data-lucide="coins" style="width: 14px; height: 14px;"></i>
                TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}
            </span>
            <span class="task-timer">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                {{ $task->duration_seconds }}s
            </span>
        </div>
        <div class="task-card-body">
            <h4 class="mb-2">{{ $task->title }}</h4>
            <p style="font-size: 0.875rem; margin-bottom: var(--space-4);">{{ $task->description }}</p>
            
            @if($task->daily_limit)
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: var(--space-4);">
                <i data-lucide="repeat" style="width: 12px; height: 12px; display: inline;"></i>
                {{ $task->userCompletionsToday(auth()->user()) }}/{{ $task->daily_limit }} {{ __('messages.tasks.today') }}
            </div>
            @endif
            
            @if($task->canUserComplete(auth()->user()) && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary" style="width: 100%;">
                <i data-lucide="play"></i>
                {{ __('messages.tasks.start_task') }}
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="lock"></i>
                {{ __('messages.tasks.task_locked') }}
            </button>
            @else
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="check"></i>
                {{ __('messages.tasks.task_completed') }}
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="card card-body text-center" style="grid-column: span 3;">
        <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
        <h4 class="mb-2">{{ __('messages.tasks.no_tasks') }}</h4>
        <p>{{ __('messages.tasks.wait_message') }}</p>
    </div>
    @endforelse
</div>

<!-- Recent Transactions -->
<div class="flex justify-between items-center mb-4">
    <h3>{{ __('messages.dashboard.recent_activity') }}</h3>
    <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-sm">
        {{ __('messages.common.view') }} {{ __('messages.common.all') }}
        <i data-lucide="arrow-right"></i>
    </a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('messages.common.date') }}</th>
                <th>{{ __('messages.common.description') }}</th>
                <th>{{ __('messages.common.status') }}</th>
                <th style="text-align: right;">{{ __('messages.dashboard.amount_label') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTransactions ?? [] as $transaction)
            <tr>
                <td style="color: var(--text-muted);">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->getCategoryLabel() }}</td>
                <td>
                    <span class="badge {{ $transaction->isCredit() ? 'badge-success' : 'badge-error' }}">
                        {{ $transaction->isCredit() ? __('messages.dashboard.credit') : __('messages.dashboard.debit') }}
                    </span>
                </td>
                <td style="text-align: right; font-weight: 600; color: {{ $transaction->isCredit() ? 'var(--success)' : 'var(--error)' }};">
                    {{ $transaction->getFormattedAmount() }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    {{ __('messages.dashboard.no_transactions') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Referral Section -->
<div class="card mt-8" style="padding: var(--space-6); background: var(--gradient-glow);">
    <div class="flex justify-between items-center" style="flex-wrap: wrap; gap: var(--space-4);">
        <div>
            <h4 class="mb-2">
                <i data-lucide="gift" style="color: var(--primary); display: inline;"></i>
                {{ __('messages.dashboard.invite_friends_bonus') }}
            </h4>
            <p style="font-size: 0.875rem;">{{ __('messages.dashboard.share_referral_code') }}</p>
        </div>
        <div class="flex gap-4 items-center">
            <div style="padding: var(--space-3) var(--space-4); background: var(--bg-dark); border-radius: var(--radius-lg); font-family: monospace; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                {{ auth()->user()->referral_code }}
            </div>
            <button onclick="copyReferralCode()" class="btn btn-primary">
                <i data-lucide="copy"></i>
                {{ __('messages.dashboard.copy') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyReferralCode() {
        const code = '{{ auth()->user()->referral_code }}';
        const url = '{{ url('/register?ref=' . auth()->user()->referral_code) }}';
        navigator.clipboard.writeText(url).then(() => {
            alert('{{ __('messages.dashboard.referral_copied') }}');
        });
    }
</script>
@endpush
@endsection
