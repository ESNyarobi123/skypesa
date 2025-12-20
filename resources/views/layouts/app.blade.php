<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111111">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
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
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="mobile-header-brand">
            <i data-lucide="coins"></i>
            SKY<span>pesa</span>
        </div>
        <div class="flex items-center gap-2">
            <!-- Balance Quick View -->
            <div class="hide-mobile" style="background: var(--gradient-glow); padding: 0.5rem 1rem; border-radius: var(--radius-lg); display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="wallet" style="width: 16px; height: 16px; color: var(--primary);"></i>
                <span style="font-weight: 700; font-size: 0.875rem; color: var(--primary);">TZS {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</span>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
                <i data-lucide="menu" id="menuIcon"></i>
            </button>
        </div>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</div>
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
        <!-- Desktop Top Bar (Hidden on Mobile) -->
        <div class="flex justify-between items-center mb-8 hide-tablet">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700;">@yield('page-title', 'Dashboard')</h1>
                <p style="font-size: 0.875rem; color: var(--text-muted);">@yield('page-subtitle', 'Karibu tena!')</p>
            </div>
        </div>
        
        <!-- Mobile Page Title -->
        <div class="show-tablet mb-4" style="display: none;">
            <h1 style="font-size: 1.25rem; font-weight: 700;">@yield('page-title', 'Dashboard')</h1>
            <p style="font-size: 0.8rem; color: var(--text-muted);">@yield('page-subtitle', 'Karibu tena!')</p>
        </div>
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                <i data-lucide="x-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-error">
                <i data-lucide="alert-triangle"></i>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="bottom-nav" id="bottomNav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('tasks.index') }}" class="bottom-nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            <i data-lucide="briefcase"></i>
            <span>Kazi</span>
        </a>
        <a href="{{ route('wallet.index') }}" class="bottom-nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
            <i data-lucide="wallet"></i>
            <span>Wallet</span>
        </a>

        <a href="{{ route('subscriptions.index') }}" class="bottom-nav-item {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
            <i data-lucide="crown"></i>
            <span>VIP</span>
        </a>
    </nav>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // DOM Elements
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const menuIcon = document.getElementById('menuIcon');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const bottomNav = document.getElementById('bottomNav');
        
        // Toggle Sidebar
        function toggleSidebar() {
            const isOpen = sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
            
            // Animate icon
            if (menuIcon) {
                menuIcon.setAttribute('data-lucide', isOpen ? 'x' : 'menu');
                lucide.createIcons();
            }
        }
        
        // Close sidebar
        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
            if (menuIcon) {
                menuIcon.setAttribute('data-lucide', 'menu');
                lucide.createIcons();
            }
        }
        
        // Event Listeners
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
        
        // Close sidebar on link click (mobile)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    closeSidebar();
                }
            });
        });
        
        // Handle resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 1024) {
                    closeSidebar();
                }
            }, 250);
        });
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
        
        // Smooth scroll to top when clicking current bottom nav item
        document.querySelectorAll('.bottom-nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.classList.contains('active')) {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
        
        // Hide bottom nav on scroll down, show on scroll up (optional enhancement)
        let lastScrollY = window.scrollY;
        let ticking = false;
        
        function updateBottomNav() {
            const currentScrollY = window.scrollY;
            
            if (bottomNav && window.innerWidth <= 1024) {
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    bottomNav.style.transform = 'translateY(100%)';
                } else {
                    bottomNav.style.transform = 'translateY(0)';
                }
            }
            
            lastScrollY = currentScrollY;
            ticking = false;
        }
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateBottomNav);
                ticking = true;
            }
        }, { passive: true });
        
        // Add transition to bottom nav
        if (bottomNav) {
            bottomNav.style.transition = 'transform 0.3s ease';
        }
    </script>
    
    @stack('scripts')
    
    {{-- Monetag Integration --}}
    @include('partials.monetag')
</body>
</html>
