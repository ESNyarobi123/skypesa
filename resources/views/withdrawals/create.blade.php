@extends('layouts.app')

@section('title', 'Toa Pesa')
@section('page-title', 'Omba Kutoa Pesa')
@section('page-subtitle', 'Jaza fomu kupata malipo yako')

@section('content')
<div style="max-width: 600px;">
    <!-- Balance Card -->
    <div class="wallet-card mb-8">
        <div style="position: relative; z-index: 10;">
            <div class="wallet-label">Salio Linalopatikana</div>
            <div class="wallet-balance">TZS {{ number_format($wallet->getAvailableBalance(), 0) }}</div>
            @if($wallet->pending_withdrawal > 0)
            <div style="margin-top: var(--space-2); font-size: 0.875rem; color: rgba(255,255,255,0.7);">
                TZS {{ number_format($wallet->pending_withdrawal, 0) }} inasubiri kulipwa
            </div>
            @endif
        </div>
    </div>
    
    <!-- Plan Info -->
    <div class="card card-body mb-8" style="background: var(--gradient-glow);">
        <div class="flex items-center gap-4 mb-4">
            <i data-lucide="info" style="color: var(--primary);"></i>
            <h4 style="margin: 0;">Masharti ya Mpango Wako ({{ auth()->user()->getPlanName() }})</h4>
        </div>
        <div class="grid grid-3" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Min. Withdrawal</div>
                <div style="font-weight: 700;">TZS {{ number_format($plan?->min_withdrawal ?? 10000, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Ada</div>
                <div style="font-weight: 700;">{{ $plan?->withdrawal_fee_percent ?? 20 }}%</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Muda</div>
                <div style="font-weight: 700;">Siku {{ $plan?->processing_days ?? 7 }}</div>
            </div>
        </div>
    </div>
    
    <!-- Withdrawal Form -->
    <div class="card card-body">
        <form action="{{ route('withdrawals.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Kiasi (TZS)</label>
                <div style="position: relative;">
                    <i data-lucide="coins" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                    <input type="number" name="amount" id="amount" class="form-control" 
                           placeholder="Weka kiasi" 
                           value="{{ old('amount') }}"
                           min="{{ $plan?->min_withdrawal ?? 10000 }}"
                           max="{{ $wallet->getAvailableBalance() }}"
                           style="padding-left: 44px;"
                           required
                           oninput="calculateFee()">
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-1);">
                    Min: TZS {{ number_format($plan?->min_withdrawal ?? 10000, 0) }} | Max: TZS {{ number_format($wallet->getAvailableBalance(), 0) }}
                </div>
            </div>
            
            <!-- Fee Calculation -->
            <div id="feeInfo" class="card" style="padding: var(--space-4); margin-bottom: var(--space-5); display: none;">
                <div class="flex justify-between mb-2">
                    <span style="color: var(--text-muted);">Kiasi</span>
                    <span id="displayAmount">TZS 0</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span style="color: var(--text-muted);">Ada ({{ $plan?->withdrawal_fee_percent ?? 20 }}%)</span>
                    <span id="displayFee" style="color: var(--error);">- TZS 0</span>
                </div>
                <div class="flex justify-between" style="padding-top: var(--space-2); border-top: 1px solid rgba(255,255,255,0.1);">
                    <span style="font-weight: 600;">Utapata</span>
                    <span id="displayNet" style="font-weight: 700; color: var(--success);">TZS 0</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mtoa Huduma</label>
                <div class="grid grid-2" style="gap: var(--space-3);">
                    <label class="card" style="padding: var(--space-3); cursor: pointer; border: 2px solid transparent;" onclick="selectProvider(this, 'mpesa')">
                        <input type="radio" name="payment_provider" value="mpesa" required style="display: none;">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #e11d48; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">M</div>
                            <span>M-Pesa</span>
                        </div>
                    </label>
                    <label class="card" style="padding: var(--space-3); cursor: pointer; border: 2px solid transparent;" onclick="selectProvider(this, 'tigopesa')">
                        <input type="radio" name="payment_provider" value="tigopesa" required style="display: none;">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #0ea5e9; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">T</div>
                            <span>Tigo Pesa</span>
                        </div>
                    </label>
                    <label class="card" style="padding: var(--space-3); cursor: pointer; border: 2px solid transparent;" onclick="selectProvider(this, 'airtelmoney')">
                        <input type="radio" name="payment_provider" value="airtelmoney" required style="display: none;">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #dc2626; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">A</div>
                            <span>Airtel Money</span>
                        </div>
                    </label>
                    <label class="card" style="padding: var(--space-3); cursor: pointer; border: 2px solid transparent;" onclick="selectProvider(this, 'halopesa')">
                        <input type="radio" name="payment_provider" value="halopesa" required style="display: none;">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #f97316; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">H</div>
                            <span>Halo Pesa</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Namba ya Simu</label>
                <div style="position: relative;">
                    <i data-lucide="phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                    <input type="tel" name="payment_number" class="form-control" 
                           placeholder="0712 345 678" 
                           value="{{ old('payment_number', auth()->user()->phone) }}"
                           style="padding-left: 44px;"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Jina Kamili la Mwenye Akaunti</label>
                <div style="position: relative;">
                    <i data-lucide="user" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                    <input type="text" name="payment_name" class="form-control" 
                           placeholder="Mfano: John Doe Mwalimu" 
                           value="{{ old('payment_name', auth()->user()->name) }}"
                           style="padding-left: 44px;"
                           required>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-1);">
                    <i data-lucide="info" style="width: 12px; height: 12px; display: inline;"></i>
                    Weka jina kamili la mtu anayemiliki namba hii ya simu
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                <i data-lucide="send"></i>
                Tuma Ombi
            </button>
        </form>
    </div>
    
    <!-- Back Link -->
    <div class="text-center mt-6">
        <a href="{{ route('withdrawals.index') }}" class="flex items-center justify-center gap-2" style="color: var(--text-muted);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Rudi kwenye Maombi
        </a>
    </div>
</div>

@push('scripts')
<script>
    const feePercent = {{ $plan?->withdrawal_fee_percent ?? 20 }};
    
    function calculateFee() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fee = (amount * feePercent) / 100;
        const net = amount - fee;
        
        if (amount > 0) {
            document.getElementById('feeInfo').style.display = 'block';
            document.getElementById('displayAmount').textContent = 'TZS ' + amount.toLocaleString();
            document.getElementById('displayFee').textContent = '- TZS ' + fee.toLocaleString();
            document.getElementById('displayNet').textContent = 'TZS ' + net.toLocaleString();
        } else {
            document.getElementById('feeInfo').style.display = 'none';
        }
    }
    
    function selectProvider(element, value) {
        // Remove selection from all
        document.querySelectorAll('input[name="payment_provider"]').forEach(function(radio) {
            radio.closest('label').style.borderColor = 'transparent';
        });
        
        // Select this one
        element.style.borderColor = 'var(--primary)';
        element.querySelector('input').checked = true;
    }
</script>
@endpush
@endsection
