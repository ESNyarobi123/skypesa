@extends('layouts.app')

@section('title', 'Manage Tasks')
@section('page-title', 'Kazi')
@section('page-subtitle', 'Dhibiti kazi za watumiaji')

@section('content')
<!-- Actions -->
<div class="flex justify-between items-center mb-8">
    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i>
        Ongeza Kazi
    </a>
</div>

<!-- Tasks Table -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Kazi</th>
                <th>Aina</th>
                <th>Provider</th>
                <th>Muda</th>
                <th>Reward</th>
                <th>Limit</th>
                <th>Completions</th>
                <th>Hali</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>
                    <div style="font-weight: 500;">{{ $task->title }}</div>
                    @if($task->is_featured)
                    <span class="badge badge-warning">FEATURED</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-primary">{{ $task->type }}</span>
                </td>
                <td>{{ ucfirst($task->provider) }}</td>
                <td>{{ $task->duration_seconds }}s</td>
                <td>
                    @if($task->reward_override)
                    <span style="color: var(--primary); font-weight: 600;">TZS {{ number_format($task->reward_override, 0) }}</span>
                    @else
                    <span style="color: var(--text-muted);">Plan default</span>
                    @endif
                </td>
                <td>{{ $task->daily_limit ?? '∞' }}/day</td>
                <td>{{ number_format($task->completions_count) }}</td>
                <td>
                    @if($task->is_active)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-error">Inactive</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="flex gap-2 justify-end">
                        <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-secondary">
                            <i data-lucide="edit"></i>
                        </a>
                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Futa kazi hii?')" style="color: var(--error);">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <i data-lucide="clipboard-list" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                    Hakuna kazi. Ongeza kazi mpya!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($tasks->hasPages())
<div class="flex justify-center mt-6">
    {{ $tasks->links() }}
</div>
@endif
@endsection
