@extends('layouts.app')

@section('title', 'Lipia ' . $plan->display_name)
@section('page-title', 'Lipia Subscription')
@section('page-subtitle', 'Jiunga na ' . $plan->display_name)

@section('content')
<div style="max-width: 500px; margin: 0 auto;">
    <!-- Plan Summary -->
    <div class="card mb-6" style="padding: var(--space-6); background: var(--gradient-primary); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50%; right: -20%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        <div style="position: relative; z-index: 10;">
            <div class="flex items-center gap-3 mb-4">
                @if($plan->icon)
                <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="{{ $plan->icon }}" style="color: white; width: 24px; height: 24px;"></i>
                </div>
                @endif
                <div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Mpango Uliochagua</div>
                    <h2 style="color: white; margin: 0;">{{ $plan->display_name }}</h2>
                </div>
            </div>
            <div style="font-size: 2.5rem; font-weight: 800; color: white;">
                TZS {{ number_format($plan->price, 0) }}
                <span style="font-size: 0.875rem; font-weight: 400; opacity: 0.8;">/ mwezi</span>
            </div>
        </div>
    </div>
    
    <!-- Plan Benefits -->
    <div class="card card-body mb-6">
        <h4 class="mb-4">
            <i data-lucide="gift" style="color: var(--primary); display: inline; width: 20px; height: 20px;"></i>
            Faida Utazozipata
        </h4>
        <div class="grid" style="gap: var(--space-3);">
            <div class="flex items-center gap-3">
                <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="check-circle" style="color: var(--success); width: 18px; height: 18px;"></i>
                </div>
                <span>Tasks <strong>{{ $plan->daily_task_limit ?? 'UNLIMITED' }}</strong> kwa siku</span>
            </div>
            <div class="flex items-center gap-3">
                <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="coins" style="color: var(--success); width: 18px; height: 18px;"></i>
                </div>
                <span>TZS <strong>{{ number_format($plan->reward_per_task, 0) }}</strong> kwa kila task</span>
            </div>
            <div class="flex items-center gap-3">
                <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="percent" style="color: var(--success); width: 18px; height: 18px;"></i>
                </div>
                <span>Ada ya withdrawal <strong>{{ $plan->withdrawal_fee_percent }}%</strong> tu</span>
            </div>
            <div class="flex items-center gap-3">
                <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="clock" style="color: var(--success); width: 18px; height: 18px;"></i>
                </div>
                <span>Processing ndani ya <strong>{{ $plan->processing_days == 0 ? 'dakika chache' : 'siku ' . $plan->processing_days }}</strong></span>
            </div>
        </div>
        
        <!-- Monthly Earnings Estimate -->
        <div style="margin-top: var(--space-6); padding: var(--space-4); background: var(--gradient-glow); border-radius: var(--radius-lg); border: 1px solid rgba(16, 185, 129, 0.2);">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Makadirio ya Mapato (Mwezi)</div>
            @php
                $dailyTasks = $plan->daily_task_limit ?? 50;
                $dailyEarnings = $dailyTasks * $plan->reward_per_task;
                $monthlyEarnings = $dailyEarnings * 30;
            @endphp
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                TZS {{ number_format($monthlyEarnings, 0) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                ({{ $dailyTasks }} tasks × TZS {{ $plan->reward_per_task }} × siku 30)
            </div>
        </div>
    </div>
    
    <!-- Payment Form -->
    <div class="card card-body" id="paymentForm">
        <h4 class="mb-4">
            <i data-lucide="smartphone" style="color: var(--primary); display: inline; width: 20px; height: 20px;"></i>
            Lipia na Mobile Money
        </h4>
        
        @if(empty(config('zenopay.api_key')))
        <div class="alert alert-warning mb-4" style="font-size: 0.875rem;">
            <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
            <strong>DEMO MODE:</strong> ZenoPay API haijawekwa. Malipo yatasimuliwa kwa testing.
        </div>
        @else
        <div class="alert alert-success mb-4" style="font-size: 0.875rem;">
            <i data-lucide="info" style="width: 18px; height: 18px;"></i>
            Utapokea PUSH notification kwenye simu yako baada ya kubonyeza "Lipia"
        </div>
        @endif
        
        <div class="form-group">
            <label class="form-label">Namba ya Simu ya Kulipa <span style="color: var(--error);">*</span></label>
            <div style="position: relative;">
                <i data-lucide="phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                <input type="tel" id="phone" class="form-control" 
                       placeholder="0712 345 678" 
                       value="{{ $user->phone }}"
                       style="padding-left: 44px; font-size: 1.1rem;"
                       pattern="[0-9]{10,12}"
                       required>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-2);">
                <i data-lucide="credit-card" style="width: 12px; height: 12px; display: inline;"></i>
                Inakubalika: M-Pesa, Tigo Pesa, Airtel Money, Halo Pesa
            </div>
        </div>
        
        <!-- Payment Provider Logos -->
        <div class="flex justify-center gap-4 mb-6" style="opacity: 0.7;">
            <div style="padding: 8px 12px; background: var(--bg-dark); border-radius: 8px;">
                <span style="font-size: 0.75rem; font-weight: 600; color: #e60000;">M-Pesa</span>
            </div>
            <div style="padding: 8px 12px; background: var(--bg-dark); border-radius: 8px;">
                <span style="font-size: 0.75rem; font-weight: 600; color: #0066b3;">Tigo Pesa</span>
            </div>
            <div style="padding: 8px 12px; background: var(--bg-dark); border-radius: 8px;">
                <span style="font-size: 0.75rem; font-weight: 600; color: #ff0000;">Airtel</span>
            </div>
        </div>
        
        <button type="button" id="payButton" class="btn btn-primary btn-lg" style="width: 100%; padding: var(--space-4);" onclick="initiatePayment()">
            <i data-lucide="lock"></i>
            Lipia TZS {{ number_format($plan->price, 0) }} Sasa
        </button>
        
        <div style="text-align: center; margin-top: var(--space-4); font-size: 0.75rem; color: var(--text-muted);">
            <i data-lucide="shield-check" style="width: 14px; height: 14px; display: inline;"></i>
            Malipo yanalindwa na ZenoPay
        </div>
        
        <div class="text-center mt-6">
            <a href="{{ route('subscriptions.index') }}" style="color: var(--text-muted); font-size: 0.875rem;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px; display: inline;"></i>
                Rudi kwenye Mipango
            </a>
        </div>
    </div>
    
    <!-- Processing State (hidden initially) -->
    <div class="card card-body text-center" id="processingState" style="display: none;">
        <div class="mb-6">
            <div style="width: 80px; height: 80px; border: 4px solid var(--bg-elevated); border-top-color: var(--primary); border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
        </div>
        <h4 class="mb-2">Inasubiri Malipo...</h4>
        <p style="color: var(--text-muted);" id="statusMessage">
            <i data-lucide="smartphone" style="width: 16px; height: 16px; display: inline;"></i>
            Angalia simu yako na lipia PUSH uliopokea
        </p>
        
        <div style="background: var(--bg-dark); padding: var(--space-4); border-radius: var(--radius-lg); margin: var(--space-6) 0;">
            <div style="font-size: 0.75rem; color: var(--text-muted);">Muda uliobaki</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);" id="countdown">2:00</div>
        </div>
        
        <button type="button" class="btn btn-secondary" onclick="cancelPayment()">
            <i data-lucide="x"></i>
            Ghairi
        </button>
    </div>
    
    <!-- Success State (hidden initially) -->
    <div class="card card-body text-center" id="successState" style="display: none;">
        <div style="width: 100px; height: 100px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
            <i data-lucide="check" style="width: 50px; height: 50px; color: white;"></i>
        </div>
        <h2 class="mb-2" style="color: var(--success);">Hongera! 🎉</h2>
        <p class="mb-2">Umejiunga na <strong>{{ $plan->display_name }}</strong></p>
        <p style="font-size: 0.875rem; color: var(--text-muted);">Sasa unaweza kufanya tasks zaidi na kupata mapato makubwa!</p>
        
        <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-lg mt-6" style="width: 100%;">
            <i data-lucide="play-circle"></i>
            Anza Kufanya Kazi!
        </a>
        
        <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-4" style="width: 100%;">
            <i data-lucide="layout-dashboard"></i>
            Nenda Dashboard
        </a>
    </div>
    
    <!-- Error State (hidden initially) -->
    <div class="card card-body text-center" id="errorState" style="display: none;">
        <div style="width: 100px; height: 100px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
            <i data-lucide="x" style="width: 50px; height: 50px; color: var(--error);"></i>
        </div>
        <h3 class="mb-2" style="color: var(--error);">Malipo Yameshindwa</h3>
        <p id="errorMessage" style="color: var(--text-muted);">Kuna tatizo. Jaribu tena.</p>
        
        <button type="button" class="btn btn-primary btn-lg mt-6" style="width: 100%;" onclick="resetForm()">
            <i data-lucide="refresh-cw"></i>
            Jaribu Tena
        </button>
        
        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary mt-4" style="width: 100%;">
            <i data-lucide="arrow-left"></i>
            Rudi kwenye Mipango
        </a>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

