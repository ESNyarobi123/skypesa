@extends('layouts.app')

@section('title', 'Weka Pesa')
@section('page-title', 'Weka Pesa (Deposit)')
@section('page-subtitle', 'Ongeza salio kwenye wallet yako')

@section('content')
<div style="max-width: 500px; margin: 0 auto;">
    <!-- Current Balance -->
    <div class="wallet-card mb-8">
        <div style="position: relative; z-index: 10;">
            <div class="wallet-label">Salio Lako Sasa</div>
            <div class="wallet-balance">TZS {{ number_format($user->wallet->balance ?? 0, 0) }}</div>
        </div>
    </div>
    
    <!-- Deposit Form -->
    <div class="card card-body" id="depositForm">
        <h4 class="mb-4">
            <i data-lucide="plus-circle" style="color: var(--primary); display: inline;"></i>
            Weka Pesa
        </h4>
        
        <!-- Amount Selection -->
        <div class="form-group">
            <label class="form-label">Chagua au weka kiasi</label>
            <div class="grid grid-3 mb-3" style="gap: var(--space-2);">
                <button type="button" class="btn btn-secondary amount-btn" onclick="setAmount(1000)">TZS 1,000</button>
                <button type="button" class="btn btn-secondary amount-btn" onclick="setAmount(5000)">TZS 5,000</button>
                <button type="button" class="btn btn-secondary amount-btn" onclick="setAmount(10000)">TZS 10,000</button>
            </div>
            <div style="position: relative;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">TZS</span>
                <input type="number" id="amount" class="form-control" 
                       placeholder="5000" 
                       min="500"
                       max="1000000"
                       style="padding-left: 50px;">
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-1);">
                Min: TZS 500 | Max: TZS 1,000,000
            </div>
        </div>
        
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
        
        <button type="button" id="payButton" class="btn btn-primary btn-lg" style="width: 100%;" onclick="initiateDeposit()">
            <i data-lucide="credit-card"></i>
            Weka Pesa
        </button>
        
        <div class="text-center mt-4">
            <a href="{{ route('wallet.index') }}" style="color: var(--text-muted); font-size: 0.875rem;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px; display: inline;"></i>
                Rudi kwenye Wallet
            </a>
        </div>
    </div>
    
    <!-- Processing State -->
    <div class="card card-body text-center" id="processingState" style="display: none;">
        <div class="mb-6">
            <div style="width: 80px; height: 80px; border: 4px solid var(--bg-tertiary); border-top-color: var(--primary); border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
        </div>
        <h4 class="mb-2">Inasubiri Malipo...</h4>
        <p style="color: var(--text-muted);" id="statusMessage">Lipia PUSH uliopokea kwenye simu yako</p>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: var(--space-4);">
            Muda: <span id="countdown">2:00</span>
        </p>
        
        <button type="button" class="btn btn-secondary mt-6" onclick="cancelDeposit()">
            <i data-lucide="x"></i>
            Ghairi
        </button>
    </div>
    
    <!-- Success State -->
    <div class="card card-body text-center" id="successState" style="display: none;">
        <div style="width: 100px; height: 100px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
            <i data-lucide="check" style="width: 50px; height: 50px; color: white;"></i>
        </div>
        <h2 class="mb-2" style="color: var(--success);">Pesa Imeongezwa! 🎉</h2>
        <p class="mb-2">Salio jipya:</p>
        <p id="newBalance" style="font-size: 2rem; font-weight: 800; color: var(--primary);"></p>
        
        <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-lg mt-6" style="width: 100%;">
            <i data-lucide="wallet"></i>
            Angalia Wallet
        </a>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
.amount-btn.active {
    background: var(--primary) !important;
    color: white !important;
}
</style>

@push('scripts')
<script>
    const initiateUrl = "{{ route('payments.deposit.initiate') }}";
    const statusUrl = "{{ route('payments.status') }}";
    const csrfToken = "{{ csrf_token() }}";
    
    let orderId = null;
    let pollInterval = null;
    let countdownInterval = null;
    let countdownSeconds = 120;

    function setAmount(amount) {
        document.getElementById('amount').value = amount;
        
        // Update active state
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    function initiateDeposit() {
        const amount = document.getElementById('amount').value;
        const phone = document.getElementById('phone').value;
        
        if (!amount || amount < 500) {
            alert('Kiasi cha chini ni TZS 500');
            return;
        }
        
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
            body: JSON.stringify({ amount: amount, phone: phone }),
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
        document.getElementById('depositForm').style.display = 'none';
        document.getElementById('processingState').style.display = 'block';
    }

    function showSuccessState(newBalance) {
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('successState').style.display = 'block';
        document.getElementById('newBalance').textContent = 'TZS ' + new Intl.NumberFormat().format(newBalance || 0);
        lucide.createIcons();
    }

    function resetPayButton() {
        const button = document.getElementById('payButton');
        button.disabled = false;
        button.innerHTML = '<i data-lucide="credit-card"></i> Weka Pesa';
        lucide.createIcons();
    }

    function startPolling() {
        pollInterval = setInterval(checkStatus, 5000);
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
                // Fetch new balance
                location.reload(); // Simple approach - reload to show updated balance
            } else if (data.status === 'FAILED' || data.status === 'CANCELLED') {
                stopPolling();
                clearInterval(countdownInterval);
                document.getElementById('statusMessage').textContent = 'Malipo yameshindwa. Jaribu tena.';
            }
        })
        .catch(error => {
            console.log('Status check error:', error);
        });
    }

    function cancelDeposit() {
        stopPolling();
        clearInterval(countdownInterval);
        document.getElementById('processingState').style.display = 'none';
        document.getElementById('depositForm').style.display = 'block';
        resetPayButton();
        orderId = null;
    }
</script>
@endpush
@endsection
