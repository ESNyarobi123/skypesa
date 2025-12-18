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
            <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Mpango Uliochagua</div>
            <h2 style="color: white; margin: var(--space-2) 0;">{{ $plan->display_name }}</h2>
            <div style="font-size: 2rem; font-weight: 800; color: white;">
                TZS {{ number_format($plan->price, 0) }}
                <span style="font-size: 0.875rem; font-weight: 400;">/ mwezi</span>
            </div>
        </div>
    </div>
    
    <!-- Plan Benefits -->
    <div class="card card-body mb-6">
        <h4 class="mb-4">Faida Utazozipata</h4>
        <ul style="list-style: none;">
            <li class="flex items-center gap-3 mb-3">
                <i data-lucide="check-circle" style="color: var(--success); width: 20px; height: 20px;"></i>
                <span>Tasks {{ $plan->daily_task_limit ?? 'UNLIMITED' }} kwa siku</span>
            </li>
            <li class="flex items-center gap-3 mb-3">
                <i data-lucide="check-circle" style="color: var(--success); width: 20px; height: 20px;"></i>
                <span>TZS {{ number_format($plan->reward_per_task, 0) }} kwa kila task</span>
            </li>
            <li class="flex items-center gap-3 mb-3">
                <i data-lucide="check-circle" style="color: var(--success); width: 20px; height: 20px;"></i>
                <span>Ada ya withdrawal {{ $plan->withdrawal_fee_percent }}% tu</span>
            </li>
            <li class="flex items-center gap-3">
                <i data-lucide="check-circle" style="color: var(--success); width: 20px; height: 20px;"></i>
                <span>Processing ndani ya {{ $plan->processing_days == 0 ? 'dakika chache' : 'siku ' . $plan->processing_days }}</span>
            </li>
        </ul>
    </div>
    
    <!-- Payment Form -->
    <div class="card card-body" id="paymentForm">
        <h4 class="mb-4">
            <i data-lucide="smartphone" style="color: var(--primary); display: inline;"></i>
            Lipia na Mobile Money
        </h4>
        
        <div class="form-group">
            <label class="form-label">Namba ya Simu ya Kulipa</label>
            <div style="position: relative;">
                <i data-lucide="phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                <input type="tel" id="phone" class="form-control" 
                       placeholder="0712 345 678" 
                       value="{{ $user->phone }}"
                       style="padding-left: 44px;">
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-1);">
                M-Pesa, Tigo Pesa, Airtel Money, au Halo Pesa
            </div>
        </div>
        
        <button type="button" id="payButton" class="btn btn-primary btn-lg" style="width: 100%;" onclick="initiatePayment()">
            <i data-lucide="credit-card"></i>
            Lipia TZS {{ number_format($plan->price, 0) }}
        </button>
        
        <div class="text-center mt-4">
            <a href="{{ route('subscriptions.index') }}" style="color: var(--text-muted); font-size: 0.875rem;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px; display: inline;"></i>
                Rudi kwenye Mipango
            </a>
        </div>
    </div>
    
    <!-- Processing State (hidden initially) -->
    <div class="card card-body text-center" id="processingState" style="display: none;">
        <div class="mb-6">
            <div style="width: 80px; height: 80px; border: 4px solid var(--bg-tertiary); border-top-color: var(--primary); border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
        </div>
        <h4 class="mb-2">Inasubiri Malipo...</h4>
        <p style="color: var(--text-muted);" id="statusMessage">Lipia PUSH uliopokea kwenye simu yako</p>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: var(--space-4);">
            Muda: <span id="countdown">2:00</span>
        </p>
        
        <button type="button" class="btn btn-secondary mt-6" onclick="cancelPayment()">
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
        <p class="mb-6">Umejiunga na <strong>{{ $plan->display_name }}</strong></p>
        
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" style="width: 100%;">
            <i data-lucide="layout-dashboard"></i>
            Nenda Dashboard
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
        const phone = document.getElementById('phone').value;
        
        if (!phone || phone.length < 10) {
            alert('Tafadhali weka namba sahihi ya simu');
            return;
        }
        
        const button = document.getElementById('payButton');
        button.disabled = true;
        button.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Inatuma...';
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
                alert(data.message || 'Kuna tatizo. Jaribu tena.');
                resetPayButton();
            }
        })
        .catch(error => {
            alert('Kuna tatizo la mtandao. Jaribu tena.');
            resetPayButton();
        });
    }

    function showProcessingState() {
        document.getElementById('paymentForm').style.display = 'none';
        document.getElementById('processingState').style.display = 'block';
    }

    function showSuccessState() {
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('successState').style.display = 'block';
        lucide.createIcons();
    }

    function resetPayButton() {
        const button = document.getElementById('payButton');
        button.disabled = false;
        button.innerHTML = '<i data-lucide="credit-card"></i> Lipia TZS {{ number_format($plan->price, 0) }}';
        lucide.createIcons();
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
                document.getElementById('statusMessage').textContent = 'Muda umekwisha. Jaribu tena.';
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
            } else if (data.status === 'FAILED' || data.status === 'CANCELLED') {
                stopPolling();
                clearInterval(countdownInterval);
                document.getElementById('statusMessage').textContent = 'Malipo yameshindwa. Jaribu tena.';
            } else {
                document.getElementById('statusMessage').textContent = 'Inasubiri malipo...';
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
