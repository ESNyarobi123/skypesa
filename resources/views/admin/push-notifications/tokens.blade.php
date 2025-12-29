@extends('layouts.admin')

@section('title', 'FCM Tokens')
@section('page-title', 'FCM Token Management')
@section('page-subtitle', 'Simamia FCM tokens za watumiaji wa app')

@section('content')
<div class="tokens-page">
    <!-- Stats Row -->
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 2rem;">
        <div class="stat-card-modern">
            <div class="stat-icon purple">
                <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Jumla Tokens</div>
            <div class="stat-number">{{ number_format($tokenStats['total_tokens']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="smartphone" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Android</div>
            <div class="stat-number">{{ number_format($tokenStats['android_tokens']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon blue">
                <i data-lucide="smartphone" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">iOS</div>
            <div class="stat-number">{{ number_format($tokenStats['ios_tokens']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon yellow">
                <i data-lucide="monitor" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Web</div>
            <div class="stat-number">{{ number_format($tokenStats['web_tokens']) }}</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon green">
                <i data-lucide="users" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="stat-title">Watumiaji Wanaofanyakazi</div>
            <div class="stat-number">{{ number_format($tokenStats['active_users_with_tokens']) }}</div>
        </div>
    </div>
    
    <!-- Actions Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <a href="{{ route('admin.push-notifications.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
            Push Notifications
        </a>
        
        <!-- Filters -->
        <form method="GET" style="display: flex; gap: 0.75rem;">
            <div class="search-input">
                <i data-lucide="search" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
                <input type="text" name="search" placeholder="Tafuta jina, email..." value="{{ request('search') }}">
            </div>
            
            <select name="device_type" class="form-input-modern" style="width: auto; padding: 0.5rem 1rem;" onchange="this.form.submit()">
                <option value="">Device Zote</option>
                <option value="android" {{ request('device_type') === 'android' ? 'selected' : '' }}>Android</option>
                <option value="ios" {{ request('device_type') === 'ios' ? 'selected' : '' }}>iOS</option>
                <option value="web" {{ request('device_type') === 'web' ? 'selected' : '' }}>Web</option>
            </select>
            
            <button type="submit" style="padding: 0.5rem 1rem; background: var(--primary); border: none; border-radius: 8px; color: white; cursor: pointer;">
                <i data-lucide="search" style="width: 16px; height: 16px;"></i>
            </button>
        </form>
    </div>
    
    <!-- Tokens Table -->
    <div class="data-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Device Type</th>
                    <th>FCM Token</th>
                    <th>Last Updated</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="user-avatar">
                            <div class="user-details">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $deviceColors = [
                                'android' => ['bg' => 'rgba(16, 185, 129, 0.15)', 'color' => 'var(--success)'],
                                'ios' => ['bg' => 'rgba(59, 130, 246, 0.15)', 'color' => 'var(--info)'],
                                'web' => ['bg' => 'rgba(139, 92, 246, 0.15)', 'color' => '#8b5cf6'],
                            ];
                            $device = $deviceColors[$user->device_type] ?? ['bg' => 'rgba(255,255,255,0.1)', 'color' => 'var(--text-muted)'];
                        @endphp
                        <span style="padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; background: {{ $device['bg'] }}; color: {{ $device['color'] }};">
                            {{ ucfirst($user->device_type) }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <code style="font-size: 0.7rem; color: var(--text-muted); background: rgba(0,0,0,0.3); padding: 0.25rem 0.5rem; border-radius: 4px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ Str::limit($user->fcm_token, 35) }}
                            </code>
                            <button onclick="copyToClipboard('{{ $user->fcm_token }}')" class="action-btn" style="width: 28px; height: 28px;" title="Copy Token">
                                <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $user->fcm_token_updated_at?->diffForHumans() ?? '-' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $user->last_login_at?->diffForHumans() ?? 'Haijulikana' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <form action="{{ route('admin.push-notifications.send-test', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="action-btn" title="Send Test Notification">
                                    <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.push-notifications.remove-token', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Remove FCM token ya {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn danger" title="Remove Token">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">
                        <div style="color: var(--text-muted);">
                            <i data-lucide="key-off" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Hakuna watumiaji wenye FCM tokens.</p>
                            <p style="font-size: 0.875rem;">Watumiaji watapata tokens wanapoweka app.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($users->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Token copied!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
@endpush

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
