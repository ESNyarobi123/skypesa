@extends('layouts.guest')

@section('title', 'Jiunge - SKYpesa')

@section('content')
<div class="hero" style="min-height: 100vh; padding: var(--space-8) 0;">
    <div class="container">
        <div style="max-width: 480px; margin: 0 auto;">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="navbar-brand justify-center" style="font-size: 2rem; display: inline-flex;">
                    <i data-lucide="coins" style="color: var(--primary); width: 40px; height: 40px;"></i>
                    SKY<span>pesa</span>
                </a>
                <p class="mt-2" style="color: var(--text-muted);">Fungua akaunti na uanze kupata pesa!</p>
            </div>
            
            <!-- Register Card -->
            <div class="card" style="padding: var(--space-8);">
                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <i data-lucide="alert-circle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Jina Kamili</label>
                        <div style="position: relative;">
                            <i data-lucide="user" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="text" name="name" class="form-control" placeholder="Jina lako kamili" value="{{ old('name') }}" required autofocus style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div style="position: relative;">
                            <i data-lucide="mail" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="email" name="email" class="form-control" placeholder="mfano@email.com" value="{{ old('email') }}" required style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Namba ya Simu</label>
                        <div style="position: relative;">
                            <i data-lucide="phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="tel" name="phone" class="form-control" placeholder="0712 345 678" value="{{ old('phone') }}" required style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nenosiri</label>
                        <div style="position: relative;">
                            <i data-lucide="lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Angalau herufi 8" required style="padding-left: 44px; padding-right: 44px;">
                            <button type="button" onclick="togglePassword('password', 'eyeIcon1')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i data-lucide="eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thibitisha Nenosiri</label>
                        <div style="position: relative;">
                            <i data-lucide="lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Rudia nenosiri" required style="padding-left: 44px; padding-right: 44px;">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eyeIcon2')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i data-lucide="eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Referral Code (Hiari)</label>
                        <div style="position: relative;">
                            <i data-lucide="gift" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="text" name="referral_code" class="form-control" placeholder="Weka code ya mtu aliyekualika" value="{{ old('referral_code', request('ref')) }}" style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: var(--space-2); cursor: pointer; font-size: 0.875rem; color: var(--text-secondary);">
                            <input type="checkbox" name="terms" required style="accent-color: var(--primary); margin-top: 3px;">
                            <span>Nakubali <a href="#" style="color: var(--primary);">Masharti na Vigezo</a> na <a href="#" style="color: var(--primary);">Sera ya Faragha</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i data-lucide="user-plus"></i>
                        Fungua Akaunti
                    </button>
                </form>
                
                <!-- Plan Selection Info -->
                <div style="margin-top: var(--space-6); padding: var(--space-4); background: var(--gradient-glow); border-radius: var(--radius-lg);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="info" style="color: var(--primary); width: 20px; height: 20px;"></i>
                        <div style="font-size: 0.875rem;">
                            <strong style="color: var(--primary);">Unaanza na Free Plan!</strong>
                            <p style="color: var(--text-secondary); margin-top: 2px;">Unaweza upgrade baadaye kupata faida zaidi.</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Una akaunti tayari? 
                        <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600;">Ingia hapa</a>
                    </p>
                </div>
            </div>
            
            <!-- Back to home -->
            <div class="text-center mt-6">
                <a href="/" class="flex items-center justify-center gap-2" style="color: var(--text-muted); font-size: 0.875rem;">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Rudi Nyumbani
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const password = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);
        
        if (password.type === 'password') {
            password.type = 'text';
            eyeIcon.setAttribute('data-lucide', 'eye-off');
        } else {
            password.type = 'password';
            eyeIcon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>
@endpush
@endsection
