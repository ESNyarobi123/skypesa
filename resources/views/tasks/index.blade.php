@extends('layouts.app')

@section('title', __('messages.tasks.title'))
@section('page-title', __('messages.tasks.available'))
@section('page-subtitle', __('messages.tasks.subtitle'))

@push('styles')
<style>
    /* Task Page Specific Styles */
    .tasks-hero {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.05) 50%, transparent 100%);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        margin-bottom: var(--space-8);
        position: relative;
        overflow: hidden;
    }
    
    .tasks-hero::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    .tasks-hero::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
        animation: float 8s ease-in-out infinite reverse;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }
    
    .hero-icon-wrapper {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 40px rgba(16, 185, 129, 0.4);
        animation: pulse-glow 2s ease-in-out infinite;
    }
    
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 60px rgba(16, 185, 129, 0.6); }
    }
    
    /* Filter Tabs */
    .filter-container {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        padding: var(--space-2);
        margin-bottom: var(--space-8);
        border: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        gap: var(--space-2);
        flex-wrap: wrap;
        position: sticky;
        top: 20px;
        z-index: 100;
        backdrop-filter: blur(12px);
    }
    
    .filter-tab {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
        background: transparent;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }
    
    .filter-tab:hover {
        color: var(--text-primary);
        background: rgba(255, 255, 255, 0.05);
    }
    
    .filter-tab.active {
        color: white;
        background: var(--gradient-primary);
        border-color: var(--primary);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }
    
    .filter-tab.active .filter-count {
        background: white;
        color: var(--primary);
    }
    
    .filter-icon {
        width: 20px;
        height: 20px;
    }
    
    .filter-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 var(--space-2);
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }
    
    /* Provider specific colors */
    .filter-tab.monetag:not(.active):hover,
    .filter-tab.monetag.active {
        --tab-color: #FF6B35;
    }
    
    .filter-tab.adsterra:not(.active):hover,
    .filter-tab.adsterra.active {
        --tab-color: #00B4D8;
    }
    

    
    .filter-tab.monetag.active {
        background: linear-gradient(135deg, #FF6B35 0%, #FF8F00 100%);
    }
    
    .filter-tab.adsterra.active {
        background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    }
    

    
    /* Enhanced Task Cards - Compact */
    .task-card-enhanced {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid rgba(255, 255, 255, 0.05);
        overflow: hidden;
        transition: all var(--transition-base);
        position: relative;
    }
    
    .task-card-enhanced:hover {
        transform: translateY(-4px);
        border-color: var(--primary);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3), 0 0 20px rgba(16, 185, 129, 0.15);
    }
    
    .task-card-enhanced.featured {
        border: 1px solid var(--primary);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
    }
    
    .task-card-enhanced.featured::before {
        content: '⭐';
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--gradient-primary);
        color: white;
        font-size: 0.6rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: var(--radius-md);
        z-index: 10;
    }
    
    .task-header {
        padding: var(--space-3) var(--space-4);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--space-3);
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }
    
    /* Task Type Icon - Colorful with Emoji */
    .task-type-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.3rem;
        position: relative;
        overflow: hidden;
    }
    
    .task-type-icon::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.15;
        border-radius: inherit;
    }
    
    .task-type-icon.monetag {
        background: linear-gradient(135deg, #FF6B35 0%, #FF8F00 100%);
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    
    .task-type-icon.adsterra {
        background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
        box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
    }
    
    .task-type-icon.default, .task-type-icon:not(.monetag):not(.adsterra) {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .task-type-icon .emoji {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }
    
    .task-provider-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: var(--radius-sm);
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    
    .task-provider-badge.monetag {
        background: rgba(255, 107, 53, 0.15);
        color: #FF6B35;
    }
    
    .task-provider-badge.adsterra {
        background: rgba(0, 180, 216, 0.15);
        color: #00B4D8;
    }
    
    .task-provider-badge.cpx {
        background: rgba(155, 93, 229, 0.15);
        color: #9B5DE5;
    }
    
    .task-reward-badge {
        display: flex;
        align-items: center;
    }
    
    .reward-amount {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: var(--gradient-primary);
        color: white;
        font-weight: 700;
        font-size: 0.8rem;
        border-radius: var(--radius-md);
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.25);
    }
    
    .reward-amount svg {
        width: 14px;
        height: 14px;
    }
    
    .task-content {
        padding: var(--space-3) var(--space-4) var(--space-4);
    }
    
    .task-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: var(--space-1);
        color: var(--text-primary);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .task-description {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-bottom: var(--space-3);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .task-meta {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        flex-wrap: wrap;
        margin-bottom: var(--space-3);
    }
    
    .task-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    
    .task-meta-item svg {
        width: 12px;
        height: 12px;
    }
    
    .task-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: var(--space-3);
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
    }
    
    .task-action-btn svg {
        width: 16px;
        height: 16px;
    }
    
    .task-action-btn.start {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
    }
    
    .task-action-btn.start:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.35);
    }
    
    .task-action-btn.disabled {
        background: var(--bg-elevated);
        color: var(--text-muted);
        cursor: not-allowed;
        font-size: 0.75rem;
    }
    
    .task-action-btn.completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
        font-size: 0.75rem;
    }
    
    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: var(--space-16);
        background: var(--bg-card);
        border-radius: var(--radius-2xl);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .empty-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto var(--space-6);
        background: var(--gradient-glow);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .empty-icon svg {
        width: 50px;
        height: 50px;
        color: var(--primary);
    }
    
    /* Stats Cards Enhanced */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-container {
            flex-direction: column;
        }
        
        .filter-tab {
            justify-content: center;
        }
    }
    
    .stat-card-glass {
        background: linear-gradient(135deg, rgba(26, 26, 26, 0.9), rgba(26, 26, 26, 0.5));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-4);
        transition: all var(--transition-base);
    }
    
    .stat-card-glass:hover {
        transform: translateY(-4px);
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .stat-icon-wrapper {
        width: 55px;
        height: 55px;
        background: var(--gradient-glow);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .stat-icon-wrapper svg {
        width: 28px;
        height: 28px;
        color: var(--primary);
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: var(--space-1);
    }
    
    .stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    /* Active Task Alert Enhanced */
    .active-task-alert {
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.12), rgba(245, 158, 11, 0.08));
        border: 2px solid var(--warning);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        margin-bottom: var(--space-8);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--space-4);
        animation: pulse-border 2s ease-in-out infinite;
    }
    
    @keyframes pulse-border {
        0%, 100% { border-color: var(--warning); }
        50% { border-color: rgba(245, 158, 11, 0.5); }
    }
    
    .active-task-icon {
        width: 60px;
        height: 60px;
        background: var(--warning);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }
    
    .active-task-icon svg {
        width: 30px;
        height: 30px;
        color: white;
    }
    
    /* Task Grid - Compact */
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: var(--space-4);
    }
    
    @media (min-width: 1200px) {
        .tasks-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .tasks-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-3);
        }
    }
    
    @media (max-width: 480px) {
        .tasks-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- Active Task Warning -->
@if($activitySummary['has_active_task'])
<div class="active-task-alert">
    <div class="flex items-center gap-4">
        <div class="active-task-icon">
            <i data-lucide="clock"></i>
        </div>
        <div>
            <h4 style="color: var(--warning); margin-bottom: 0.25rem; font-size: 1rem;">
                ⚠️ {{ __('messages.tasks.in_progress') }}!
            </h4>
            <p style="font-size: 0.875rem; margin-bottom: 0.25rem; color: var(--text-primary);">
                <strong>{{ $activitySummary['active_task']['task']->title }}</strong>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                {{ $activitySummary['active_task']['remaining_seconds'] }} {{ __('messages.time.seconds_ago') }} - {{ __('messages.tasks.complete_task') }}!
            </p>
        </div>
    </div>
    <a href="{{ route('tasks.show', $activitySummary['active_task']['task']) }}" class="btn btn-warning btn-lg">
        <i data-lucide="arrow-right"></i>
        {{ __('messages.common.next') }}
    </a>
</div>
@endif

<!-- Hero Stats Section -->
<div class="tasks-hero">
    <div class="flex items-center gap-6" style="position: relative; z-index: 10; flex-wrap: wrap;">
        <div class="hero-icon-wrapper">
            <i data-lucide="briefcase" style="color: white; width: 40px; height: 40px;"></i>
        </div>
        <div>
            <h2 style="font-size: 1.75rem; margin-bottom: var(--space-2);">{{ __('messages.tasks.todays_tasks') }} 🔥</h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                {{ __('messages.tasks.choose_task') }}
            </p>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-grid">
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.tasks.completed') }}</div>
            <div class="stat-value">{{ auth()->user()->tasksCompletedToday() }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="target"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.tasks.daily_limit') }}</div>
            <div class="stat-value">{{ auth()->user()->getDailyTaskLimit() ?? '∞' }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.tasks.reward') }}</div>
            <div class="stat-value">TZS {{ number_format(auth()->user()->getRewardPerTask(), 0) }}</div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
@if(auth()->user()->getDailyTaskLimit())
<div class="card card-body mb-8">
    <div class="flex justify-between items-center mb-2">
        <span style="font-size: 0.875rem; color: var(--text-secondary);">{{ __('messages.tasks.todays_progress') }}</span>
        <span style="font-size: 0.875rem; font-weight: 600; color: var(--primary);">
            {{ auth()->user()->tasksCompletedToday() }} / {{ auth()->user()->getDailyTaskLimit() }}
        </span>
    </div>
    @php
        $limit = auth()->user()->getDailyTaskLimit();
        $completed = auth()->user()->tasksCompletedToday();
        $percentage = min(100, ($completed / $limit) * 100);
    @endphp
    <div class="progress" style="height: 12px;">
        <div class="progress-bar" style="width: {{ $percentage }}%;"></div>
    </div>
    @if($percentage >= 100)
    <div class="alert alert-warning mt-4">
        <i data-lucide="alert-circle"></i>
        <span>{{ __('messages.tasks.limit_reached') }} <a href="{{ route('subscriptions.index') }}">{{ __('subscriptions.upgrade') }}</a> {{ __('messages.tasks.upgrade_for_more') }}</span>
    </div>
    @endif
</div>
@endif

<!-- Plan-based Task Limit Info Banner -->
@if(isset($planInfo) && !$planInfo['is_unlimited'])
<div class="card mb-8" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.1)); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-xl); padding: var(--space-5);">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #3b82f6); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                <i data-lucide="target" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 0.25rem; color: var(--text-primary);">
                    📊 Mpango: <span style="color: var(--primary); font-weight: 700;">{{ $planInfo['name'] }}</span>
                </h4>
                <p style="font-size: 0.875rem; color: var(--text-secondary);">
                    Una <strong style="color: var(--primary);">{{ $planInfo['total_slots'] }}</strong> task slots kwa siku
                    (Tasks {{ $planInfo['tasks_shown'] }} × limits tofauti)
                </p>
            </div>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #3b82f6); border: none; padding: 0.75rem 1.5rem;">
            <i data-lucide="crown" style="width: 18px; height: 18px;"></i>
            Upgrade kwa Zaidi
        </a>
    </div>
