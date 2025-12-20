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
    
    .filter-tab.bitlabs:not(.active):hover,
    .filter-tab.bitlabs.active {
        --tab-color: #9B5DE5;
    }
    
    .filter-tab.monetag.active {
        background: linear-gradient(135deg, #FF6B35 0%, #FF8F00 100%);
    }
    
    .filter-tab.adsterra.active {
        background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    }
    
    .filter-tab.bitlabs.active {
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
        <i data-lucide="rocket" class="filter-icon"></i>
        <span>SkyBoost™</span>
        <span class="filter-count">{{ $providerCounts['monetag'] ?? 0 }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'adsterra']) }}" class="filter-tab adsterra {{ $provider === 'adsterra' ? 'active' : '' }}">
        <i data-lucide="link" class="filter-icon"></i>
        <span>SkyLinks™</span>
        <span class="filter-count">{{ $providerCounts['adsterra'] ?? 0 }}</span>
    </a>
    
    <a href="{{ route('tasks.index', ['provider' => 'bitlabs']) }}" class="filter-tab bitlabs {{ $provider === 'bitlabs' ? 'active' : '' }}">
        <i data-lucide="message-circle" class="filter-icon"></i>
        <span>SkyOpinions™</span>
        <span class="filter-count">{{ $providerCounts['bitlabs'] ?? 0 }}</span>
    </a>
</div>

<!-- Section Title with current filter -->
<div class="flex justify-between items-center mb-6">
    <h3>
        @if($provider === 'monetag')
            <span style="color: #FF6B35;">🚀 SkyBoost™</span>
        @elseif($provider === 'adsterra')
            <span style="color: #00B4D8;">🔗 SkyLinks™</span>
        @elseif($provider === 'bitlabs')
            <span style="color: #9B5DE5;">💬 SkyOpinions™</span>
        @else
            <span style="color: var(--primary);">📋 Kazi Zote</span>
        @endif
    </h3>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
        @if($provider === 'bitlabs')
            Maoni zinapatikana
        @else
            {{ $tasks->count() }} fursa zinapatikana
        @endif
    </span>
</div>

