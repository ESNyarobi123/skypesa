<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SKYpesa - Pata Pesa Kwa Kutazama na Kukamilisha Task')</title>
    <meta name="description" content="@yield('description', 'SKYpesa ni jukwaa la kujipatia pesa mtandaoni kwa kukamilisha kazi rahisi. Tazama matangazo, shiriki links, na upate malipo halali.')">
    
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
    
    @stack('styles')
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="particles" id="particles"></div>
    
    <!-- Language Switcher for Guest Pages -->
    <div style="position: fixed; top: 1rem; right: 1rem; z-index: 1000;">
        @include('components.language-switcher')
    </div>
    
    @yield('content')
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                container.appendChild(particle);
            }
        }
        createParticles();
    </script>
    
    @stack('scripts')
    
    {{-- Monetag Integration --}}
    @include('partials.monetag')
    
    {{-- PWA Install Prompt --}}
    @include('components.pwa-install')
    
    {{-- PWA Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/pwa-sw.js', { scope: '/' })
                    .then((registration) => {
                        console.log('[PWA] Service Worker registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('[PWA] SW registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
