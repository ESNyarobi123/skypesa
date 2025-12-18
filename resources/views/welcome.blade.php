@extends('layouts.guest')

@section('title', 'SKYpesa - Pata Pesa Kwa Kutazama Matangazo')
@section('description', 'SKYpesa ni jukwaa la kujipatia pesa mtandaoni kwa kukamilisha kazi rahisi. Tazama matangazo, shiriki links, na upate malipo halali.')

@section('content')
<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="navbar-container">
        <a href="/" class="navbar-brand">
            <i data-lucide="coins" style="color: var(--primary);"></i>
            SKY<span>pesa</span>
        </a>
        
        <ul class="navbar-nav">
            <li><a href="#features" class="nav-link">Jinsi Inavyofanya Kazi</a></li>
            <li><a href="#plans" class="nav-link">Mipango</a></li>
            <li><a href="#about" class="nav-link">Kuhusu</a></li>
        </ul>
        
        <div class="flex gap-4">
            <a href="{{ route('login') }}" class="btn btn-secondary">Ingia</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Jiunge Sasa</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content fade-in">
        <div class="badge badge-primary mb-4">
            <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
            Jukwaa Halali la Kujipatia Pesa
        </div>
        
        <h1 class="hero-title">
            Pata Pesa Kwa<br>Kutazama Matangazo
        </h1>
        
        <p class="hero-subtitle">
            Jiunge na maelfu ya Watanzania wanaopata pesa kila siku kwa kukamilisha 
            kazi rahisi. Hakuna udanganyifu, hakuna minyororo - malipo halali tu!
        </p>
        
        <div class="flex gap-4 justify-center">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                <i data-lucide="rocket"></i>
                Anza Sasa - Bure!
            </a>
            <a href="#how-it-works" class="btn btn-outline btn-lg">
                <i data-lucide="play-circle"></i>
                Jinsi Inavyofanya Kazi
            </a>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-3 mt-16" style="max-width: 800px; margin-left: auto; margin-right: auto;">
            <div class="stat-card slide-up" style="animation-delay: 0.1s;">
                <div class="stat-value">10K+</div>
                <div class="stat-label">Watumiaji</div>
            </div>
            <div class="stat-card slide-up" style="animation-delay: 0.2s;">
                <div class="stat-value">TZS 50M+</div>
                <div class="stat-label">Imelipwa</div>
            </div>
            <div class="stat-card slide-up" style="animation-delay: 0.3s;">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Msaada</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-16" style="background: var(--bg-dark);">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">Jinsi Inavyofanya Kazi</h2>
            <p>Hatua 4 rahisi za kuanza kupata pesa</p>
        </div>
        
        <div class="grid grid-4">
            <div class="card card-body text-center">
                <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <i data-lucide="user-plus" style="color: var(--primary);"></i>
                </div>
                <h4 class="mb-2">1. Jiunge</h4>
                <p style="font-size: 0.875rem;">Fungua akaunti bure kwa dakika 1 tu</p>
            </div>
            
            <div class="card card-body text-center">
                <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <i data-lucide="play-circle" style="color: var(--primary);"></i>
                </div>
                <h4 class="mb-2">2. Kamilisha Kazi</h4>
                <p style="font-size: 0.875rem;">Tazama matangazo au shiriki links</p>
            </div>
            
            <div class="card card-body text-center">
                <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <i data-lucide="wallet" style="color: var(--primary);"></i>
                </div>
                <h4 class="mb-2">3. Kusanya Pesa</h4>
                <p style="font-size: 0.875rem;">Pesa inaingia wallet yako moja kwa moja</p>
            </div>
            
            <div class="card card-body text-center">
                <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <i data-lucide="banknote" style="color: var(--primary);"></i>
                </div>
                <h4 class="mb-2">4. Toa Pesa</h4>
                <p style="font-size: 0.875rem;">Toa pesa kupitia M-Pesa, Tigo Pesa</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-16">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">Kwa Nini SKYpesa?</h2>
            <p>Faida za kujiunga na jukwaa letu</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card card-body">
                <i data-lucide="shield-check" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Salama & Halali</h4>
                <p style="font-size: 0.875rem;">Tunafanya kazi na advertising networks halali. Hakuna minyororo wala udanganyifu.</p>
            </div>
            
            <div class="card card-body">
                <i data-lucide="zap" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Malipo ya Haraka</h4>
                <p style="font-size: 0.875rem;">Omba kutoa pesa na upate ndani ya masaa 24-48 kulingana na mpango wako.</p>
            </div>
            
            <div class="card card-body">
                <i data-lucide="smartphone" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Tumia Simu Yoyote</h4>
                <p style="font-size: 0.875rem;">Inafanya kazi kwenye simu yoyote yenye internet. Huhitaji smartphone ghali.</p>
            </div>
            
            <div class="card card-body">
                <i data-lucide="clock" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Fanya Wakati Wowote</h4>
                <p style="font-size: 0.875rem;">Asubuhi, mchana, au usiku - kazi zinapatikana masaa 24/7.</p>
            </div>
            
            <div class="card card-body">
                <i data-lucide="users" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Referral Bonus</h4>
                <p style="font-size: 0.875rem;">Alika marafiki na upate bonus kwa kila mtu anayejiunga kupitia wewe.</p>
            </div>
            
            <div class="card card-body">
                <i data-lucide="headphones" style="color: var(--primary); width: 40px; height: 40px; margin-bottom: var(--space-4);"></i>
                <h4 class="mb-2">Msaada 24/7</h4>
                <p style="font-size: 0.875rem;">Timu yetu iko tayari kukusaidia wakati wowote unapohitaji.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="plans" class="py-16" style="background: var(--bg-dark);">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">Chagua Mpango Wako</h2>
            <p>Anza bure au upgrade kwa faida zaidi</p>
        </div>
        
        <div class="grid grid-4">
            <!-- Free Plan -->
            <div class="plan-card">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--space-2);">Bure</div>
                <div class="plan-name">Free</div>
                <div class="plan-price">TZS 0</div>
                <p style="font-size: 0.875rem; margin-top: var(--space-2);">Anza bila kulipa chochote</p>
                
                <ul class="plan-features">
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Tasks 5 kwa siku
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        TZS 50 kwa task
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Min. Withdrawal: TZS 10,000
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Fee: 20%
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Processing: Siku 7
                    </li>
                </ul>
                
                <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">
                    Jiunge Bure
                </a>
            </div>
            
            <!-- Phase 1 -->
            <div class="plan-card">
                <div style="font-size: 0.75rem; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--space-2);">Hatua 1</div>
                <div class="plan-name">Phase 1</div>
                <div class="plan-price">TZS 5,000<span>/mwezi</span></div>
                <p style="font-size: 0.875rem; margin-top: var(--space-2);">Panda ngazi ya kwanza</p>
                
                <ul class="plan-features">
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Tasks 15 kwa siku
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        TZS 75 kwa task
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Min. Withdrawal: TZS 5,000
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Fee: 10%
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Processing: Siku 3
                    </li>
                </ul>
                
                <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">
                    Chagua Phase 1
                </a>
            </div>
            
            <!-- Phase 2 -->
            <div class="plan-card featured">
                <div style="font-size: 0.75rem; color: #8b5cf6; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--space-2);">Hatua 2</div>
                <div class="plan-name">Phase 2</div>
                <div class="plan-price">TZS 15,000<span>/mwezi</span></div>
                <p style="font-size: 0.875rem; margin-top: var(--space-2);">Mapato makubwa zaidi</p>
                
                <ul class="plan-features">
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Tasks 30 kwa siku
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        TZS 100 kwa task
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Min. Withdrawal: TZS 3,000
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Fee: 5%
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Processing: Saa 24
                    </li>
                </ul>
                
                <a href="{{ route('register') }}" class="btn btn-primary" style="width: 100%;">
                    Chagua Phase 2
                </a>
            </div>
            
            <!-- Premium -->
            <div class="plan-card">
                <div style="font-size: 0.75rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--space-2);">Premium</div>
                <div class="plan-name">Premium</div>
                <div class="plan-price">TZS 30,000<span>/mwezi</span></div>
                <p style="font-size: 0.875rem; margin-top: var(--space-2);">Bila mipaka!</p>
                
                <ul class="plan-features">
                    <li>
                        <i data-lucide="infinity" style="width: 18px; height: 18px;"></i>
                        Tasks UNLIMITED
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        TZS 150 kwa task
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Min. Withdrawal: TZS 2,000
                    </li>
                    <li>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Fee: 2% tu!
                    </li>
                    <li>
                        <i data-lucide="zap" style="width: 18px; height: 18px;"></i>
                        Processing: PAPO HAPO!
                    </li>
                </ul>
                
                <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">
                    Chagua Premium
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16">
    <div class="container">
        <div class="card" style="background: var(--gradient-primary); padding: var(--space-12); text-align: center; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50%; right: -20%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%); transform: rotate(30deg);"></div>
            
            <div style="position: relative; z-index: 10;">
                <h2 style="color: white; margin-bottom: var(--space-4);">Tayari Kuanza Kupata Pesa?</h2>
                <p style="color: rgba(255,255,255,0.8); max-width: 500px; margin: 0 auto var(--space-6);">
                    Jiunge sasa na uanze kupata pesa leo! Ni bure kuanza na unaweza upgrade wakati wowote.
                </p>
                <a href="{{ route('register') }}" class="btn btn-lg" style="background: white; color: var(--primary);">
                    <i data-lucide="rocket"></i>
                    Jiunge Sasa - Bure!
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer style="background: var(--bg-dark); padding: var(--space-8) 0; border-top: 1px solid rgba(255,255,255,0.05);">
    <div class="container">
        <div class="flex justify-between items-center" style="flex-wrap: wrap; gap: var(--space-4);">
            <div class="flex items-center gap-2">
                <i data-lucide="coins" style="color: var(--primary);"></i>
                <span style="font-weight: 700;">SKY<span style="color: var(--primary);">pesa</span></span>
            </div>
            
            <div style="color: var(--text-muted); font-size: 0.875rem;">
                &copy; {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.
            </div>
            
            <div class="flex gap-4">
                <a href="#" style="color: var(--text-muted);"><i data-lucide="facebook"></i></a>
                <a href="#" style="color: var(--text-muted);"><i data-lucide="instagram"></i></a>
                <a href="#" style="color: var(--text-muted);"><i data-lucide="twitter"></i></a>
            </div>
        </div>
    </div>
</footer>

@push('scripts')
<script>
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>
@endpush
@endsection
