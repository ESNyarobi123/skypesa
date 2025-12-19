@extends('layouts.admin')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan: {{ $plan->display_name }}')
@section('page-subtitle', '{{ $plan->subscriptions_count }} active subscribers')

@section('content')
<div style="max-width: 700px;">
    <div class="chart-card">
        <div class="chart-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 50px; height: 50px; background: {{ $plan->badge_color ?? 'var(--primary)' }}20; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="{{ $plan->icon ?? 'crown' }}" style="width: 24px; height: 24px; color: {{ $plan->badge_color ?? 'var(--primary)' }};"></i>
                </div>
                <div>
                    <div class="chart-title">{{ $plan->display_name }}</div>
                    <div class="chart-subtitle">{{ $plan->subscriptions_count }} active subscribers</div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; gap: 1.25rem;">
                <!-- Basic Info -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Internal Name *</label>
                        <input type="text" name="name" class="form-input-modern" value="{{ old('name', $plan->name) }}" required {{ $plan->name === 'free' ? 'readonly' : '' }}>
                        @error('name')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Display Name *</label>
                        <input type="text" name="display_name" class="form-input-modern" value="{{ old('display_name', $plan->display_name) }}" required>
                        @error('display_name')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Description</label>
                    <textarea name="description" class="form-input-modern" rows="2">{{ old('description', $plan->description) }}</textarea>
                    @error('description')
                    <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Pricing -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Price (TZS) *</label>
                        <input type="number" name="price" class="form-input-modern" value="{{ old('price', $plan->price) }}" required min="0" step="0.01">
                        @error('price')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Duration (Days) *</label>
                        <input type="number" name="duration_days" class="form-input-modern" value="{{ old('duration_days', $plan->duration_days) }}" required min="1" max="365">
                        @error('duration_days')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Task Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Daily Task Limit</label>
                        <input type="number" name="daily_task_limit" class="form-input-modern" value="{{ old('daily_task_limit', $plan->daily_task_limit) }}" min="1" placeholder="Leave empty for unlimited">
                        @error('daily_task_limit')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Reward Per Task (TZS) *</label>
                        <input type="number" name="reward_per_task" class="form-input-modern" value="{{ old('reward_per_task', $plan->reward_per_task) }}" required min="0" step="0.01">
                        @error('reward_per_task')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Withdrawal Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Min Withdrawal (TZS) *</label>
                        <input type="number" name="min_withdrawal" class="form-input-modern" value="{{ old('min_withdrawal', $plan->min_withdrawal) }}" required min="0">
                        @error('min_withdrawal')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Withdrawal Fee (%) *</label>
                        <input type="number" name="withdrawal_fee_percent" class="form-input-modern" value="{{ old('withdrawal_fee_percent', $plan->withdrawal_fee_percent) }}" required min="0" max="100" step="0.1">
                        @error('withdrawal_fee_percent')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Processing Days *</label>
                        <input type="number" name="processing_days" class="form-input-modern" value="{{ old('processing_days', $plan->processing_days) }}" required min="0" max="30">
                        @error('processing_days')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Display Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Badge Color</label>
                        <input type="text" name="badge_color" class="form-input-modern" value="{{ old('badge_color', $plan->badge_color) }}" placeholder="#10b981">
                        @error('badge_color')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Icon</label>
                        <input type="text" name="icon" class="form-input-modern" value="{{ old('icon', $plan->icon) }}" placeholder="crown">
                        @error('icon')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Sort Order *</label>
                        <input type="number" name="sort_order" class="form-input-modern" value="{{ old('sort_order', $plan->sort_order) }}" required min="0">
                        @error('sort_order')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Toggles -->
                <div style="display: flex; gap: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);" {{ $plan->name === 'free' ? 'disabled checked' : '' }}>
                        <span style="color: white; font-size: 0.9rem;">Active</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Featured</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Save Changes
                </button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
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
