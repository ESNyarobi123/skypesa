@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-subtitle', 'Real-time system overview and analytics')

@section('content')
<!-- Quick Actions -->
<div class="quick-actions">
    <a href="{{ route('admin.users.index') }}" class="quick-action-card animate-in">
        <div class="quick-action-icon stat-icon green">
            <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="quick-action-text">
            <h4>Manage Users</h4>
            <p>Create, edit & delete users</p>
        </div>
    </a>
    <a href="{{ route('admin.plans.index') }}" class="quick-action-card animate-in delay-1">
        <div class="quick-action-icon stat-icon purple">
            <i data-lucide="crown" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="quick-action-text">
            <h4>Subscription Plans</h4>
            <p>Manage plan pricing</p>
        </div>
    </a>
    <a href="{{ route('admin.tasks.create') }}" class="quick-action-card animate-in delay-2">
        <div class="quick-action-icon stat-icon blue">
            <i data-lucide="plus-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="quick-action-text">
            <h4>Add New Task</h4>
            <p>Create ad/task for users</p>
        </div>
    </a>
    <a href="{{ route('admin.withdrawals.index') }}" class="quick-action-card animate-in delay-3">
        <div class="quick-action-icon stat-icon yellow">
            <i data-lucide="credit-card" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="quick-action-text">
            <h4>Pending Withdrawals</h4>
            <p>{{ $pendingWithdrawals }} awaiting approval</p>
        </div>
    </a>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card-modern animate-in">
        <div class="stat-icon green">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Users</div>
        <div class="stat-number">{{ number_format($totalUsers) }}</div>
        <div class="stat-change positive">
            <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
            +{{ $newUsersToday }} today
        </div>
    </div>
    
    <div class="stat-card-modern animate-in delay-1">
        <div class="stat-icon blue">
            <i data-lucide="activity" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Tasks Completed Today</div>
        <div class="stat-number">{{ number_format($completionsToday) }}</div>
        <div class="stat-change positive">
            <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
            {{ number_format($completionsThisMonth) }} this month
        </div>
    </div>
    
    <div class="stat-card-modern animate-in delay-2">
        <div class="stat-icon purple">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Earnings Today</div>
        <div class="stat-number">TZS {{ number_format($earningsToday, 0) }}</div>
        <div class="stat-change positive">
            <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
            TZS {{ number_format($earningsThisMonth, 0) }} this month
        </div>
    </div>
    
    <div class="stat-card-modern animate-in delay-3">
        <div class="stat-icon yellow">
            <i data-lucide="banknote" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Pending Payouts</div>
        <div class="stat-number">{{ $pendingWithdrawals }}</div>
        <div class="stat-change {{ $pendingAmount > 0 ? 'negative' : 'positive' }}">
            TZS {{ number_format($pendingAmount, 0) }} pending
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <!-- Main Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Revenue & Activity Trend</div>
                <div class="chart-subtitle">Task completions and earnings over time</div>
            </div>
            <div class="chart-actions">
                <button class="chart-btn active" data-period="7">7 Days</button>
                <button class="chart-btn" data-period="30">30 Days</button>
                <button class="chart-btn" data-period="90">90 Days</button>
            </div>
        </div>
        <div style="height: 300px; max-height: 300px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Activity Feed -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Live Activity</div>
                <div class="chart-subtitle">Real-time user actions</div>
            </div>
            <div class="chart-actions">
                <button class="chart-btn active" onclick="location.reload()">
                    <i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i>
                </button>
            </div>
        </div>
        <div class="activity-feed" style="max-height: 280px; overflow-y: auto;">
            @forelse($recentCompletions as $completion)
            <div class="activity-item">
                <div class="activity-icon stat-icon green">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">
                        <strong>{{ $completion->user->name }}</strong> completed task "{{ Str::limit($completion->task->title, 20) }}"
                    </div>
                    <div class="activity-time">
                        <i data-lucide="clock" style="width: 12px; height: 12px; display: inline;"></i>
                        {{ $completion->created_at->diffForHumans() }} • TZS {{ number_format($completion->reward_earned, 0) }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>No recent activity</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Data Tables Grid -->
<style>
    .data-tables-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    @@media (max-width: 1200px) {
        .data-tables-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="data-tables-grid">
    <!-- Recent Users Table -->
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">New Users</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                View All
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="user-avatar">
                            <div class="user-details">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-primary" style="font-size: 0.65rem;">{{ $user->getPlanName() }}</span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">
                        {{ $user->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No users yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pending Withdrawals Table -->
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Pending Withdrawals</h3>
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                Manage
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentWithdrawals as $withdrawal)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="{{ $withdrawal->user->getAvatarUrl() }}" alt="{{ $withdrawal->user->name }}" class="user-avatar">
                            <div class="user-details">
                                <div class="user-name">{{ $withdrawal->user->name }}</div>
                                <div class="user-email">{{ $withdrawal->payment_number }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight: 600; color: var(--success);">
                        TZS {{ number_format($withdrawal->net_amount, 0) }}
                    </td>
                    <td>
                        @php
                            $statusClasses = [
                                'pending' => 'pending',
                                'approved' => 'active',
                                'paid' => 'active',
                                'rejected' => 'inactive',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusClasses[$withdrawal->status] ?? 'pending' }}">
                            {{ ucfirst($withdrawal->status) }}
                        </span>
                    </td>
                    <td>
                        @if($withdrawal->status === 'pending')
                        <div class="action-btns">
                            <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn" title="Approve">
                                    <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn danger" title="Reject">
                                    <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="color: var(--text-muted); font-size: 0.75rem;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No pending withdrawals
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- System Health -->
<h3 style="font-size: 1rem; font-weight: 700; color: white; margin-bottom: 1rem;">System Health & Insights</h3>
<div class="system-health">
    <!-- User Metrics -->
    <div class="health-card">
        <div class="health-header">
            <div class="health-icon stat-icon green">
                <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
            <div class="health-title">User Metrics</div>
        </div>
        <div class="health-metric">
            <span class="metric-label">Total Users</span>
            <span class="metric-value">{{ number_format($totalUsers) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Active Users</span>
            <span class="metric-value good">{{ number_format($activeUsers) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">New Today</span>
            <span class="metric-value good">+{{ $newUsersToday }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">This Month</span>
            <span class="metric-value">{{ $newUsersThisMonth }}</span>
        </div>
    </div>
    
    <!-- Task Metrics -->
    <div class="health-card">
        <div class="health-header">
            <div class="health-icon stat-icon blue">
                <i data-lucide="clipboard-check" style="width: 20px; height: 20px;"></i>
            </div>
            <div class="health-title">Task Performance</div>
        </div>
        <div class="health-metric">
            <span class="metric-label">Active Tasks</span>
            <span class="metric-value good">{{ $activeTasks }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Total Tasks</span>
            <span class="metric-value">{{ $totalTasks }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Completions Today</span>
            <span class="metric-value good">{{ number_format($completionsToday) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Completions This Month</span>
            <span class="metric-value">{{ number_format($completionsThisMonth) }}</span>
        </div>
    </div>
    
    <!-- Financial Overview -->
    <div class="health-card">
        <div class="health-header">
            <div class="health-icon stat-icon yellow">
                <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i>
            </div>
            <div class="health-title">Financial Overview</div>
        </div>
        <div class="health-metric">
            <span class="metric-label">Total Earnings Paid</span>
            <span class="metric-value">TZS {{ number_format($totalEarnings, 0) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Paid This Month</span>
            <span class="metric-value good">TZS {{ number_format($paidThisMonth, 0) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Pending Payouts</span>
            <span class="metric-value {{ $pendingAmount > 10000 ? 'warning' : '' }}">TZS {{ number_format($pendingAmount, 0) }}</span>
        </div>
        <div class="health-metric">
            <span class="metric-label">Pending Count</span>
            <span class="metric-value {{ $pendingWithdrawals > 5 ? 'warning' : 'good' }}">{{ $pendingWithdrawals }}</span>
        </div>
    </div>
</div>

<!-- Subscription Distribution Chart -->
<div class="chart-card" style="margin-top: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Subscription Distribution</div>
            <div class="chart-subtitle">User distribution by subscription plan</div>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem; align-items: center; max-height: 250px;">
        <div style="height: 200px; max-height: 200px; position: relative;">
            <canvas id="subscriptionChart"></canvas>
        </div>
        <div id="subscriptionLegend" style="display: grid; gap: 0.5rem; max-height: 200px; overflow-y: auto;"></div>
    </div>
</div>

<!-- Referral Program Stats -->
<div class="chart-card" style="margin-top: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Referral Program Performance</div>
            <div class="chart-subtitle">Track referral growth and conversions</div>
        </div>
        <a href="{{ route('admin.referrals') }}" class="btn btn-sm btn-primary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
            View Details
        </a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
        <div style="text-align: center; padding: 1.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 12px;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--success);">{{ $referredUsersCount ?? 0 }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Referred Users</div>
        </div>
        <div style="text-align: center; padding: 1.5rem; background: rgba(59, 130, 246, 0.1); border-radius: 12px;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--info);">{{ $activeReferrersCount ?? 0 }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Active Referrers</div>
        </div>
        <div style="text-align: center; padding: 1.5rem; background: rgba(139, 92, 246, 0.1); border-radius: 12px;">
            <div style="font-size: 2rem; font-weight: 800; color: #8b5cf6;">{{ $referralConversionRate ?? 0 }}%</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Conversion Rate</div>
        </div>
        <div style="text-align: center; padding: 1.5rem; background: rgba(245, 158, 11, 0.1); border-radius: 12px;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--warning);">TZS {{ number_format($referralBonusesPaid ?? 0, 0) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Bonuses Paid</div>
        </div>
    </div>
</div>

<!-- Profit Analytics Section -->
<div class="chart-card" style="margin-top: 1.5rem; background: linear-gradient(145deg, rgba(16, 185, 129, 0.05) 0%, #151515 100%); border-color: rgba(16, 185, 129, 0.2);">
    <div class="chart-header">
        <div>
            <div class="chart-title" style="color: var(--success);">
                <i data-lucide="trending-up" style="width: 20px; height: 20px; display: inline;"></i>
                Profit Analytics
            </div>
            <div class="chart-subtitle">Platform revenue and profit breakdown</div>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
            <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
            Configure
        </a>
    </div>
    
    <!-- Quick Profit Stats -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="text-align: center; padding: 1.25rem; background: var(--gradient-primary); border-radius: 12px;">
            <div style="font-size: 1.75rem; font-weight: 800; color: white;">TZS {{ number_format($profitData['today_profit'] ?? 0, 0) }}</div>
            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8); text-transform: uppercase;">Today's Profit</div>
        </div>
        <div style="text-align: center; padding: 1.25rem; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 12px;">
            <div style="font-size: 1.75rem; font-weight: 800; color: white;">TZS {{ number_format($profitData['month_profit'] ?? 0, 0) }}</div>
            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8); text-transform: uppercase;">This Month's Profit</div>
        </div>
        <div style="text-align: center; padding: 1.25rem; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px;">
            <div style="font-size: 1.75rem; font-weight: 800; color: white;">TZS {{ number_format($profitData['total_profit'] ?? 0, 0) }}</div>
            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8); text-transform: uppercase;">Total Profit</div>
        </div>
    </div>
    
    <!-- Revenue Breakdown -->
    <h4 style="color: white; font-size: 0.9rem; margin-bottom: 1rem;">
        <i data-lucide="pie-chart" style="width: 16px; height: 16px; display: inline; color: var(--primary);"></i>
        Revenue Breakdown
    </h4>
    <div style="display: grid; gap: 0.75rem;">
        <!-- Ad Revenue -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 10px; border-left: 3px solid var(--success);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="tv" style="width: 18px; height: 18px; color: var(--success);"></i>
                <span style="color: white;">Estimated Ad Revenue</span>
            </div>
            <span style="font-weight: 700; color: var(--success);">+ TZS {{ number_format($profitData['estimated_ad_revenue'] ?? 0, 0) }}</span>
        </div>
        
        <!-- Subscription Revenue -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(59, 130, 246, 0.1); border-radius: 10px; border-left: 3px solid var(--info);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="crown" style="width: 18px; height: 18px; color: var(--info);"></i>
                <span style="color: white;">Subscription Revenue</span>
            </div>
            <span style="font-weight: 700; color: var(--info);">+ TZS {{ number_format($profitData['subscription_revenue'] ?? 0, 0) }}</span>
        </div>
        
        <!-- Withdrawal Fees -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(139, 92, 246, 0.1); border-radius: 10px; border-left: 3px solid #8b5cf6;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="percent" style="width: 18px; height: 18px; color: #8b5cf6;"></i>
                <span style="color: white;">Withdrawal Fees Collected</span>
            </div>
            <span style="font-weight: 700; color: #8b5cf6;">+ TZS {{ number_format($profitData['withdrawal_fees'] ?? 0, 0) }}</span>
        </div>
        
        <!-- User Payouts (expense) -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px; border-left: 3px solid var(--error);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="users" style="width: 18px; height: 18px; color: var(--error);"></i>
                <span style="color: white;">User Payouts (Task Rewards)</span>
            </div>
            <span style="font-weight: 700; color: var(--error);">- TZS {{ number_format($profitData['user_payouts'] ?? 0, 0) }}</span>
        </div>
        
        <!-- Referral Bonuses (expense) -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 10px; border-left: 3px solid var(--warning);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="gift" style="width: 18px; height: 18px; color: var(--warning);"></i>
                <span style="color: white;">Referral Bonuses Paid</span>
            </div>
            <span style="font-weight: 700; color: var(--warning);">- TZS {{ number_format($profitData['referral_bonuses'] ?? 0, 0) }}</span>
        </div>
        
        <!-- Net Profit -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gradient-primary); border-radius: 12px; margin-top: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="wallet" style="width: 20px; height: 20px; color: white;"></i>
                <span style="color: white; font-weight: 700; font-size: 1.1rem;">NET PROFIT</span>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1.5rem; font-weight: 800; color: white;">TZS {{ number_format($profitData['total_profit'] ?? 0, 0) }}</div>
                <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8);">{{ $profitData['profit_margin'] ?? 0 }}% margin</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
            datasets: [
                {
                    label: 'Earnings (TZS)',
                    data: {!! json_encode($chartEarnings ?? [1200, 1900, 3000, 5000, 2000, 3000, 4500]) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Task Completions',
                    data: {!! json_encode($chartCompletions ?? [120, 190, 300, 500, 200, 300, 450]) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#a1a1aa',
                        font: {
                            size: 12,
                            family: 'Inter'
                        },
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleColor: '#fff',
                    bodyColor: '#a1a1aa',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255,255,255,0.03)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#71717a',
                        font: {
                            size: 11,
                            family: 'Inter'
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255,255,255,0.03)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#71717a',
                        font: {
                            size: 11,
                            family: 'Inter'
                        }
                    }
                }
            }
        }
    });
    
    // Subscription Distribution Chart
    const subCtx = document.getElementById('subscriptionChart').getContext('2d');
    const subscriptionData = {!! json_encode($subscriptionDistribution ?? [
        ['name' => 'Free', 'count' => 150, 'color' => '#71717a'],
        ['name' => 'Basic', 'count' => 45, 'color' => '#3b82f6'],
        ['name' => 'Standard', 'count' => 28, 'color' => '#8b5cf6'],
        ['name' => 'Premium', 'count' => 12, 'color' => '#f59e0b'],
        ['name' => 'VIP', 'count' => 5, 'color' => '#10b981'],
    ]) !!};
    
    new Chart(subCtx, {
        type: 'doughnut',
        data: {
            labels: subscriptionData.map(d => d.name),
            datasets: [{
                data: subscriptionData.map(d => d.count),
                backgroundColor: subscriptionData.map(d => d.color),
                borderColor: '#1a1a1a',
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Build custom legend
    const legendContainer = document.getElementById('subscriptionLegend');
    const total = subscriptionData.reduce((sum, d) => sum + d.count, 0);
    subscriptionData.forEach(item => {
        const percent = ((item.count / total) * 100).toFixed(1);
        legendContainer.innerHTML += `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: rgba(255,255,255,0.02); border-radius: 10px;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: ${item.color};"></div>
                    <span style="font-size: 0.875rem; color: white;">${item.name}</span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.875rem; font-weight: 700; color: white;">${item.count}</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">${percent}%</div>
                </div>
            </div>
        `;
    });
    
    // Chart period buttons
    document.querySelectorAll('.chart-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-btn[data-period]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            // Here you would typically fetch new data based on the period
            // For now, just showing the UI interaction
        });
    });
</script>
@endpush
