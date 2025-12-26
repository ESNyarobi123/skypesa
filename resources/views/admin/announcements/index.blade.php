@extends('layouts.admin')

@section('title', 'Announcements')
@section('page-title', 'Announcements')
@section('page-subtitle', 'Broadcast news and updates to all users')

@section('content')
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div class="chart-header">
        <div>
            <div class="chart-title">
                <i data-lucide="megaphone" style="width: 20px; height: 20px; display: inline; color: #f59e0b;"></i>
                All Announcements
            </div>
            <div class="chart-subtitle">Manage news and updates for users</div>
        </div>
        <div class="chart-actions">
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                New Announcement
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 1rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem;">
        <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($announcements->count() > 0)
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Media</th>
                    <th>Type</th>
                    <th>Popup</th>
                    <th>Views</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($announcements as $announcement)
                <tr>
                    <td>
                        <div style="max-width: 250px;">
                            <div style="font-weight: 600; color: white; margin-bottom: 0.25rem;">{{ $announcement->title }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ Str::limit($announcement->body, 40) }}
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($announcement->isVideo())
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                                🎬 Video
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                                📝 Text
                            </span>
                        @endif
                    </td>
                    <td>
                        <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; background: {{ $announcement->getTypeBadgeColor() }}20; color: {{ $announcement->getTypeBadgeColor() }};">
                            {{ ucfirst($announcement->type) }}
                        </span>
                    </td>
                    <td>
                        @if($announcement->show_as_popup)
                            <span style="color: var(--success);">
                                <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                                {{ $announcement->max_popup_views }}x
                            </span>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="color: white; font-weight: 600;">{{ $announcement->reads_count }}</span>
                        <span style="color: var(--text-muted);"> users</span>
                    </td>
                    <td style="font-size: 0.7rem;">
                        @if($announcement->starts_at || $announcement->expires_at)
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                @if($announcement->starts_at)
                                    <div style="color: var(--text-muted);">
                                        <i data-lucide="play" style="width: 12px; height: 12px; display: inline;"></i>
                                        {{ $announcement->starts_at->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                                @if($announcement->expires_at)
                                    <div style="color: {{ $announcement->expires_at->isPast() ? 'var(--error)' : 'var(--warning)' }};">
                                        <i data-lucide="clock" style="width: 12px; height: 12px; display: inline;"></i>
                                        {{ $announcement->expires_at->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') }}
                                        @if($announcement->expires_at->isFuture())
                                            <span style="opacity: 0.7;">({{ $announcement->expires_at->diffForHumans() }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--success);">
                                <i data-lucide="infinity" style="width: 14px; height: 14px; display: inline;"></i>
                                Always
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($announcement->isCurrentlyActive())
                            <span class="status-badge active">
                                <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i>
                                Active
                            </span>
                        @else
                            <span class="status-badge inactive">
                                <i data-lucide="x-circle" style="width: 12px; height: 12px;"></i>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td style="font-size: 0.75rem;">
                        <div style="color: white;">{{ $announcement->created_at->timezone('Africa/Dar_es_Salaam')->format('d M Y') }}</div>
                        <div style="color: var(--text-muted); font-size: 0.65rem;">
                            {{ $announcement->created_at->timezone('Africa/Dar_es_Salaam')->format('H:i') }} 
                            <span style="opacity: 0.7;">• {{ $announcement->created_at->diffForHumans() }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.announcements.stats', $announcement) }}" class="action-btn" title="View Stats">
                                <i data-lucide="bar-chart-2" style="width: 14px; height: 14px;"></i>
                            </a>
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="action-btn" title="Edit">
                                <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                            </a>
                            <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn" title="{{ $announcement->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i data-lucide="{{ $announcement->is_active ? 'eye-off' : 'eye' }}" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn danger" title="Delete">
                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="padding: 1rem; display: flex; justify-content: center;">
        {{ $announcements->links() }}
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <i data-lucide="megaphone" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
        <p style="margin-bottom: 1rem;">No announcements yet</p>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
            Create First Announcement
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
