@extends('layouts.admin')

@section('title', 'Announcement Stats')
@section('page-title', 'Announcement Statistics')
@section('page-subtitle', $announcement->title)

@section('content')
<div style="max-width: 1000px;">
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
        <div class="stat-card-modern">
            <div class="stat-icon blue">
                <i data-lucide="eye" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Total Views</div>
            <div class="stat-number">{{ number_format($stats['total_views']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="users" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Unique Users</div>
            <div class="stat-number">{{ number_format($stats['unique_users']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon purple">
                <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Dismissed Popup</div>
            <div class="stat-number">{{ number_format($stats['dismissed']) }}</div>
        </div>
    </div>

    <div class="chart-card" style="margin-bottom: 1.5rem;">
        <div class="chart-header">
            <div>
                <div class="chart-title">
                    <i data-lucide="file-text" style="width: 20px; height: 20px; display: inline; color: var(--primary);"></i>
                    Announcement Content
                </div>
            </div>
            <div class="chart-actions">
                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: {{ $announcement->getTypeBadgeColor() }}20; color: {{ $announcement->getTypeBadgeColor() }};">
                    {{ ucfirst($announcement->type) }}
                </span>
                @if($announcement->isCurrentlyActive())
                    <span class="status-badge active">Active</span>
                @else
                    <span class="status-badge inactive">Inactive</span>
                @endif
            </div>
        </div>

        <div style="padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 10px; margin-bottom: 1rem;">
            <h3 style="color: white; font-size: 1.1rem; margin-bottom: 0.5rem;">{{ $announcement->title }}</h3>
            <p style="color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">{{ $announcement->body }}</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; font-size: 0.8rem;">
            <div>
                <div style="color: var(--text-muted);">Created</div>
                <div style="color: white;">{{ $announcement->created_at->format('M j, Y H:i') }}</div>
            </div>
            <div>
                <div style="color: var(--text-muted);">By</div>
                <div style="color: white;">{{ $announcement->creator?->name ?? 'System' }}</div>
            </div>
            <div>
                <div style="color: var(--text-muted);">Starts</div>
                <div style="color: white;">{{ $announcement->starts_at?->format('M j, Y H:i') ?? 'Immediately' }}</div>
            </div>
            <div>
                <div style="color: var(--text-muted);">Expires</div>
                <div style="color: white;">{{ $announcement->expires_at?->format('M j, Y H:i') ?? 'Never' }}</div>
            </div>
        </div>
    </div>

    @if($announcement->reads->count() > 0)
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">
                    <i data-lucide="users" style="width: 20px; height: 20px; display: inline; color: var(--info);"></i>
                    User Views ({{ $announcement->reads->count() }})
                </div>
                <div class="chart-subtitle">Recent users who viewed this announcement</div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Views</th>
                        <th>Dismissed</th>
                        <th>First Seen</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($announcement->reads->take(50) as $read)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <img src="{{ $read->user->getAvatarUrl() }}" alt="{{ $read->user->name }}" class="user-avatar">
                                <div class="user-details">
                                    <div class="user-name">{{ $read->user->name }}</div>
                                    <div class="user-email">{{ $read->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: white;">{{ $read->view_count }}</span>
                        </td>
                        <td>
                            @if($read->popup_dismissed)
                                <span style="color: var(--success);">
                                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                                </span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ $read->first_seen_at?->format('M j, H:i') ?? '—' }}
                        </td>
                        <td style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ $read->last_seen_at?->format('M j, H:i') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Back to List
        </a>
        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary">
            <i data-lucide="edit-2" style="width: 16px; height: 16px;"></i>
            Edit Announcement
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
