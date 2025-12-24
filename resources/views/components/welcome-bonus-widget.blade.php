{{-- 
    Welcome Bonus Widget Component
    Shows welcome bonus info for new users (first task = x10 reward!)
    Include in dashboard: @include('components.welcome-bonus-widget')
--}}

@php
    $user = auth()->user();
    $showWelcomeBonus = !$user->first_task_completed;
@endphp

@if($showWelcomeBonus)
<div class="welcome-bonus-widget" id="welcomeBonusWidget">
    <div class="welcome-bonus-content">
        <div class="welcome-gift-icon">
            <span class="gift-emoji">🎁</span>
            <div class="gift-glow"></div>
        </div>
        
        <div class="welcome-text">
            <h3>{{ __('messages.dashboard.welcome_skypesa') }}</h3>
            <p>{!! __('messages.dashboard.first_task_gets') !!}</p>
            <div class="multiplier-badge">
                <span class="multiplier">x10</span>
                <span class="multiplier-text">{{ __('messages.dashboard.reward_text') }}</span>
            </div>
            <p class="welcome-hint">{{ __('messages.dashboard.instead_of', ['normal' => number_format($user->getRewardPerTask()), 'bonus' => number_format($user->getRewardPerTask() * 10)]) }}</p>
        </div>
        
        <a href="{{ route('tasks.index') }}" class="btn btn-lg welcome-cta">
            <i data-lucide="play"></i>
            {{ __('messages.dashboard.start_now_get') }}
        </a>
    </div>
    
    <div class="welcome-confetti">
        <span class="confetti-piece" style="--delay: 0s; --left: 10%;"></span>
        <span class="confetti-piece" style="--delay: 0.2s; --left: 25%;"></span>
        <span class="confetti-piece" style="--delay: 0.4s; --left: 45%;"></span>
        <span class="confetti-piece" style="--delay: 0.6s; --left: 65%;"></span>
        <span class="confetti-piece" style="--delay: 0.8s; --left: 85%;"></span>
    </div>
</div>

<style>
    .welcome-bonus-widget {
        background: linear-gradient(135deg, #8B5CF6 0%, #EC4899 50%, #F59E0B 100%);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        margin-bottom: var(--space-6);
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
    }
    
    .welcome-bonus-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }
    
    .welcome-gift-icon {
        position: relative;
        display: inline-block;
        margin-bottom: var(--space-4);
    }
    
    .gift-emoji {
        font-size: 4rem;
        display: block;
        animation: bounce 1s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .gift-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255,255,255,0.4), transparent);
        border-radius: 50%;
        animation: pulse-glow 2s infinite;
    }
    
    @keyframes pulse-glow {
        0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
        50% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
    }
    
    .welcome-text h3 {
        color: white;
        margin: 0 0 var(--space-2) 0;
        font-size: 1.5rem;
    }
    
    .welcome-text p {
        color: rgba(255, 255, 255, 0.9);
        margin: 0 0 var(--space-3) 0;
        font-size: 1rem;
    }
    
    .welcome-text strong {
        color: white;
        text-decoration: underline;
    }
    
    .multiplier-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        background: white;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-full);
        margin: var(--space-3) 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .multiplier {
        font-size: 2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #8B5CF6, #EC4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .multiplier-text {
        font-size: 1rem;
        font-weight: 700;
        color: #8B5CF6;
    }
    
    .welcome-hint {
        font-size: 0.875rem !important;
        color: rgba(255, 255, 255, 0.8) !important;
    }
    
    .welcome-cta {
        background: white !important;
        color: #8B5CF6 !important;
        font-weight: 700;
        font-size: 1.1rem;
        padding: 1rem 2rem;
        border-radius: var(--radius-full) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        border: none;
        margin-top: var(--space-4);
        animation: cta-pulse 2s infinite;
    }
    
    .welcome-cta:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.3);
    }
    
    @keyframes cta-pulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); }
        50% { box-shadow: 0 4px 30px rgba(255, 255, 255, 0.3); }
    }
    
    .welcome-cta i {
        width: 20px;
        height: 20px;
    }
    
    /* Confetti */
    .welcome-confetti {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        overflow: hidden;
    }
    
    .confetti-piece {
        position: absolute;
        top: -20px;
        left: var(--left, 50%);
        width: 10px;
        height: 10px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 2px;
        animation: confetti-fall 3s linear infinite;
        animation-delay: var(--delay, 0s);
    }
    
    .confetti-piece:nth-child(1) { background: #FFD700; }
    .confetti-piece:nth-child(2) { background: #FF6B6B; }
    .confetti-piece:nth-child(3) { background: #4ECDC4; }
    .confetti-piece:nth-child(4) { background: #45B7D1; }
    .confetti-piece:nth-child(5) { background: #96CEB4; }
    
    @keyframes confetti-fall {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(400px) rotate(720deg);
            opacity: 0;
        }
    }
    
    /* Mobile responsive */
    @media (max-width: 640px) {
        .welcome-bonus-widget {
            padding: var(--space-4);
        }
        
        .gift-emoji {
            font-size: 3rem;
        }
        
        .welcome-text h3 {
            font-size: 1.25rem;
        }
        
        .multiplier {
            font-size: 1.5rem;
        }
        
        .welcome-cta {
            width: 100%;
            font-size: 1rem;
        }
    }
</style>
@endif
