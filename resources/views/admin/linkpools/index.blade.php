@extends('layouts.admin')

@section('title', 'Link Pools')
@section('page-title', 'Link Pools')
@section('page-subtitle', 'Manage SkyBoost™, SkyLinks™ and other link pools')

@section('content')
<div class="animate-in">
    <!-- Stats Grid -->
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card-modern">
            <div class="stat-icon blue">
                <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Total Pools</div>
            <div class="stat-number">{{ $stats['total_pools'] }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Active Pools</div>
            <div class="stat-number">{{ $stats['active_pools'] }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon purple">
                <i data-lucide="link" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Total Links</div>
            <div class="stat-number">{{ $stats['total_links'] }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon yellow">
                <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Clicks Today</div>
            <div class="stat-number">{{ number_format($stats['total_clicks_today']) }}</div>
        </div>
    </div>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">All Link Pools</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Each pool contains multiple links that rotate randomly</p>
        </div>
        <a href="{{ route('admin.linkpools.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--gradient-primary); border: none; border-radius: 10px; color: white; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
            Create Pool
        </a>
    </div>

    <!-- Pools Grid -->
    @if($pools->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
        @foreach($pools as $pool)
        <div class="chart-card" style="position: relative; overflow: hidden;">
            <!-- Status Badge -->
            <div style="position: absolute; top: 1rem; right: 1rem;">
                @if($pool->is_active)
                    <span class="status-badge active">
                        <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                        Active
                    </span>
                @else
                    <span class="status-badge inactive">
                        <i data-lucide="x" style="width: 12px; height: 12px;"></i>
                        Inactive
                    </span>
                @endif
            </div>

            <!-- Pool Icon & Name -->
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: {{ $pool->color }}20; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="{{ $pool->icon }}" style="width: 28px; height: 28px; color: {{ $pool->color }};"></i>
                </div>
                <div>
                    <h3 style="color: white; font-size: 1.125rem; font-weight: 700; margin-bottom: 0.25rem;">{{ $pool->name }}</h3>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">slug: {{ $pool->slug }}</p>
                </div>
            </div>

            @if($pool->description)
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem; line-height: 1.5;">{{ $pool->description }}</p>
            @endif

            <!-- Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem; padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 10px;">
                <div style="text-align: center;">
                    <div style="color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.25rem;">Links</div>
                    <div style="color: white; font-size: 1.25rem; font-weight: 700;">{{ $pool->links_count }}/{{ $pool->active_links_count }}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.25rem;">Base Rate</div>
                    <div style="color: var(--success); font-size: 1.25rem; font-weight: 700;">TZS {{ number_format($pool->reward_amount) }}</div>
                    <div style="color: var(--text-muted); font-size: 0.6rem;">*Per user plan</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.25rem;">Duration</div>
                    <div style="color: white; font-size: 1.25rem; font-weight: 700;">{{ $pool->duration_seconds }}s</div>
                </div>
            </div>

            <!-- Limits Info -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                @if($pool->daily_user_limit)
                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.75rem; background: rgba(59, 130, 246, 0.1); border-radius: 6px;">
                    <i data-lucide="user" style="width: 14px; height: 14px; color: var(--info);"></i>
                    <span style="color: var(--info); font-size: 0.75rem; font-weight: 500;">{{ $pool->daily_user_limit }}/user/day</span>
                </div>
                @endif
                @if($pool->cooldown_seconds)
                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.75rem; background: rgba(245, 158, 11, 0.1); border-radius: 6px;">
                    <i data-lucide="clock" style="width: 14px; height: 14px; color: var(--warning);"></i>
                    <span style="color: var(--warning); font-size: 0.75rem; font-weight: 500;">{{ $pool->cooldown_seconds }}s cooldown</span>
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05);">
                <a href="{{ route('admin.linkpools.show', $pool) }}" class="action-btn" title="View & Manage Links" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; color: var(--success); text-decoration: none; font-size: 0.8rem; font-weight: 500;">
                    <i data-lucide="link" style="width: 16px; height: 16px;"></i>
                    Manage Links
                </a>
                <a href="{{ route('admin.linkpools.edit', $pool) }}" class="action-btn" title="Edit Pool" style="padding: 0.75rem;">
                    <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                </a>
                <form action="{{ route('admin.linkpools.toggle-status', $pool) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="action-btn" title="{{ $pool->is_active ? 'Deactivate' : 'Activate' }}" style="padding: 0.75rem;">
                        <i data-lucide="{{ $pool->is_active ? 'pause' : 'play' }}" style="width: 16px; height: 16px;"></i>
                    </button>
                </form>
                <form action="{{ route('admin.linkpools.destroy', $pool) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this pool and all its links?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn danger" title="Delete Pool" style="padding: 0.75rem;">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty State -->
    <div class="chart-card" style="text-align: center; padding: 4rem 2rem;">
        <div style="width: 80px; height: 80px; border-radius: 20px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i data-lucide="layers" style="width: 40px; height: 40px; color: var(--primary);"></i>
        </div>
        <h3 style="color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">No Link Pools Yet</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Create your first pool to start rotating links randomly.</p>
        <a href="{{ route('admin.linkpools.create') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.5rem; background: var(--gradient-primary); border-radius: 10px; color: white; font-weight: 600; text-decoration: none;">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
            Create First Pool
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
