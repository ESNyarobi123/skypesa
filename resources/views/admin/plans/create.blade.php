@extends('layouts.admin')

@section('title', 'Create Plan')
@section('page-title', 'Create Subscription Plan')
@section('page-subtitle', 'Add a new subscription tier')

@section('content')
<div style="max-width: 700px;">
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Plan Details</div>
                <div class="chart-subtitle">Configure the new subscription plan</div>
            </div>
        </div>
        
        <form action="{{ route('admin.plans.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; gap: 1.25rem;">
                <!-- Basic Info -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Internal Name *</label>
                        <input type="text" name="name" class="form-input-modern" value="{{ old('name') }}" required placeholder="e.g., premium">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Lowercase, no spaces</p>
                        @error('name')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Display Name *</label>
                        <input type="text" name="display_name" class="form-input-modern" value="{{ old('display_name') }}" required placeholder="e.g., Premium Plan">
                        @error('display_name')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Description</label>
                    <textarea name="description" class="form-input-modern" rows="2" placeholder="Brief description of this plan">{{ old('description') }}</textarea>
                    @error('description')
                    <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Pricing -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Price (TZS) *</label>
                        <input type="number" name="price" class="form-input-modern" value="{{ old('price', 0) }}" required min="0" step="0.01">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Set to 0 for free plan</p>
                        @error('price')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Duration (Days) *</label>
                        <input type="number" name="duration_days" class="form-input-modern" value="{{ old('duration_days', 30) }}" required min="1" max="365">
                        @error('duration_days')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Task Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Daily Task Limit</label>
                        <input type="number" name="daily_task_limit" class="form-input-modern" value="{{ old('daily_task_limit') }}" min="1" placeholder="Leave empty for unlimited">
                        @error('daily_task_limit')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Reward Per Task (TZS) *</label>
                        <input type="number" name="reward_per_task" class="form-input-modern" value="{{ old('reward_per_task', 50) }}" required min="0" step="0.01">
                        @error('reward_per_task')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Withdrawal Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Min Withdrawal (TZS) *</label>
                        <input type="number" name="min_withdrawal" class="form-input-modern" value="{{ old('min_withdrawal', 5000) }}" required min="0">
                        @error('min_withdrawal')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Withdrawal Fee (%) *</label>
                        <input type="number" name="withdrawal_fee_percent" class="form-input-modern" value="{{ old('withdrawal_fee_percent', 10) }}" required min="0" max="100" step="0.1">
                        @error('withdrawal_fee_percent')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Processing Days *</label>
                        <input type="number" name="processing_days" class="form-input-modern" value="{{ old('processing_days', 3) }}" required min="0" max="30">
                        @error('processing_days')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Display Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Badge Color</label>
                        <input type="text" name="badge_color" class="form-input-modern" value="{{ old('badge_color', '#10b981') }}" placeholder="#10b981">
                        @error('badge_color')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Icon</label>
                        <input type="text" name="icon" class="form-input-modern" value="{{ old('icon', 'crown') }}" placeholder="crown">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Lucide icon name</p>
                        @error('icon')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Sort Order *</label>
                        <input type="number" name="sort_order" class="form-input-modern" value="{{ old('sort_order', 0) }}" required min="0">
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
                    Create Plan
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
