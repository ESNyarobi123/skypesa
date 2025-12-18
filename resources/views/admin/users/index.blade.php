@extends('layouts.app')

@section('title', 'Manage Users')
@section('page-title', 'Watumiaji')
@section('page-subtitle', 'Dhibiti watumiaji wote')

@section('content')
<!-- Search & Filters -->
<div class="card card-body mb-8">
    <form method="GET" class="flex gap-4 items-center" style="flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Tafuta jina, email, au simu..." value="{{ request('search') }}">
        </div>
        <select name="status" class="form-control" style="max-width: 150px;">
            <option value="">Hali Zote</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-primary">
            <i data-lucide="search"></i>
            Tafuta
        </button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i data-lucide="x"></i>
            Clear
        </a>
        @endif
    </form>
</div>

<!-- Users Table -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Mtumiaji</th>
                <th>Simu</th>
                <th>Mpango</th>
                <th>Salio</th>
                <th>Tasks</th>
                <th>Hali</th>
                <th>Alijiunga</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->getAvatarUrl() }}" style="width: 40px; height: 40px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 500;">{{ $user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $user->phone }}</td>
                <td>
                    <span class="badge badge-primary">{{ $user->activeSubscription?->plan?->display_name ?? 'Free' }}</span>
                </td>
                <td style="font-weight: 600;">TZS {{ number_format($user->wallet?->balance ?? 0, 0) }}</td>
                <td>{{ $user->taskCompletions()->count() }}</td>
                <td>
                    @if($user->is_active)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-error">Inactive</span>
                    @endif
                </td>
                <td style="color: var(--text-muted);">{{ $user->created_at->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    <div class="flex gap-2 justify-end">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-secondary">
                            <i data-lucide="eye"></i>
                        </a>
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} user?')">
                                @if($user->is_active)
                                <i data-lucide="user-x" style="color: var(--error);"></i>
                                @else
                                <i data-lucide="user-check" style="color: var(--success);"></i>
                                @endif
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <i data-lucide="users" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                    Hakuna watumiaji
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($users->hasPages())
<div class="flex justify-center mt-6">
    {{ $users->links() }}
</div>
@endif
@endsection
