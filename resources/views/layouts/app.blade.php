<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SKYpesa - Pata pesa kwa kufanya kazi rahisi kwenye simu yako. Fanya tasks, jibu surveys, na uweze kuchangia pesa halisi!">
    <title>@yield('title', 'Dashboard') - SKYpesa</title>
     
    {{-- PWA Meta Tags --}}
    @include('components.pwa-meta')
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Desktop Sidebar Toggle Styles */
        @media (min-width: 1025px) {
            .sidebar {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .main-content {
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            body.sidebar-collapsed .sidebar {
                transform: translateX(-100%);
            }
            body.sidebar-collapsed .main-content {
                margin-left: 0;
            }
        }
        
        .mobile-header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-lg);
            transition: all 0.2s ease;
        }
        .mobile-header-icon:hover {
            background: rgba(16, 185, 129, 0.1) !important;
            color: var(--primary) !important;
            border-color: var(--primary) !important;
        }
    </style>
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
            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="mobile-header-icon" style="position: relative;">
                <i data-lucide="bell"></i>
                @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                @if($unreadCount > 0)
                    <span style="position: absolute; top: -5px; right: -5px; background: var(--error); color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; min-width: 18px; text-align: center; border: 2px solid var(--bg-card);">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </a>
            
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
                <li>
                    <a href="{{ route('leaderboard') }}" class="sidebar-link {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
                        <i data-lucide="trophy"></i>
                        Leaderboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('support.index') }}" class="sidebar-link {{ request()->routeIs('support.*') ? 'active' : '' }}">
                        <i data-lucide="message-square"></i>
                        Support
                        @php 
                            $unreadSupport = auth()->user()->supportTickets()
                                ->whereHas('messages', function($q) {
                                    $q->where('is_admin', true)->where('is_read', false);
                                })->count();
                        @endphp
                        @if($unreadSupport > 0)
                            <span style="margin-left: auto; background: var(--primary); color: white; font-size: 0.65rem; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $unreadSupport }}</span>
                        @endif
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
                <li>
                    <a href="{{ route('admin.support.index') }}" class="sidebar-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                        <i data-lucide="message-circle"></i>
                        Support Center
                        @php 
                            $unreadAdminSupport = \App\Models\SupportMessage::where('is_admin', false)->where('is_read', false)->count();
                        @endphp
                        @if($unreadAdminSupport > 0)
                            <span style="margin-left: auto; background: var(--primary); color: white; font-size: 0.65rem; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $unreadAdminSupport }}</span>
                        @endif
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        
        <!-- User Info at Bottom -->
        <div style="margin-top: auto; padding-top: 1.5rem;">
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
        <!-- Desktop Top Bar -->
        <div class="hide-tablet" style="margin-bottom: 2rem;">
            <div style="background: rgba(26, 26, 26, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-xl); padding: 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <div class="flex items-center gap-4">
                    <button id="desktopMenuBtn" class="mobile-menu-btn" style="display: flex; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); width: 40px; height: 40px;">
                        <i data-lucide="menu" style="width: 20px; height: 20px;"></i>
                    </button>
                    <div>
                        <h1 style="font-size: 1.25rem; font-weight: 800; margin: 0; line-height: 1;">@yield('page-title', 'Dashboard')</h1>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">@yield('page-subtitle', 'Karibu tena!')</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <a href="{{ route('notifications.index') }}" class="mobile-header-icon" style="position: relative; width: 40px; height: 40px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                        <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
                        @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                        @if($unreadCount > 0)
                            <span style="position: absolute; top: -2px; right: -2px; background: var(--error); color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; min-width: 18px; text-align: center; border: 2px solid #1a1a1a; font-weight: 700;">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Balance Pill -->
                    <div style="background: var(--gradient-glow); padding: 0.5rem 1rem; border-radius: var(--radius-lg); border: 1px solid rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 24px; height: 24px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i data-lucide="wallet" style="width: 12px; height: 12px;"></i>
                        </div>
                        <div style="font-weight: 800; font-size: 0.875rem; color: var(--primary);">
                            TZS {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}
                        </div>
                    </div>
                </div>
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

        const desktopMenuBtn = document.getElementById('desktopMenuBtn');
        if (desktopMenuBtn) {
            desktopMenuBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                const icon = desktopMenuBtn.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-lucide', isCollapsed ? 'menu' : 'x');
                    lucide.createIcons();
                }
            });
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
    
    {{-- PWA Install Prompt --}}
    @include('components.pwa-install')
    
    {{-- PWA Service Worker Registration --}}
    <script>
        // Register PWA Service Worker (Silent Updates)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/pwa-sw.js', { scope: '/' })
                    .then((registration) => {
                        console.log('[PWA] Service Worker registered successfully:', registration.scope);
                        
                        // Check for updates silently - no notification popup
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            console.log('[PWA] New service worker installing...');
                            
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available - update silently
                                    console.log('[PWA] New version available, will activate on next visit');
                                    // Skip waiting and activate immediately
                                    newWorker.postMessage({ type: 'SKIP_WAITING' });
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        console.log('[PWA] Service Worker registration failed:', error);
                    });
            });
            
            // Refresh page when new service worker takes control
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    // Silently refresh - no popup needed
                    console.log('[PWA] Controller changed, page will update');
                }
            });
        }
        
        // Request notification permission on first interaction
        document.addEventListener('click', () => {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('[PWA] Notification permission:', permission);
                });
            }
        }, { once: true });
    </script>
</body>
</html>
