@extends('layouts.guest')

@section('title', 'Ingia - SKYpesa')

@section('content')
<div class="hero" style="min-height: 100vh;">
    <div class="container">
        <div style="max-width: 440px; margin: 0 auto; padding: var(--space-8);">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="navbar-brand justify-center" style="font-size: 2rem; display: inline-flex;">
                    <i data-lucide="coins" style="color: var(--primary); width: 40px; height: 40px;"></i>
                    SKY<span>pesa</span>
                </a>
                <p class="mt-2" style="color: var(--text-muted);">Karibu tena! Ingia kwenye akaunti yako</p>
            </div>
            
            <!-- Login Card -->
            <div class="card" style="padding: var(--space-8);">
                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email au Namba ya Simu</label>
                        <div style="position: relative;">
                            <i data-lucide="mail" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="text" name="email" class="form-control" placeholder="mfano@email.com" value="{{ old('email') }}" required autofocus style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nenosiri</label>
                        <div style="position: relative;">
                            <i data-lucide="lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="padding-left: 44px; padding-right: 44px;">
                            <button type="button" onclick="togglePassword()" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i data-lucide="eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mb-6">
                        <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer; font-size: 0.875rem; color: var(--text-secondary);">
                            <input type="checkbox" name="remember" style="accent-color: var(--primary);">
                            Nikumbuke
                        </label>
                        <a href="{{ route('password.request') }}" style="font-size: 0.875rem;">Umesahau nenosiri?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i data-lucide="log-in"></i>
                        Ingia
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Huna akaunti? 
                        <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 600;">Jiunge sasa</a>
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
    function togglePassword() {
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
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
