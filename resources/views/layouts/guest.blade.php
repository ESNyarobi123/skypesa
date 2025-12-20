<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SKYpesa - Pata Pesa Kwa Kutazama na Kukamilisha Task')</title>
    <meta name="description" content="@yield('description', 'SKYpesa ni jukwaa la kujipatia pesa mtandaoni kwa kukamilisha kazi rahisi. Tazama matangazo, shiriki links, na upate malipo halali.')">
    
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
</body>
</html>
