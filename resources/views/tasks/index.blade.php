@extends('layouts.app')

@section('title', 'Kazi')
@section('page-title', 'Kazi Zinazopatikana')
@section('page-subtitle', 'Kamilisha kazi na upate malipo!')

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
    
    .filter-tab.cpx:not(.active):hover,
    .filter-tab.cpx.active {
        --tab-color: #9B5DE5;
    }
    
    .filter-tab.monetag.active {
        background: linear-gradient(135deg, #FF6B35 0%, #FF8F00 100%);
    }
    
    .filter-tab.adsterra.active {
        background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    }
    
    .filter-tab.cpx.active {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
    }
    
    /* Enhanced Task Cards */
    .task-card-enhanced {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.05);
        overflow: hidden;
        transition: all var(--transition-base);
        position: relative;
    }
    
    .task-card-enhanced:hover {
        transform: translateY(-8px);
        border-color: var(--primary);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(16, 185, 129, 0.2);
    }
    
    .task-card-enhanced.featured {
        border: 2px solid var(--primary);
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
    }
    
    .task-card-enhanced.featured::before {
        content: '⭐ MAALUM';
        position: absolute;
        top: 0;
        right: 0;
        background: var(--gradient-primary);
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: var(--space-1) var(--space-3);
        border-radius: 0 0 0 var(--radius-lg);
        z-index: 10;
        letter-spacing: 0.05em;
    }
    
    .task-header {
        padding: var(--space-5);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: var(--space-4);
    }
    
    .task-provider-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-1);
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius-md);
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .task-provider-badge.monetag {
        background: rgba(255, 107, 53, 0.2);
        color: #FF6B35;
    }
    
    .task-provider-badge.adsterra {
        background: rgba(0, 180, 216, 0.2);
        color: #00B4D8;
    }
    
    .task-provider-badge.cpx {
        background: rgba(155, 93, 229, 0.2);
        color: #9B5DE5;
    }
    
    .task-reward-badge {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .reward-amount {
        display: flex;
        align-items: center;
        gap: var(--space-1);
        padding: var(--space-2) var(--space-3);
        background: var(--gradient-primary);
        color: white;
        font-weight: 800;
        font-size: 0.9rem;
        border-radius: var(--radius-lg);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }
    
    .task-content {
        padding: 0 var(--space-5) var(--space-5);
    }
    
    .task-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: var(--space-2);
        color: var(--text-primary);
        line-height: 1.3;
    }
    
    .task-description {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: var(--space-4);
        line-height: 1.5;
    }
    
    .task-meta {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        flex-wrap: wrap;
        margin-bottom: var(--space-4);
    }
    
    .task-meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-1);
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    
    .task-meta-item svg {
        width: 14px;
        height: 14px;
    }
    
    .task-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        width: 100%;
        padding: var(--space-4);
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: var(--radius-lg);
        border: none;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
    }
    
    .task-action-btn.start {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .task-action-btn.start:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
    }
    
    .task-action-btn.disabled {
        background: var(--bg-elevated);
        color: var(--text-muted);
        cursor: not-allowed;
    }
    
    .task-action-btn.completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
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
    
    /* Task Grid */
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-6);
    }
    
    @media (max-width: 768px) {
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
                ⚠️ Una Kazi Inayoendelea!
            </h4>
            <p style="font-size: 0.875rem; margin-bottom: 0.25rem; color: var(--text-primary);">
                <strong>{{ $activitySummary['active_task']['task']->title }}</strong>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                Sekunde {{ $activitySummary['active_task']['remaining_seconds'] }} zimebaki - Kamilisha kwanza!
            </p>
        </div>
    </div>
    <a href="{{ route('tasks.show', $activitySummary['active_task']['task']) }}" class="btn btn-warning btn-lg">
        <i data-lucide="arrow-right"></i>
        Endelea na Kazi
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
            <h2 style="font-size: 1.75rem; margin-bottom: var(--space-2);">Kazi za Leo 🔥</h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                Chagua kazi unayoipenda na uanze kupata malipo. Kazi zaidi = Pesa zaidi!
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
            <div class="stat-label">Umekamilisha Leo</div>
            <div class="stat-value">{{ auth()->user()->tasksCompletedToday() }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="target"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Limit ya Leo</div>
            <div class="stat-value">{{ auth()->user()->getDailyTaskLimit() ?? '∞' }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Malipo kwa Task</div>
            <div class="stat-value">TZS {{ number_format(auth()->user()->getRewardPerTask(), 0) }}</div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
@if(auth()->user()->getDailyTaskLimit())
<div class="card card-body mb-8">
    <div class="flex justify-between items-center mb-2">
        <span style="font-size: 0.875rem; color: var(--text-secondary);">Maendeleo ya Leo</span>
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
        <span>Umefika limit ya leo! <a href="{{ route('subscriptions.index') }}">Upgrade</a> kwa tasks zaidi.</span>
    </div>
    @endif
</div>
@endif

<!-- Filter Tabs -->
<div class="filter-container">
    <a href="{{ route('tasks.index') }}" class="filter-tab {{ !$provider ? 'active' : '' }}">
        <i data-lucide="layers" class="filter-icon"></i>
        <span>Zote</span>
        <span class="filter-count">{{ $providerCounts['all'] ?? 0 }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'monetag']) }}" class="filter-tab monetag {{ $provider === 'monetag' ? 'active' : '' }}">
        <i data-lucide="play-circle" class="filter-icon"></i>
        <span>Monetag</span>
        <span class="filter-count">{{ $providerCounts['monetag'] ?? 0 }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'adsterra']) }}" class="filter-tab adsterra {{ $provider === 'adsterra' ? 'active' : '' }}">
        <i data-lucide="globe" class="filter-icon"></i>
        <span>Adsterra</span>
        <span class="filter-count">{{ $providerCounts['adsterra'] ?? 0 }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'cpx']) }}" class="filter-tab cpx {{ $provider === 'cpx' ? 'active' : '' }}">
        <i data-lucide="bar-chart-3" class="filter-icon"></i>
        <span>CPX Research</span>
        <span class="filter-count">{{ $providerCounts['cpx'] ?? 0 }}</span>
    </a>
