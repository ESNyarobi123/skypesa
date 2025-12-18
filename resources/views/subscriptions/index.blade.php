@extends('layouts.app')

@section('title', 'Subscription')
@section('page-title', 'Mipango ya Subscription')
@section('page-subtitle', 'Chagua mpango unaokufaa')

@section('content')
<!-- Current Plan -->
@if($currentSubscription)
<div class="card mb-8" style="background: var(--gradient-primary); padding: var(--space-6);">
    <div style="position: absolute; top: -50%; right: -20%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%); transform: rotate(30deg);"></div>
    <div style="position: relative; z-index: 10;">
        <div class="flex justify-between items-center flex-wrap" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Mpango Wako wa Sasa</div>
                <h2 style="color: white;">{{ $currentSubscription->plan->display_name }}</h2>
                @if($currentSubscription->daysRemaining() !== null)
                <div style="color: rgba(255,255,255,0.8); margin-top: var(--space-2);">
                    <i data-lucide="clock" style="width: 16px; height: 16px; display: inline;"></i>
                    Inaisha baada ya siku {{ $currentSubscription->daysRemaining() }}
                </div>
                @else
                <div style="color: rgba(255,255,255,0.8); margin-top: var(--space-2);">
                    <i data-lucide="infinity" style="width: 16px; height: 16px; display: inline;"></i>
                    Unlimited
                </div>
                @endif
            </div>
            <div class="grid grid-3" style="gap: var(--space-6);">
                <div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">Tasks/Siku</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: white;">{{ $currentSubscription->plan->daily_task_limit ?? '∞' }}</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">TZS/Task</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: white;">{{ number_format($currentSubscription->plan->reward_per_task, 0) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">Ada</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: white;">{{ $currentSubscription->plan->withdrawal_fee_percent }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- All Plans -->
<h3 class="mb-6">Mipango Yote</h3>
<div class="grid grid-5">
    @foreach($plans as $plan)
    <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }} {{ $currentSubscription?->plan_id == $plan->id ? 'current' : '' }}">
        @if($currentSubscription?->plan_id == $plan->id)
        <div style="position: absolute; top: var(--space-4); left: var(--space-4); padding: var(--space-1) var(--space-3); background: var(--primary); color: white; font-size: 0.625rem; font-weight: 700; border-radius: var(--radius-full); letter-spacing: 0.1em;">
            MPANGO WAKO
        </div>
        @endif
        
        <div style="font-size: 0.75rem; color: {{ $plan->badge_color }}; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--space-2); margin-top: {{ $currentSubscription?->plan_id == $plan->id ? 'var(--space-6)' : '0' }};">
            {{ strtoupper($plan->display_name) }}
        </div>
        
        <div class="plan-name">{{ $plan->display_name }}</div>
        <div class="plan-price">
            TZS {{ number_format($plan->price, 0) }}
            @if(!$plan->isFree())
            <span>/mwezi</span>
            @endif
        </div>
        <p style="font-size: 0.875rem; margin-top: var(--space-2);">{{ $plan->description }}</p>
        
        <ul class="plan-features">
            <li>
                @if($plan->daily_task_limit)
                <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                Tasks {{ $plan->daily_task_limit }} kwa siku
                @else
                <i data-lucide="infinity" style="width: 18px; height: 18px;"></i>
                Tasks UNLIMITED
                @endif
            </li>
            <li>
                <i data-lucide="coins" style="width: 18px; height: 18px;"></i>
                TZS {{ number_format($plan->reward_per_task, 0) }} kwa task
            </li>
            <li>
                <i data-lucide="banknote" style="width: 18px; height: 18px;"></i>
                Min: TZS {{ number_format($plan->min_withdrawal, 0) }}
            </li>
            <li>
                <i data-lucide="percent" style="width: 18px; height: 18px;"></i>
                Ada: {{ $plan->withdrawal_fee_percent }}%
            </li>
            <li>
                @if($plan->processing_days == 0)
                <i data-lucide="zap" style="width: 18px; height: 18px;"></i>
                Malipo PAPO HAPO!
                @else
                <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                Processing: Siku {{ $plan->processing_days }}
                @endif
            </li>
        </ul>
        
        @if($currentSubscription?->plan_id == $plan->id)
        <button class="btn btn-secondary" style="width: 100%;" disabled>
            <i data-lucide="check"></i>
            Mpango Wako
        </button>
        @elseif($plan->isFree())
        <form action="{{ route('subscriptions.subscribe', $plan) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary" style="width: 100%;">
                Chagua Free
            </button>
        </form>
        @else
        <form action="{{ route('subscriptions.subscribe', $plan) }}" method="POST">
            @csrf
            <button type="submit" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-secondary' }}" style="width: 100%;">
                Chagua {{ $plan->display_name }}
            </button>
        </form>
        @endif
    </div>
    @endforeach
</div>

<!-- Monthly Earnings Calculator -->
<div class="card mt-8" style="padding: var(--space-6);">
    <h4 class="mb-6">
        <i data-lucide="calculator" style="color: var(--primary); display: inline;"></i>
        Hesabu Mapato Yako ya Mwezi
    </h4>
    
    <table class="table">
        <thead>
            <tr>
                <th>Mpango</th>
                <th style="text-align: center;">Tasks/Siku</th>
                <th style="text-align: center;">TZS/Task</th>
                <th style="text-align: center;">Mapato ya Siku</th>
                <th style="text-align: center;">Mapato ya Mwezi</th>
                <th style="text-align: center;">Bei</th>
                <th style="text-align: right;">Faida (NET)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
            @php
                $dailyTasks = $plan->daily_task_limit ?? 50;
                $dailyEarnings = $dailyTasks * $plan->reward_per_task;
                $monthlyEarnings = $dailyEarnings * 30;
                $netProfit = $monthlyEarnings * (1 - $plan->withdrawal_fee_percent / 100) - $plan->price;
            @endphp
            <tr>
                <td style="font-weight: 600;">{{ $plan->display_name }}</td>
                <td style="text-align: center;">{{ $plan->daily_task_limit ?? '50+' }}</td>
                <td style="text-align: center;">TZS {{ number_format($plan->reward_per_task, 0) }}</td>
                <td style="text-align: center;">TZS {{ number_format($dailyEarnings, 0) }}</td>
                <td style="text-align: center;">TZS {{ number_format($monthlyEarnings, 0) }}</td>
                <td style="text-align: center;">TZS {{ number_format($plan->price, 0) }}</td>
                <td style="text-align: right; font-weight: 700; color: var(--success);">TZS {{ number_format($netProfit, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-4);">
        * Hesabu hizi zinadhani umekamilisha tasks zote kwa siku 30. Faida halisi inategemea juhudi zako.
    </p>
</div>
@endsection
