@extends('layouts.admin')

@section('title', 'Analytics')
@section('page-title', 'Analytics & Insights')
@section('page-subtitle', 'Detailed platform analytics')

@section('content')
<!-- Period Filter -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
    <div class="chart-actions" style="background: rgba(255,255,255,0.03); padding: 0.25rem; border-radius: 10px;">
        <a href="{{ route('admin.analytics', ['period' => 7]) }}" class="chart-btn {{ $period == 7 ? 'active' : '' }}">7 Days</a>
        <a href="{{ route('admin.analytics', ['period' => 30]) }}" class="chart-btn {{ $period == 30 ? 'active' : '' }}">30 Days</a>
        <a href="{{ route('admin.analytics', ['period' => 90]) }}" class="chart-btn {{ $period == 90 ? 'active' : '' }}">90 Days</a>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-grid" style="margin-bottom: 1.5rem;">
    <!-- User Growth Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">User Growth</div>
                <div class="chart-subtitle">New registrations over time</div>
            </div>
        </div>
        <div style="height: 250px; max-height: 250px; position: relative;">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>
    
    <!-- Revenue Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Daily Earnings</div>
                <div class="chart-subtitle">Rewards paid to users</div>
            </div>
        </div>
        <div style="height: 250px; max-height: 250px; position: relative;">
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</div>

<!-- Peak Hours -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Peak Activity Hours</div>
            <div class="chart-subtitle">Task completions by hour of day</div>
        </div>
    </div>
    <div style="height: 200px; max-height: 200px; position: relative;">
        <canvas id="peakHoursChart"></canvas>
    </div>
</div>

<!-- Tables Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Top Tasks -->
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Top Performing Tasks</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Completions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topTasks as $task)
                <tr>
                    <td>
                        <div style="max-width: 200px;">
                            <div style="color: white; font-weight: 500;">{{ Str::limit($task->title, 30) }}</div>
                            <span class="badge badge-primary" style="font-size: 0.6rem; margin-top: 0.25rem;">
                                {{ ucfirst(str_replace('_', ' ', $task->type)) }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                            {{ number_format($task->completions_count) }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No data available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Top Earners -->
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Top Earners</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Earned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topEarners as $earner)
                @if($earner->total_earned > 0)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="{{ $earner->getAvatarUrl() }}" class="user-avatar">
                            <div class="user-details">
                                <div class="user-name">{{ $earner->name }}</div>
                                <div class="user-email">{{ $earner->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--success);">
                            TZS {{ number_format($earner->total_earned, 0) }}
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No data available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthData = {!! json_encode($userGrowth) !!};
    
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: Object.keys(userGrowthData),
            datasets: [{
                label: 'New Users',
                data: Object.values(userGrowthData),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a', maxTicksLimit: 10 }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a' }
                }
            }
        }
    });
    
    // Earnings Chart
    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    const completionData = {!! json_encode($taskCompletionTrend) !!};
    
    new Chart(earningsCtx, {
        type: 'bar',
        data: {
            labels: completionData.map(d => d.date),
            datasets: [{
                label: 'Earnings (TZS)',
                data: completionData.map(d => d.earnings),
                backgroundColor: 'rgba(245, 158, 11, 0.5)',
                borderColor: '#f59e0b',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a', maxTicksLimit: 10 }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a' }
                }
            }
        }
    });
    
    // Peak Hours Chart
    const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
    const peakHoursData = {!! json_encode($peakHours) !!};
    
    // Fill in missing hours
    const hours = [];
    const counts = [];
    for (let i = 0; i < 24; i++) {
        hours.push(i + ':00');
        counts.push(peakHoursData[i] || 0);
    }
    
    new Chart(peakHoursCtx, {
        type: 'bar',
        data: {
            labels: hours,
            datasets: [{
                label: 'Completions',
                data: counts,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: '#3b82f6',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#71717a' }
                }
            }
        }
    });
</script>
@endpush
