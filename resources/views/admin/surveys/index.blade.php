@extends('layouts.admin')

@section('title', 'Surveys - BitLabs')
@section('page-title', 'Surveys')
@section('page-subtitle', 'Usimamizi wa BitLabs Surveys')

@section('content')
<!-- Configuration Alert -->
@if(!$config['is_configured'])
<div class="alert alert-warning mb-6" style="padding: var(--space-4); border-radius: var(--radius-lg); background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning);">
    <div class="flex items-center gap-3">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong>BitLabs Haijasanidiwa!</strong><br>
            <span style="font-size: 0.875rem;">Tafadhali weka <code>BITLABS_API_TOKEN</code> na <code>BITLABS_SECRET_KEY</code> kwenye <code>.env</code> file</span>
        </div>
    </div>
</div>
@endif

@if($config['demo_mode'])
<div class="alert alert-info mb-6" style="padding: var(--space-4); border-radius: var(--radius-lg); background: rgba(59, 130, 246, 0.1); border: 1px solid var(--info);">
    <div class="flex items-center gap-3">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="10" cy="10" r="8"></circle>
            <path d="M10 6v4M10 14h.01"></path>
        </svg>
        <span><strong>Demo Mode Active</strong> - Surveys za mfano zinaonyeshwa kwa watumiaji</span>
    </div>
</div>
@endif

<!-- Quick Stats -->
<div class="grid grid-4 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_completions']) }}</div>
        <div class="stat-label">Jumla Surveys</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['today_completions']) }}</div>
        <div class="stat-label">Surveys Leo</div>
    </div>
    <div class="stat-card" style="border-color: var(--success);">
        <div class="stat-value" style="color: var(--success);">TZS {{ number_format($stats['today_credited'], 0) }}</div>
        <div class="stat-label">Malipo Leo</div>
    </div>
    <div class="stat-card" style="border-color: var(--warning);">
        <div class="stat-value" style="color: var(--warning);">{{ $stats['pending_count'] }}</div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<!-- Survey Types Breakdown -->
<div class="grid grid-3 mb-8">
    <div class="card card-body" style="border-left: 4px solid var(--info);">
        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-2);">
            📊 Short Surveys (5-7 min)
        </div>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($stats['by_type']['short']) }}</div>
        <div style="font-size: 0.75rem; color: var(--success);">TZS 200 kwa kila moja</div>
    </div>
    <div class="card card-body" style="border-left: 4px solid var(--primary);">
        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-2);">
            📋 Medium Surveys (8-12 min)
        </div>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($stats['by_type']['medium']) }}</div>
        <div style="font-size: 0.75rem; color: var(--success);">TZS 300 kwa kila moja</div>
    </div>
    <div class="card card-body" style="border-left: 4px solid var(--warning);">
        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-2);">
            📝 Long Surveys (15+ min)
        </div>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($stats['by_type']['long']) }}</div>
        <div style="font-size: 0.75rem; color: var(--warning);">TZS 500 - VIP Only</div>
    </div>
</div>