@push('scripts')
<script>
    const planId = {{ $plan->id }};
    const initiateUrl = "{{ route('payments.subscription.initiate', $plan) }}";
    const statusUrl = "{{ route('payments.status') }}";
    const csrfToken = "{{ csrf_token() }}";
    
    let orderId = null;
    let pollInterval = null;
    let countdownInterval = null;
    let countdownSeconds = 120;

    function initiatePayment() {
        const phone = document.getElementById('phone').value.replace(/\s/g, '');
        
        // Validate phone number
        if (!phone || phone.length < 10) {
            alert('Tafadhali weka namba sahihi ya simu (mfano: 0712345678)');
            return;
        }
        
        // Check if starts with 06 or 07
        if (!phone.startsWith('06') && !phone.startsWith('07') && !phone.startsWith('255')) {
            alert('Namba ya simu lazima ianze na 06, 07, au 255');
            return;
        }
        
        const button = document.getElementById('payButton');
        button.disabled = true;
        button.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inatuma ombi...';
        lucide.createIcons();
        
        fetch(initiateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ phone: phone }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                orderId = data.order_id;
                showProcessingState();
                startPolling();
                startCountdown();
            } else {
                showError(data.message || 'Kuna tatizo. Jaribu tena.');
                resetPayButton();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Kuna tatizo la mtandao. Jaribu tena.');
            resetPayButton();
        });
    }

    function showProcessingState() {
        document.getElementById('paymentForm').style.display = 'none';
        document.getElementById('processingState').style.display = 'block';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('successState').style.display = 'none';
    }

    function showSuccessState() {
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('paymentForm').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('successState').style.display = 'block';
        lucide.createIcons();
    }
    
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('paymentForm').style.display = 'none';
        document.getElementById('successState').style.display = 'none';
        document.getElementById('errorState').style.display = 'block';
        lucide.createIcons();
    }

    function resetPayButton() {
        const button = document.getElementById('payButton');
        button.disabled = false;
        button.innerHTML = '<i data-lucide="lock"></i> Lipia TZS {{ number_format($plan->price, 0) }} Sasa';
        lucide.createIcons();
    }
    
    function resetForm() {
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('paymentForm').style.display = 'block';
        resetPayButton();
        orderId = null;
    }

    function startPolling() {
        pollInterval = setInterval(checkStatus, 5000); // Check every 5 seconds
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function startCountdown() {
        countdownSeconds = 120;
        updateCountdownDisplay();
        
        countdownInterval = setInterval(() => {
            countdownSeconds--;
            updateCountdownDisplay();
            
            if (countdownSeconds <= 0) {
                stopPolling();
                clearInterval(countdownInterval);
                showError('Muda wa kulipa umekwisha. Jaribu tena.');
            }
        }, 1000);
    }

    function updateCountdownDisplay() {
        const mins = Math.floor(countdownSeconds / 60);
        const secs = countdownSeconds % 60;
        document.getElementById('countdown').textContent = 
            mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function checkStatus() {
        if (!orderId) return;
        
        fetch(statusUrl + '?order_id=' + orderId, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'COMPLETED') {
                stopPolling();
                clearInterval(countdownInterval);
                showSuccessState();
            } else if (data.status === 'FAILED' || data.status === 'CANCELLED' || data.status === 'EXPIRED') {
                stopPolling();
                clearInterval(countdownInterval);
                showError('Malipo yameshindwa au yameghairiwa. Jaribu tena.');
            } else {
                document.getElementById('statusMessage').innerHTML = 
                    '<i data-lucide="smartphone" style="width: 16px; height: 16px; display: inline;"></i> ' +
                    'Inasubiri malipo... Lipia PUSH kwenye simu yako.';
                lucide.createIcons();
            }
        })
        .catch(error => {
            console.log('Status check error:', error);
        });
    }

    function cancelPayment() {
        stopPolling();
        clearInterval(countdownInterval);
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('paymentForm').style.display = 'block';
        resetPayButton();
        orderId = null;
    }
</script>
@endpush
@endsection
