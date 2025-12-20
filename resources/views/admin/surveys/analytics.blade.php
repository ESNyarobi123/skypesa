@extends('layouts.admin')

@section('title', 'Survey Analytics')
@section('page-title', 'Survey Analytics')
@section('page-subtitle', 'Takwimu za Surveys za siku {{ $days }} zilizopita')

@section('content')
<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex gap-4 items-end">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kipindi</label>
                <select name="days" class="form-control" onchange="this.form.submit()">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Wiki 1</option>
                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>Wiki 2</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Mwezi 1</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>Miezi 3</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-2" style="gap: var(--space-6);">
    <!-- Daily Completions Chart -->
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">📈 Survey Completions kwa Siku</h4>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>
    
    <!-- Type Distribution -->
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">📊 Mgawanyo wa Aina</h4>
            <canvas id="typeChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Top Earners -->
<div class="card mt-6">
    <div class="card-body" style="padding-bottom: 0;">
        <h4>🏆 Top Survey Earners</h4>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Mtumiaji</th>
                <th>Surveys Zilizokamilika</th>
                <th>Jumla ya Mapato</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topEarners as $index => $user)
            <tr>
                <td>
                    @if($index === 0)
                        <span style="font-size: 1.5rem;">🥇</span>
                    @elseif($index === 1)
                        <span style="font-size: 1.5rem;">🥈</span>
                    @elseif($index === 2)
                        <span style="font-size: 1.5rem;">🥉</span>
                    @else
                        <span style="font-weight: 600;">{{ $index + 1 }}</span>
                    @endif
                </td>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->getAvatarUrl() }}" style="width: 36px; height: 36px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 600;">{{ $user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-weight: 600;">{{ number_format($user->survey_count) }}</span> surveys
                </td>
                <td style="font-weight: 700; color: var(--success);">
                    TZS {{ number_format($user->total_earned, 0) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    Hakuna data ya earners
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Actions -->
<div class="flex gap-4 mt-6">
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-primary">
        ← Rudi Surveys
    </a>
    <a href="{{ route('admin.surveys.settings') }}" class="btn btn-secondary">
        ⚙️ Settings
    </a>
</div>
@endsection

@push('scripts')
<script>
    // Daily Completions Chart
    const dailyData = @json($dailyCompletions);
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: 'Completions',
                data: dailyData.map(d => d.count),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Mapato (TZS)',
                data: dailyData.map(d => d.total_reward / 100), // Scale down for visibility
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#9ca3af' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#6b7280' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    ticks: { color: '#6b7280' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });
    
    // Type Distribution Chart
    const typeData = @json($typeDistribution);
    const typeLabels = { short: 'Short (5-7 min)', medium: 'Medium (8-12 min)', long: 'Long (15+ min)' };
    const typeColors = { short: '#3b82f6', medium: '#10b981', long: '#f59e0b' };
    
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: typeData.map(d => typeLabels[d.survey_type] || d.survey_type),
            datasets: [{
                data: typeData.map(d => d.count),
                backgroundColor: typeData.map(d => typeColors[d.survey_type] || '#6b7280'),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#9ca3af' }
                }
            }
        }
    });
</script>
@endpush
