@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Muhtasari wa platform')

@section('content')
<!-- Quick Stats -->
<div class="grid grid-4 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($totalUsers) }}</div>
        <div class="stat-label">Watumiaji Wote</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($completionsToday) }}</div>
        <div class="stat-label">Tasks Leo</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">TZS {{ number_format($earningsToday, 0) }}</div>
        <div class="stat-label">Mapato Leo</div>
    </div>
    <div class="stat-card" style="border-color: var(--warning);">
        <div class="stat-value" style="color: var(--warning);">{{ $pendingWithdrawals }}</div>
        <div class="stat-label">Pending Withdrawals</div>
    </div>
</div>

<!-- Main Stats Cards -->
<div class="grid grid-3 mb-8">
    <!-- Users Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>Watumiaji</h4>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                Angalia Wote
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Active</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ number_format($activeUsers) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Wapya Leo</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $newUsersToday }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Mwezi Huu</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $newUsersThisMonth }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Jumla</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($totalUsers) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Tasks Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>Tasks</h4>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-secondary">
                Manage
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Active Tasks</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $activeTasks }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Total Tasks</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ $totalTasks }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Completions Leo</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($completionsToday) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Mwezi Huu</div>
                <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($completionsThisMonth) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Withdrawals Card -->
    <div class="card card-body">
        <div class="flex justify-between items-center mb-4">
            <h4>Malipo</h4>
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-secondary">
                Angalia Wote
            </a>
        </div>
        <div class="grid grid-2" style="gap: var(--space-4);">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Pending</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">{{ $pendingWithdrawals }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Pending Amount</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--warning);">TZS {{ number_format($pendingAmount, 0) }}</div>
            </div>
            <div style="grid-column: span 2;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">Umelipa Mwezi Huu</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">TZS {{ number_format($paidThisMonth, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Overview -->
<div class="card mb-8" style="padding: var(--space-6); background: var(--gradient-primary);">
    <div style="position: relative; z-index: 10;">
        <h4 style="color: white; margin-bottom: var(--space-4);">Muhtasari wa Mapato</h4>
        <div class="grid grid-3" style="gap: var(--space-8);">
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Mapato Leo</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($earningsToday, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Mapato ya Mwezi</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($earningsThisMonth, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.7);">Jumla (Lifetime)</div>
                <div style="font-size: 2rem; font-weight: 800; color: white;">TZS {{ number_format($totalEarnings, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="gap: var(--space-6);">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-body" style="padding-bottom: 0;">
            <h4>Watumiaji Wapya</h4>
        </div>
        <table class="table">
            <tbody>
                @forelse($recentUsers as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->getAvatarUrl() }}" style="width: 36px; height: 36px; border-radius: 50%;">
                            <div>
                                <div style="font-weight: 500;">{{ $user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right; color: var(--text-muted);">
                        {{ $user->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                        Hakuna watumiaji wapya
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pending Withdrawals -->
    <div class="card">
        <div class="card-body" style="padding-bottom: 0;">
            <h4>Maombi ya Hivi Karibuni</h4>
        </div>
        <table class="table">
            <tbody>
                @forelse($recentWithdrawals as $withdrawal)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $withdrawal->user->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $withdrawal->payment_number }}</div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $colors = [
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'paid' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                        @endphp
                        <span class="badge {{ $colors[$withdrawal->status] ?? '' }}">
                            {{ $withdrawal->getStatusLabel() }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        TZS {{ number_format($withdrawal->net_amount, 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                        Hakuna maombi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Task Completions -->
<div class="card mt-8">
    <div class="card-body" style="padding-bottom: 0;">
        <h4>Tasks Zilizokamilishwa Hivi Karibuni</h4>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Mtumiaji</th>
                <th>Task</th>
                <th>Malipo</th>
                <th>Wakati</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentCompletions as $completion)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $completion->user->getAvatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%;">
                        <span>{{ $completion->user->name }}</span>
                    </div>
                </td>
                <td>{{ $completion->task->title }}</td>
                <td style="font-weight: 600; color: var(--success);">TZS {{ number_format($completion->reward_earned, 0) }}</td>
                <td style="color: var(--text-muted);">{{ $completion->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: var(--space-4); color: var(--text-muted);">
                    Hakuna completions
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
