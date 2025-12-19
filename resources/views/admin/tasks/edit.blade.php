@extends('layouts.admin')

@section('title', 'Hariri Kazi')
@section('page-title', 'Hariri Kazi')
@section('page-subtitle', $task->title)

@section('content')
<div style="max-width: 600px;">
    <div class="card card-body">
        <form action="{{ route('admin.tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Jina la Kazi</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Maelezo</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $task->description) }}</textarea>
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Aina</label>
                    <select name="type" class="form-control" required>
                        <option value="view_ad" {{ old('type', $task->type) == 'view_ad' ? 'selected' : '' }}>View Ad</option>
                        <option value="share_link" {{ old('type', $task->type) == 'share_link' ? 'selected' : '' }}>Share Link</option>
                        <option value="survey" {{ old('type', $task->type) == 'survey' ? 'selected' : '' }}>Survey</option>
                        <option value="other" {{ old('type', $task->type) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Provider</label>
                    <select name="provider" class="form-control" required>
                        <option value="monetag" {{ old('provider', $task->provider) == 'monetag' ? 'selected' : '' }}>Monetag</option>
                        <option value="adsterra" {{ old('provider', $task->provider) == 'adsterra' ? 'selected' : '' }}>Adsterra</option>
                        <option value="custom" {{ old('provider', $task->provider) == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="url" name="url" class="form-control" value="{{ old('url', $task->url) }}" required>
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Muda (sekunde)</label>
                    <input type="number" name="duration_seconds" class="form-control" value="{{ old('duration_seconds', $task->duration_seconds) }}" min="5" max="300" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reward Override (TZS)</label>
                    <input type="number" name="reward_override" class="form-control" value="{{ old('reward_override', $task->reward_override) }}" min="0">
                </div>
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Daily Limit</label>
                    <input type="number" name="daily_limit" class="form-control" value="{{ old('daily_limit', $task->daily_limit) }}" min="1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Total Limit</label>
                    <input type="number" name="total_limit" class="form-control" value="{{ old('total_limit', $task->total_limit) }}" min="1">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $task->sort_order) }}" min="0">
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4); margin-bottom: var(--space-6);">
                <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $task->is_active) ? 'checked' : '' }} style="accent-color: var(--primary);">
                    <span>Active</span>
                </label>
                
                <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $task->is_featured) ? 'checked' : '' }} style="accent-color: var(--primary);">
                    <span>Featured</span>
                </label>
            </div>
            
            <!-- Stats -->
            <div class="card" style="background: var(--gradient-glow); padding: var(--space-4); margin-bottom: var(--space-6);">
                <div class="grid grid-2" style="gap: var(--space-4);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Total Completions</div>
                        <div style="font-size: 1.25rem; font-weight: 700;">{{ number_format($task->completions_count) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Created</div>
                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $task->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-4">
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary" style="flex: 1;">Ghairi</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="save"></i>
                    Hifadhi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
