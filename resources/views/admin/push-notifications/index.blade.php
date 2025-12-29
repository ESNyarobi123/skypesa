@extends('layouts.admin')

@section('title', 'Push Notifications')
@section('page-title', 'Push Notifications')
@section('page-subtitle', 'Tuma arifa kwa watumiaji wa app kupitia Firebase')

@section('content')
<div class="push-notifications-page">
    @if(!$isConfigured)
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.2); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-triangle" style="color: var(--error); width: 24px; height: 24px;"></i>
        </div>
        <div>
            <h4 style="color: var(--error); font-weight: 600; margin-bottom: 0.25rem;">Firebase Haijasanidiwa</h4>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Hakikisha faili ya Firebase credentials ipo: sky-pesa-firebase-adminsdk-fbsvc-6ac6dd3f6d.json</p>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 2rem;">
        <div class="stat-card-modern">
            <div class="stat-icon purple">
                <i data-lucide="smartphone" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Vifaa Vilivyosajiliwa</div>
            <div class="stat-number">{{ number_format($tokenStats['total_tokens']) }}</div>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <span style="font-size: 0.7rem; padding: 0.2rem 0.5rem; background: rgba(16, 185, 129, 0.15); color: var(--success); border-radius: 50px;">
                    <i data-lucide="smartphone" style="width: 10px; height: 10px; display: inline;"></i> {{ $tokenStats['android_tokens'] }}
                </span>
                <span style="font-size: 0.7rem; padding: 0.2rem 0.5rem; background: rgba(59, 130, 246, 0.15); color: var(--info); border-radius: 50px;">
                    <i data-lucide="apple" style="width: 10px; height: 10px; display: inline;"></i> {{ $tokenStats['ios_tokens'] }}
                </span>
            </div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="send" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Jumla Zilizotumwa</div>
            <div class="stat-number">{{ number_format($stats['total_sent']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon blue">
                <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Mafanikio</div>
            <div class="stat-number">{{ number_format($stats['total_success']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon red">
                <i data-lucide="x-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Zilizoshindwa</div>
            <div class="stat-number">{{ number_format($stats['total_failure']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon yellow">
                <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Zinasubiri</div>
            <div class="stat-number">{{ $stats['pending'] }}</div>
        </div>
    </div>
    
    <!-- Actions Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.push-notifications.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--gradient-primary); border-radius: 10px; color: white; font-weight: 600; font-size: 0.875rem; text-decoration: none;">
                <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                Tuma Notification Mpya
            </a>
            <a href="{{ route('admin.push-notifications.tokens') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; text-decoration: none;">
                <i data-lucide="key" style="width: 18px; height: 18px;"></i>
                FCM Tokens
            </a>
        </div>
        
        <!-- Filters -->
        <form method="GET" style="display: flex; gap: 0.75rem;">
            <select name="status" class="form-input-modern" style="width: auto; padding: 0.5rem 1rem;" onchange="this.form.submit()">
                <option value="">Status Zote</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>Sending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            
            <select name="target_type" class="form-input-modern" style="width: auto; padding: 0.5rem 1rem;" onchange="this.form.submit()">
                <option value="">Target Zote</option>
                <option value="all" {{ request('target_type') === 'all' ? 'selected' : '' }}>All Users</option>
                <option value="segment" {{ request('target_type') === 'segment' ? 'selected' : '' }}>Segment</option>
                <option value="specific" {{ request('target_type') === 'specific' ? 'selected' : '' }}>Specific Users</option>
            </select>
        </form>
    </div>
    
    <!-- Notifications Table -->
    <div class="data-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Notification</th>
                    <th>Target</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Sent By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr>
                    <td>
                        <div style="max-width: 250px;">
                            <div style="font-weight: 600; color: white; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $notification->title }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit($notification->body, 50) }}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 500; background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                            {{ $notification->target_type_label }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; min-width: 80px;">
                                <div style="height: 100%; background: var(--success); width: {{ $notification->success_rate }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                {{ $notification->success_count }}/{{ $notification->total_tokens }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge" style="background: {{ $notification->status_color }}20; color: {{ $notification->status_color }};">
                            {{ ucfirst($notification->status) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">
                            {{ $notification->sender?->name ?? 'System' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $notification->created_at->format('d M Y, H:i') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.push-notifications.show', $notification) }}" class="action-btn" title="View Details">
                                <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                            </a>
                            @if($notification->status === 'failed')
                            <form action="{{ route('admin.push-notifications.resend', $notification) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="action-btn" title="Resend">
                                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.push-notifications.destroy', $notification) }}" method="POST" style="display: inline;" onsubmit="return confirm('Una uhakika unataka kufuta?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn danger" title="Delete">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <div style="color: var(--text-muted);">
                            <i data-lucide="bell-off" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Hakuna push notifications zilizotumwa bado.</p>
                            <a href="{{ route('admin.push-notifications.create') }}" style="color: var(--primary); text-decoration: underline;">
                                Tuma notification ya kwanza
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($notifications->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    gap: 1rem;
}

@media (max-width: 1400px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
</style>
@endsection
