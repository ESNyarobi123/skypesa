@extends('layouts.guest')

@section('title', 'Sahau Nenosiri - SKYpesa')

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
                <p class="mt-2" style="color: var(--text-muted);">Rudisha nenosiri lako</p>
            </div>
            
            <!-- Reset Card -->
            <div class="card" style="padding: var(--space-8);">
                @if(session('status'))
                    <div class="alert alert-success mb-4">
                        <i data-lucide="check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                
                <div class="text-center mb-6">
                    <div style="width: 64px; height: 64px; background: rgba(16, 185, 129, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                        <i data-lucide="key" style="color: var(--primary); width: 32px; height: 32px;"></i>
                    </div>
                    <h4 class="mb-2">Umesahau Nenosiri?</h4>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Weka email yako na tutakutumia link ya kurudisha nenosiri.
                    </p>
                </div>
                
                <form method="POST" action="{{ route('password.request') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div style="position: relative;">
                            <i data-lucide="mail" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                            <input type="email" name="email" class="form-control" placeholder="mfano@email.com" value="{{ old('email') }}" required autofocus style="padding-left: 44px;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i data-lucide="send"></i>
                        Tuma Link ya Kurudisha
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2" style="color: var(--text-muted); font-size: 0.875rem;">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        Rudi kwenye Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
