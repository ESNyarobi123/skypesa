@extends('layouts.admin')

@section('title', 'Direct Links & Ads')
@section('page-title', 'Direct Links & Ads')
@section('page-subtitle', 'Manage all tasks, ads, and earning opportunities')

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="link" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Links</div>
        <div class="stat-number">{{ $stats['total_links'] }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Active</div>
        <div class="stat-number">{{ $stats['active_links'] }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Completions</div>
        <div class="stat-number">{{ number_format($stats['total_completions']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="coins" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Earnings Generated</div>
        <div class="stat-number">TZS {{ number_format($stats['earnings_generated'], 0) }}</div>
    </div>
</div>

<!-- Table -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">All Tasks & Links</h3>
        <div class="table-actions">
            <!-- Search -->
            <form action="{{ route('admin.directlinks.index') }}" method="GET" class="search-input">
                <i data-lucide="search" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
            </form>
            
            <!-- Filter by Type -->
            <select name="type" class="form-input-modern form-select-modern" style="width: 150px; padding: 0.6rem 1rem;" 
                    onchange="window.location.href='{{ route('admin.directlinks.index') }}?type=' + this.value + '&search={{ request('search') }}&status={{ request('status') }}'">
                <option value="all">All Types</option>
                @foreach($taskTypes as $key => $label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            
            <!-- Filter by Status -->
            <select name="status" class="form-input-modern form-select-modern" style="width: 130px; padding: 0.6rem 1rem;"
                    onchange="window.location.href='{{ route('admin.directlinks.index') }}?status=' + this.value + '&search={{ request('search') }}&type={{ request('type') }}'">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            
            <!-- Add Button -->
            <a href="{{ route('admin.directlinks.create') }}" class="btn btn-primary" style="padding: 0.6rem 1rem;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                Add Link/Ad
            </a>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Task/Link</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Reward</th>
                <th>Completions</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($directLinks as $link)
            <tr>
                <td>
                    <div style="max-width: 300px;">
                        <div style="font-weight: 600; color: white; margin-bottom: 0.25rem;">{{ Str::limit($link->title, 40) }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ Str::limit($link->url, 50) }}
                        </div>
                        @if($link->provider)
                        <span class="badge badge-primary" style="margin-top: 0.25rem; font-size: 0.65rem;">{{ $link->provider }}</span>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: var(--info);">
                        {{ $taskTypes[$link->type] ?? ucfirst(str_replace('_', ' ', $link->type)) }}
                    </span>
                </td>
                <td style="color: var(--text-secondary);">
                    <i data-lucide="clock" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                    {{ $link->duration_seconds }}s
                </td>
                <td style="font-weight: 600; color: var(--success);">
                    @if($link->reward_override)
                    TZS {{ number_format($link->reward_override, 0) }}
                    @else
                    <span style="color: var(--text-muted);">Plan default</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 600; color: white;">{{ number_format($link->completions_count) }}</span>
                        @if($link->total_limit)
                        <span style="color: var(--text-muted);">/ {{ number_format($link->total_limit) }}</span>
                        @endif
                    </div>
                    @if($link->daily_limit)
                    <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $link->daily_limit }}/day limit</div>
                    @endif
                </td>
                <td>
                    @if($link->is_active)
                        @if($link->ends_at && $link->ends_at->isPast())
                        <span class="status-badge inactive">Expired</span>
                        @elseif($link->starts_at && $link->starts_at->isFuture())
                        <span class="status-badge pending">Scheduled</span>
                        @else
                        <span class="status-badge active">Active</span>
                        @endif
                    @else
                    <span class="status-badge inactive">Inactive</span>
                    @endif
                    
                    @if($link->is_featured)
                    <span class="badge badge-warning" style="margin-left: 0.25rem; font-size: 0.6rem;">★</span>
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.directlinks.analytics', $link) }}" class="action-btn" title="Analytics">
                            <i data-lucide="bar-chart-2" style="width: 14px; height: 14px;"></i>
                        </a>
                        <a href="{{ route('admin.directlinks.edit', $link) }}" class="action-btn" title="Edit">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                        </a>
                        <form action="{{ route('admin.directlinks.toggle-status', $link) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="action-btn" title="{{ $link->is_active ? 'Deactivate' : 'Activate' }}">
                                <i data-lucide="{{ $link->is_active ? 'eye-off' : 'eye' }}" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.directlinks.duplicate', $link) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="action-btn" title="Duplicate">
                                <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.directlinks.destroy', $link) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn danger" title="Delete">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i data-lucide="link" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>No tasks or links found</p>
                    <a href="{{ route('admin.directlinks.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                        Add Your First Link
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($directLinks->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
        {{ $directLinks->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
