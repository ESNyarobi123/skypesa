@extends('layouts.admin')

@section('title', $linkpool->name . ' - Links')
@section('page-title', $linkpool->name)
@section('page-subtitle', 'Manage links in this pool')

@section('content')
<div class="animate-in">
    <!-- Back Button & Pool Info -->
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
        <a href="{{ route('admin.linkpools.index') }}" 
           style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--text-muted); text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
        </a>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $linkpool->color }}20; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="{{ $linkpool->icon }}" style="width: 24px; height: 24px; color: {{ $linkpool->color }};"></i>
            </div>
            <div>
                <h2 style="color: white; font-size: 1.25rem; font-weight: 700;">{{ $linkpool->name }}</h2>
                <p style="color: var(--text-muted); font-size: 0.8rem;">Reward: TZS {{ number_format($linkpool->reward_amount) }} • Duration: {{ $linkpool->duration_seconds }}s</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
        <div class="stat-card-modern">
            <div class="stat-icon purple">
                <i data-lucide="link" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Total Links</div>
            <div class="stat-number">{{ $stats['total_links'] }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Active Links</div>
            <div class="stat-number">{{ $stats['active_links'] }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon yellow">
                <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Clicks Today</div>
            <div class="stat-number">{{ number_format($stats['clicks_today']) }}</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon blue">
                <i data-lucide="bar-chart-2" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Total Clicks</div>
            <div class="stat-number">{{ number_format($stats['total_clicks']) }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Links List -->
        <div class="data-table-container">
            <div class="table-header">
                <div class="table-title">Links in Pool</div>
                <a href="{{ route('admin.linkpools.links.create', $linkpool) }}" 
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; background: var(--gradient-primary); border-radius: 8px; color: white; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Add Link
                </a>
            </div>

            @if($linkpool->links->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Link</th>
                        <th>Provider</th>
                        <th>Clicks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($linkpool->links as $link)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                    {{ $link->provider_icon }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $link->name }}</div>
                                    <div class="user-email" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ Str::limit($link->url, 40) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="padding: 0.3rem 0.6rem; background: rgba(139, 92, 246, 0.1); border-radius: 6px; color: #8b5cf6; font-size: 0.75rem; font-weight: 500; text-transform: capitalize;">
                                {{ $link->provider }}
                            </span>
                        </td>
                        <td>
                            <div>
                                <div style="color: white; font-weight: 600;">{{ number_format($link->total_clicks) }}</div>
                                <div style="color: var(--text-muted); font-size: 0.7rem;">{{ $link->clicks_today }} today</div>
                            </div>
                        </td>
                        <td>
                            @if($link->is_active)
                                <span class="status-badge active">Active</span>
                            @else
                                <span class="status-badge inactive">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.linkpools.links.edit', [$linkpool, $link]) }}" class="action-btn" title="Edit">
                                    <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form action="{{ route('admin.linkpools.links.toggle-status', [$linkpool, $link]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn" title="{{ $link->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i data-lucide="{{ $link->is_active ? 'pause' : 'play' }}" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.linkpools.links.destroy', [$linkpool, $link]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this link?');">
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
            @else
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i data-lucide="link-2-off" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p style="margin-bottom: 1rem;">No links in this pool yet</p>
                <a href="{{ route('admin.linkpools.links.create', $linkpool) }}" 
                   style="color: var(--primary); text-decoration: none; font-weight: 500;">
                    + Add your first link
                </a>
            </div>
            @endif
        </div>

        <!-- Quick Add / Bulk Import -->
        <div class="chart-card">
            <h3 style="color: white; font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                Bulk Import Links
            </h3>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 1rem;">
                Paste multiple URLs (one per line) to quickly add many links.
            </p>
            
            <form action="{{ route('admin.linkpools.links.bulk-import', $linkpool) }}" method="POST">
                @csrf
                
                <div class="form-group-modern">
                    <label class="form-label-modern">Provider</label>
                    <select name="provider" class="form-input-modern form-select-modern" required>
                        <option value="adsterra">Adsterra</option>
                        <option value="monetag">Monetag</option>
                        <option value="propellerads">PropellerAds</option>
                        <option value="hilltopads">HilltopAds</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">URLs (one per line)</label>
                    <textarea name="links" class="form-input-modern" rows="8" 
                              placeholder="https://example.com/ad1&#10;https://example.com/ad2&#10;https://example.com/ad3" required></textarea>
                </div>

                <button type="submit" 
                        style="width: 100%; padding: 0.875rem; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 10px; color: var(--info); font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                    Import Links
                </button>
            </form>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 1.5rem 0;">

            <!-- Pool Settings Summary -->
            <h4 style="color: white; font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem;">Pool Settings</h4>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Status</span>
                    <span class="status-badge {{ $linkpool->is_active ? 'active' : 'inactive' }}">
                        {{ $linkpool->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Reward</span>
                    <span style="color: var(--success); font-weight: 600;">TZS {{ number_format($linkpool->reward_amount) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Duration</span>
                    <span style="color: white; font-weight: 600;">{{ $linkpool->duration_seconds }}s</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Daily Limit/User</span>
                    <span style="color: white; font-weight: 600;">{{ $linkpool->daily_user_limit ?? '∞' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Cooldown</span>
                    <span style="color: white; font-weight: 600;">{{ $linkpool->cooldown_seconds }}s</span>
                </div>
            </div>

            <a href="{{ route('admin.linkpools.edit', $linkpool) }}" 
               style="display: block; margin-top: 1rem; text-align: center; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-secondary); text-decoration: none; font-size: 0.8rem; font-weight: 500;">
                <i data-lucide="settings" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                Edit Pool Settings
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