</div>

<!-- Section Title with current filter -->
<div class="flex justify-between items-center mb-6">
    <h3>
        @if($provider === 'monetag')
            <span style="color: #FF6B35;">🎬 Monetag Tasks</span>
        @elseif($provider === 'adsterra')
            <span style="color: #00B4D8;">🌐 Adsterra Tasks</span>
        @elseif($provider === 'cpx')
            <span style="color: #9B5DE5;">📊 CPX Research</span>
        @else
            <span style="color: var(--primary);">📋 Kazi Zote</span>
        @endif
    </h3>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
        {{ $tasks->count() }} kazi zinapatikana
    </span>
</div>

<!-- Tasks Grid -->
<div class="tasks-grid">
    @forelse($tasks as $task)
    <div class="task-card-enhanced {{ $task->is_featured ? 'featured' : '' }}">
        <div class="task-header">
            <div>
                <span class="task-provider-badge {{ $task->provider }}">
                    @if($task->provider === 'monetag')
                        <i data-lucide="play-circle" style="width: 12px; height: 12px;"></i>
                    @elseif($task->provider === 'adsterra')
                        <i data-lucide="globe" style="width: 12px; height: 12px;"></i>
                    @elseif($task->provider === 'cpx')
                        <i data-lucide="bar-chart-3" style="width: 12px; height: 12px;"></i>
                    @else
                        <i data-lucide="star" style="width: 12px; height: 12px;"></i>
                    @endif
                    {{ ucfirst($task->provider ?? 'Custom') }}
                </span>
            </div>
            <div class="task-reward-badge">
                <span class="reward-amount">
                    <i data-lucide="coins" style="width: 16px; height: 16px;"></i>
                    TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}
                </span>
            </div>
        </div>
        
        <div class="task-content">
            <h4 class="task-title">{{ $task->title }}</h4>
            <p class="task-description">{{ Str::limit($task->description, 80) }}</p>
            
            <div class="task-meta">
                <div class="task-meta-item">
                    <i data-lucide="clock"></i>
                    <span>{{ $task->duration_seconds }} sekunde</span>
                </div>
                
                @if($task->daily_limit)
                <div class="task-meta-item">
                    <i data-lucide="repeat"></i>
                    <span>{{ $task->userCompletionsToday(auth()->user()) }}/{{ $task->daily_limit }} leo</span>
                </div>
                @endif
            </div>
            
            @if($task->canUserComplete(auth()->user()) && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $task) }}" class="task-action-btn start">
                <i data-lucide="play" style="width: 18px; height: 18px;"></i>
                Anza Kazi
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="task-action-btn disabled" disabled>
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                Umefika Limit ya Leo
            </button>
            @else
            <button class="task-action-btn completed" disabled>
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                Umekamilisha Leo
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
                Hakuna Kazi za {{ ucfirst($provider) }}
            @else
                Hakuna Kazi Kwa Sasa
            @endif
        </h3>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-6);">
            @if($provider)
                Kazi za {{ ucfirst($provider) }} zitaongezwa hivi karibuni. Jaribu filter nyingine!
            @else
                Kazi mpya zitaongezwa hivi karibuni. Rudi baadaye!
            @endif
        </p>
        @if($provider)
        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
            <i data-lucide="layers"></i>
            Angalia Kazi Zote
        </a>
        @endif
    </div>
    @endforelse
</div>
@endsection
