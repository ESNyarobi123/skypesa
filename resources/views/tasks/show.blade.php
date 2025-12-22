@extends('layouts.app')

@section('title', $task->title)
@section('page-title', 'Kamilisha Kazi')
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
                    <span style="font-weight: 600;">Malipo</span>
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
                            <div style="font-size: 1.25rem; font-weight: 700;">{{ $task->duration_seconds }} sekunde</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Muda wa Kazi</div>
                        </div>
                    </div>
                </div>
                <div class="card" style="padding: 1rem; background: var(--bg-dark);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="eye" style="color: var(--primary);"></i>
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 700;">Tazama Tangazo</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Aina ya Kazi</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="alert mb-6" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--info);">
                <i data-lucide="info"></i>
                <div>
                    <strong>Jinsi Inavyofanya Kazi:</strong>
                    <ul style="margin-top: var(--space-2); padding-left: var(--space-4);">
                        <li>Tangazo litafunguka ndani ya page hii</li>
                        <li>Timer itaanza kuhesabu - <strong>huwezi kuanza kazi nyingine</strong></li>
                        <li>Baada ya muda kuisha, bonyeza "Pata Malipo"</li>
                    </ul>
                </div>
            </div>
            
            <!-- Start Button -->
            <button id="startButton" class="btn btn-primary btn-lg" style="width: 100%;" onclick="startTask()">
                <i data-lucide="play"></i>
                Anza Kazi - Pata TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}
            </button>
        </div>
    </div>
    
    <!-- Back Link -->
    <div class="text-center mt-6">
        <a href="{{ route('tasks.index') }}" class="flex items-center justify-center gap-2" style="color: var(--text-muted);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Rudi kwenye Kazi
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
                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">Tazama tangazo hadi timer imalizike</p>
            </div>
        </div>
        
        <div class="task-timer-box">
            <div class="timer-display">
                <i data-lucide="clock" style="color: var(--primary);"></i>
                <div>
                    <div id="timerNumber" class="timer-number">{{ $task->duration_seconds }}</div>
                    <div class="timer-label">Sekunde</div>
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
            ⚠️ Usitoke kwenye page hii! Timer inaendelea...
        </div>
        
        <div id="iframeLoading" class="iframe-loading">
            <div class="spinner"></div>
            <p>Inapakia tangazo...</p>
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
            Pata Malipo!
        </button>
        
        <div id="waitingText" style="color: var(--text-muted); font-size: 0.875rem;">
            <i data-lucide="loader" class="animate-spin" style="width: 16px; height: 16px; display: inline;"></i>
            Subiri timer imalizike...
        </div>
        
        <button id="abandonButton" class="btn btn-ghost" style="color: var(--danger); font-size: 0.75rem; padding: 0.5rem 1rem;" onclick="abandonTask()">
            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            Acha Kazi
        </button>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal">
    <div class="success-content">
        <div class="success-icon">
            <i data-lucide="check" style="width: 60px; height: 60px; color: white;"></i>
        </div>
        <h1 style="color: var(--primary); margin-bottom: 0.5rem;">Hongera! 🎉</h1>
        <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">Umepata <strong>TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}</strong></p>
        <p id="newBalanceText" style="color: var(--text-muted); margin-bottom: 2rem;"></p>
        
        <div class="flex gap-4 justify-center">
            <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-lg">
                <i data-lucide="arrow-left"></i>
                Kazi Zaidi
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-lg">
                <i data-lucide="wallet"></i>
                Wallet
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const taskId = {{ $task->id }};
    const taskDuration = {{ $task->duration_seconds }};
    const taskUrl = "{{ $task->url }}";
    const startUrl = "{{ route('tasks.start', $task) }}";
    const completeUrl = "{{ route('tasks.complete', $task) }}";
    const csrfToken = "{{ csrf_token() }}";
    
    let lockToken = {!! json_encode($lockToken) !!};
    let countdown = taskDuration; // Always start fresh with full duration
    let timerInterval = null;
    let taskStarted = false;
    
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
    
    // Note: Users always start fresh - old tasks are cancelled when they return
    
    function startTask() {
        const startBtn = document.getElementById('startButton');
        startBtn.disabled = true;
        startBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inaanza...';
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
                throw new Error('Server error. Jaribu tena.');
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
                showFullscreenView();
            } else {
                throw new Error(data.message || 'Imeshindwa kuanza kazi');
            }
        })
        .catch(error => {
            console.error('Start task error:', error);
            if (error.message !== 'Task locked') {
                alert(error.message || 'Kuna tatizo. Jaribu tena.');
                startBtn.disabled = false;
                startBtn.innerHTML = '<i data-lucide="play"></i> Anza Kazi';
                lucide.createIcons();
            }
        });
    }
    
    function showFullscreenView() {
        taskStarted = true;
        
        // Hide before start, show fullscreen
        document.getElementById('beforeStartView').style.display = 'none';
        document.getElementById('taskFullscreen').style.display = 'flex';
        
        // Load iframe
        const iframe = document.getElementById('taskIframe');
        iframe.src = taskUrl;
        
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
            progressTextEl.textContent = '100% - Imekamilika!';
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
                (countdown > 0 ? 'Tazama tangazo...' : 'Imekamilika!');
            
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
        completeBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inachakatwa...';
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
                showSuccessModal(data.new_balance);
            } else {
                throw new Error(data.message || 'Imeshindwa kukamilika');
            }
        })
        .catch(error => {
            console.error('Complete task error:', error);
            alert(error.message || 'Kuna tatizo. Jaribu tena.');
            completeBtn.disabled = false;
            completeBtn.innerHTML = '<i data-lucide="check"></i> Pata Malipo!';
            lucide.createIcons();
        });
    }
    
    function abandonTask() {
        if (!confirm('Una uhakika unataka kuacha kazi hii? Hutapata malipo na utaanza upya.')) {
            return;
        }
        
        const abandonBtn = document.getElementById('abandonButton');
        abandonBtn.disabled = true;
        abandonBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inafuta...';
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
    
    function showSuccessModal(newBalance) {
        document.getElementById('taskFullscreen').style.display = 'none';
        document.getElementById('successModal').style.display = 'flex';
        document.getElementById('newBalanceText').textContent = 'Salio jipya: TZS ' + new Intl.NumberFormat().format(newBalance);
        
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
