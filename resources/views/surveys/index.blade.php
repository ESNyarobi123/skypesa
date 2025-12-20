@extends('layouts.app')

@section('title', 'Surveys - Pata Pesa')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="page-header" style="margin-bottom: var(--space-6);">
        <div>
            <h1 class="page-title">📊 Surveys</h1>
            <p class="page-subtitle">Jibu maswali, pata pesa halisi!</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-4 mb-6" style="gap: var(--space-4);">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['remaining_today'] ?? 0 }}</div>
            <div class="stat-label">Zimebaki Leo</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['today_completed'] ?? 0 }}</div>
            <div class="stat-label">Umekamilisha Leo</div>
        </div>
        <div class="stat-card" style="border-color: var(--success);">
            <div class="stat-value" style="color: var(--success);">TZS {{ number_format($stats['today_earned'] ?? 0, 0) }}</div>
            <div class="stat-label">Umepata Leo</div>
        </div>
        <div class="stat-card" style="border-color: var(--primary);">
            <div class="stat-value" style="color: var(--primary);">TZS {{ number_format($stats['total_earned'] ?? 0, 0) }}</div>
            <div class="stat-label">Jumla (Lifetime)</div>
        </div>
    </div>

    <!-- Reward Info -->
    <div class="card mb-6" style="background: var(--gradient-primary); border: none;">
        <div class="card-body">
            <h4 style="color: white; margin-bottom: var(--space-4);">💰 Malipo ya Surveys</h4>
            <div class="grid grid-3" style="gap: var(--space-4);">
                <div style="background: rgba(255,255,255,0.1); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;">TZS 200</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Short (5-7 min)</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;">TZS 300</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Medium (8-12 min)</div>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(255,255,255,0.3);">
                    <div style="font-size: 1.5rem; font-weight: 800; color: #fbbf24;">TZS 500</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Long (15+ min)</div>
                    @if(!$isVip)
                    <div style="font-size: 0.7rem; color: #fbbf24; margin-top: 4px;">👑 VIP Only</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!$isVip)
    <!-- VIP Upgrade Banner -->
    <div class="card mb-6" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border: 1px solid #fbbf24;">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span style="font-size: 2rem;">👑</span>
                    <div>
                        <h4 style="color: #fbbf24; margin-bottom: var(--space-1);">Upgrade kwa VIP</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">Pata surveys za TZS 500 na faida zaidi!</p>
                    </div>
                </div>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                    Upgrade Sasa
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Surveys -->
    @if(count($surveys) > 0)
    
    <!-- Short Surveys -->
    @if($shortSurveys->count() > 0)
    <div class="mb-6">
        <h3 class="mb-4" style="display: flex; align-items: center; gap: var(--space-2);">
            <span style="background: var(--info); padding: 4px 8px; border-radius: 6px; font-size: 0.75rem;">SHORT</span>
            Surveys za Haraka (5-7 min) - TZS 200
        </h3>
        <div class="grid grid-2" style="gap: var(--space-4);">
            @foreach($shortSurveys as $survey)
            <div class="card survey-card" style="border-left: 4px solid var(--info);">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="badge badge-info">{{ $survey['loi_label'] }}</span>
                            @if($survey['is_top'])
                            <span class="badge badge-success" style="margin-left: 4px;">⭐ TOP</span>
                            @endif
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--success);">
                            {{ $survey['reward_formatted'] }}
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Survey #{{ $survey['id'] }} • Conversion {{ $survey['conversion_rate'] }}%
                    </p>
                    <a href="{{ $survey['href'] }}" target="_blank" class="btn btn-primary btn-block">
                        Anza Survey →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Medium Surveys -->
    @if($mediumSurveys->count() > 0)
    <div class="mb-6">
        <h3 class="mb-4" style="display: flex; align-items: center; gap: var(--space-2);">
            <span style="background: var(--primary); padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; color: white;">MEDIUM</span>
            Surveys za Kati (8-12 min) - TZS 300
        </h3>
        <div class="grid grid-2" style="gap: var(--space-4);">
            @foreach($mediumSurveys as $survey)
            <div class="card survey-card" style="border-left: 4px solid var(--primary);">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="badge badge-primary">{{ $survey['loi_label'] }}</span>
                            @if($survey['is_top'])
                            <span class="badge badge-success" style="margin-left: 4px;">⭐ TOP</span>
                            @endif
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--success);">
                            {{ $survey['reward_formatted'] }}
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Survey #{{ $survey['id'] }} • Conversion {{ $survey['conversion_rate'] }}%
                    </p>
                    <a href="{{ $survey['href'] }}" target="_blank" class="btn btn-primary btn-block">
                        Anza Survey →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Long Surveys (VIP Only) -->
    @if($longSurveys->count() > 0 && $isVip)
    <div class="mb-6">
        <h3 class="mb-4" style="display: flex; align-items: center; gap: var(--space-2);">
            <span style="background: #fbbf24; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; color: black;">👑 VIP</span>
            Surveys Ndefu (15+ min) - TZS 500
        </h3>
        <div class="grid grid-2" style="gap: var(--space-4);">
            @foreach($longSurveys as $survey)
            <div class="card survey-card" style="border-left: 4px solid #fbbf24;">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="badge badge-warning">{{ $survey['loi_label'] }}</span>
                            @if($survey['is_top'])
                            <span class="badge badge-success" style="margin-left: 4px;">⭐ TOP</span>
                            @endif
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 800; color: #fbbf24;">
                            {{ $survey['reward_formatted'] }}
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Survey #{{ $survey['id'] }} • Conversion {{ $survey['conversion_rate'] }}%
                    </p>
                    <a href="{{ $survey['href'] }}" target="_blank" class="btn btn-warning btn-block" style="background: #fbbf24; color: black;">
                        Anza Survey →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @else
    <!-- No Surveys Available -->
    <div class="card">
        <div class="card-body text-center" style="padding: var(--space-12);">
            <div style="font-size: 4rem; margin-bottom: var(--space-4);">📊</div>
            <h3 style="margin-bottom: var(--space-2);">Hakuna Surveys kwa Sasa</h3>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto var(--space-6);">
                @if(isset($result['status']) && $result['status'] === 'limit_reached')
                    Umefika kikomo cha surveys kwa leo. Rudi kesho!
                @else
                    Surveys mpya zinaongezwa mara kwa mara. Jaribu tena baadaye.
                @endif
            </p>
            <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                Fanya Tasks Badala yake
            </a>
        </div>
    </div>
    @endif

    <!-- Quick Tips -->
    <div class="card mt-6">
        <div class="card-body">
            <h4 class="mb-4">💡 Vidokezo vya Kupata Pesa Zaidi</h4>
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="flex items-start gap-3">
                    <span style="font-size: 1.5rem;">✅</span>
                    <div>
                        <strong>Jibu kwa Uaminifu</strong>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">Majibu ya uongo yanaweza kusababisha survey kukataliwa</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span style="font-size: 1.5rem;">⏰</span>
                    <div>
                        <strong>Chukua Muda Wako</strong>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">Kumaliza haraka sana kunaweza kusababisha disqualification</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span style="font-size: 1.5rem;">📱</span>
                    <div>
                        <strong>Tumia Browser Vizuri</strong>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">Usifungue tabs nyingine wakati wa survey</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span style="font-size: 1.5rem;">🔄</span>
                    <div>
                        <strong>Jaribu Tena</strong>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">Kama survey moja haikufaa, jaribu nyingine!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Link -->
    <div class="text-center mt-6">
        <a href="{{ route('surveys.history') }}" class="btn btn-secondary">
            📜 Angalia Historia ya Surveys
        </a>
    </div>
</div>

<style>
    .survey-card {
        transition: all 0.3s ease;
    }
    .survey-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    .btn-block {
        display: block;
        width: 100%;
        text-align: center;
    }
</style>
@endsection