</div>
@elseif(isset($planInfo) && $planInfo['is_unlimited'])
<!-- VIP Unlimited Banner -->
<div class="card mb-8" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(16, 185, 129, 0.1)); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--radius-xl); padding: var(--space-5);">
    <div class="flex items-center gap-4">
        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.5); animation: pulse 2s infinite;">
            <span style="font-size: 1.5rem;">👑</span>
        </div>
        <div>
            <h4 style="font-size: 1rem; margin-bottom: 0.25rem; color: var(--text-primary);">
                ✨ <span style="background: linear-gradient(135deg, #f59e0b, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 700;">{{ $planInfo['name'] }}</span> - Unlimited Access!
            </h4>
            <p style="font-size: 0.875rem; color: var(--text-secondary);">
                Una <strong style="color: #f59e0b;">∞ UNLIMITED</strong> tasks kwa siku. Fanya tasks kadri unavyotaka! 🚀
            </p>
        </div>
    </div>
</div>
@endif

<!-- Filter Tabs -->
<div class="filter-container">
    <a href="{{ route('tasks.index') }}" class="filter-tab {{ !$provider ? 'active' : '' }}">
        <i data-lucide="layers" class="filter-icon"></i>
        <span>{{ __('messages.common.all') }}</span>
        <span class="filter-count">{{ isset($planInfo) && $planInfo['is_unlimited'] ? '∞' : ($providerCounts['all'] ?? 0) }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'monetag']) }}" class="filter-tab monetag {{ $provider === 'monetag' ? 'active' : '' }}">
        <i data-lucide="rocket" class="filter-icon"></i>
        <span>SkyBoost™</span>
        <span class="filter-count">{{ isset($planInfo) && $planInfo['is_unlimited'] ? '∞' : ($providerCounts['monetag'] ?? 0) }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'adsterra']) }}" class="filter-tab adsterra {{ $provider === 'adsterra' ? 'active' : '' }}">
        <i data-lucide="link" class="filter-icon"></i>
        <span>SkyLinks™</span>
        <span class="filter-count">{{ isset($planInfo) && $planInfo['is_unlimited'] ? '∞' : ($providerCounts['adsterra'] ?? 0) }}</span>
    </a>
    