@if($provider === 'bitlabs')
<!-- BitLabs Enhanced Section -->
<style>
    .cpx-hero {
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.15) 0%, rgba(123, 44, 191, 0.08) 50%, transparent 100%);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        margin-bottom: var(--space-6);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(155, 93, 229, 0.2);
    }
    
    .cpx-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(155, 93, 229, 0.25) 0%, transparent 70%);
        animation: cpx-float 6s ease-in-out infinite;
    }
    
    .cpx-hero::after {
        content: '';
        position: absolute;
        bottom: -40px;
        left: -40px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(155, 93, 229, 0.15) 0%, transparent 70%);
        animation: cpx-float 8s ease-in-out infinite reverse;
    }
    
    @keyframes cpx-float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-15px) rotate(5deg); }
    }
    
    .cpx-icon-wrapper {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 35px rgba(155, 93, 229, 0.5);
        animation: cpx-pulse 2s ease-in-out infinite;
    }
    
    @keyframes cpx-pulse {
        0%, 100% { box-shadow: 0 0 35px rgba(155, 93, 229, 0.5); }
        50% { box-shadow: 0 0 50px rgba(155, 93, 229, 0.7); }
    }
    
    .cpx-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }
    
    @media (max-width: 768px) {
        .cpx-stats-row {
            grid-template-columns: 1fr;
        }
    }
    
    .cpx-stat-card {
        background: linear-gradient(135deg, rgba(26, 26, 26, 0.95), rgba(26, 26, 26, 0.7));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(155, 93, 229, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-4);
        transition: all 0.3s ease;
    }
    
    .cpx-stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(155, 93, 229, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), 0 0 20px rgba(155, 93, 229, 0.15);
    }
    
    .cpx-stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.25), rgba(155, 93, 229, 0.1));
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .cpx-stat-icon svg {
        width: 24px;
        height: 24px;
        color: #9B5DE5;
    }
    
    .cpx-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .cpx-stat-value.success { color: #10B981; }
    .cpx-stat-value.purple { color: #9B5DE5; }
    
    .cpx-wall-container {
        background: var(--bg-card);
        border-radius: var(--radius-2xl);
        border: 1px solid rgba(155, 93, 229, 0.15);
        overflow: hidden;
        margin-bottom: var(--space-6);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
    }
    
    .cpx-wall-header {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        padding: var(--space-5) var(--space-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--space-4);
    }
    
    .cpx-wall-header h3 {
        color: white;
        font-size: 1.15rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin: 0;
    }
    
    .cpx-live-badge {
        background: white;
        color: #7B2CBF;
        padding: 6px 14px;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .cpx-live-dot {
        width: 8px;
        height: 8px;
        background: #10B981;
        border-radius: 50%;
        animation: cpx-blink 1s infinite;
    }
    
    @keyframes cpx-blink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }
    
    .cpx-info-bar {
        background: rgba(155, 93, 229, 0.08);
        border-bottom: 1px solid rgba(155, 93, 229, 0.15);
        padding: var(--space-4) var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }
    
    .cpx-info-icon {
        width: 36px;
        height: 36px;
        background: rgba(155, 93, 229, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .cpx-frame-wrapper {
        position: relative;
        min-height: 650px;
        background: linear-gradient(180deg, #1a1a1a 0%, #0d0d0d 100%);
    }
    
    .cpx-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: opacity 0.3s ease;
    }
    
    .cpx-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(155, 93, 229, 0.2);
        border-top-color: #9B5DE5;
        border-radius: 50%;
        animation: cpx-spin 1s linear infinite;
    }
    
    @keyframes cpx-spin {
        to { transform: rotate(360deg); }
    }
    
    .cpx-wall-actions {
        background: rgba(155, 93, 229, 0.05);
        border-top: 1px solid rgba(155, 93, 229, 0.15);
        padding: var(--space-5) var(--space-6);
        display: flex;
        justify-content: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    
    .cpx-action-btn {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .cpx-action-btn.primary {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(155, 93, 229, 0.3);
    }
    
    .cpx-action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(155, 93, 229, 0.4);
    }
    
    .cpx-action-btn.secondary {
        background: var(--bg-elevated);
        color: var(--text-primary);
        border: 1px solid rgba(155, 93, 229, 0.2);
    }
    
    .cpx-action-btn.secondary:hover {
        background: rgba(155, 93, 229, 0.15);
        border-color: rgba(155, 93, 229, 0.4);
    }
    
    .cpx-action-btn svg {
        width: 16px;
        height: 16px;
    }
    
    .cpx-rewards-section {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        margin-bottom: var(--space-6);
        position: relative;
        overflow: hidden;
    }
    
    .cpx-rewards-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .cpx-rewards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-4);
    }
    
    @media (max-width: 768px) {
        .cpx-rewards-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .cpx-reward-card {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .cpx-reward-card:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.18);
    }
    
    .cpx-reward-card.premium {
        border-color: #fbbf24;
        background: rgba(255, 255, 255, 0.18);
    }
    
    .cpx-reward-amount {
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
        margin-bottom: 4px;
    }
    
    .cpx-reward-card.premium .cpx-reward-amount {
        color: #fbbf24;
    }
    
    .cpx-reward-duration {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: var(--space-3);
    }
    
    .cpx-reward-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        color: white;
        font-weight: 600;
    }
    
    .cpx-reward-card.premium .cpx-reward-badge {
        background: rgba(251, 191, 36, 0.3);
        color: #fbbf24;
    }
    
    .cpx-tips-section {
        background: var(--bg-card);
        border-radius: var(--radius-2xl);
        padding: var(--space-6);
        border: 1px solid rgba(155, 93, 229, 0.1);
    }
    
    .cpx-tips-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-4);
    }
    
    @media (max-width: 768px) {
        .cpx-tips-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .cpx-tip-card {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-4);
        background: var(--bg-elevated);
        border-radius: var(--radius-xl);
        transition: all 0.3s ease;
    }
    
    .cpx-tip-card:hover {
        transform: translateX(4px);
        background: rgba(155, 93, 229, 0.08);
    }
    
    .cpx-tip-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.2), rgba(155, 93, 229, 0.1));
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .cpx-tip-content h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 2px;
        color: var(--text-primary);
    }
    
    .cpx-tip-content p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.4;
    }
</style>

<!-- SkyOpinions Hero Section -->
<div class="cpx-hero">
    <div class="flex items-center gap-5" style="position: relative; z-index: 10; flex-wrap: wrap;">
        <div class="cpx-icon-wrapper">
            <i data-lucide="message-circle" style="color: white; width: 35px; height: 35px;"></i>
        </div>
        <div>
            <h2 style="font-size: 1.5rem; margin-bottom: var(--space-2); color: var(--text-primary);">SkyOpinions™ 💬</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
                Shiriki maoni yako na upate malipo halisi moja kwa moja kwenye wallet yako!
            </p>
        </div>
    </div>
