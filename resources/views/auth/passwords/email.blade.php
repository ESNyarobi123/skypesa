@extends('layouts.guest')

@section('title', 'Umesahau Nenosiri - SKYpesa')

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
                <p class="mt-2" style="color: var(--text-muted);">Umesahau nenosiri? Usijali, tutakusaidia.</p>
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
                
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Barua Pepe (Email)</label>
                        <div style="position: relative;">
                            <i data-lucide="mail" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="mfano@email.com" value="{{ old('email') }}" required autofocus style="padding-left: 44px;">
                        </div>
                        @error('email')
                            <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                        <i data-lucide="send"></i>
                        Tuma Kodi ya Uhakiki
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Unakumbuka nenosiri? 
                        <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600;">Rudi Kuingia</a>
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
@endsection
