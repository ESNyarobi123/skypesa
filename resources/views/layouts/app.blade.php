<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SKYpesa</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i data-lucide="coins" style="color: var(--primary);"></i>
            SKY<span>pesa</span>
        </div>
        
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('tasks.index') }}" class="sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                        <i data-lucide="clipboard-list"></i>
                        Kazi
                    </a>
                </li>
                <li>
                    <a href="{{ route('wallet.index') }}" class="sidebar-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                        <i data-lucide="wallet"></i>
                        Wallet
                    </a>
                </li>
                <li>
                    <a href="{{ route('withdrawals.index') }}" class="sidebar-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}">
                        <i data-lucide="banknote"></i>
                        Withdrawals
                    </a>
                </li>
                <li>
                    <a href="{{ route('subscriptions.index') }}" class="sidebar-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
                        <i data-lucide="crown"></i>
                        Subscription
                    </a>
                </li>
                <li>
                    <a href="{{ route('referrals.index') }}" class="sidebar-link {{ request()->routeIs('referrals.*') ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        Referrals
                    </a>
                </li>
                <li>
                    <a href="{{ route('surveys.index') }}" class="sidebar-link {{ request()->routeIs('surveys.*') ? 'active' : '' }}">
                        <i data-lucide="message-circle"></i>
                        SkyOpinions™
                    </a>
                </li>
                
                @if(auth()->user()->isAdmin())
                <li style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 1rem;">Admin</span>
                </li>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i data-lucide="shield"></i>
                        Admin Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i data-lucide="users-2"></i>
                        Watumiaji
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tasks.index') }}" class="sidebar-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                        <i data-lucide="list-todo"></i>
                        Manage Tasks
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.withdrawals.index') }}" class="sidebar-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                        <i data-lucide="credit-card"></i>
                        Malipo
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.adsterra.index') }}" class="sidebar-link {{ request()->routeIs('admin.adsterra.*') ? 'active' : '' }}">
                        <i data-lucide="link"></i>
                        SkyLinks Manager
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        
        <!-- User Info at Bottom -->
        <div style="position: absolute; bottom: 1.5rem; left: 1.5rem; right: 1.5rem;">
            <div class="card" style="padding: 1rem;">
                <div class="flex items-center gap-4">
                    <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }}" 
                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">{{ auth()->user()->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ auth()->user()->getPlanName() }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 0.75rem;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        Toka
                    </button>
                </form>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700;">@yield('page-title', 'Dashboard')</h1>
                <p style="font-size: 0.875rem; color: var(--text-muted);">@yield('page-subtitle', 'Karibu tena!')</p>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="btn btn-icon btn-secondary" id="menuToggle" style="display: none;">
                <i data-lucide="menu"></i>
            </button>
        </div>
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                <i data-lucide="x-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-error">
                <i data-lucide="alert-triangle"></i>
                <ul style="list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <script>
        lucide.createIcons();
        
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
        
        // Show menu toggle on mobile
        if (window.innerWidth <= 1024) {
            menuToggle.style.display = 'flex';
        }
        
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                menuToggle.style.display = 'flex';
            } else {
                menuToggle.style.display = 'none';
                sidebar.classList.remove('open');
            }
        });
    </script>
    
    @stack('scripts')
    
    {{-- Monetag Integration --}}
    @include('partials.monetag')
</body>
</html>
