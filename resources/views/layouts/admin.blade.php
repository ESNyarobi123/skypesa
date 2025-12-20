<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - SKYpesa Admin</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Admin Layout Specific Styles */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f0f0f 0%, #1a1a1a 100%);
            border-right: 1px solid rgba(255,255,255,0.05);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
        }
        
        .sidebar-logo span {
            color: var(--primary);
        }
        
        .sidebar-logo .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }
        
        .menu-section {
            padding: 0.5rem 1.5rem;
            margin-top: 1rem;
        }
        
        .menu-section-title {
            font-size: 0.675rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }
        
        .sidebar-nav {
            list-style: none;
        }
        
        .sidebar-nav-item {
            margin: 0.25rem 0.75rem;
        }
        
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .sidebar-nav-link:hover {
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
        }
        
        .sidebar-nav-link.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .sidebar-nav-link .nav-icon {
            width: 20px;
            height: 20px;
        }
        
        .nav-badge {
            margin-left: auto;
            padding: 0.2rem 0.5rem;
            background: var(--error);
            color: white;
            font-size: 0.675rem;
            font-weight: 700;
            border-radius: 100px;
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .admin-info {
            flex: 1;
        }
        
        .admin-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: white;
        }
        
        .admin-role {
            font-size: 0.75rem;
            color: var(--primary);
        }
        
        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            background: var(--bg-darker);
        }
        
        .admin-header {
            height: 70px;
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .page-info h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
        }
        
        .page-info p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-btn {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .header-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .header-btn .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--error);
            border-radius: 50%;
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        /* Stats Grid Animation */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-card-modern {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card-modern:hover {
            transform: translateY(-4px);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 30px rgba(16, 185, 129, 0.1);
        }
        
        .stat-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon.green {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }
        
        .stat-icon.blue {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
        }
        
        .stat-icon.yellow {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }
        
        .stat-icon.red {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
        }
        
        .stat-icon.purple {
            background: rgba(139, 92, 246, 0.15);
            color: #8b5cf6;
        }
        
        .stat-title {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-number {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            font-variant-numeric: tabular-nums;
        }
        
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
        }
        
        .stat-change.positive {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }
        
        .stat-change.negative {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
        }
        
        /* Charts Container */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            padding: 1.5rem;
        }
        
        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .chart-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }
        
        .chart-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .chart-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .chart-btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 6px;
            border: none;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .chart-btn:hover, .chart-btn.active {
            background: var(--primary);
            color: white;
        }
        
        /* Activity Feed */
        .activity-feed {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .activity-text strong {
            color: white;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        /* Data Table */
        .data-table-container {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .table-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }
        
        .table-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .search-input {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 0 1rem;
        }
        
        .search-input input {
            background: transparent;
            border: none;
            padding: 0.75rem;
            color: white;
            font-size: 0.875rem;
            outline: none;
            width: 200px;
        }
        
        .search-input input::placeholder {
            color: var(--text-muted);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .admin-table td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        
        .admin-table tbody tr:hover {
            background: rgba(16, 185, 129, 0.03);
        }
        
        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .user-details {
            line-height: 1.3;
        }
        
        .user-name {
            font-weight: 600;
            color: white;
        }
        
        .user-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge.active {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }
        
        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
        }
        
        .status-badge.pending {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }
        
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .action-btn.danger:hover {
            background: var(--error);
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.08);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.active .modal {
            transform: scale(1);
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: white;
        }
        
        .modal-close {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            background: var(--error);
            color: white;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        /* Form Styles for Admin */
        .form-group-modern {
            margin-bottom: 1.25rem;
        }
        
        .form-label-modern {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .form-input-modern {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: white;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .form-input-modern:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        
        .form-input-modern::placeholder {
            color: var(--text-muted);
        }
        
        .form-select-modern {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2371717a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px;
            padding-right: 2.5rem;
        }
        
        /* Quick Actions Panel */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .quick-action-card {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.05);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .quick-action-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .quick-action-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quick-action-text h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.25rem;
        }
        
        .quick-action-text p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        /* System Health */
        .system-health {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        
        @media (max-width: 1200px) {
            .system-health {
                grid-template-columns: 1fr;
            }
        }
        
        .health-card {
            background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            padding: 1.5rem;
        }
        
        .health-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .health-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .health-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
        }
        
        .health-metric {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        
        .health-metric:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .metric-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: white;
        }
        
        .metric-value.good {
            color: var(--success);
        }
        
        .metric-value.warning {
            color: var(--warning);
        }
        
        .metric-value.critical {
            color: var(--error);
        }
        
        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-in {
            animation: slideIn 0.4s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="logo-icon">
                        <i data-lucide="zap" style="width: 24px; height: 24px; color: white;"></i>
                    </div>
                    <span>SKY</span>pesa
                </div>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Main</div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.analytics') }}" class="sidebar-nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                                <i data-lucide="trending-up" class="nav-icon"></i>
                                Analytics
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Management</div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.users.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i data-lucide="users" class="nav-icon"></i>
                                Users
                                <span class="nav-badge">{{ $usersCount ?? '' }}</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.plans.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                                <i data-lucide="crown" class="nav-icon"></i>
                                Subscription Plans
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.tasks.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                                <i data-lucide="clipboard-list" class="nav-icon"></i>
                                Tasks/Ads
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.directlinks.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.directlinks.*') ? 'active' : '' }}">
                                <i data-lucide="link" class="nav-icon"></i>
                                Direct Links
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Finance</div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.withdrawals.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                                <i data-lucide="banknote" class="nav-icon"></i>
                                Withdrawals
                                @if(isset($pendingWithdrawalsCount) && $pendingWithdrawalsCount > 0)
                                <span class="nav-badge">{{ $pendingWithdrawalsCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.transactions') }}" class="sidebar-nav-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
                                <i data-lucide="receipt" class="nav-icon"></i>
                                Transactions
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Growth</div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.referrals') }}" class="sidebar-nav-link {{ request()->routeIs('admin.referrals') ? 'active' : '' }}">
                                <i data-lucide="share-2" class="nav-icon"></i>
                                Referral Program
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.adsterra.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.adsterra.*') ? 'active' : '' }}">
                                <i data-lucide="link" class="nav-icon"></i>
                                SkyLinks Manager
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.settings') }}" class="sidebar-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                                <i data-lucide="settings" class="nav-icon"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="sidebar-footer">
                <div class="admin-profile">
                    <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }}" class="admin-avatar">
                    <div class="admin-info">
                        <div class="admin-name">{{ auth()->user()->name }}</div>
                        <div class="admin-role">Administrator</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: auto;">
                        @csrf
                        <button type="submit" class="action-btn" title="Logout">
                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="header-btn" id="sidebarToggle" style="display: none;">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="page-info">
                        <h1>@yield('page-title', 'Dashboard')</h1>
                        <p>@yield('page-subtitle', now()->format('l, F j, Y'))</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-btn" title="Refresh">
                        <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i>
                    </button>
                    <button class="header-btn" title="Notifications">
                        <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
                        @if(isset($pendingWithdrawalsCount) && $pendingWithdrawalsCount > 0)
                        <span class="notification-dot"></span>
                        @endif
                    </button>
                    <a href="{{ route('dashboard') }}" class="header-btn" title="View Site">
                        <i data-lucide="external-link" style="width: 18px; height: 18px;"></i>
                    </a>
                </div>
            </header>
            
            <div class="admin-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; color: var(--success); display: flex; align-items: center; gap: 0.75rem;">
                        <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; color: var(--error); display: flex; align-items: center; gap: 0.75rem;">
                        <i data-lucide="x-circle" style="width: 20px; height: 20px;"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; color: var(--error);">
                        <ul style="list-style: none;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
        
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        
        if (window.innerWidth <= 1024) {
            sidebarToggle.style.display = 'flex';
        }
        
        window.addEventListener('resize', () => {
            sidebarToggle.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
            }
        });
        
        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
