@extends('layouts.admin')

@section('title', 'Blocked Users & Fraud Detection')
@section('page-title', 'Watumiaji Waliofungwa')
@section('page-subtitle', 'Fuatilia clicks za tuhuma, block/unblock watumiaji')

@section('content')
@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 1.5rem;">
    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
    {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning" style="margin-bottom: 1.5rem;">
    <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
    {{ session('warning') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="margin-bottom: 1.5rem;">
    <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
    {{ session('error') }}
</div>
@endif

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon red">
            <i data-lucide="ban" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Blocked</div>
        <div class="stat-number">{{ $stats['total_blocked'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon orange">
            <i data-lucide="bot" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Auto-Blocked</div>
        <div class="stat-number">{{ $stats['auto_blocked'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="shield-x" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Admin Blocked</div>
        <div class="stat-number">{{ $stats['admin_blocked'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="flag" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Flagged Users</div>
        <div class="stat-number">{{ $stats['flagged_users'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">At Risk (&ge;15)</div>
        <div class="stat-number">{{ $stats['at_risk'] }}</div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="mouse-pointer-click" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Flags Today</div>
        <div class="stat-number">{{ $stats['total_flags_today'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; justify-content: space-between;">
        <!-- Filter Tabs -->
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('admin.blocked-users.index', ['filter' => 'all']) }}" 
               class="btn {{ $filter == 'all' ? 'btn-primary' : 'btn-secondary' }}">
                <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                Wote
            </a>
            <a href="{{ route('admin.blocked-users.index', ['filter' => 'blocked']) }}" 
               class="btn {{ $filter == 'blocked' ? 'btn-primary' : 'btn-secondary' }}">
                <i data-lucide="ban" style="width: 16px; height: 16px;"></i>
                Waliofungwa ({{ $stats['total_blocked'] }})
            </a>
            <a href="{{ route('admin.blocked-users.index', ['filter' => 'flagged']) }}" 
               class="btn {{ $filter == 'flagged' ? 'btn-primary' : 'btn-secondary' }}">
                <i data-lucide="flag" style="width: 16px; height: 16px;"></i>
                Flagged
            </a>
            <a href="{{ route('admin.blocked-users.index', ['filter' => 'at_risk']) }}" 
               class="btn {{ $filter == 'at_risk' ? 'btn-primary' : 'btn-secondary' }}">
                <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                At Risk
            </a>
            <a href="{{ route('admin.blocked-users.index', ['filter' => 'auto_blocked']) }}" 
               class="btn {{ $filter == 'auto_blocked' ? 'btn-primary' : 'btn-secondary' }}">
                <i data-lucide="bot" style="width: 16px; height: 16px;"></i>
                Auto
            </a>
        </div>

        <!-- Search -->
        <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" class="form-input-modern" placeholder="Tafuta jina, email, simu..." 
                   value="{{ request('search') }}" style="min-width: 250px;">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" style="width: 16px; height: 16px;"></i>
            </button>
            @if(request('search'))
            <a href="{{ route('admin.blocked-users.index', ['filter' => $filter]) }}" class="btn btn-secondary">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </a>
            @endif
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="data-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Clicks</th>
                <th>Hali</th>
                <th>Sababu</th>
                <th>Tarehe</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="{{ $user->getAvatarUrl() }}" alt="" 
                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div style="font-weight: 600;">{{ $user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $user->phone }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @php
                        $clickCount = $user->total_flagged_clicks ?? 0;
                        $threshold = \App\Models\UserClickFlag::AUTO_BLOCK_THRESHOLD;
                        $percentage = min(100, ($clickCount / $threshold) * 100);
                        
                        if ($percentage >= 100) {
                            $color = '#ef4444'; // red
                        } elseif ($percentage >= 75) {
                            $color = '#f97316'; // orange
                        } elseif ($percentage >= 50) {
                            $color = '#eab308'; // yellow
                        } else {
                            $color = '#10b981'; // green
                        }
                    @endphp
                    <div style="min-width: 80px;">
                        <div style="font-weight: 600; color: {{ $color }};">{{ $clickCount }} / {{ $threshold }}</div>
                        <div style="width: 100%; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 4px;">
                            <div style="width: {{ $percentage }}%; height: 100%; background: {{ $color }}; border-radius: 2px; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->is_blocked)
                        <span class="status-badge danger">
                            <i data-lucide="ban" style="width: 12px; height: 12px;"></i>
                            Blocked
                        </span>
                        @if(!$user->blocked_by)
                            <span class="status-badge warning" style="margin-left: 4px; font-size: 0.65rem;">AUTO</span>
                        @endif
                    @elseif($clickCount >= 15)
                        <span class="status-badge warning">
                            <i data-lucide="alert-triangle" style="width: 12px; height: 12px;"></i>
                            At Risk
                        </span>
                    @elseif($clickCount > 0)
                        <span class="status-badge pending">
                            <i data-lucide="flag" style="width: 12px; height: 12px;"></i>
                            Flagged
                        </span>
                    @else
                        <span class="status-badge success">
                            <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                            OK
                        </span>
                    @endif
                </td>
                <td>
                    @if($user->blocked_reason)
                        <div style="max-width: 200px; font-size: 0.75rem; color: var(--text-muted);">
                            {{ Str::limit($user->blocked_reason, 50) }}
                        </div>
                    @else
                        <span style="color: var(--text-muted);">-</span>
                    @endif
                </td>
                <td>
                    @if($user->blocked_at)
                        <div style="font-size: 0.75rem;">
                            <div>{{ $user->blocked_at->format('M d, Y') }}</div>
                            <div style="color: var(--text-muted);">{{ $user->blocked_at->diffForHumans() }}</div>
                        </div>
                    @else
                        <span style="color: var(--text-muted);">-</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="action-btns" style="justify-content: flex-end;">
                        <!-- View Details -->
                        <a href="{{ route('admin.blocked-users.show', $user) }}" class="btn-icon" title="Angalia Details">
                            <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                        </a>

                        @if($user->is_blocked)
                            <!-- Unblock Button -->
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="openUnblockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    title="Fungua">
                                <i data-lucide="unlock" style="width: 14px; height: 14px;"></i>
                                Fungua
                            </button>
                        @else
                            <!-- Block Button -->
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    title="Zuia">
                                <i data-lucide="ban" style="width: 14px; height: 14px;"></i>
                                Zuia
                            </button>
                        @endif

                        @if($user->total_flagged_clicks > 0)
                            <!-- Reset Click Count -->
                            <form action="{{ route('admin.blocked-users.reset-clicks', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-icon" title="Reset Clicks" 
                                        onclick="return confirm('Unataka reset click counter ya {{ $user->name }}?')">
                                    <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem;">
                    <i data-lucide="users" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-muted);">Hakuna watumiaji wanaolingana na filter iliyochaguliwa.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div style="margin-top: 1.5rem;">
    {{ $users->links() }}
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
.form-textarea-modern:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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

// Close modals on click outside
document.getElementById('blockModal').addEventListener('click', function(e) {
    if (e.target === this) closeBlockModal();
});
document.getElementById('unblockModal').addEventListener('click', function(e) {
    if (e.target === this) closeUnblockModal();
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBlockModal();
        closeUnblockModal();
    }
});
</script>
@endsection
