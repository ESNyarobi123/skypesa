@extends('layouts.app')

@section('title', $task->title)
@section('page-title', 'Kamilisha Kazi')
@section('page-subtitle', $task->title)

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
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
            
            <!-- Timer -->
            <div id="taskContainer">
                <div class="text-center mb-6">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-2);">Muda Unaobaki</div>
                    <div id="timerDisplay" style="font-size: 4rem; font-weight: 800; color: var(--primary); line-height: 1;">
                        {{ $task->duration_seconds }}
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">sekunde</div>
                </div>
                
                <!-- Progress -->
                <div class="progress mb-6" style="height: 16px;">
                    <div id="progressBar" class="progress-bar" style="width: 0%;"></div>
                </div>
                
                <!-- Instructions -->
                <div class="alert" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--info);">
                    <i data-lucide="info"></i>
                    <div>
                        <strong>Maelekezo:</strong>
                        <ul style="margin-top: var(--space-2); padding-left: var(--space-4);">
                            <li>Bonyeza "Anza Kazi" kufungua tangazo</li>
                            <li>Subiri hadi timer ikamilike</li>
                            <li>Usifunge tab kabla ya muda kumalizika</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Start Button -->
                <button id="startButton" class="btn btn-primary btn-lg" style="width: 100%;" onclick="startTask()">
                    <i data-lucide="play"></i>
                    Anza Kazi
                </button>
                
                <!-- Complete Button (hidden initially) -->
                <button id="completeButton" class="btn btn-primary btn-lg" style="width: 100%; display: none;" onclick="completeTask()">
                    <i data-lucide="check"></i>
                    Pata Malipo
                </button>
            </div>
            
            <!-- Success Message (hidden initially) -->
            <div id="successContainer" style="display: none; text-align: center;">
                <div style="width: 100px; height: 100px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
                    <i data-lucide="check" style="width: 50px; height: 50px; color: white;"></i>
                </div>
                <h2 class="mb-2" style="color: var(--primary);">Hongera! 🎉</h2>
                <p class="mb-4">Umepata <strong>TZS {{ number_format($task->getRewardFor(auth()->user()), 0) }}</strong></p>
                <p id="newBalance" style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-6);"></p>
                
                <div class="flex gap-4">
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary" style="flex: 1;">
                        <i data-lucide="arrow-left"></i>
                        Kazi Zaidi
                    </a>
                    <a href="{{ route('wallet.index') }}" class="btn btn-secondary" style="flex: 1;">
                        <i data-lucide="wallet"></i>
                        Wallet
                    </a>
                </div>
            </div>
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

@push('scripts')
<script>
    const taskDuration = {{ $task->duration_seconds }};
    const taskUrl = "{{ $task->url }}";
    const completeUrl = "{{ route('tasks.complete', $task) }}";
    const csrfToken = "{{ csrf_token() }}";
    
    let countdown = taskDuration;
    let timerInterval = null;
    let adWindow = null;
    
    function startTask() {
        // Open ad in new window/tab
        adWindow = window.open(taskUrl, '_blank');
        
        // Hide start button, show timer
        document.getElementById('startButton').style.display = 'none';
        
        // Start countdown
        timerInterval = setInterval(function() {
            countdown--;
            
            // Update display
            document.getElementById('timerDisplay').textContent = countdown;
            
            // Update progress bar
            const progress = ((taskDuration - countdown) / taskDuration) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            
            if (countdown <= 0) {
                clearInterval(timerInterval);
                document.getElementById('completeButton').style.display = 'block';
            }
        }, 1000);
    }
    
    function completeTask() {
        document.getElementById('completeButton').disabled = true;
        document.getElementById('completeButton').innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inachakatwa...';
        lucide.createIcons();
        
        fetch(completeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                duration: taskDuration - countdown,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success
                document.getElementById('taskContainer').style.display = 'none';
                document.getElementById('successContainer').style.display = 'block';
                document.getElementById('newBalance').textContent = 'Salio jipya: TZS ' + new Intl.NumberFormat().format(data.new_balance);
                lucide.createIcons();
            } else {
                alert(data.message || 'Kuna tatizo. Jaribu tena.');
                document.getElementById('completeButton').disabled = false;
                document.getElementById('completeButton').innerHTML = '<i data-lucide="check"></i> Pata Malipo';
                lucide.createIcons();
            }
        })
        .catch(error => {
            alert('Kuna tatizo la mtandao. Jaribu tena.');
            document.getElementById('completeButton').disabled = false;
            document.getElementById('completeButton').innerHTML = '<i data-lucide="check"></i> Pata Malipo';
            lucide.createIcons();
        });
    }
    
    // Close ad window when leaving page
    window.addEventListener('beforeunload', function() {
        if (adWindow && !adWindow.closed) {
            adWindow.close();
        }
    });
</script>
@endpush
@endsection
