@extends('layouts.app')

@section('title', $task->title)
@section('page-title', __('messages.tasks.complete_task'))
@section('page-subtitle', $task->title)

@push('styles')
<style>
    .task-fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        background: var(--bg-dark);
        display: none;
        flex-direction: column;
    }
    
    .task-header {
        background: linear-gradient(135deg, var(--bg-darker), var(--bg-dark));
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    
    .task-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .task-timer-box {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    
    .timer-display {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        background: var(--gradient-glow);
        border-radius: var(--radius-lg);
        border: 2px solid var(--primary);
    }
    
    .timer-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary);
        min-width: 70px;
        text-align: center;
    }
    
    .timer-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
    }
    
    .reward-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: var(--gradient-primary);
        border-radius: var(--radius-lg);
        color: white;
        font-weight: 600;
    }
    
    .task-iframe-container {
        flex: 1;
        position: relative;
        overflow: hidden;
        background: #000;
    }
    
    .task-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .iframe-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--text-muted);
    }
    
    .iframe-loading .spinner {
        width: 60px;
        height: 60px;
        border: 4px solid var(--bg-darker);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .task-footer {
        background: var(--bg-darker);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    
    .progress-container {
        flex: 1;
        max-width: 400px;
        margin-right: 2rem;
    }
    
    .progress-bar-container {
        height: 8px;
        background: var(--bg-dark);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: var(--gradient-primary);
        transition: width 1s linear;
        border-radius: 4px;
    }
    
    .progress-text {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }
    
    .complete-btn {
        padding: 1rem 3rem;
        font-size: 1.1rem;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    }
    
    .warning-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to bottom, rgba(234, 179, 8, 0.9), transparent);
        padding: 1rem;
        text-align: center;
        color: #000;
        font-weight: 600;
        display: none;
        z-index: 10;
    }
    
    /* Before Start View */
    .before-start-container {
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* Success Modal */
    .success-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .success-content {
        text-align: center;
        padding: 3rem;
    }
    
    .success-icon {
        width: 120px;
        height: 120px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        animation: successPop 0.5s ease;
    }
    
    @keyframes successPop {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background: var(--primary);
        animation: confettiFall 3s ease-out forwards;
    }
    
    @keyframes confettiFall {
        0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
</style>
@endpush

@section('content')
<!-- Before Start View -->
<div id="beforeStartView" class="before-start-container">
    <!-- Task Card -->
    <div class="card" style="overflow: hidden;">
        <!-- Header -->
        <div style="padding: var(--space-6); background: var(--gradient-primary); text-align: center;">
            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                <i data-lucide="play-circle" style="width: 40px; height: 40px; color: white;"></i>
            </div>
            <h2 style="color: white; margin-bottom: var(--space-2);">{{ $task->title }}</h2>
            <p style="color: rgba(255,255,255,0.8);">{{ $task->description }}</p>
        </div>
        
        <!-- Body -->
        <div class="card-body">
            <!-- Reward Info -->
            <div class="flex justify-between items-center" style="padding: var(--space-4); background: var(--gradient-glow); border-radius: var(--radius-lg); margin-bottom: var(--space-6);">
                <div class="flex items-center gap-3">
                    <i data-lucide="coins" style="color: var(--primary);"></i>
                    <span style="font-weight: 600;">{{ __('messages.tasks.payment') }}</span>
                </div>
                <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                    TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}
                </span>
            </div>
            
            <!-- Task Details -->
            <div class="grid grid-2 mb-6" style="gap: 1rem;">
                <div class="card" style="padding: 1rem; background: var(--bg-dark);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="clock" style="color: var(--primary);"></i>
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 700;">{{ $task->duration_seconds }} {{ __('messages.tasks.seconds') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.tasks.task_duration') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card" style="padding: 1rem; background: var(--bg-dark);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="eye" style="color: var(--primary);"></i>
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 700;">{{ __('messages.tasks.view_ad') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.tasks.task_type') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions (Collapsible - hidden after first visit) -->
            <div id="instructionsContainer" style="margin-bottom: var(--space-6);">
                <!-- Toggle Button -->
                <button type="button" id="instructionsToggle" onclick="toggleInstructions()" 
                        style="display: flex; align-items: center; gap: var(--space-2); width: 100%; padding: var(--space-3); 
                               background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); 
                               border-radius: var(--radius-lg); color: var(--info); cursor: pointer; font-size: 0.875rem;">
                    <i data-lucide="info" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('messages.tasks.how_it_works') }}</span>
                    <i data-lucide="chevron-down" id="instructionsChevron" style="width: 16px; height: 16px; margin-left: auto; transition: transform 0.3s;"></i>
                </button>
                
                <!-- Collapsible Content -->
                <div id="instructionsContent" style="overflow: hidden; transition: max-height 0.3s ease, padding 0.3s ease; max-height: 0; padding: 0 var(--space-4);">
                    <div style="padding-top: var(--space-3);">
                        <ul style="padding-left: var(--space-4); color: var(--text-secondary); font-size: 0.875rem; line-height: 1.8;">
                            <li>{{ __('messages.tasks.ad_opens_here') }}</li>
                            <li>{{ __('messages.tasks.timer_starts') }} - <strong style="color: var(--warning);">{{ __('messages.tasks.cannot_start_another') }}</strong></li>
                            <li>{{ __('messages.tasks.after_time_click') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Start Button -->
            <button id="startButton" class="btn btn-primary btn-lg" style="width: 100%;" onclick="startTask()">
                <i data-lucide="play"></i>
                {{ __('messages.tasks.start_task_earn') }} {{ number_format($task->getRewardFor(auth()->user()), 0) }}
            </button>
        </div>
    </div>
    
    <!-- Back Link -->
    <div class="text-center mt-6">
        <a href="{{ route('tasks.index') }}" class="flex items-center justify-center gap-2" style="color: var(--text-muted);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            {{ __('messages.tasks.back_to_tasks') }}
        </a>
    </div>
</div>

<!-- Fullscreen Task View -->
<div id="taskFullscreen" class="task-fullscreen">
    <!-- Header with Timer -->
    <div class="task-header">
        <div class="task-info">
            <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="eye" style="color: white; width: 20px; height: 20px;"></i>
            </div>
            <div>
                <h4 style="margin: 0;">{{ $task->title }}</h4>
                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">{{ __('messages.tasks.watch_ad_until') }}</p>
            </div>
        </div>
        
        <div class="task-timer-box">
            <div class="timer-display">
                <i data-lucide="clock" style="color: var(--primary);"></i>
                <div>
                    <div id="timerNumber" class="timer-number">{{ $task->duration_seconds }}</div>
                    <div class="timer-label">{{ __('messages.tasks.seconds') }}</div>
                </div>
            </div>
            
            <div class="reward-badge">
                <i data-lucide="coins" style="width: 20px; height: 20px;"></i>
                <span>TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}</span>
            </div>
        </div>
    </div>
    
    <!-- Iframe Container -->
    <div class="task-iframe-container">
        <div id="warningOverlay" class="warning-overlay">
            ⚠️ {{ __('messages.tasks.dont_leave') }}
        </div>
        
        <div id="iframeLoading" class="iframe-loading">
            <div class="spinner"></div>
            <p>{{ __('messages.tasks.loading_ad') }}</p>
        </div>
        
        <iframe id="taskIframe" class="task-iframe" sandbox="allow-scripts allow-same-origin allow-popups allow-forms" allowfullscreen></iframe>
    </div>
    
    <!-- Footer with Progress -->
    <div class="task-footer">
        <div class="progress-container">
            <div class="progress-bar-container">
                <div id="progressBar" class="progress-bar-fill" style="width: 0%;"></div>
            </div>
            <div id="progressText" class="progress-text">0% - Tazama tangazo...</div>
        </div>
        
        <button id="completeButton" class="btn btn-primary complete-btn" style="display: none;" onclick="completeTask()">
            <i data-lucide="check"></i>
            {{ __('messages.tasks.get_payment') }}
        </button>
        
        <div id="waitingText" style="color: var(--text-muted); font-size: 0.875rem;">
            <i data-lucide="loader" class="animate-spin" style="width: 16px; height: 16px; display: inline;"></i>
            {{ __('messages.tasks.wait_timer') }}
        </div>
        
        <button id="abandonButton" class="btn btn-ghost" style="color: var(--danger); font-size: 0.75rem; padding: 0.5rem 1rem;" onclick="abandonTask()">
            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            {{ __('messages.tasks.abandon_task') }}
        </button>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal">
    <div class="success-content">
        <div class="success-icon">
            <i data-lucide="check" style="width: 60px; height: 60px; color: white;"></i>
        </div>
        <h1 style="color: var(--primary); margin-bottom: 0.5rem;">{{ __('messages.tasks.congratulations') }} 🎉</h1>
        <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">{{ __('messages.tasks.you_earned') }} <strong>TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}</strong></p>
        <p id="newBalanceText" style="color: var(--text-muted); margin-bottom: 1rem;"></p>
        
        <!-- Daily Goal Progress Update -->
        <div id="dailyGoalUpdateBox" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-lg); padding: 1rem; margin-bottom: 1.5rem; display: none;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i data-lucide="target" style="width: 18px; height: 18px; color: var(--primary);"></i>
                <span style="font-weight: 600; color: var(--primary);">🎯 {{ __('messages.tasks.daily_goal') }}</span>
            </div>
            <div id="dailyGoalUpdateText" style="font-size: 0.9rem; color: var(--text-secondary);"></div>
            <div style="margin-top: 0.5rem;">
                <div style="height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
                    <div id="dailyGoalProgressBar" style="height: 100%; background: var(--gradient-primary); transition: width 1s ease; width: 0%;"></div>
                </div>
            </div>
        </div>
        
        <div class="flex gap-4 justify-center">
            <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-lg">
                <i data-lucide="arrow-left"></i>
                {{ __('messages.tasks.more_tasks') }}
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-lg">
                <i data-lucide="wallet"></i>
                {{ __('messages.nav.wallet') }}
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const taskId = {{ $task->id }};
    const taskDuration = {{ $task->duration_seconds }};
    const defaultTaskUrl = "{{ $task->url }}"; // Fallback URL
    const startUrl = "{{ route('tasks.start', $task) }}";
    const completeUrl = "{{ route('tasks.complete', $task) }}";
    const csrfToken = "{{ csrf_token() }}";
    
    // Translation strings for JavaScript
    const translations = {
        starting: "{{ __('messages.tasks.starting') }}",
        processing: "{{ __('messages.tasks.processing') }}",
        deleting: "{{ __('messages.tasks.deleting') }}",
        server_error: "{{ __('messages.tasks.server_error') }}",
        failed_start: "{{ __('messages.tasks.failed_start') }}",
        problem_try_again: "{{ __('messages.tasks.problem_try_again') }}",
        failed_complete: "{{ __('messages.tasks.failed_complete') }}",
        confirm_abandon: "{{ __('messages.tasks.confirm_abandon') }}",
        start_task: "{{ __('messages.tasks.start_task') }}",
        get_payment: "{{ __('messages.tasks.get_payment') }}",
        new_balance: "{{ __('messages.tasks.new_balance') }}",
        watching: "{{ __('messages.tasks.watching') }}",
        done: "{{ __('messages.tasks.done') }}",
        completed_goal: "{{ __('messages.tasks.completed_goal') }}",
        claim_bonus: "{{ __('messages.tasks.claim_bonus') }}",
        already_claimed: "{{ __('messages.tasks.already_claimed') }}",
        remaining_for_bonus: "{{ __('messages.tasks.remaining_for_bonus') }}"
    };
    
    let lockToken = {!! json_encode($lockToken) !!};
    let countdown = taskDuration; // Always start fresh with full duration
    let timerInterval = null;
    let taskStarted = false;
    let activeUrl = null; // The actual URL to display (may be random from pool)
    
    // Maximum valid countdown is 10 minutes (600 seconds)
    const maxValidCountdown = 600;
    
    // Validate countdown before starting timer
    function validateCountdown() {
        // If countdown is negative, zero, or unreasonably large, reset to task duration
        if (countdown < 0 || countdown > maxValidCountdown) {
            console.warn('Invalid countdown detected:', countdown, '- resetting to task duration');
            countdown = taskDuration;
        }
        // Also ensure countdown doesn't exceed task duration
        if (countdown > taskDuration) {
            countdown = taskDuration;
        }
    }
    
    // ==========================================
    // INSTRUCTIONS TOGGLE (show/hide)
    // ==========================================
    const INSTRUCTIONS_KEY = 'skypesa_instructions_seen';
    let instructionsOpen = false;
    
    function toggleInstructions() {
        const content = document.getElementById('instructionsContent');
        const chevron = document.getElementById('instructionsChevron');
        
        instructionsOpen = !instructionsOpen;
        
        if (instructionsOpen) {
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.paddingTop = 'var(--space-3)';
            chevron.style.transform = 'rotate(180deg)';
        } else {
            content.style.maxHeight = '0';
            content.style.paddingTop = '0';
            chevron.style.transform = 'rotate(0deg)';
        }
    }
    
    function initInstructions() {
        // Check if user has seen instructions before
        const hasSeenInstructions = localStorage.getItem(INSTRUCTIONS_KEY);
        
        if (!hasSeenInstructions) {
            // First time - show instructions expanded
            instructionsOpen = true;
            const content = document.getElementById('instructionsContent');
            const chevron = document.getElementById('instructionsChevron');
            
            if (content && chevron) {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.style.paddingTop = 'var(--space-3)';
                chevron.style.transform = 'rotate(180deg)';
            }
            
            // Mark as seen after a short delay (user has seen it)
            setTimeout(() => {
                localStorage.setItem(INSTRUCTIONS_KEY, 'true');
            }, 2000);
        }
        // If already seen, instructions stay collapsed by default
    }
    
    // Initialize instructions on page load
    document.addEventListener('DOMContentLoaded', initInstructions);
    
    // Note: Users always start fresh - old tasks are cancelled when they return
    
    function startTask() {
        const startBtn = document.getElementById('startButton');
        startBtn.disabled = true;
        startBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> ' + translations.starting;
        lucide.createIcons();
        
        fetch(startUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Server error:', text.substring(0, 500));
                throw new Error(translations.server_error);
            }
            
            const data = await response.json();
            
            if (response.status === 423) {
                alert(data.message);
                window.location.href = "{{ route('tasks.index') }}";
                throw new Error('Task locked');
            }
            
            if (!response.ok) {
                throw new Error(data.message || 'Server error');
            }
            
            return data;
        })
        .then(data => {
            if (data.success) {
                lockToken = data.lock_token;
                countdown = data.duration;
                // MUHIMU: Tumia link iliyochaguliwa na server (Random Link)
                activeUrl = data.used_url || defaultTaskUrl;
                showFullscreenView(activeUrl);
            } else {
                throw new Error(data.message || translations.failed_start);
            }
        })
        .catch(error => {
            console.error('Start task error:', error);
            if (error.message !== 'Task locked') {
                alert(error.message || translations.problem_try_again);
                startBtn.disabled = false;
                startBtn.innerHTML = '<i data-lucide="play"></i> ' + translations.start_task;
                lucide.createIcons();
            }
        });
    }
    
    function showFullscreenView(urlToLoad) {
        taskStarted = true;
        
        // Hide before start, show fullscreen
        document.getElementById('beforeStartView').style.display = 'none';
        document.getElementById('taskFullscreen').style.display = 'flex';
        
        // Load iframe with the URL (may be random from pool)
        const iframe = document.getElementById('taskIframe');
        iframe.src = urlToLoad || defaultTaskUrl;
        
        console.log('Loading task URL:', urlToLoad || defaultTaskUrl);
        
        // Hide loading when iframe loads
        iframe.onload = function() {
            document.getElementById('iframeLoading').style.display = 'none';
        };
        
        // Start timer
        startTimer();
        
        // Hide sidebar if exists
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.style.display = 'none';
        
        // Hide main content wrapper padding
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.style.marginLeft = '0';
            mainContent.style.padding = '0';
        }
        
        lucide.createIcons();
    }
    
    function startTimer() {
        const timerEl = document.getElementById('timerNumber');
        const progressEl = document.getElementById('progressBar');
        const progressTextEl = document.getElementById('progressText');
        const completeBtn = document.getElementById('completeButton');
        const waitingText = document.getElementById('waitingText');
        
        // Validate countdown before starting
        validateCountdown();
        
        // Update initial display
        timerEl.textContent = Math.max(0, countdown);
        
        // If countdown is already complete, show complete button immediately
        if (countdown <= 0) {
            countdown = 0;
            completeBtn.style.display = 'flex';
            waitingText.style.display = 'none';
            timerEl.textContent = '✓';
            timerEl.style.color = 'var(--success)';
            progressEl.style.width = '100%';
            progressTextEl.textContent = '100% - ' + translations.done;
            lucide.createIcons();
            return;
        }
        
        timerInterval = setInterval(function() {
            countdown--;
            
            // Ensure countdown never goes below 0
            countdown = Math.max(0, countdown);
            
            // Update timer
            timerEl.textContent = countdown;
            
            // Update progress (ensure progress is between 0-100)
            const progress = Math.max(0, Math.min(100, ((taskDuration - countdown) / taskDuration) * 100));
            progressEl.style.width = progress + '%';
            progressTextEl.textContent = Math.round(progress) + '% - ' + 
                (countdown > 0 ? translations.watching : translations.done);
            
            // Timer complete
            if (countdown <= 0) {
                clearInterval(timerInterval);
                
                // Show complete button
                completeBtn.style.display = 'flex';
                waitingText.style.display = 'none';
                
                // Update timer style
                timerEl.textContent = '✓';
                timerEl.style.color = 'var(--success)';
                
                lucide.createIcons();
            }
        }, 1000);
    }
    
    function completeTask() {
        const completeBtn = document.getElementById('completeButton');
        completeBtn.disabled = true;
        completeBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> ' + translations.processing;
        lucide.createIcons();
        
        fetch(completeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                lock_token: lockToken,
            }),
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server error');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                taskStarted = false;
                showSuccessModal(data.new_balance, data.daily_goal);
            } else {
                throw new Error(data.message || translations.failed_complete);
            }
        })
        .catch(error => {
            console.error('Complete task error:', error);
            alert(error.message || translations.problem_try_again);
            completeBtn.disabled = false;
            completeBtn.innerHTML = '<i data-lucide="check"></i> ' + translations.get_payment;
            lucide.createIcons();
        });
    }
    
    function abandonTask() {
        if (!confirm(translations.confirm_abandon)) {
            return;
        }
        
        const abandonBtn = document.getElementById('abandonButton');
        abandonBtn.disabled = true;
        abandonBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> ' + translations.deleting;
        lucide.createIcons();
        
        // Cancel the task on server
        fetch("{{ route('tasks.cancel') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                lock_token: lockToken,
            }),
        })
        .then(response => response.json())
        .then(data => {
            // Clear timer
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            taskStarted = false;
            
            // Redirect to tasks list
            window.location.href = "{{ route('tasks.index') }}";
        })
        .catch(error => {
            console.error('Abandon task error:', error);
            // Still redirect even if there's an error
            window.location.href = "{{ route('tasks.index') }}";
        });
    }
    
    function showSuccessModal(newBalance, dailyGoal) {
        document.getElementById('taskFullscreen').style.display = 'none';
        document.getElementById('successModal').style.display = 'flex';
        document.getElementById('newBalanceText').textContent = translations.new_balance + ': TZS ' + new Intl.NumberFormat().format(newBalance);
        
        // Update daily goal progress with animation
        if (dailyGoal) {
            const goalBox = document.getElementById('dailyGoalUpdateBox');
            const goalText = document.getElementById('dailyGoalUpdateText');
            const goalBar = document.getElementById('dailyGoalProgressBar');
            
            goalBox.style.display = 'block';
            
            if (dailyGoal.is_complete && !dailyGoal.is_claimed) {
                goalText.innerHTML = `<strong style="color: var(--success);">\ud83c\udf89 ${translations.completed_goal} ${dailyGoal.completed}/${dailyGoal.target}</strong><br>${translations.claim_bonus} TZS ${new Intl.NumberFormat().format(dailyGoal.bonus_amount)}!`;
            } else if (dailyGoal.is_claimed) {
                goalText.innerHTML = `<strong style="color: var(--success);">\u2713 ${dailyGoal.completed}/${dailyGoal.target}</strong><br>${translations.already_claimed}`;
            } else {
                goalText.innerHTML = `<strong>${dailyGoal.completed}/${dailyGoal.target}</strong> tasks<br><strong style="color: var(--primary);">${dailyGoal.remaining}</strong> ${translations.remaining_for_bonus}`;
            }
            
            // Animate progress bar
            setTimeout(() => {
                goalBar.style.width = dailyGoal.percentage + '%';
            }, 300);
        }
        
        // Create confetti
        createConfetti();
        
        lucide.createIcons();
    }
    
    function createConfetti() {
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
        const modal = document.getElementById('successModal');
        
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 0.5 + 's';
                confetti.style.width = (Math.random() * 10 + 5) + 'px';
                confetti.style.height = (Math.random() * 10 + 5) + 'px';
                modal.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 3000);
            }, i * 50);
        }
    }
    
    // Show warning when user tries to leave (but DON'T cancel - they must continue when they return)
    window.addEventListener('beforeunload', function(e) {
        if (taskStarted && countdown > 0) {
            // Just show warning - don't cancel
            e.preventDefault();
            e.returnValue = 'Una kazi inayoendelea! Ukiondoka utarudishwa kwa kazi hii hii.';
            return e.returnValue;
        }
    });
    
    // Show warning when tab loses focus
    document.addEventListener('visibilitychange', function() {
        if (taskStarted && countdown > 0) {
            const overlay = document.getElementById('warningOverlay');
            if (document.hidden) {
                overlay.style.display = 'block';
            } else {
                overlay.style.display = 'none';
            }
        }
    });
    
    // Prevent back navigation - show warning only
    history.pushState(null, null, location.href);
    window.addEventListener('popstate', function() {
        if (taskStarted && countdown > 0) {
            // Push state back to prevent navigation
            history.pushState(null, null, location.href);
            
            // Show warning
            document.getElementById('warningOverlay').style.display = 'block';
            setTimeout(() => {
                document.getElementById('warningOverlay').style.display = 'none';
            }, 3000);
        }
    });
</script>
@endpush
@endsection
