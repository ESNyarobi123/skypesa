@extends('layouts.admin')

@section('title', __('messages.admin.manage_tasks'))
@section('page-title', __('messages.admin.task_list'))
@section('page-subtitle', __('messages.admin.manage_tasks_subtitle'))

@section('content')
<!-- Actions -->
<div class="flex justify-between items-center mb-8">
    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i>
        {{ __('messages.admin.add_task') }}
    </a>
</div>

<!-- Tasks Table -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('messages.admin.task') }}</th>
                <th>{{ __('messages.admin.type') }}</th>
                <th>{{ __('messages.admin.provider') }}</th>
                <th>{{ __('messages.admin.duration') }}</th>
                <th>{{ __('messages.admin.reward') }}</th>
                <th>{{ __('messages.admin.limit') }}</th>
                <th>{{ __('messages.admin.completions') }}</th>
                <th>{{ __('messages.admin.status') }}</th>
                <th style="text-align: right;">{{ __('messages.admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>
                    <div style="font-weight: 500;">{{ $task->title }}</div>
                    <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; margin-top: 0.25rem;">
                        @if($task->is_featured)
                        <span class="badge badge-warning">{{ __('messages.admin.featured') }}</span>
                        @endif
                        @if($task->usesLinkPool())
                        <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #8b5cf6;">
                            🔀 {{ $task->linkPool?->name }}
                        </span>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="badge badge-primary">{{ $task->type }}</span>
                </td>
                <td>
                    {{ ucfirst($task->provider) }}
                    @if($task->usesLinkPool())
                        <br><small style="color: var(--text-muted);">{{ $task->linkPool?->activeLinks()->count() }} {{ __('messages.admin.links') }}</small>
                    @endif
                </td>
                <td>{{ $task->duration_seconds }}s</td>
                <td>
                    @if($task->reward_override)
                    <span style="color: var(--primary); font-weight: 600;">TZS {{ number_format($task->reward_override, 0) }}</span>
                    @else
                    <span style="color: var(--text-muted);">{{ __('messages.admin.plan_default') }}</span>
                    @endif
                </td>
                <td>{{ $task->daily_limit ?? '∞' }}{{ __('messages.admin.per_day') }}</td>
                <td>{{ number_format($task->completions_count) }}</td>
                <td>
                    @if($task->is_active)
                    <span class="badge badge-success">{{ __('messages.admin.active') }}</span>
                    @else
                    <span class="badge badge-error">{{ __('messages.admin.inactive') }}</span>
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
                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('{{ __('messages.admin.delete_confirm') }}')" style="color: var(--error);">
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
                    {{ __('messages.admin.no_tasks') }}
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
