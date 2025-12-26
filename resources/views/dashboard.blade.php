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

    /* Announcement Popup Modal */
    .announcement-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .announcement-modal {
        background: linear-gradient(145deg, #1a1a1a 0%, #0f0f0f 100%);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.1);
        max-width: 450px;
        width: 100%;
        overflow: hidden;
        animation: slideUp 0.4s ease;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .announcement-modal-header {
        padding: var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .announcement-modal-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .announcement-modal-icon.info {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .announcement-modal-icon.success {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }

    .announcement-modal-icon.warning {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .announcement-modal-icon.urgent {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .announcement-modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
    }

    .announcement-modal-body {
        padding: var(--space-5);
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.6;
        max-height: 300px;
        overflow-y: auto;
    }

    .announcement-modal-body p {
        white-space: pre-wrap;
    }

    .announcement-modal-footer {
        padding: var(--space-4) var(--space-5);
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .announcement-modal-date {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .announcement-ok-btn {
        padding: var(--space-3) var(--space-6);
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .announcement-ok-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
    }
</style>
@endpush

@section('content')
<!-- Announcement Popup Modal -->
@if(isset($popupAnnouncements) && $popupAnnouncements->count() > 0)
    @foreach($popupAnnouncements as $index => $announcement)
    <div class="announcement-modal-overlay" id="announcementModal{{ $announcement->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
        <div class="announcement-modal">
            <div class="announcement-modal-header">
                <div class="announcement-modal-icon {{ $announcement->type }}">
                    @if($announcement->type === 'success')
                        ✅
                    @elseif($announcement->type === 'warning')
                        ⚠️
                    @elseif($announcement->type === 'urgent')
                        🚨
                    @else
                        📢
                    @endif
                </div>
                <div class="announcement-modal-title">{{ $announcement->title }}</div>
            </div>
            <div class="announcement-modal-body">
                <p>{{ $announcement->body }}</p>
            </div>
            <div class="announcement-modal-footer">
                <span class="announcement-modal-date">
                    {{ $announcement->created_at->timezone('Africa/Dar_es_Salaam')->format('d M Y, H:i') }}
                </span>
                <button class="announcement-ok-btn" onclick="dismissAnnouncement({{ $announcement->id }}, {{ $index }})">
                    OK, Nimeelewa
                </button>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        const announcementIds = @json($popupAnnouncements->pluck('id')->toArray());
        let currentAnnouncementIndex = 0;

        function dismissAnnouncement(id, index) {
            // Hide current modal
            const modal = document.getElementById('announcementModal' + id);
            if (modal) {
                modal.style.display = 'none';
            }

            // Record the view via AJAX
            fetch('/announcements/' + id + '/dismiss', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).catch(console.error);

            // Show next announcement if exists
            currentAnnouncementIndex++;
            if (currentAnnouncementIndex < announcementIds.length) {
                const nextId = announcementIds[currentAnnouncementIndex];
                const nextModal = document.getElementById('announcementModal' + nextId);
                if (nextModal) {
                    nextModal.style.display = 'flex';
                }
            }
        }
    </script>
@endif

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

<div class="dashboard-tasks-grid mb-8">
    @forelse($tasks ?? [] as $taskData)
    @php
        $taskObj = $taskData['task'];
        $isUnlimited = $taskData['is_unlimited'] ?? false;
        $dynamicLimit = $taskData['daily_limit'];
        $completionsToday = $taskData['completions_today'];
        $canComplete = $taskData['can_complete'];
    @endphp
    <div class="dash-task-card {{ $taskData['is_featured'] ? 'featured' : '' }}">
        <div class="dash-task-header">
            <div class="dash-task-left">
                {{-- Task Type Icon with Emoji --}}
                <div class="dash-task-icon {{ $taskData['provider'] }}">
                    @if($taskData['provider'] === 'monetag')
                        <span>🚀</span>
                    @elseif($taskData['provider'] === 'adsterra')
                        <span>🔗</span>
                    @else
                        <span>⭐</span>
                    @endif
                </div>
                <span class="dash-task-badge {{ $taskData['provider'] }}">
                    @if($taskData['provider'] === 'monetag')
                        SkyBoost™
                    @elseif($taskData['provider'] === 'adsterra')
                        SkyLinks™
                    @else
                        SkyTask™
                    @endif
                </span>
            </div>
            <div class="dash-task-reward">
                💰 TZS {{ number_format($taskData['reward'], 0) }}
            </div>
        </div>
        
        <div class="dash-task-body">
            <h4 class="dash-task-title">{{ Str::limit($taskData['title'], 25) }}</h4>
            <p class="dash-task-desc">{{ Str::limit($taskData['description'], 50) }}</p>
            
            <div class="dash-task-meta">
                <span>⏱️ {{ $taskData['duration_seconds'] }}s</span>
                @if($isUnlimited)
                    <span style="color: #f59e0b; font-weight: 600;">♾️ ∞</span>
                @elseif($dynamicLimit)
                    <span>🔄 {{ $completionsToday }}/{{ $dynamicLimit }}</span>
                @endif
            </div>
            
            @if($canComplete && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $taskObj) }}" class="dash-task-btn start">
                ▶️ {{ __('messages.tasks.start_task') }}
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="dash-task-btn locked" disabled>
                🔒 {{ __('messages.tasks.task_locked') }}
            </button>
            @else
            <button class="dash-task-btn completed" disabled>
                ✅ {{ __('messages.tasks.task_completed') }}
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="card card-body text-center" style="grid-column: span 3;">
        <div style="font-size: 3rem; margin-bottom: var(--space-4);">📭</div>
        <h4 class="mb-2">{{ __('messages.tasks.no_tasks') }}</h4>
        <p style="color: var(--text-muted);">{{ __('messages.tasks.wait_message') }}</p>
    </div>
    @endforelse
</div>

<style>
/* Dashboard Task Cards - Compact with Emojis */
.dashboard-tasks-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4);
}

@media (max-width: 768px) {
    .dashboard-tasks-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-3);
    }
}

@media (max-width: 480px) {
    .dashboard-tasks-grid {
        grid-template-columns: 1fr;
    }
}

.dash-task-card {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.dash-task-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
}

.dash-task-card.featured {
    border-color: var(--primary);
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
}

.dash-task-header {
    padding: var(--space-3);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.dash-task-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dash-task-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.dash-task-icon.monetag {
    background: linear-gradient(135deg, #FF6B35, #FF8F00);
    box-shadow: 0 3px 10px rgba(255, 107, 53, 0.3);
}

.dash-task-icon.adsterra {
    background: linear-gradient(135deg, #00B4D8, #0077B6);
    box-shadow: 0 3px 10px rgba(0, 180, 216, 0.3);
}

.dash-task-icon:not(.monetag):not(.adsterra) {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
}

.dash-task-badge {
    font-size: 0.55rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: var(--radius-sm);
}

.dash-task-badge.monetag {
    background: rgba(255, 107, 53, 0.15);
    color: #FF6B35;
}

.dash-task-badge.adsterra {
    background: rgba(0, 180, 216, 0.15);
    color: #00B4D8;
}

.dash-task-badge:not(.monetag):not(.adsterra) {
    background: rgba(16, 185, 129, 0.15);
    color: var(--primary);
}

.dash-task-reward {
    background: var(--gradient-primary);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: var(--radius-md);
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}

.dash-task-body {
    padding: var(--space-3);
}

.dash-task-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: var(--text-primary);
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dash-task-desc {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-bottom: var(--space-2);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.dash-task-meta {
    display: flex;
    gap: var(--space-3);
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-bottom: var(--space-3);
}

.dash-task-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: var(--space-2);
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--radius-md);
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}

.dash-task-btn.start {
    background: var(--gradient-primary);
    color: white;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
}

.dash-task-btn.start:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.35);
}

.dash-task-btn.locked {
    background: var(--bg-elevated);
    color: var(--text-muted);
    cursor: not-allowed;
}

.dash-task-btn.completed {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}
</style>

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
