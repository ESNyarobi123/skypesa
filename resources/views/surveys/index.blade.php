@extends('layouts.app')

@section('title', 'SkyOpinions™ - Maoni')
@section('page-title', 'SkyOpinions™')
@section('page-subtitle', 'Shiriki maoni yako, pata malipo halisi!')

@push('styles')
<style>
    /* Surveys Page Enhanced Styles */
    .surveys-hero {
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.15) 0%, rgba(155, 93, 229, 0.05) 50%, transparent 100%);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        margin-bottom: var(--space-8);
        position: relative;
        overflow: hidden;
    }
    
    .surveys-hero::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(155, 93, 229, 0.2) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    .surveys-hero::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(155, 93, 229, 0.15) 0%, transparent 70%);
        animation: float 8s ease-in-out infinite reverse;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }
    
    .hero-icon-wrapper {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 40px rgba(155, 93, 229, 0.4);
        animation: pulse-glow 2s ease-in-out infinite;
    }
    
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 40px rgba(155, 93, 229, 0.4); }
        50% { box-shadow: 0 0 60px rgba(155, 93, 229, 0.6); }
    }
    
    /* Stats Cards Glass Effect */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }
    
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
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
        border-color: rgba(155, 93, 229, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .stat-card-glass.highlight {
        border-color: rgba(155, 93, 229, 0.4);
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.15), rgba(155, 93, 229, 0.05));
    }
    
    .stat-icon-wrapper {
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.2), rgba(155, 93, 229, 0.1));
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .stat-icon-wrapper svg {
        width: 28px;
        height: 28px;
        color: #9B5DE5;
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
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .stat-value.success { color: #10B981; }
    .stat-value.primary { color: #9B5DE5; }
    
    /* VIP Banner Enhanced */
    .vip-banner {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        border: 2px solid #fbbf24;
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        margin-bottom: var(--space-8);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--space-4);
        position: relative;
        overflow: hidden;
    }
    
    .vip-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .vip-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 0 30px rgba(251, 191, 36, 0.4);
    }
    
    /* Survey Wall Container */
    .survey-wall-container {
        background: var(--bg-card);
        border-radius: var(--radius-2xl);
        border: 1px solid rgba(255, 255, 255, 0.05);
        overflow: hidden;
        margin-bottom: var(--space-8);
    }
    
    .survey-wall-header {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        padding: var(--space-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--space-4);
    }
    
    .survey-wall-header h3 {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin: 0;
    }
    
    .survey-wall-header .badge-live {
        background: white;
        color: #7B2CBF;
        padding: var(--space-2) var(--space-3);
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: var(--space-1);
        animation: pulse 2s infinite;
    }
    
    .survey-wall-header .badge-live::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #10B981;
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .survey-wall-info {
        background: rgba(155, 93, 229, 0.08);
        border-bottom: 1px solid rgba(155, 93, 229, 0.2);
        padding: var(--space-4) var(--space-6);
    }
    
    .survey-wall-info p {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .survey-wall-info p i {
        color: #9B5DE5;
    }
    
    .survey-frame-container {
        position: relative;
        min-height: 700px;
        background: #1a1a1a;
    }
    
    .frame-loading {
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
    }
    
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(155, 93, 229, 0.2);
        border-top-color: #9B5DE5;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .loading-text {
        margin-top: var(--space-4);
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    .survey-wall-actions {
        background: rgba(155, 93, 229, 0.05);
        border-top: 1px solid rgba(155, 93, 229, 0.15);
        padding: var(--space-4) var(--space-6);
        display: flex;
        justify-content: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all var(--transition-base);
        border: none;
        cursor: pointer;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        color: white;
    }
    
    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(155, 93, 229, 0.4);
    }
    
    .action-btn.secondary {
        background: var(--bg-elevated);
        color: var(--text-primary);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .action-btn.secondary:hover {
        background: rgba(155, 93, 229, 0.15);
        border-color: rgba(155, 93, 229, 0.3);
    }
    
    .action-btn svg {
        width: 18px;
        height: 18px;
    }
    
    /* Reward Cards */
    .rewards-section {
        background: linear-gradient(135deg, #9B5DE5 0%, #7B2CBF 100%);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        margin-bottom: var(--space-8);
        position: relative;
        overflow: hidden;
    }
    
    .rewards-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .rewards-section h3 {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .rewards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-4);
    }
    
    @media (max-width: 768px) {
        .rewards-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .reward-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        text-align: center;
        transition: all var(--transition-base);
        border: 1px solid transparent;
    }
    
    .reward-card:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.15);
    }
    
    .reward-card.premium {
        border-color: #fbbf24;
        background: rgba(255, 255, 255, 0.15);
    }
    
    .reward-amount {
        font-size: 2rem;
        font-weight: 800;
        color: white;
        margin-bottom: var(--space-1);
    }
    
    .reward-card.premium .reward-amount {
        color: #fbbf24;
    }
    
    .reward-duration {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: var(--space-3);
    }
    
    .reward-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-1);
        padding: var(--space-1) var(--space-3);
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        color: white;
    }
    
    .reward-card.premium .reward-badge {
        background: rgba(251, 191, 36, 0.3);
        color: #fbbf24;
    }
    
    /* Tips Section */
    .tips-section {
        background: var(--bg-card);
        border-radius: var(--radius-2xl);
        padding: var(--space-8);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .tips-section h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .tips-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-5);
    }
    
    @media (max-width: 768px) {
        .tips-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .tip-card {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-4);
        background: var(--bg-elevated);
        border-radius: var(--radius-xl);
        transition: all var(--transition-base);
    }
    
    .tip-card:hover {
        transform: translateX(4px);
        background: rgba(155, 93, 229, 0.08);
    }
    
    .tip-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, rgba(155, 93, 229, 0.2), rgba(155, 93, 229, 0.1));
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .tip-content h4 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: var(--space-1);
        color: var(--text-primary);
    }
    
    .tip-content p {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="surveys-hero">
    <div class="flex items-center gap-6" style="position: relative; z-index: 10; flex-wrap: wrap;">
        <div class="hero-icon-wrapper">
            <i data-lucide="message-circle" style="color: white; width: 40px; height: 40px;"></i>
        </div>
        <div>
            <h2 style="font-size: 1.75rem; margin-bottom: var(--space-2);">SkyOpinions™ 💬</h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                Shiriki maoni yako na upate malipo halisi! Fursa mpya zinapatikana kila wakati.
            </p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="target"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Zimebaki Leo</div>
            <div class="stat-value">{{ $stats['remaining_today'] ?? 0 }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="stat-icon-wrapper">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Umekamilisha Leo</div>
            <div class="stat-value">{{ $stats['today_completed'] ?? 0 }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass highlight">
        <div class="stat-icon-wrapper">
            <i data-lucide="coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Umepata Leo</div>
            <div class="stat-value success">TZS {{ number_format($stats['today_earned'] ?? 0, 0) }}</div>
        </div>
    </div>
    
    <div class="stat-card-glass highlight">
        <div class="stat-icon-wrapper">
            <i data-lucide="wallet"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Jumla (Lifetime)</div>
            <div class="stat-value primary">TZS {{ number_format($stats['total_earned'] ?? 0, 0) }}</div>
        </div>
    </div>
</div>

@if(!$isVip)
<!-- VIP Upgrade Banner -->
<div class="vip-banner">
    <div class="flex items-center gap-4">
        <div class="vip-icon">👑</div>
        <div>
            <h4 style="color: #fbbf24; margin-bottom: var(--space-1); font-size: 1.1rem;">Upgrade kwa VIP</h4>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Pata fursa za TZS 500+, x2 malipo na kazi zaidi kwa siku!
            </p>
        </div>
    </div>
    <a href="{{ route('subscriptions.index') }}" class="action-btn primary">
        <i data-lucide="crown"></i>
        Upgrade Sasa
    </a>
</div>
@endif

<!-- SkyOpinions Portal Container -->
<div class="survey-wall-container">
    <div class="survey-wall-header">
        <h3>
            <i data-lucide="message-circle" style="width: 24px; height: 24px;"></i>
            SkyOpinions™ Portal
        </h3>
        <div class="flex items-center gap-3">
            <div class="badge-live">LIVE</div>
            <button onclick="refreshSurveyWall()" class="action-btn secondary" style="padding: 10px 16px;">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
    </div>
    
    <div class="survey-wall-info">
        <p>
            <i data-lucide="info" style="width: 18px; height: 18px;"></i>
            Bonyeza fursa yoyote hapa chini. Ukimaliza, malipo yataongezwa kwenye wallet yako <strong>automatically!</strong>
        </p>
    </div>
    
    @if(isset($bitlabsWallUrl))
    <div class="survey-frame-container">
        <!-- Loading Overlay -->
        <div id="frameLoading" class="frame-loading">
            <div class="loading-spinner"></div>
            <p class="loading-text">Inapakia fursa zilizo available...</p>
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
            onload="hideFrameLoading()">
        </iframe>
    </div>
    
    <div class="survey-wall-actions">
        <a href="{{ $bitlabsWallUrl }}" target="_blank" class="action-btn secondary">
            <i data-lucide="external-link"></i>
            Fungua kwa Tab Mpya
        </a>
        <button onclick="refreshSurveyWall()" class="action-btn secondary">
            <i data-lucide="refresh-cw"></i>
            Refresh
        </button>
        <a href="{{ route('surveys.history') }}" class="action-btn primary">
            <i data-lucide="history"></i>
            Historia
        </a>
    </div>
    @else
    <div style="padding: var(--space-16); text-align: center;">
        <div style="font-size: 4rem; margin-bottom: var(--space-6);">⚙️</div>
        <h4 style="margin-bottom: var(--space-2);">SkyOpinions™ Hazijaanzishwa</h4>
        <p style="color: var(--text-muted);">Portal haijawekwa vizuri. Tafadhali wasiliana na msaada.</p>
    </div>
    @endif
</div>

<!-- Rewards Section -->
<div class="rewards-section">
    <h3>💰 Malipo ya SkyOpinions™</h3>
    <div class="rewards-grid">
        <div class="reward-card">
            <div class="reward-amount">TZS 200+</div>
            <div class="reward-duration">Short (5-7 min)</div>
            <span class="reward-badge">
                <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                Haraka
            </span>
        </div>
        <div class="reward-card">
            <div class="reward-amount">TZS 300+</div>
            <div class="reward-duration">Medium (8-12 min)</div>
            <span class="reward-badge">
                <i data-lucide="trending-up" style="width: 12px; height: 12px;"></i>
                Popular
            </span>
        </div>
        <div class="reward-card premium">
            <div class="reward-amount">TZS 500+</div>
            <div class="reward-duration">Long (15+ min)</div>
            <span class="reward-badge">
                <i data-lucide="crown" style="width: 12px; height: 12px;"></i>
                @if($isVip)
                    x2 VIP Bonus!
                @else
                    VIP Only
                @endif
            </span>
        </div>
    </div>
</div>

<!-- Tips Section -->
<div class="tips-section">
    <h3>💡 Vidokezo vya Kupata Pesa Zaidi</h3>
    <div class="tips-grid">
        <div class="tip-card">
            <div class="tip-icon">✅</div>
            <div class="tip-content">
                <h4>Jibu kwa Uaminifu</h4>
                <p>Majibu ya uongo yanaweza kusababisha survey kukataliwa na hupati malipo.</p>
            </div>
        </div>
        <div class="tip-card">
            <div class="tip-icon">⏰</div>
            <div class="tip-content">
                <h4>Chukua Muda Wako</h4>
                <p>Kumaliza haraka sana kunaweza kusababisha disqualification na kupoteza malipo.</p>
            </div>
        </div>
        <div class="tip-card">
            <div class="tip-icon">📱</div>
            <div class="tip-content">
                <h4>Focus kwenye Survey</h4>
                <p>Usifungue tabs nyingine wakati wa survey ili kuepuka matatizo ya kiufundi.</p>
            </div>
        </div>
        <div class="tip-card">
            <div class="tip-icon">🔄</div>
            <div class="tip-content">
                <h4>Jaribu Tena Baadaye</h4>
                <p>Kama hakuna survey sasa, rudi baadaye! Surveys mpya zinaongezwa kila wakati.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function hideFrameLoading() {
        const loading = document.getElementById('frameLoading');
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => {
                loading.style.display = 'none';
            }, 300);
        }
    }
    
    function refreshSurveyWall() {
        const frame = document.getElementById('bitlabsFrame');
        const loading = document.getElementById('frameLoading');
        
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
        hideFrameLoading();
    }, 6000);
    
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endsection
