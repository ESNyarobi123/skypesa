@extends('layouts.app')

@section('title', 'Kazi')
@section('page-title', 'Kazi Zinazopatikana')
@section('page-subtitle', 'Kamilisha kazi na upate malipo!')

@section('content')
<!-- Stats Row -->
<div class="grid grid-3 mb-8">
    <div class="card card-body flex items-center gap-4">
        <div style="width: 50px; height: 50px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="color: var(--primary);"></i>
        </div>
        <div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Umekamilisha Leo</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ auth()->user()->tasksCompletedToday() }}</div>
        </div>
    </div>
    
    <div class="card card-body flex items-center gap-4">
        <div style="width: 50px; height: 50px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="target" style="color: var(--primary);"></i>
        </div>
        <div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Limit ya Leo</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ auth()->user()->getDailyTaskLimit() ?? '∞' }}</div>
        </div>
    </div>
    
    <div class="card card-body flex items-center gap-4">
        <div style="width: 50px; height: 50px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="coins" style="color: var(--primary);"></i>
        </div>
        <div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Malipo kwa Task</div>
            <div style="font-size: 1.5rem; font-weight: 700;">TZS {{ number_format(auth()->user()->getRewardPerTask(), 0) }}</div>
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

<!-- Featured Tasks -->
@php $featuredTasks = $tasks->where('is_featured', true); @endphp
@if($featuredTasks->count() > 0)
<h3 class="mb-4">
    <i data-lucide="star" style="color: var(--primary); display: inline; width: 20px; height: 20px;"></i>
    Kazi Maalum
</h3>
<div class="grid grid-2 mb-8">
    @foreach($featuredTasks as $task)
    <div class="task-card" style="border-color: var(--primary);">
        <div class="task-card-header flex justify-between items-center" style="background: var(--gradient-primary);">
            <span class="task-reward" style="background: white; color: var(--primary);">
                <i data-lucide="coins" style="width: 14px; height: 14px;"></i>
                TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}
            </span>
            <span class="task-timer" style="color: white;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                {{ $task->duration_seconds }}s
            </span>
        </div>
        <div class="task-card-body">
            <div class="flex items-center gap-2 mb-2">
                <span class="badge badge-warning">MAALUM</span>
                <h4>{{ $task->title }}</h4>
            </div>
            <p style="font-size: 0.875rem; margin-bottom: var(--space-4);">{{ $task->description }}</p>
            
            @if($task->daily_limit)
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: var(--space-4);">
                <i data-lucide="repeat" style="width: 12px; height: 12px; display: inline;"></i>
                {{ $task->userCompletionsToday(auth()->user()) }}/{{ $task->daily_limit }} leo
            </div>
            @endif
            
            @if($task->canUserComplete(auth()->user()) && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary" style="width: 100%;">
                <i data-lucide="play"></i>
                Anza Kazi
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="lock"></i>
                Umefika Limit
            </button>
            @else
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="check"></i>
                Imekamilika Leo
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- All Tasks -->
<h3 class="mb-4">Kazi Zote</h3>
<div class="grid grid-3">
    @forelse($tasks->where('is_featured', false) as $task)
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
                {{ $task->userCompletionsToday(auth()->user()) }}/{{ $task->daily_limit }} leo
            </div>
            @endif
            
            @if($task->canUserComplete(auth()->user()) && auth()->user()->canCompleteMoreTasks())
            <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary" style="width: 100%;">
                <i data-lucide="play"></i>
                Anza Kazi
            </a>
            @elseif(!auth()->user()->canCompleteMoreTasks())
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="lock"></i>
                Umefika Limit
            </button>
            @else
            <button class="btn btn-secondary" style="width: 100%;" disabled>
                <i data-lucide="check"></i>
                Imekamilika
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="card card-body text-center" style="grid-column: span 3;">
        <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
        <h4 class="mb-2">Hakuna Kazi Kwa Sasa</h4>
        <p>Kazi mpya zitaongezwa hivi karibuni. Rudi baadaye!</p>
    </div>
    @endforelse
</div>
@endsection
