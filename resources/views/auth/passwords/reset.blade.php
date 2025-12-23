@extends('layouts.guest')

@section('title', 'Nenosiri Jipya - SKYpesa')

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
                <p class="mt-2" style="color: var(--text-muted);">Uhakiki umekamilika. Weka nenosiri jipya.</p>
            </div>
            
            <!-- Card -->
            <div class="card" style="padding: var(--space-8);">
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        <i data-lucide="check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('password.reset.update') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Nenosiri Jipya</label>
                        <div style="position: relative;">
                            <i data-lucide="lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="••••••••" required autofocus style="padding-left: 44px; padding-right: 44px;">
                            <button type="button" onclick="togglePassword('password', 'eyeIcon1')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i data-lucide="eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                        @error('password')
                            <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rudia Nenosiri Jipya</label>
                        <div style="position: relative;">
                            <i data-lucide="shield-check" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                   placeholder="••••••••" required style="padding-left: 44px; padding-right: 44px;">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eyeIcon2')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i data-lucide="eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                        <i data-lucide="refresh-cw"></i>
                        Badilisha Nenosiri
                    </button>
                </form>
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