<!-- Postback URL Info -->
<div class="card mb-6">
    <div class="card-body">
        <h4 class="mb-4">🔗 BitLabs Callback URL</h4>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-3);">
            Weka URL hii kwenye BitLabs dashboard yako kama "Reward Callback URL":
        </p>
        <div style="background: var(--bg-tertiary); padding: var(--space-3); border-radius: var(--radius-md); font-family: monospace; font-size: 0.875rem; word-break: break-all;">
            {{ $config['callback_url'] }}?tx=[TX]&user_id=[USER_ID]&value=[REWARD_RAW]&status=[STATUS]&loi=[SURVEY_LOI]&hash=[HASH]
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex gap-4 items-end flex-wrap">
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">Zote</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="credited" {{ request('status') == 'credited' ? 'selected' : '' }}>Credited</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label">Aina</label>
                <select name="type" class="form-control">
                    <option value="">Zote</option>
                    <option value="short" {{ request('type') == 'short' ? 'selected' : '' }}>Short</option>
                    <option value="medium" {{ request('type') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="long" {{ request('type') == 'long' ? 'selected' : '' }}>Long</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                <label class="form-label">Tafuta</label>
                <input type="text" name="search" class="form-control" placeholder="Jina au email..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tarehe Kuanzia</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tarehe Kuishia</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary">Tafuta</button>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Completions Table -->
<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        <div class="flex justify-between items-center">
            <h4>Survey Completions</h4>
            <span style="color: var(--text-muted);">{{ $completions->total() }} records</span>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Mtumiaji</th>
                <th>Survey ID</th>
                <th>Aina</th>
                <th>Muda</th>
                <th>Malipo</th>
                <th>Status</th>
                <th>Wakati</th>
                <th>Vitendo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completions as $completion)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $completion->user->getAvatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 500;">{{ $completion->user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $completion->user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <code style="font-size: 0.75rem;">{{ $completion->survey_id }}</code>
                </td>
                <td>
                    @php
                        $typeColors = [
                            'short' => 'badge-info',
                            'medium' => 'badge-primary',
                            'long' => 'badge-warning',
                        ];
                    @endphp
                    <span class="badge {{ $typeColors[$completion->survey_type] ?? '' }}">
                        {{ $completion->getTypeLabel() }}
                    </span>
                </td>
                <td>{{ $completion->loi }} min</td>
                <td style="font-weight: 600; color: var(--success);">
                    TZS {{ number_format($completion->user_reward, 0) }}
                </td>
                <td>
                    @php
                        $statusColors = [
                            'pending' => 'badge-warning',
                            'completed' => 'badge-info',
                            'credited' => 'badge-success',
                            'rejected' => 'badge-error',
                            'reversed' => 'badge-error',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$completion->status] ?? '' }}">
                        {{ $completion->getStatusLabel() }}
                    </span>
                </td>
                <td style="color: var(--text-muted); font-size: 0.875rem;">
                    {{ $completion->created_at->diffForHumans() }}
                </td>
                <td>
                    <div class="flex gap-2">
                        @if(in_array($completion->status, ['pending', 'completed']))
                        <form method="POST" action="{{ route('admin.surveys.credit', $completion) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Lipa">
                                ✓
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.surveys.reject', $completion) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-error" title="Kataa">
                                ✕
                            </button>
                        </form>
                        @elseif($completion->status === 'credited')
                        <form method="POST" action="{{ route('admin.surveys.reverse', $completion) }}" style="display: inline;" onsubmit="return confirm('Una uhakika unataka kurudisha malipo haya?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" title="Reverse">
                                ↩
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: var(--space-2);">📊</div>
                    <div>Hakuna survey completions</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($completions->hasPages())
    <div class="card-body">
        {{ $completions->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Totals Summary -->
<div class="card mt-6" style="padding: var(--space-6); background: var(--gradient-primary);">
    <div style="position: relative; z-index: 10;">
        <h4 style="color: white; margin-bottom: var(--space-4);">💰 Muhtasari wa Surveys</h4>
        <div class="grid grid-3" style="gap: var(--space-8);">
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Jumla Imelipwa (Users)</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($stats['total_credited'], 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">BitLabs Earnings (USD)</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">${{ number_format($stats['bitlabs_earnings'], 2) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Profit Margin</div>
                @php
                    $bitlabsEarningsTzs = $stats['bitlabs_earnings'] * 2500; // Approximate TZS/USD rate
                    $profit = $bitlabsEarningsTzs - $stats['total_credited'];
                    $profitPercentage = $bitlabsEarningsTzs > 0 ? ($profit / $bitlabsEarningsTzs) * 100 : 0;
                @endphp
                <div style="font-size: 2rem; font-weight: 800; color: white;">{{ number_format($profitPercentage, 1) }}%</div>
            </div>
        </div>
    </div>
</div>
@endsection
