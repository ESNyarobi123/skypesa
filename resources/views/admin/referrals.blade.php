@extends('layouts.admin')

@section('title', 'Referral Program')
@section('page-title', 'Referral Program')
@section('page-subtitle', 'Track referral performance and growth')

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Referred</div>
        <div class="stat-number">{{ number_format($referralStats['total_referrals']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="share-2" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Referral Rate</div>
        <div class="stat-number">{{ $referralStats['referral_rate'] }}%</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="users-2" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Avg. Referrals/User</div>
        <div class="stat-number">{{ number_format($referralStats['avg_referrals_per_user'], 1) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="coins" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Bonuses Paid</div>
        <div class="stat-number">TZS {{ number_format($referralStats['referral_bonuses'], 0) }}</div>
    </div>
</div>

<!-- Monthly Trend Chart -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Monthly Referral Trend</div>
            <div class="chart-subtitle">New referrals over the last 6 months</div>
        </div>
    </div>
    <div style="height: 300px; max-height: 300px; position: relative;">
        <canvas id="referralChart"></canvas>
    </div>
</div>

<!-- Top Referrers Table -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Top Referrers</h3>
        <div class="table-actions">
            <span style="color: var(--text-muted); font-size: 0.875rem;">
                Users ranked by successful referrals
            </span>
        </div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>User</th>
                <th>Referral Code</th>
                <th>Referrals</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topReferrers as $index => $referrer)
            <tr>
                <td>
                    @if($index < 3)
                    <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; 
                        {{ $index === 0 ? 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;' : '' }}
                        {{ $index === 1 ? 'background: linear-gradient(135deg, #9ca3af, #6b7280); color: white;' : '' }}
                        {{ $index === 2 ? 'background: linear-gradient(135deg, #d97706, #b45309); color: white;' : '' }}
                    ">
                        {{ $index + 1 }}
                    </div>
                    @else
                    <span style="color: var(--text-muted); font-weight: 600;">#{{ $index + 1 }}</span>
                    @endif
                </td>
                <td>
                    <div class="user-cell">
                        <img src="{{ $referrer->getAvatarUrl() }}" class="user-avatar">
                        <div class="user-details">
                            <div class="user-name">{{ $referrer->name }}</div>
                            <div class="user-email">{{ $referrer->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <code style="background: rgba(16, 185, 129, 0.15); padding: 0.25rem 0.5rem; border-radius: 4px; color: var(--primary); font-weight: 600;">
                        {{ $referrer->referral_code }}
                    </code>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">{{ $referrer->referrals_count }}</span>
                        <span style="color: var(--text-muted); font-size: 0.75rem;">users</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge {{ $referrer->is_active ? 'active' : 'inactive' }}">
                        {{ $referrer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.users.show', $referrer) }}" class="action-btn" title="View Profile">
                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i data-lucide="share-2" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>No referrers yet</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- How Referral Program Works -->
<div class="chart-card" style="margin-top: 1.5rem;">
    <div class="chart-header">
        <div class="chart-title">Referral Program Structure</div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
        <div style="text-align: center; padding: 1.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 12px;">
            <div style="width: 60px; height: 60px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="share-2" style="width: 28px; height: 28px; color: white;"></i>
            </div>
            <h4 style="color: white; margin-bottom: 0.5rem;">1. Share Code</h4>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Users share their unique referral code with friends</p>
        </div>
        <div style="text-align: center; padding: 1.5rem; background: rgba(59, 130, 246, 0.1); border-radius: 12px;">
            <div style="width: 60px; height: 60px; background: var(--info); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="user-plus" style="width: 28px; height: 28px; color: white;"></i>
            </div>
            <h4 style="color: white; margin-bottom: 0.5rem;">2. Friend Joins</h4>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Friend registers using the referral code</p>
        </div>
        <div style="text-align: center; padding: 1.5rem; background: rgba(245, 158, 11, 0.1); border-radius: 12px;">
            <div style="width: 60px; height: 60px; background: var(--warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="gift" style="width: 28px; height: 28px; color: white;"></i>
            </div>
            <h4 style="color: white; margin-bottom: 0.5rem;">3. Both Earn</h4>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Both referrer and new user receive bonus rewards</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Monthly Referral Chart
    const ctx = document.getElementById('referralChart').getContext('2d');
    const monthlyData = {!! json_encode($monthlyReferrals) !!};
    
    const labels = monthlyData.map(d => {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months[d.month - 1] + ' ' + d.year;
    });
    
    const values = monthlyData.map(d => d.count);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Referrals',
                data: values,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255,255,255,0.03)'
                    },
                    ticks: {
                        color: '#71717a'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255,255,255,0.03)'
                    },
                    ticks: {
                        color: '#71717a'
                    }
                }
            }
        }
    });
</script>
@endpush