</div>

<!-- Section Title with current filter -->
<div class="flex justify-between items-center mb-6">
    <h3>
        @if($provider === 'monetag')
            <span style="color: #FF6B35;">🚀 SkyBoost™</span>
        @elseif($provider === 'adsterra')
            <span style="color: #00B4D8;">🔗 SkyLinks™</span>
        @else
            <span style="color: var(--primary);">📋 {{ __('messages.tasks.available') }}</span>
        @endif
    </h3>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
        @if(isset($planInfo) && !$planInfo['is_unlimited'])
            <span style="color: var(--primary); font-weight: 600;">{{ collect($tasks)->sum('daily_limit') }}</span> slots
            <span style="font-size: 0.7rem; color: var(--text-secondary);">({{ $planInfo['name'] }} - {{ count($tasks) }} tasks)</span>
        @elseif(isset($planInfo) && $planInfo['is_unlimited'])
            <span style="color: #f59e0b; font-weight: 600;">∞ Unlimited</span>
            <span style="font-size: 0.7rem; color: #f59e0b;">👑 {{ $planInfo['name'] }} - {{ count($tasks) }} tasks</span>
        @else
            {{ count($tasks) }} {{ __('messages.tasks.available') }}
        @endif
    </span>
</div>

<!-- Tasks Grid (for Monetag, Adsterra, or All) -->
<div class="tasks-grid">
    @forelse($tasks as $taskData)
    @php
        $taskObj = $taskData['task'];
        $dynamicLimit = $taskData['daily_limit'];
        $completionsToday = $taskData['completions_today'];
        $canComplete = $taskData['can_complete'];
        $remaining = $taskData['remaining'] ?? 0;
    @endphp
    <div class="task-card-enhanced {{ $taskData['is_featured'] ? 'featured' : '' }}">
        <div class="task-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                {{-- Task Type Icon with Emoji --}}
                <div class="task-type-icon {{ $taskData['provider'] }}">
                    @if($taskData['provider'] === 'monetag')
                        <span class="emoji">🚀</span>
                    @elseif($taskData['provider'] === 'adsterra')
                        <span class="emoji">🔗</span>
                    @else
                        <span class="emoji">⭐</span>
                    @endif
                </div>
                <span class="task-provider-badge {{ $taskData['provider'] }}">
                    @if($taskData['provider'] === 'monetag')
                        SkyBoost™
                    @elseif($taskData['provider'] === 'adsterra')
                        SkyLinks™
                    @else
                        SkyTask™
                    @endif
                </span>
            </div>
            <div class="task-reward-badge">
                <span class="reward-amount">
                    💰 TZS {{ number_format($taskData['reward'], 0) }}
                </span>
            </div>
        </div>
        
        <div class="task-content">
            <h4 class="task-title">{{ $taskData['title'] }}</h4>
            <p class="task-description">{{ Str::limit($taskData['description'], 80) }}</p>
            
            <div class="task-meta">
                <div class="task-meta-item">
                    ⏱️ <span>{{ $taskData['duration_seconds'] }}s</span>
                </div>
                
                @if(isset($planInfo) && $planInfo['is_unlimited'])
                {{-- VIP/Unlimited plan - ALWAYS show infinity symbol --}}
                <div class="task-meta-item" style="color: #f59e0b;">
                    <span style="font-weight: 600;">♾️ ∞</span>
                </div>
                @elseif($dynamicLimit)
                {{-- Limited plan - show numeric limit --}}
                <div class="task-meta-item" style="{{ $remaining <= 0 ? 'color: var(--danger);' : ($remaining <= 2 ? 'color: var(--warning);' : '') }}">
                    🔄 <span>{{ $completionsToday }}/{{ $dynamicLimit }}</span>
                </div>
                @endif
            </div>
            
            @if($activitySummary['has_active_task'])
            {{-- User has active task - show locked button --}}
            <a href="{{ route('tasks.show', $activitySummary['active_task']['task']) }}" class="task-action-btn disabled" style="background: rgba(234, 179, 8, 0.2); color: var(--warning); border: 1px solid var(--warning);">
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                {{ __('messages.tasks.in_progress') }}
            </a>
            @elseif($canComplete && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $taskObj) }}" class="task-action-btn start">
                <i data-lucide="play" style="width: 18px; height: 18px;"></i>
                {{ __('messages.tasks.start_task') }}
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="task-action-btn disabled" disabled>
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                {{ __('messages.tasks.task_locked') }}
            </button>
            @else
            <button class="task-action-btn completed" disabled>
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                {{ __('messages.tasks.task_completed') }}
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon">
            <i data-lucide="inbox"></i>
        </div>
        <h3 style="margin-bottom: var(--space-2);">
            @if($provider)
                {{ __('messages.tasks.no_tasks') }} {{ ucfirst($provider) }}
            @else
                {{ __('messages.tasks.no_tasks') }}
            @endif
        </h3>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-6);">
            {{ __('messages.tasks.wait_message') }}
        </p>
        @if($provider)
        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
            <i data-lucide="layers"></i>
            {{ __('messages.common.view') }} {{ __('messages.common.all') }}
        </a>
        @endif
    </div>
    @endforelse
</div>
@endsection
