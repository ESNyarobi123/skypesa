{{-- 
    Daily Goal Widget Component
    Shows daily challenge progress with beautiful UI
    Include in dashboard: @include('components.daily-goal-widget')
--}}

@php
    $gamification = app(\App\Services\GamificationService::class);
    $dailyGoal = $gamification->getDailyGoalData(auth()->user());
    $user = auth()->user();
@endphp

@if($dailyGoal)
<div class="daily-goal-widget" id="dailyGoalWidget">
    <div class="daily-goal-header">
        <div class="daily-goal-icon">
            <i data-lucide="target"></i>
        </div>
        <div class="daily-goal-title">
            <h4>🎯 Mkakamavu wa Leo</h4>
            <p>Kamilisha tasks {{ $dailyGoal['target'] }} upate bonus!</p>
        </div>
        <div class="daily-goal-bonus">
            <span class="bonus-amount">+TZS {{ number_format($dailyGoal['bonus_amount']) }}</span>
        </div>
    </div>
    
    <div class="daily-goal-progress">
        <div class="progress-stats">
            <span class="completed-count">{{ $dailyGoal['completed'] }}</span>
            <span class="target-count">/ {{ $dailyGoal['target'] }} tasks</span>
        </div>
        <div class="progress-bar-wrapper">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $dailyGoal['percentage'] }}%"></div>
            </div>
        </div>
        <div class="progress-percentage">{{ $dailyGoal['percentage'] }}%</div>
    </div>
    
    @if($dailyGoal['is_complete'] && !$dailyGoal['is_claimed'])
    <button class="btn btn-primary claim-bonus-btn" onclick="claimDailyBonus()" id="claimBonusBtn">
        <i data-lucide="gift"></i>
        Chukua Bonus TZS {{ number_format($dailyGoal['bonus_amount']) }}!
    </button>
    @elseif($dailyGoal['is_claimed'])
    <div class="bonus-claimed">
        <i data-lucide="check-circle"></i>
        <span>Umeshachukua bonus ya leo! 🎉</span>
    </div>
    @else
    <div class="tasks-remaining">
        <i data-lucide="arrow-right"></i>
        <span>Fanya tasks <strong>{{ $dailyGoal['remaining'] }}</strong> zaidi!</span>
    </div>
    @endif
</div>

<style>
    .daily-goal-widget {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        margin-bottom: var(--space-6);
        position: relative;
        overflow: hidden;
    }
    
    .daily-goal-widget::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2), transparent);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }
    
    .daily-goal-header {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        position: relative;
        z-index: 1;
    }
    
    .daily-goal-icon {
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .daily-goal-icon i {
        color: white;
        width: 24px;
        height: 24px;
    }
    
    .daily-goal-title {
        flex: 1;
    }
    
    .daily-goal-title h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.9375rem; /* was 1.1rem */
    }
    
    .daily-goal-title p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.75rem; /* was 0.875rem */
    }
    
    .daily-goal-bonus {
        text-align: right;
    }
    
    .bonus-amount {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        background: var(--gradient-primary);
        border-radius: var(--radius-full);
        color: white;
        font-weight: 700;
        font-size: 0.75rem; /* was 0.9rem */
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    }
    
    .daily-goal-progress {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        position: relative;
        z-index: 1;
    }
    
    .progress-stats {
        min-width: 70px;
    }
    
    .completed-count {
        font-size: 1.25rem; /* was 1.5rem */
        font-weight: 800;
        color: var(--primary);
    }
    
    .target-count {
        color: var(--text-muted);
        font-size: 0.75rem; /* was 0.875rem */
    }
    
    .progress-bar-wrapper {
        flex: 1;
    }
    
    .progress-bar-bg {
        height: 12px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 6px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: var(--gradient-primary);
        border-radius: 6px;
        transition: width 0.5s ease;
        position: relative;
    }
    
    .progress-bar-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .progress-percentage {
        min-width: 45px;
        text-align: right;
        font-weight: 700;
        font-size: 0.8125rem;
        color: var(--primary);
    }
    
    .claim-bonus-btn {
        width: 100%;
        padding: 0.75rem;
        font-size: 0.875rem;
        animation: pulse 2s infinite;
    }
    
    .bonus-claimed {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        padding: 0.75rem;
        background: rgba(16, 185, 129, 0.2);
        border-radius: var(--radius-lg);
        color: var(--success);
        font-weight: 500;
        font-size: 0.8125rem;
    }
    
    .bonus-claimed i {
        width: 16px;
        height: 16px;
    }
    
    .tasks-remaining {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: var(--radius-lg);
        color: var(--text-secondary);
        font-size: 0.75rem;
    }
    
    .tasks-remaining i {
        width: 14px;
        height: 14px;
        color: var(--primary);
    }
    
    .tasks-remaining strong {
        color: var(--primary);
    }
</style>

<script>
    function claimDailyBonus() {
        const btn = document.getElementById('claimBonusBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inachakata...';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        fetch('{{ route("daily-goal.claim") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success
                btn.outerHTML = `
                    <div class="bonus-claimed">
                        <i data-lucide="check-circle"></i>
                        <span>${data.message}</span>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                // Refresh page after delay to update balance
                setTimeout(() => window.location.reload(), 2000);
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="gift"></i> Chukua Bonus!';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="gift"></i> Jaribu Tena';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }
</script>
@endif
