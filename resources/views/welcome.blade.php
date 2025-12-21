<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SKYpesa - Pata Pesa Kwa Kutazama Matangazo. Jukwaa halali la Tanzania la kujipatia pesa mtandaoni.">
    <meta name="keywords" content="pesa, earn money, tanzania, matangazo, ads, online earning">
    <meta name="author" content="SKYpesa">
    <meta property="og:title" content="SKYpesa - Pata Pesa Kwa Kutazama Matangazo">
    <meta property="og:description" content="Jiunge na maelfu ya Watanzania wanaopata pesa kila siku kwa kukamilisha kazi rahisi.">
    <meta property="og:type" content="website">
    <title>SKYpesa - Kutazama Matangazo | Tanzania</title>
    
    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SKYpesa">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: #34d399;
            --secondary: #6366f1;
            --bg-dark: #0f172a;
            --bg-darker: #020617;
            --bg-card: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --gradient-primary: linear-gradient(135deg, #10b981, #059669);
            --gradient-glow: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-darker);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--text-primary);
        }

        .logo span {
            color: var(--primary);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary);
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            background: rgba(16, 185, 129, 0.1);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 8rem 0 4rem;
            background: var(--gradient-hero);
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-content h1 span {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .hero-image {
            position: relative;
        }

        .hero-phone {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        .phone-mockup {
            width: 100%;
            height: auto;
            background: var(--bg-card);
            border-radius: 40px;
            padding: 10px;
            box-shadow: 
                0 50px 100px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .phone-screen {
            background: var(--bg-darker);
            border-radius: 32px;
            padding: 2rem;
            min-height: 500px;
        }

        .phone-balance {
            text-align: center;
            padding: 2rem;
            background: var(--gradient-glow);
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }

        .phone-balance-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .phone-balance-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .phone-task {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .phone-task-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .phone-task-info h4 {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .phone-task-info p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .phone-task-reward {
            margin-left: auto;
            font-weight: 700;
            color: var(--primary);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Features */
        .section {
            padding: 6rem 0;
        }

        .section-dark {
            background: var(--bg-dark);
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: var(--text-secondary);
            font-size: 1.125rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 100px;
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .step-card {
            text-align: center;
            padding: 2rem;
            background: var(--bg-card);
            border-radius: 24px;
            position: relative;
            transition: transform 0.3s;
        }

        .step-card:hover {
            transform: translateY(-10px);
        }

        .step-number {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-glow);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem auto 1.5rem;
            color: var(--primary);
        }

        .step-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .step-card p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            padding: 2rem;
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-glow);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Pricing */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
        }

        .plan-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
            position: relative;
        }

        .plan-card.featured {
            border-color: var(--primary);
            transform: scale(1.05);
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.2);
        }

        .plan-card.featured::before {
            content: 'POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gradient-primary);
            padding: 0.25rem 1rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .plan-card:hover {
            border-color: var(--primary);
        }

        .plan-tier {
            font-size: 0.75rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0.5rem 0;
        }

        .plan-price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .plan-price span {
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--text-muted);
        }

        .plan-features {
            list-style: none;
            margin: 1.5rem 0;
        }

        .plan-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .plan-features li i {
            color: var(--primary);
            width: 18px;
            height: 18px;
        }

        /* Testimonials */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 2rem;
        }

        .testimonial-stars {
            color: #f59e0b;
            margin-bottom: 1rem;
        }

        .testimonial-text {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .author-info h4 {
            font-size: 1rem;
        }

        .author-info p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: var(--gradient-primary);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .cta-content {
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.125rem;
            opacity: 0.9;
            max-width: 500px;
            margin: 0 auto 2rem;
        }

        .cta-content .btn {
            background: white;
            color: var(--primary);
        }

        .cta-content .btn:hover {
            background: var(--text-primary);
        }

        /* Footer */
        .footer {
            background: var(--bg-darker);
            padding: 4rem 0 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand p {
            color: var(--text-secondary);
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        .footer-links h4 {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .footer-bottom p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: var(--bg-card);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--primary);
            color: white;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .hero-phone {
                max-width: 300px;
            }

            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .plan-card.featured {
                transform: scale(1);
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .steps-grid,
            .features-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="navbar-inner">
                <a href="/" class="logo">
                    <div class="logo-icon">
                        <i data-lucide="coins" style="width: 24px; height: 24px; color: white;"></i>
                    </div>
                    SKY<span>pesa</span>
                </a>

                <ul class="nav-links">
                    <li><a href="#how-it-works">Jinsi Inavyofanya Kazi</a></li>
                    <li><a href="#features">Faida</a></li>
                    <li><a href="#pricing">Mipango</a></li>
                    <li><a href="#testimonials">Maoni</a></li>
                </ul>

                <div class="nav-buttons">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Ingia</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Jiunge Sasa</a>
                </div>

                <button class="mobile-menu-btn">
                    <i data-lucide="menu" style="width: 28px; height: 28px;"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content fade-in">
                    <div class="badge">
                        <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                        Jukwaa Halali la Tanzania
                    </div>

                    <h1>Pata Pesa Kwa<br><span>Tazama Maudhui, Jipatie Rewards</span></h1>

                    <p>
                        Jiunge na maelfu ya Watanzania wanaopata pesa kila siku kwa kukamilisha 
                        kazi rahisi. Hakuna udanganyifu, hakuna minyororo - malipo halali tu!
                    </p>

                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            <i data-lucide="rocket" style="width: 20px; height: 20px;"></i>
                            Anza Sasa - Bure!
                        </a>
                        <a href="#how-it-works" class="btn btn-outline btn-lg">
                            <i data-lucide="play-circle" style="width: 20px; height: 20px;"></i>
                            Jinsi Inavyofanya Kazi
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-value">10K+</div>
                            <div class="stat-label">Watumiaji</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">TZS 50M+</div>
                            <div class="stat-label">Imelipwa</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Msaada</div>
                        </div>
                    </div>
                </div>

                <div class="hero-image fade-in" style="animation-delay: 0.3s;">
                    <div class="hero-phone">
                        <div class="phone-mockup">
                            <div class="phone-screen">
                                <div class="phone-balance">
                                    <div class="phone-balance-label">Salio Lako</div>
                                    <div class="phone-balance-value">TZS 15,420</div>
                                </div>

                                <div class="phone-task">
                                    <div class="phone-task-icon">
                                        <i data-lucide="play" style="width: 24px; height: 24px;"></i>
                                    </div>
                                    <div class="phone-task-info">
                                        <h4>Tazama Video</h4>
                                        <p>30 sekunde</p>
                                    </div>
                                    <div class="phone-task-reward">+TZS 5</div>
                                </div>

                                <div class="phone-task">
                                    <div class="phone-task-icon">
                                        <i data-lucide="eye" style="width: 24px; height: 24px;"></i>
                                    </div>
                                    <div class="phone-task-info">
                                        <h4>Angalia Tangazo</h4>
                                        <p>45 sekunde</p>
                                    </div>
                                    <div class="phone-task-reward">+TZS 7</div>
                                </div>

                                <div class="phone-task">
                                    <div class="phone-task-icon">
                                        <i data-lucide="gift" style="width: 24px; height: 24px;"></i>
                                    </div>
                                    <div class="phone-task-info">
                                        <h4>Ofa Maalum</h4>
                                        <p>60 sekunde</p>
                                    </div>
                                    <div class="phone-task-reward">+TZS 10</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="section section-dark">
        <div class="container">
            <div class="section-title">
                <div class="badge">
                    <i data-lucide="zap" style="width: 16px; height: 16px;"></i>
                    Rahisi Sana
                </div>
                <h2>Jinsi Inavyofanya Kazi</h2>
                <p>Hatua 4 rahisi za kuanza kupata pesa leo</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i data-lucide="user-plus" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h3>Jiunge Bure</h3>
                    <p>Fungua akaunti kwa dakika 1 tu. Hakuna malipo ya kwanza.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i data-lucide="play-circle" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h3>Kamilisha Kazi</h3>
                    <p>Tazama matangazo mafupi na ukamilishe tasks rahisi.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i data-lucide="wallet" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h3>Kusanya Pesa</h3>
                    <p>Pesa inaingia wallet yako moja kwa moja kwa sekunde.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i data-lucide="banknote" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h3>Toa Pesa</h3>
                    <p>Toa pesa kupitia M-Pesa, Tigo Pesa, au Airtel Money.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="section">
        <div class="container">
            <div class="section-title">
                <div class="badge">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
                    Kwa Nini Sisi
                </div>
                <h2>Faida za SKYpesa</h2>
                <p>Tunalichotofautisha na wengine</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>Salama & Halali</h3>
                    <p>Tunafanya kazi na advertising networks halali. Hakuna minyororo wala udanganyifu.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="zap" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>Malipo ya Haraka</h3>
                    <p>Omba kutoa pesa na upate ndani ya masaa 24-48 kulingana na mpango wako.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="smartphone" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>Simu Yoyote</h3>
                    <p>Inafanya kazi kwenye simu yoyote yenye internet. Huhitaji smartphone ghali.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="clock" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>24/7 Availability</h3>
                    <p>Asubuhi, mchana, au usiku - kazi zinapatikana masaa 24/7.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="users" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>Referral Bonus</h3>
                    <p>Alika marafiki na upate bonus kwa kila mtu anayejiunga kupitia wewe.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="headphones" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3>Msaada 24/7</h3>
                    <p>Timu yetu iko tayari kukusaidia wakati wowote unapohitaji.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="section section-dark">
        <div class="container">
            <div class="section-title">
                <div class="badge">
                    <i data-lucide="crown" style="width: 16px; height: 16px;"></i>
                    Mipango
                </div>
                <h2>Chagua Mpango Wako</h2>
                <p>Anza bure au upgrade kwa faida zaidi</p>
            </div>

            <div class="pricing-grid">
                <!-- Free -->
                <div class="plan-card">
                    <div class="plan-tier">Bure</div>
                    <div class="plan-name">Free</div>
                    <div class="plan-price">TZS 0</div>
                    <ul class="plan-features">
                        <li><i data-lucide="check"></i> Tasks 20/siku</li>
                        <li><i data-lucide="coins"></i> TZS 3/task</li>
                        <li><i data-lucide="banknote"></i> Min: TZS 5,000</li>
                        <li><i data-lucide="percent"></i> Fee: 20%</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">Jiunge Bure</a>
                </div>

                <!-- Starter -->
                <div class="plan-card">
                    <div class="plan-tier" style="color: #3b82f6;">Starter</div>
                    <div class="plan-name">Starter</div>
                    <div class="plan-price">TZS 2,000<span>/mwezi</span></div>
                    <ul class="plan-features">
                        <li><i data-lucide="check"></i> Tasks 40/siku</li>
                        <li><i data-lucide="coins"></i> TZS 4/task</li>
                        <li><i data-lucide="banknote"></i> Min: TZS 3,000</li>
                        <li><i data-lucide="percent"></i> Fee: 15%</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">Chagua</a>
                </div>

                <!-- Silver - Featured -->
                <div class="plan-card featured">
                    <div class="plan-tier">Bora</div>
                    <div class="plan-name">Silver</div>
                    <div class="plan-price">TZS 5,000<span>/mwezi</span></div>
                    <ul class="plan-features">
                        <li><i data-lucide="check"></i> Tasks 60/siku</li>
                        <li><i data-lucide="coins"></i> TZS 5/task</li>
                        <li><i data-lucide="banknote"></i> Min: TZS 2,000</li>
                        <li><i data-lucide="percent"></i> Fee: 10%</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="width: 100%;">Chagua</a>
                </div>

                <!-- Gold -->
                <div class="plan-card">
                    <div class="plan-tier" style="color: #f59e0b;">Dhahabu</div>
                    <div class="plan-name">Gold</div>
                    <div class="plan-price">TZS 10,000<span>/mwezi</span></div>
                    <ul class="plan-features">
                        <li><i data-lucide="check"></i> Tasks 100/siku</li>
                        <li><i data-lucide="coins"></i> TZS 7/task</li>
                        <li><i data-lucide="banknote"></i> Min: TZS 1,500</li>
                        <li><i data-lucide="percent"></i> Fee: 7%</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">Chagua</a>
                </div>

                <!-- VIP -->
                <div class="plan-card">
                    <div class="plan-tier" style="color: var(--primary);">VIP</div>
                    <div class="plan-name">VIP</div>
                    <div class="plan-price">TZS 25,000<span>/mwezi</span></div>
                    <ul class="plan-features">
                        <li><i data-lucide="infinity"></i> UNLIMITED</li>
                        <li><i data-lucide="coins"></i> TZS 10/task</li>
                        <li><i data-lucide="banknote"></i> Min: TZS 1,000</li>
                        <li><i data-lucide="zap"></i> Fee: 5%</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-secondary" style="width: 100%;">Chagua</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="section">
        <div class="container">
            <div class="section-title">
                <div class="badge">
                    <i data-lucide="message-circle" style="width: 16px; height: 16px;"></i>
                    Maoni
                </div>
                <h2>Watumiaji Wanasema Nini</h2>
                <p>Maoni kutoka kwa watumiaji wetu</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        ★★★★★
                    </div>
                    <p class="testimonial-text">
                        "Nimekuwa nikitumia SKYpesa kwa miezi 3 sasa. Nimepata zaidi ya TZS 150,000. Inafanya kazi kweli!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JM</div>
                        <div class="author-info">
                            <h4>Juma Mohamed</h4>
                            <p>Dar es Salaam</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        ★★★★★
                    </div>
                    <p class="testimonial-text">
                        "Malipo yanakuja haraka sana. Nimealika marafiki 20 na napata bonus kila wakati."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AM</div>
                        <div class="author-info">
                            <h4>Anna Mwakasege</h4>
                            <p>Arusha</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        ★★★★★
                    </div>
                    <p class="testimonial-text">
                        "Kazi rahisi sana! Ninafanya wakati wa mapumziko na ninapata pesa ya ziada kwa mwezi."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">SE</div>
                        <div class="author-info">
                            <h4>Said Emmanuel</h4>
                            <p>Mwanza</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Tayari Kuanza Kupata Pesa?</h2>
                <p>
                    Jiunge sasa na uanze kupata pesa leo! Ni bure kuanza na unaweza upgrade wakati wowote.
                </p>
                <a href="{{ route('register') }}" class="btn btn-lg">
                    <i data-lucide="rocket" style="width: 20px; height: 20px;"></i>
                    Jiunge Sasa - Bure!
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <div class="logo-icon">
                            <i data-lucide="coins" style="width: 24px; height: 24px; color: white;"></i>
                        </div>
                        SKY<span>pesa</span>
                    </a>
                    <p>
                        Jukwaa halali la Tanzania la kujipatia pesa mtandaoni kwa kutazama matangazo na kukamilisha kazi rahisi.
                    </p>
                </div>

                <div class="footer-links">
                    <h4>Haraka</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Ingia</a></li>
                        <li><a href="{{ route('register') }}">Jiunge</a></li>
                        <li><a href="#how-it-works">Jinsi Inavyofanya Kazi</a></li>
                        <li><a href="#pricing">Mipango</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Msaada</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Wasiliana Nasi</a></li>
                        <li><a href="#">Masharti</a></li>
                        <li><a href="#">Faragha</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Wasiliana</h4>
                    <ul>
                        <li><a href="mailto:support@skypesa.com">support@skypesa.com</a></li>
                        <li><a href="tel:+255700000000">+255 700 000 000</a></li>
                        <li><a href="#">WhatsApp</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.</p>
                <div class="social-links">
                    <a href="#"><i data-lucide="facebook" style="width: 20px; height: 20px;"></i></a>
                    <a href="#"><i data-lucide="instagram" style="width: 20px; height: 20px;"></i></a>
                    <a href="#"><i data-lucide="twitter" style="width: 20px; height: 20px;"></i></a>
                    <a href="#"><i data-lucide="youtube" style="width: 20px; height: 20px;"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.step-card, .feature-card, .plan-card, .testimonial-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/pwa-sw.js', { scope: '/' })
                .then((registration) => {
                    console.log('[PWA] Service Worker registered:', registration.scope);
                })
                .catch((error) => {
                    console.log('[PWA] SW registration failed:', error);
                });
        }
    </script>
    
    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" style="display: none; position: fixed; bottom: 20px; left: 20px; right: 20px; padding: 1rem 1.5rem; background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 16px; z-index: 9999; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="flex-shrink: 0; width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="download" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <div style="font-weight: 700; font-size: 1rem; color: white;">Install SKYpesa App</div>
                <div style="font-size: 0.8rem; color: #94a3b8;">Pakua app kwenye simu yako!</div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button id="pwa-install-action" onclick="installPWA()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">Install</button>
                <button onclick="dismissPWA()" style="padding: 0.75rem; background: rgba(255,255,255,0.1); color: #94a3b8; border: none; border-radius: 50px; cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // PWA Installation for landing page
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-install-banner');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show banner after delay
            setTimeout(() => {
                if (!localStorage.getItem('pwa-dismissed')) {
                    banner.style.display = 'block';
                    lucide.createIcons();
                }
            }, 5000);
        });
        
        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        banner.style.display = 'none';
                    }
                    deferredPrompt = null;
                });
            }
        }
        
        function dismissPWA() {
            banner.style.display = 'none';
            localStorage.setItem('pwa-dismissed', Date.now());
        }
        
        // Check if iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
        
        if (isIOS && !isStandalone && !localStorage.getItem('pwa-dismissed')) {
            setTimeout(() => {
                banner.style.display = 'block';
                document.getElementById('pwa-install-action').textContent = 'View Instructions';
                document.getElementById('pwa-install-action').onclick = function() {
                    alert('Ili kupakua SKYpesa:\n\n1. Bonyeza Share icon hapo chini\n2. Tafuta "Add to Home Screen"\n3. Bonyeza "Add"');
                };
                lucide.createIcons();
            }, 5000);
        }
    </script>
</body>
</html>
