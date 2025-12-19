@extends('layouts.admin')

@section('title', 'Manage Users')
@section('page-title', 'User Management')
@section('page-subtitle', 'Create, edit, and manage all users')

@section('content')
<!-- Stats Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Users</div>
        <div class="stat-number">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Active Users</div>
        <div class="stat-number">{{ number_format($stats['active']) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">New Today</div>
        <div class="stat-number">{{ $stats['new_today'] }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">This Month</div>
        <div class="stat-number">{{ $stats['new_this_month'] }}</div>
    </div>
</div>

<!-- Users Table -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">All Users</h3>
        <div class="table-actions">
            <!-- Search -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="search-input">
                <i data-lucide="search" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}">
            </form>
            
            <!-- Filter by Plan -->
            <select name="plan" class="form-input-modern form-select-modern" style="width: 160px; padding: 0.6rem 1rem;" onchange="this.form.submit()">
                <option value="">All Plans</option>
                @foreach($plans as $plan)
                <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>
                    {{ $plan->display_name }}
                </option>
                @endforeach
            </select>
            
            <!-- Filter by Status -->
            <select name="status" class="form-input-modern form-select-modern" style="width: 130px; padding: 0.6rem 1rem;" onchange="window.location.href='{{ route('admin.users.index') }}?status=' + this.value + '&search={{ request('search') }}'">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            
            <!-- Export Button -->
            <a href="{{ route('admin.users.export') }}" class="btn btn-secondary" style="padding: 0.6rem 1rem;">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                Export
            </a>
            
            <!-- Add User Button -->
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="padding: 0.6rem 1rem;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                Add User
            </a>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Subscription</th>
                <th>Balance</th>
                <th>Tasks</th>
                <th>Referrals</th>
                <th>Status</th>
                <th>Joined</th>
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
                    @if($user->activeSubscription)
                    <span class="badge" style="background: {{ $user->activeSubscription->plan->badge_color ?? 'var(--primary)' }}20; color: {{ $user->activeSubscription->plan->badge_color ?? 'var(--primary)' }};">
                        {{ $user->activeSubscription->plan->display_name }}
                    </span>
                    @else
                    <span class="badge badge-primary">Free</span>
                    @endif
                </td>
                <td style="font-weight: 600; color: var(--success);">
                    TZS {{ number_format($user->wallet?->balance ?? 0, 0) }}
                </td>
                <td>{{ number_format($user->task_completions_count) }}</td>
                <td>{{ $user->referrals_count }}</td>
                <td>
                    <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                        <i data-lucide="{{ $user->is_active ? 'check-circle' : 'x-circle' }}" style="width: 12px; height: 12px;"></i>
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8rem;">
                    {{ $user->created_at->format('M d, Y') }}
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.users.show', $user) }}" class="action-btn" title="View">
                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="action-btn" title="Edit">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                        </a>
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="action-btn" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i data-lucide="users" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>No users found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Pagination -->
    @if($users->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
