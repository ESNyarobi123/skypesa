@extends('layouts.guest')

@section('title', 'Uhakiki wa OTP - SKYpesa')

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
                <p class="mt-2" style="color: var(--text-muted);">Tumetuma kodi ya uhakiki kwenye email yako.</p>
            </div>
            
            <!-- Card -->
            <div class="card" style="padding: var(--space-8);">
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        <i data-lucide="check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error mb-4">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <p style="font-size: 0.875rem; color: var(--text-secondary);">Email: <strong>{{ $email }}</strong></p>
                </div>
                
                <form method="POST" action="{{ route('password.otp.verify') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div class="form-group">
                        <label class="form-label">Kodi ya OTP (Namba 6)</label>
                        <div style="position: relative;">
                            <i data-lucide="key" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="text" name="otp" class="form-control @error('otp') is-invalid @enderror" 
                                   placeholder="123456" maxlength="6" required autofocus 
                                   style="padding-left: 44px; text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: 800;">
                        </div>
                        @error('otp')
                            <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                        <i data-lucide="shield-check"></i>
                        Hakiki Kodi
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Hukupokea kodi? 
                        <a href="{{ route('password.request') }}" style="color: var(--primary); font-weight: 600;">Jaribu Tena</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
