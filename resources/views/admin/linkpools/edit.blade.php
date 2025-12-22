@extends('layouts.admin')

@section('title', 'Edit Link Pool')
@section('page-title', 'Edit Link Pool')
@section('page-subtitle', 'Update pool settings')

@section('content')
<div class="animate-in" style="max-width: 800px;">
    <div class="chart-card">
        <form action="{{ route('admin.linkpools.update', $linkpool) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Pool Name -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Pool Name *</label>
                    <input type="text" name="name" value="{{ old('name', $linkpool->name) }}" 
                           class="form-input-modern" placeholder="e.g., SkyBoost™" required>
                    @error('name')
                        <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Slug (URL-friendly) *</label>
                    <input type="text" name="slug" value="{{ old('slug', $linkpool->slug) }}" 
                           class="form-input-modern" placeholder="e.g., skyboost" required
                           pattern="[a-z0-9_]+" title="Lowercase letters, numbers and underscores only">
                    @error('slug')
                        <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="form-group-modern">
                <label class="form-label-modern">Description</label>
                <textarea name="description" class="form-input-modern" rows="3" 
                          placeholder="Describe this pool...">{{ old('description', $linkpool->description) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Icon -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Icon (Lucide icon name)</label>
                    <input type="text" name="icon" value="{{ old('icon', $linkpool->icon) }}" 
                           class="form-input-modern" placeholder="e.g., zap, rocket, star">
                    <p style="color: var(--text-muted); font-size: 0.7rem; margin-top: 0.25rem;">
                        See icons at <a href="https://lucide.dev/icons" target="_blank" style="color: var(--primary);">lucide.dev</a>
                    </p>
                </div>

                <!-- Color -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Theme Color</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="color" name="color" value="{{ old('color', $linkpool->color) }}" 
                               style="width: 50px; height: 42px; border: none; border-radius: 8px; cursor: pointer;">
                        <input type="text" value="{{ old('color', $linkpool->color) }}" 
                               class="form-input-modern" style="flex: 1;" 
                               onchange="this.previousElementSibling.value = this.value"
                               id="colorText">
                    </div>
                </div>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 1.5rem 0;">
            
            <h3 style="color: white; font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
                <i data-lucide="settings" style="width: 18px; height: 18px; display: inline; vertical-align: middle;"></i>
                Task Settings
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Reward Amount (Reference) -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Base Reward Reference (TZS)</label>
                    <input type="number" name="reward_amount" value="{{ old('reward_amount', $linkpool->reward_amount) }}" 
                           class="form-input-modern" min="0" max="1000" step="0.01" required>
                    <p style="color: var(--warning); font-size: 0.7rem; margin-top: 0.25rem;">
                        ⚠️ Hii ni reference tu. User atalipwa kulingana na <strong>subscription plan</strong> yake.
                    </p>
                </div>

                <!-- Duration -->
                <div class="form-group-modern">
                    <label class="form-label-modern">View Duration (seconds) *</label>
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', $linkpool->duration_seconds) }}" 
                           class="form-input-modern" min="5" max="300" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Daily User Limit -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Daily Limit per User</label>
                    <input type="number" name="daily_user_limit" value="{{ old('daily_user_limit', $linkpool->daily_user_limit) }}" 
                           class="form-input-modern" min="1" placeholder="Leave empty for unlimited">
                </div>

                <!-- Cooldown -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Cooldown Between Tasks (seconds)</label>
                    <input type="number" name="cooldown_seconds" value="{{ old('cooldown_seconds', $linkpool->cooldown_seconds) }}" 
                           class="form-input-modern" min="0" placeholder="0 for no cooldown">
                </div>
            </div>

            <!-- Active Status -->
            <div class="form-group-modern">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $linkpool->is_active) ? 'checked' : '' }}
                           style="width: 20px; height: 20px; accent-color: var(--primary);">
                    <span style="color: white; font-weight: 500;">Pool is Active</span>
                </label>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 1.5rem 0;">

            <!-- Actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.linkpools.index') }}" 
                   style="padding: 0.875rem 1.5rem; background: rgba(255,255,255,0.05); border-radius: 10px; color: var(--text-secondary); text-decoration: none; font-weight: 500;">
                    Cancel
                </a>
                <button type="submit" 
                        style="padding: 0.875rem 2rem; background: var(--gradient-primary); border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Sync color picker with text input
    document.querySelector('input[name="color"]').addEventListener('input', function() {
        document.getElementById('colorText').value = this.value;
    });
</script>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