</div>

<!-- CPX Stats Row -->
@php
    $cpxUser = auth()->user();
    $cpxTodayCompleted = \App\Models\SurveyCompletion::where('user_id', $cpxUser->id)->whereDate('created_at', today())->count();
    $cpxTodayEarned = \App\Models\SurveyCompletion::where('user_id', $cpxUser->id)->whereDate('created_at', today())->where('status', 'completed')->sum('user_reward');
    $cpxTotalEarned = \App\Models\SurveyCompletion::where('user_id', $cpxUser->id)->where('status', 'completed')->sum('user_reward');
@endphp
<div class="cpx-stats-row">
    <div class="cpx-stat-card">
        <div class="cpx-stat-icon">
            <i data-lucide="check-circle"></i>
        </div>
        <div>
            <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted);">Leo Umekamilisha</div>
            <div class="cpx-stat-value">{{ $cpxTodayCompleted }}</div>
        </div>
    </div>
    <div class="cpx-stat-card">
        <div class="cpx-stat-icon">
            <i data-lucide="coins"></i>
        </div>
        <div>
            <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted);">Umepata Leo</div>
            <div class="cpx-stat-value success">TZS {{ number_format($cpxTodayEarned, 0) }}</div>
        </div>
    </div>
    <div class="cpx-stat-card">
        <div class="cpx-stat-icon">
            <i data-lucide="wallet"></i>
        </div>
        <div>
            <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted);">Jumla Yote</div>
            <div class="cpx-stat-value purple">TZS {{ number_format($cpxTotalEarned, 0) }}</div>
        </div>
    </div>
</div>

<!-- BitLabs Survey Wall Container -->
@php
    // Generate BitLabs Offerwall URL
    $user = auth()->user();
    $apiToken = config('bitlabs.api_token');
    
    $bitlabsWallUrl = "https://web.bitlabs.ai?" . http_build_query([
        'token' => $apiToken,
        'uid' => $user->id,
        'username' => $user->name,
        'email' => $user->email,
        'country' => 'TZ',
    ]);
@endphp

<div class="cpx-wall-container">
    <div class="cpx-wall-header">
        <h3>
            <i data-lucide="message-circle" style="width: 22px; height: 22px;"></i>
            SkyOpinions™ Portal
        </h3>
        <div class="flex items-center gap-3">
            <div class="cpx-live-badge">
                <span class="cpx-live-dot"></span>
                LIVE
            </div>
            <button onclick="refreshCpxWall()" class="cpx-action-btn secondary" style="padding: 8px 14px;">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
    </div>
    
    <div class="cpx-info-bar">
        <div class="cpx-info-icon">
            <i data-lucide="info" style="width: 18px; height: 18px; color: #9B5DE5;"></i>
        </div>
        <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
            Bonyeza fursa yoyote hapa chini. Ukimaliza, malipo yataongezwa kwenye wallet yako <strong style="color: var(--text-primary);">automatically!</strong>
        </p>
    </div>
    
    <div class="cpx-frame-wrapper">
        <!-- Loading Overlay -->
        <div id="cpxFrameLoading" class="cpx-loading-overlay">
            <div class="cpx-spinner"></div>
            <p style="color: var(--text-muted); margin-top: var(--space-4); font-size: 0.9rem;">Inapakia fursa zilizo available...</p>
        </div>
        
        <!-- BitLabs Frame Integration -->
        <iframe 
            id="bitlabsFrame"
            width="100%" 
            frameBorder="0" 
            height="1800px"  
            src="{{ $bitlabsWallUrl }}"
            style="border: none; display: block;"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            onload="hideBitlabsLoading()">
        </iframe>
    </div>
    
    <div class="cpx-wall-actions">
        <a href="{{ $bitlabsWallUrl }}" target="_blank" class="cpx-action-btn secondary">
            <i data-lucide="external-link"></i>
            Fungua kwa Tab Mpya
        </a>
        <button onclick="refreshBitlabsWall()" class="cpx-action-btn secondary">
            <i data-lucide="refresh-cw"></i>
            Refresh
        </button>
        <a href="{{ route('surveys.history') }}" class="cpx-action-btn primary">
            <i data-lucide="history"></i>
            Historia
        </a>
    </div>
</div>

