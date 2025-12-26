<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SKYpesa - Pata pesa kwa kufanya kazi rahisi kwenye simu yako.">
    <title>@yield('title', 'SKYpesa') - SKYpesa</title>
     
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
        .public-header {
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .public-header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .public-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
        }
        .public-logo span {
            color: var(--primary);
        }
        .public-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .public-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: calc(100vh - 200px);
        }
        .public-footer {
            padding: 2rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            text-align: center;
        }
        .public-footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem 2rem;
            margin-bottom: 1rem;
        }
        .public-footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        .public-footer-links a:hover {
            color: var(--primary);
        }
        @media (max-width: 640px) {
            .hide-mobile {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="public-header">
        <div class="public-header-inner">
            <a href="{{ route('home') }}" class="public-logo">
                <i data-lucide="coins" style="color: var(--primary);"></i>
                SKY<span>pesa</span>
            </a>
            <nav class="public-nav">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                        <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i>
                        <span class="hide-mobile">Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                        <i data-lucide="log-in" style="width: 16px; height: 16px;"></i>
                        <span class="hide-mobile">Ingia</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                        <span class="hide-mobile">Jisajili</span>
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="public-main">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <i data-lucide="check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error mb-4">
                <i data-lucide="x-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="public-footer-links">
            <a href="{{ route('pages.faq') }}">
                <i data-lucide="help-circle" style="width: 14px; height: 14px; display: inline;"></i> FAQ
            </a>
            <a href="{{ route('pages.contact') }}">
                <i data-lucide="mail" style="width: 14px; height: 14px; display: inline;"></i> Wasiliana Nasi
            </a>
            <a href="{{ route('pages.terms') }}">
                <i data-lucide="file-text" style="width: 14px; height: 14px; display: inline;"></i> Masharti
            </a>
            <a href="{{ route('pages.privacy') }}">
                <i data-lucide="shield" style="width: 14px; height: 14px; display: inline;"></i> Faragha
            </a>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
            © {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.
        </p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    
    @stack('scripts')

    {{-- WhatsApp Floating Button --}}
    @php
        $whatsappNumber = \App\Models\Setting::get('whatsapp_support_number', '255700000000');
        $whatsappMessage = urlencode('Habari SKYpesa, nahitaji msaada.');
    @endphp
    <a href="https://wa.me/{{ str_replace(['+', ' '], '', $whatsappNumber) }}?text={{ $whatsappMessage }}" 
       target="_blank" 
       class="whatsapp-float"
       style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; background: #25D366; color: white; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4); transition: transform 0.3s ease;">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>
    <style>
        .whatsapp-float:hover {
            transform: scale(1.1);
        }
    </style>

</body>
</html>
