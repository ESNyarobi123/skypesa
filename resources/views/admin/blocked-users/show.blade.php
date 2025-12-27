@extends('layouts.admin')

@section('title', 'User Click Flags - ' . $user->name)
@section('page-title', 'Click Flags: ' . $user->name)
@section('page-subtitle', 'Historia ya clicks za tuhuma kwa mtumiaji huyu')

@section('content')
@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 1.5rem;">
    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
    {{ session('success') }}
</div>
@endif

<!-- Back Button -->
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.blocked-users.index') }}" class="btn btn-secondary">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        Rudi Nyuma
    </a>
</div>

<!-- User Info Card -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
        <img src="{{ $user->getAvatarUrl() }}" alt="" 
             style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid {{ $user->is_blocked ? 'var(--error)' : 'var(--primary)' }};">
        
        <div style="flex: 1; min-width: 200px;">
            <h3 style="margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                {{ $user->name }}
                @if($user->is_blocked)
                    <span class="status-badge danger">
                        <i data-lucide="ban" style="width: 12px; height: 12px;"></i>
                        Blocked
                    </span>
                @endif
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; font-size: 0.875rem;">
                <div><strong>Email:</strong> {{ $user->email }}</div>
                <div><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</div>
                <div><strong>Plan:</strong> {{ $user->getPlanName() }}</div>
                <div><strong>Registered:</strong> {{ $user->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            @if($user->is_blocked)
                <button type="button" class="btn btn-success" 
                        onclick="openUnblockModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                    <i data-lucide="unlock" style="width: 16px; height: 16px;"></i>
                    Fungua Mtumiaji
                </button>
            @else
                <button type="button" class="btn btn-danger" 
                        onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                    <i data-lucide="ban" style="width: 16px; height: 16px;"></i>
                    Zuia Mtumiaji
                </button>
            @endif
            
            @if($user->clickFlags()->unreviewed()->count() > 0)
                <form action="{{ route('admin.blocked-users.review-all', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="check-check" style="width: 16px; height: 16px;"></i>
                        Mark All Reviewed
                    </button>
                </form>
            @endif

            @if($user->total_flagged_clicks > 0)
                <form action="{{ route('admin.blocked-users.reset-clicks', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Reset click counter?')">
                        <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i>
                        Reset Clicks
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card-modern">
        <div class="stat-icon {{ $stats['total_flags'] >= 20 ? 'red' : ($stats['total_flags'] >= 10 ? 'orange' : 'yellow') }}">
            <i data-lucide="flag" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Flags</div>
        <div class="stat-number">{{ $stats['total_flags'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Clicks</div>
        <div class="stat-number">{{ $stats['total_clicks'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="eye-off" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Unreviewed</div>
        <div class="stat-number">{{ $stats['unreviewed_flags'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Today</div>
        <div class="stat-number">{{ $stats['today_flags'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon {{ $stats['remaining_before_block'] <= 5 ? 'red' : 'yellow' }}">
            <i data-lucide="shield-alert" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Before Auto-Block</div>
        <div class="stat-number">{{ $stats['remaining_before_block'] }}</div>
    </div>
</div>

@if($user->is_blocked && $user->blocked_reason)
<!-- Block Info -->
<div class="alert alert-danger" style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: flex-start; gap: 1rem;">
        <i data-lucide="ban" style="width: 24px; height: 24px; flex-shrink: 0;"></i>
        <div>
            <strong>Sababu ya Kuzuiwa:</strong>
            <p style="margin: 0.5rem 0 0 0;">{{ $user->blocked_reason }}</p>
            <small style="color: rgba(255,255,255,0.7);">
                Blocked {{ $user->blocked_at->diffForHumans() }} 
                by {{ $user->blockedByAdmin?->name ?? 'System (Auto-block)' }}
            </small>
        </div>
    </div>
</div>
@endif

<!-- Click Flags Table -->
<div class="chart-card">
    <h4 style="margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
        <i data-lucide="list" style="width: 20px; height: 20px;"></i>
        Historia ya Click Flags
    </h4>
    
    <div class="data-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tarehe</th>
                    <th>Task</th>
                    <th>Fraud Type</th>
                    <th>IP</th>
                    <th>Device</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clickFlags as $flag)
                <tr>
                    <td>
                        <div style="font-size: 0.875rem;">
                            <div>{{ $flag->created_at->format('M d, Y') }}</div>
                            <div style="color: var(--text-muted); font-size: 0.75rem;">{{ $flag->created_at->format('H:i:s') }}</div>
                        </div>
                    </td>
                    <td>
                        @if($flag->task)
                            <div style="max-width: 150px;">
                                <div style="font-weight: 500;">{{ Str::limit($flag->task->title, 25) }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">ID: {{ $flag->task_id }}</div>
                            </div>
                        @else
                            <span style="color: var(--text-muted);">N/A</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $notes = $flag->notes ?? '';
                            $isNoClickFraud = str_contains($notes, 'NO-CLICK');
                        @endphp
                        @if($isNoClickFraud)
                            <div>
                                <span class="status-badge danger" style="white-space: nowrap;">
                                    <i data-lucide="eye-off" style="width: 12px; height: 12px;"></i>
                                    HAKUBOFYA
                                </span>
                                <div style="font-size: 0.65rem; color: var(--error); margin-top: 4px; max-width: 120px;">
                                    Hakuclick kwenye ad (bot/cheat)
                                </div>
                            </div>
                        @else
                            <div>
                                <span class="status-badge warning" style="white-space: nowrap;">
                                    <i data-lucide="mouse-pointer-click" style="width: 12px; height: 12px;"></i>
                                    {{ $flag->click_count }} clicks
                                </span>
                                @if($notes)
                                    <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px; max-width: 120px;">
                                        {{ Str::limit($notes, 30) }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <code style="font-size: 0.75rem;">{{ $flag->ip_address ?? 'N/A' }}</code>
                    </td>
                    <td>
                        <div style="max-width: 120px; font-size: 0.7rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ Str::limit($flag->device_info, 30) }}
                        </div>
                    </td>
                    <td>
                        @if($flag->is_reviewed)
                            <span class="status-badge success">
                                <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                                Reviewed
                            </span>
                            @if($flag->reviewer)
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">
                                    by {{ $flag->reviewer->name }}
                                </div>
                            @endif
                        @else
                            <span class="status-badge pending">
                                <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                Pending
                            </span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if(!$flag->is_reviewed)
                            <form action="{{ route('admin.blocked-users.review-flag', $flag) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                    Review
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <i data-lucide="check-circle" style="width: 48px; height: 48px; color: var(--success); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-muted);">Mtumiaji huyu hana click flags.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div style="margin-top: 1rem;">
        {{ $clickFlags->links() }}
    </div>
</div>

<!-- Block Modal -->
<div id="blockModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i data-lucide="ban" style="width: 24px; height: 24px; color: var(--error);"></i> Zuia Mtumiaji</h3>
            <button type="button" onclick="closeBlockModal()" class="btn-icon">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <form id="blockForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>Unataka kumzuia <strong id="blockUserName"></strong>?</p>
                <div class="form-group-modern">
                    <label class="form-label-modern">Sababu ya Kuzuia *</label>
                    <textarea name="reason" class="form-textarea-modern" rows="3" required 
                              placeholder="Eleza sababu ya kumzuia mtumiaji huyu..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeBlockModal()" class="btn btn-secondary">Ghairi</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="ban" style="width: 16px; height: 16px;"></i>
                    Zuia Mtumiaji
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Unblock Modal -->
<div id="unblockModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i data-lucide="unlock" style="width: 24px; height: 24px; color: var(--success);"></i> Fungua Mtumiaji</h3>
            <button type="button" onclick="closeUnblockModal()" class="btn-icon">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <form id="unblockForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>Unataka kumfungua <strong id="unblockUserName"></strong>?</p>
                <div class="form-group-modern" style="margin-top: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="reset_clicks" value="1" style="width: 18px; height: 18px;">
                        <span>Reset click counter pia (kuanza upya)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeUnblockModal()" class="btn btn-secondary">Ghairi</button>
                <button type="submit" class="btn btn-success">
                    <i data-lucide="unlock" style="width: 16px; height: 16px;"></i>
                    Fungua Mtumiaji
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}
.modal-content {
    background: var(--bg-card);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    border: 1px solid var(--border-color);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.modal-header h3 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
    font-size: 1.1rem;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
}
.form-textarea-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    resize: vertical;
}
.btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.75rem;
}
</style>

<script>
function openBlockModal(userId, userName) {
    document.getElementById('blockModal').style.display = 'flex';
    document.getElementById('blockUserName').textContent = userName;
    document.getElementById('blockForm').action = '/admin/blocked-users/' + userId + '/block';
}

function closeBlockModal() {
    document.getElementById('blockModal').style.display = 'none';
}

function openUnblockModal(userId, userName) {
    document.getElementById('unblockModal').style.display = 'flex';
    document.getElementById('unblockUserName').textContent = userName;
    document.getElementById('unblockForm').action = '/admin/blocked-users/' + userId + '/unblock';
}

function closeUnblockModal() {
    document.getElementById('unblockModal').style.display = 'none';
}

document.getElementById('blockModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBlockModal();
});
document.getElementById('unblockModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeUnblockModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBlockModal();
        closeUnblockModal();
    }
});
</script>
@endsection
