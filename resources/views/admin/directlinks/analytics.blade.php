@extends('layouts.admin')

@section('title', 'Analytics: {{ Str::limit($directlink->title, 20) }}')
@section('page-title', 'Task Analytics')
@section('page-subtitle', $directlink->title)

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Completions</div>
        <div class="stat-number">{{ number_format($stats['total_completions']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Today</div>
        <div class="stat-number">{{ number_format($stats['completions_today']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="calendar-days" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">This Week</div>
        <div class="stat-number">{{ number_format($stats['completions_this_week']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="coins" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Rewards Paid</div>
        <div class="stat-number">TZS {{ number_format($stats['total_rewards_paid'], 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon red">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Unique Users</div>
        <div class="stat-number">{{ number_format($stats['unique_users']) }}</div>
    </div>
</div>

<!-- Task Info -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(59, 130, 246, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="{{ $directlink->icon ?? 'link' }}" style="width: 28px; height: 28px; color: var(--info);"></i>
            </div>
            <div>
                <h3 style="color: white; font-weight: 700; margin-bottom: 0.25rem;">{{ $directlink->title }}</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: var(--info);">{{ ucfirst(str_replace('_', ' ', $directlink->type)) }}</span>
                    @if($directlink->provider)
                    <span class="badge badge-primary">{{ $directlink->provider }}</span>
                    @endif
                    <span class="status-badge {{ $directlink->is_active ? 'active' : 'inactive' }}">
                        {{ $directlink->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.directlinks.edit', $directlink) }}" class="btn btn-secondary">
                <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                Edit
            </a>
            <a href="{{ route('admin.directlinks.index') }}" class="btn btn-secondary">
                Back to List
            </a>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Completions - Last 7 Days</div>
            <div class="chart-subtitle">Daily completion trend</div>
        </div>
    </div>
    <canvas id="completionsChart" height="100"></canvas>
</div>

<!-- Recent Completions -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Recent Completions</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Reward Earned</th>
                <th>Duration</th>
                <th>Completed At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($directlink->completions as $completion)
            <tr>
                <td>
                    <div class="user-cell">
                        <img src="{{ $completion->user->getAvatarUrl() }}" class="user-avatar">
                        <div class="user-details">
                            <div class="user-name">{{ $completion->user->name }}</div>
                            <div class="user-email">{{ $completion->user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="color: var(--success); font-weight: 600;">
                    TZS {{ number_format($completion->reward_earned, 0) }}
                </td>
                <td style="color: var(--text-secondary);">
                    {{ $completion->time_spent ?? $directlink->duration_seconds }}s
                </td>
                <td style="color: var(--text-muted);">
                    {{ $completion->created_at->format('M d, Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    No completions yet
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Completions Chart
    const ctx = document.getElementById('completionsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($dailyCompletions)) !!},
            datasets: [{
                label: 'Completions',
                data: {!! json_encode(array_values($dailyCompletions)) !!},
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 8,
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