<!-- SkyOpinions Rewards Section -->
<div class="cpx-rewards-section">
    <h4 style="color: white; margin-bottom: var(--space-6); font-size: 1.1rem; display: flex; align-items: center; gap: var(--space-2);">
        💰 Malipo ya SkyOpinions™
    </h4>
    <div class="cpx-rewards-grid">
        <div class="cpx-reward-card">
            <div class="cpx-reward-amount">TZS 200+</div>
            <div class="cpx-reward-duration">Short (5-7 min)</div>
            <span class="cpx-reward-badge">
                <i data-lucide="clock" style="width: 10px; height: 10px;"></i>
                Haraka
            </span>
        </div>
        <div class="cpx-reward-card">
            <div class="cpx-reward-amount">TZS 300+</div>
            <div class="cpx-reward-duration">Medium (8-12 min)</div>
            <span class="cpx-reward-badge">
                <i data-lucide="trending-up" style="width: 10px; height: 10px;"></i>
                Popular
            </span>
        </div>
        <div class="cpx-reward-card premium">
            <div class="cpx-reward-amount">TZS 500+</div>
            <div class="cpx-reward-duration">Long (15+ min)</div>
            <span class="cpx-reward-badge">
                <i data-lucide="crown" style="width: 10px; height: 10px;"></i>
                VIP Bonus
            </span>
        </div>
    </div>
</div>

<!-- CPX Tips Section -->
<div class="cpx-tips-section">
    <h4 style="margin-bottom: var(--space-5); font-size: 1rem; display: flex; align-items: center; gap: var(--space-2);">
        💡 Vidokezo vya Kupata Pesa Zaidi
    </h4>
    <div class="cpx-tips-grid">
        <div class="cpx-tip-card">
            <div class="cpx-tip-icon">✅</div>
            <div class="cpx-tip-content">
                <h4>Jibu kwa Uaminifu</h4>
                <p>Majibu ya uongo yanaweza kusababisha survey kukataliwa.</p>
            </div>
        </div>
        <div class="cpx-tip-card">
            <div class="cpx-tip-icon">⏰</div>
            <div class="cpx-tip-content">
                <h4>Chukua Muda Wako</h4>
                <p>Kumaliza haraka sana kunaweza kusababisha disqualification.</p>
            </div>
        </div>
        <div class="cpx-tip-card">
            <div class="cpx-tip-icon">📱</div>
            <div class="cpx-tip-content">
                <h4>Focus kwenye Survey</h4>
                <p>Usifungue tabs nyingine wakati wa survey.</p>
            </div>
        </div>
        <div class="cpx-tip-card">
            <div class="cpx-tip-icon">🔄</div>
            <div class="cpx-tip-content">
                <h4>Jaribu Tena Baadaye</h4>
                <p>Hakuna survey sasa? Rudi baadaye, zinaongezwa kila wakati!</p>
            </div>
        </div>
    </div>
</div>

<script>
    function hideBitlabsLoading() {
        const loading = document.getElementById('cpxFrameLoading');
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => {
                loading.style.display = 'none';
            }, 300);
        }
    }
    
    function refreshBitlabsWall() {
        const frame = document.getElementById('bitlabsFrame');
        const loading = document.getElementById('cpxFrameLoading');
        
        if (loading) {
            loading.style.display = 'flex';
            loading.style.opacity = '1';
        }
        
        if (frame) {
            frame.src = frame.src;
        }
    }
    
    // Auto-hide loading after timeout (fallback)
    setTimeout(function() {
        hideBitlabsLoading();
    }, 6000);
</script>

@else
<!-- Tasks Grid (for Monetag, Adsterra, or All) -->
<div class="tasks-grid">
    @forelse($tasks as $task)
    <div class="task-card-enhanced {{ $task->is_featured ? 'featured' : '' }}">
        <div class="task-header">
            <div>
                <span class="task-provider-badge {{ $task->provider }}">
                    @if($task->provider === 'monetag')
                        <i data-lucide="rocket" style="width: 12px; height: 12px;"></i>
                        SkyBoost™
                    @elseif($task->provider === 'adsterra')
                        <i data-lucide="link" style="width: 12px; height: 12px;"></i>
                        SkyLinks™
                    @elseif($task->provider === 'cpx')
                        <i data-lucide="message-circle" style="width: 12px; height: 12px;"></i>
                        SkyOpinions™
                    @else
                        <i data-lucide="star" style="width: 12px; height: 12px;"></i>
                        SkyTask™
                    @endif
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
@endif
@endsection
