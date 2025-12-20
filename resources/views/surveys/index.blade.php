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

    @if(!$isVip)
    <!-- VIP Upgrade Banner -->
    <div class="card mb-6" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border: 1px solid #fbbf24;">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span style="font-size: 2rem;">👑</span>
                    <div>
                        <h4 style="color: #fbbf24; margin-bottom: var(--space-1);">Upgrade kwa VIP</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">Pata surveys za TZS 500, x2 malipo na faida zaidi!</p>
                    </div>
                </div>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                    Upgrade Sasa
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- CPX Research Frame Integration - Primary Survey Wall -->
    <div class="card mb-6">
        <div class="card-body" style="padding: var(--space-4);">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="clipboard-list" style="width: 20px; height: 20px; color: white;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0;">🌐 CPX Research Surveys</h4>
                        <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">Powered by CPX Research</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-success" style="animation: pulse 2s infinite;">
                        <i data-lucide="wifi" style="width: 12px; height: 12px;"></i>
                        Live
                    </span>
                    <button onclick="refreshSurveyWall()" class="btn btn-secondary" style="padding: 8px 12px;">
                        <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>
            
            <div class="info-box mb-4" style="background: var(--gradient-glow); border-radius: var(--radius-lg); padding: var(--space-3);">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" style="width: 16px; height: 16px; color: var(--primary);"></i>
                    <span style="font-size: 0.875rem;">Bonyeza survey yoyote hapa chini. Ukimaliza, malipo yataongezwa kwenye wallet yako <strong>automaticly!</strong></span>
                </div>
            </div>
            
            @if(isset($cpxWallUrl))
            <div class="cpx-frame-container" style="background: var(--bg-tertiary); border-radius: var(--radius-lg); overflow: hidden; position: relative;">
                <!-- Loading Overlay -->
                <div id="frameLoading" class="frame-loading">
                    <div class="loading-spinner"></div>
                    <p style="color: var(--text-muted); margin-top: var(--space-3);">Inapakia surveys...</p>
                </div>
                
                <!-- CPX Research Frame Integration -->
                <iframe 
                    id="cpxFrame"
                    width="100%" 
                    frameBorder="0" 
                    height="2000px"  
                    src="{{ $cpxWallUrl }}"
                    style="border: none; display: block;"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    onload="hideFrameLoading()">
                </iframe>
            </div>
            
            <div class="mt-4" style="display: flex; gap: var(--space-3); flex-wrap: wrap; justify-content: center;">
                <a href="{{ $cpxWallUrl }}" target="_blank" class="btn btn-secondary">
                    <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                    Fungua kwa Tab Mpya
                </a>
                <button onclick="refreshSurveyWall()" class="btn btn-secondary">
                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                    Refresh Surveys
                </button>
                <a href="{{ route('surveys.history') }}" class="btn btn-secondary">
                    <i data-lucide="history" style="width: 16px; height: 16px;"></i>
                    Historia
                </a>
            </div>
            @else
            <div class="text-center" style="padding: var(--space-8);">
                <div style="font-size: 3rem; margin-bottom: var(--space-4);">⚙️</div>
                <p style="color: var(--text-muted);">Survey wall haijawekwa vizuri. Wasiliana na msaada.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Reward Info Card -->
    <div class="card mb-6" style="background: var(--gradient-primary); border: none;">
        <div class="card-body">
            <h4 style="color: white; margin-bottom: var(--space-4);">💰 Malipo ya Surveys</h4>
            <div class="grid grid-3" style="gap: var(--space-4);">
                <div style="background: rgba(255,255,255,0.1); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;">TZS 200+</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Short (5-7 min)</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;">TZS 300+</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Medium (8-12 min)</div>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: var(--space-4); border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(255,255,255,0.3);">
                    <div style="font-size: 1.5rem; font-weight: 800; color: #fbbf24;">TZS 500+</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8);">Long (15+ min)</div>
                    @if($isVip)
                    <div style="font-size: 0.7rem; color: #fbbf24; margin-top: 4px;">👑 VIP x2 Bonus!</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="card">
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
</div>

<style>
    .cpx-frame-container {
        position: relative;
        min-height: 600px;
    }
    
    .frame-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--bg-tertiary);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--bg-dark);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    .stat-card {
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .grid-3 {
            grid-template-columns: 1fr;
        }
        
        .cpx-frame-container iframe {
            height: 1500px !important;
        }
    }
</style>

<script>
    function hideFrameLoading() {
        const loading = document.getElementById('frameLoading');
        if (loading) {
            loading.style.display = 'none';
        }
    }
    
    function refreshSurveyWall() {
        const frame = document.getElementById('cpxFrame');
        const loading = document.getElementById('frameLoading');
        
        if (loading) {
            loading.style.display = 'flex';
        }
        
        if (frame) {
            frame.src = frame.src;
        }
    }
    
    // Auto-hide loading after timeout (fallback)
    setTimeout(function() {
        hideFrameLoading();
    }, 5000);
</script>
@endsection
