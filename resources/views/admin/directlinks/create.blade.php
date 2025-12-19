@extends('layouts.admin')

@section('title', 'Create Task/Link')
@section('page-title', 'Create New Task/Link')
@section('page-subtitle', 'Add a new earning opportunity for users')

@section('content')
<div style="max-width: 700px;">
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Task Details</div>
                <div class="chart-subtitle">Configure the new task or direct link</div>
            </div>
        </div>
        
        <form action="{{ route('admin.directlinks.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; gap: 1.25rem;">
                <!-- Title -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Title *</label>
                    <input type="text" name="title" class="form-input-modern" value="{{ old('title') }}" required placeholder="e.g., Watch Video Ad">
                    @error('title')
                    <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Description -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Description</label>
                    <textarea name="description" class="form-input-modern" rows="2" placeholder="What should users do?">{{ old('description') }}</textarea>
                    @error('description')
                    <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Type & Provider -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Type *</label>
                        <select name="type" class="form-input-modern form-select-modern" required>
                            @foreach($taskTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Provider/Source</label>
                        <input type="text" name="provider" class="form-input-modern" value="{{ old('provider') }}" placeholder="e.g., Adsterra, Monetag">
                        @error('provider')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- URL -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Target URL *</label>
                    <input type="url" name="url" class="form-input-modern" value="{{ old('url') }}" required placeholder="https://...">
                    @error('url')
                    <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Duration & Reward -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Duration (Seconds) *</label>
                        <input type="number" name="duration_seconds" class="form-input-modern" value="{{ old('duration_seconds', 30) }}" required min="1" max="300">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">How long user must view/engage</p>
                        @error('duration_seconds')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Reward Override (TZS)</label>
                        <input type="number" name="reward_override" class="form-input-modern" value="{{ old('reward_override') }}" min="0" step="0.01" placeholder="Leave empty for plan default">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Optional: Override plan's reward/task</p>
                        @error('reward_override')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Limits -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Daily Limit per User</label>
                        <input type="number" name="daily_limit" class="form-input-modern" value="{{ old('daily_limit') }}" min="1" placeholder="Unlimited">
                        @error('daily_limit')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Total Completions Limit</label>
                        <input type="number" name="total_limit" class="form-input-modern" value="{{ old('total_limit') }}" min="1" placeholder="Unlimited">
                        @error('total_limit')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Media -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Thumbnail URL</label>
                        <input type="url" name="thumbnail" class="form-input-modern" value="{{ old('thumbnail') }}" placeholder="https://...">
                        @error('thumbnail')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Icon</label>
                        <input type="text" name="icon" class="form-input-modern" value="{{ old('icon', 'play-circle') }}" placeholder="play-circle">
                        @error('icon')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Scheduling -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="form-input-modern" value="{{ old('starts_at') }}">
                        @error('starts_at')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Ends At</label>
                        <input type="datetime-local" name="ends_at" class="form-input-modern" value="{{ old('ends_at') }}">
                        @error('ends_at')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Sort Order</label>
                        <input type="number" name="sort_order" class="form-input-modern" value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Toggles -->
                <div style="display: flex; gap: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Active</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Featured</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    Create Task
                </button>
                <a href="{{ route('admin.directlinks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
