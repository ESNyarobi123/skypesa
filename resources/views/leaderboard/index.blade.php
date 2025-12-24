@extends('layouts.app')

@section('title', __('messages.leaderboard.title'))
@section('page-title', '🏆 ' . __('messages.leaderboard.title'))
@section('page-subtitle', __('messages.leaderboard.subtitle'))

@push('styles')
<style>
    .leaderboard-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    /* Period Tabs */
    .period-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: var(--space-6);
        background: var(--bg-elevated);
        padding: 0.25rem;
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .period-tab {
        flex: 1;
        padding: 0.75rem 1.5rem;
        text-align: center;
        border-radius: var(--radius-lg);
        color: var(--text-muted);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        background: transparent;
    }
    
    .period-tab.active {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .period-tab:hover:not(.active) {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-primary);
    }
    
    /* Your Stats Card */
    .your-stats-card {
        background: var(--gradient-glow);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-4);
    }
    
    @media (max-width: 640px) {
        .your-stats-card {
            flex-direction: column;
            text-align: center;
        }
    }
    
    .your-rank {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }
    
    .rank-badge {
        width: 60px;
        height: 60px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: white;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
    }
    
    /* Top 3 Podium */
    .podium-container {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: var(--space-8);
        padding: var(--space-6) 0;
    }
    
    .podium-item {
        text-align: center;
        flex: 1;
        max-width: 160px;
    }
    
    .podium-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin: 0 auto var(--space-2);
        border: 3px solid;
        position: relative;
    }
    
    .podium-item.first .podium-avatar {
        width: 90px;
        height: 90px;
        font-size: 2rem;
        border-color: #FFD700;
        background: linear-gradient(135deg, #FFD700, #FFA500);
        box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
    }
    
    .podium-item.second .podium-avatar {
        border-color: #C0C0C0;
        background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
        box-shadow: 0 0 20px rgba(192, 192, 192, 0.4);
    }
    
    .podium-item.third .podium-avatar {
        border-color: #CD7F32;
        background: linear-gradient(135deg, #CD7F32, #8B4513);
        box-shadow: 0 0 20px rgba(205, 127, 50, 0.4);
    }
    
    .crown-icon {
        position: absolute;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1.5rem;
    }
    
    .podium-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .podium-earnings {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .podium-tasks {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    
    .podium-bar {
        width: 100%;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        margin-top: var(--space-3);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: var(--space-2);
    }
    
    .podium-item.first .podium-bar {
        height: 100px;
        background: linear-gradient(to top, rgba(255, 215, 0, 0.3), rgba(255, 215, 0, 0.1));
        border: 1px solid rgba(255, 215, 0, 0.3);
    }
    
    .podium-item.second .podium-bar {
        height: 70px;
        background: linear-gradient(to top, rgba(192, 192, 192, 0.3), rgba(192, 192, 192, 0.1));
        border: 1px solid rgba(192, 192, 192, 0.3);
    }
    
    .podium-item.third .podium-bar {
        height: 50px;
        background: linear-gradient(to top, rgba(205, 127, 50, 0.3), rgba(205, 127, 50, 0.1));
        border: 1px solid rgba(205, 127, 50, 0.3);
    }
    
    .podium-position {
        font-size: 1.5rem;
        font-weight: 800;
        opacity: 0.7;
    }
    
    /* Leaderboard List */
    .leaderboard-list {
        background: var(--bg-elevated);
        border-radius: var(--radius-xl);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .leaderboard-item {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-4) var(--space-5);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.2s ease;
    }
    
    .leaderboard-item:last-child {
        border-bottom: none;
    }
    
    .leaderboard-item:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    
    .leaderboard-item.is-user {
        background: var(--gradient-glow);
        border-left: 3px solid var(--primary);
    }
    
    .leaderboard-rank {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
        background: var(--bg-dark);
        color: var(--text-muted);
    }
    
    .leaderboard-rank.top-3 {
        background: var(--gradient-primary);
        color: white;
    }
    
    .leaderboard-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        background: var(--gradient-primary);
    }
    
    .leaderboard-info {
        flex: 1;
        min-width: 0;
    }
    
    .leaderboard-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .leaderboard-tasks {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    
    .leaderboard-earnings {
        text-align: right;
    }
    
    .leaderboard-amount {
        font-weight: 700;
        color: var(--primary);
        font-size: 1.1rem;
    }
    
    .leaderboard-label {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    
    /* Empty state */
    .empty-leaderboard {
        text-align: center;
        padding: var(--space-8);
        color: var(--text-muted);
    }
    
    /* Animation */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .leaderboard-item {
        animation: slideIn 0.3s ease forwards;
    }
    
    .leaderboard-item:nth-child(1) { animation-delay: 0.05s; }
    .leaderboard-item:nth-child(2) { animation-delay: 0.1s; }
    .leaderboard-item:nth-child(3) { animation-delay: 0.15s; }
    .leaderboard-item:nth-child(4) { animation-delay: 0.2s; }
    .leaderboard-item:nth-child(5) { animation-delay: 0.25s; }
</style>
@endpush

@section('content')
<div class="leaderboard-container">
    <!-- Period Tabs -->
    <div class="period-tabs">
        <a href="{{ route('leaderboard', ['period' => 'weekly']) }}" 
           class="period-tab {{ $period === 'weekly' ? 'active' : '' }}">
            <i data-lucide="calendar" style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
            {{ __('messages.leaderboard.this_week') }}
        </a>
        <a href="{{ route('leaderboard', ['period' => 'monthly']) }}" 
           class="period-tab {{ $period === 'monthly' ? 'active' : '' }}">
            <i data-lucide="calendar-days" style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
            {{ __('messages.leaderboard.this_month') }}
        </a>
    </div>
    
    <!-- Your Stats -->
    <div class="your-stats-card">
        <div class="your-rank">
            <div class="rank-badge">
                @if($userStats['rank'] && is_numeric($userStats['rank']) && $userStats['rank'] <= 3)
                    @if($userStats['rank'] == 1) 🥇 @endif
                    @if($userStats['rank'] == 2) 🥈 @endif
                    @if($userStats['rank'] == 3) 🥉 @endif
                @else
                    #{{ $userStats['rank'] }}
                @endif
            </div>
            <div>
                <div style="font-weight: 600; font-size: 1.1rem;">{{ __('messages.leaderboard.your_position') }}</div>
                <div style="color: var(--text-muted); font-size: 0.875rem;">
                    {{ $userStats['tasks_completed'] }} tasks
                </div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.dashboard.earnings') }}</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">
                TZS {{ number_format($userStats['earnings'], 0) }}
            </div>
        </div>
    </div>
    
    @if(count($leaderboard) >= 3)
    <!-- Top 3 Podium -->
    <div class="podium-container">
        <!-- 2nd Place -->
        @if(isset($leaderboard[1]))
        <div class="podium-item second">
            <div class="podium-avatar">
                {{ $leaderboard[1]['avatar_initial'] }}
            </div>
            <div class="podium-name">{{ Str::limit($leaderboard[1]['user']->name, 12) }}</div>
            <div class="podium-earnings">TZS {{ number_format($leaderboard[1]['earnings'], 0) }}</div>
            <div class="podium-tasks">{{ $leaderboard[1]['tasks_completed'] }} tasks</div>
            <div class="podium-bar">
                <span class="podium-position">🥈</span>
            </div>
        </div>
        @endif
        
        <!-- 1st Place -->
        @if(isset($leaderboard[0]))
        <div class="podium-item first">
            <div class="podium-avatar">
                <span class="crown-icon">👑</span>
                {{ $leaderboard[0]['avatar_initial'] }}
            </div>
            <div class="podium-name">{{ Str::limit($leaderboard[0]['user']->name, 12) }}</div>
            <div class="podium-earnings">TZS {{ number_format($leaderboard[0]['earnings'], 0) }}</div>
            <div class="podium-tasks">{{ $leaderboard[0]['tasks_completed'] }} tasks</div>
            <div class="podium-bar">
                <span class="podium-position">🥇</span>
            </div>
        </div>
        @endif
        
        <!-- 3rd Place -->
        @if(isset($leaderboard[2]))
        <div class="podium-item third">
            <div class="podium-avatar">
                {{ $leaderboard[2]['avatar_initial'] }}
            </div>
            <div class="podium-name">{{ Str::limit($leaderboard[2]['user']->name, 12) }}</div>
            <div class="podium-earnings">TZS {{ number_format($leaderboard[2]['earnings'], 0) }}</div>
            <div class="podium-tasks">{{ $leaderboard[2]['tasks_completed'] }} tasks</div>
            <div class="podium-bar">
                <span class="podium-position">🥉</span>
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Leaderboard List (4-10) -->
    @if(count($leaderboard) > 3)
    <div class="leaderboard-list">
        @foreach(array_slice($leaderboard, 3) as $entry)
        <div class="leaderboard-item {{ $entry['user']->id === auth()->id() ? 'is-user' : '' }}">
            <div class="leaderboard-rank">{{ $entry['rank'] }}</div>
            <div class="leaderboard-avatar">{{ $entry['avatar_initial'] }}</div>
            <div class="leaderboard-info">
                <div class="leaderboard-name">
                    {{ $entry['user']->name }}
                    @if($entry['user']->id === auth()->id())
                    <span style="color: var(--primary); font-size: 0.75rem;">(Wewe)</span>
                    @endif
                </div>
                <div class="leaderboard-tasks">{{ $entry['tasks_completed'] }} tasks</div>
            </div>
            <div class="leaderboard-earnings">
                <div class="leaderboard-amount">TZS {{ number_format($entry['earnings'], 0) }}</div>
                <div class="leaderboard-label">{{ $period === 'weekly' ? 'wiki hii' : 'mwezi huu' }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @elseif(count($leaderboard) == 0)
    <div class="card card-body empty-leaderboard">
        <i data-lucide="trophy" style="width: 64px; height: 64px; margin: 0 auto var(--space-4); opacity: 0.3;"></i>
        <h4>{{ __('messages.leaderboard.no_data') }}</h4>
        <p>{{ __('messages.tasks.start_task') }}</p>
        <a href="{{ route('tasks.index') }}" class="btn btn-primary mt-4">
            <i data-lucide="briefcase"></i>
            {{ __('messages.tasks.start_task') }}
        </a>
    </div>
    @endif
    
    <!-- Motivation Message -->
    <div class="card card-body text-center mt-6" style="background: var(--gradient-glow); border: 1px solid rgba(16, 185, 129, 0.2);">
        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💪</div>
        <p style="color: var(--text-secondary); margin: 0;">
            Fanya tasks zaidi kupanda kwenye leaderboard na kushinda zawadi!
        </p>
    </div>
</div>
@endsection
