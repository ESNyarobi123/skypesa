@extends('layouts.admin')

@section('title', 'Add Link to ' . $linkpool->name)
@section('page-title', 'Add Link')
@section('page-subtitle', 'Add a new link to ' . $linkpool->name)

@section('content')
<div class="animate-in" style="max-width: 600px;">
    <!-- Back Link -->
    <a href="{{ route('admin.linkpools.show', $linkpool) }}" 
       style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); text-decoration: none; margin-bottom: 1.5rem; font-size: 0.875rem;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to {{ $linkpool->name }}
    </a>

    <div class="chart-card">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $linkpool->color }}20; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="{{ $linkpool->icon }}" style="width: 24px; height: 24px; color: {{ $linkpool->color }};"></i>
            </div>
            <div>
                <h3 style="color: white; font-weight: 600;">Adding to: {{ $linkpool->name }}</h3>
                <p style="color: var(--text-muted); font-size: 0.8rem;">Links in this pool rotate randomly</p>
            </div>
        </div>

        <form action="{{ route('admin.linkpools.links.store', $linkpool) }}" method="POST">
            @csrf
            
            <!-- Link Name -->
            <div class="form-group-modern">
                <label class="form-label-modern">Link Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" 
                       class="form-input-modern" placeholder="e.g., Adsterra Smartlink #1" required>
                @error('name')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <!-- URL -->
            <div class="form-group-modern">
                <label class="form-label-modern">Link URL *</label>
                <input type="url" name="url" value="{{ old('url') }}" 
                       class="form-input-modern" placeholder="https://..." required>
                @error('url')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Provider -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Provider *</label>
                    <select name="provider" class="form-input-modern form-select-modern" required>
                        <option value="">Select...</option>
                        <option value="adsterra" {{ old('provider') === 'adsterra' ? 'selected' : '' }}>Adsterra</option>
                        <option value="monetag" {{ old('provider') === 'monetag' ? 'selected' : '' }}>Monetag</option>
                        <option value="propellerads" {{ old('provider') === 'propellerads' ? 'selected' : '' }}>PropellerAds</option>
                        <option value="hilltopads" {{ old('provider') === 'hilltopads' ? 'selected' : '' }}>HilltopAds</option>
                        <option value="other" {{ old('provider') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Weight -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Weight</label>
                    <input type="number" name="weight" value="{{ old('weight', 1) }}" 
                           class="form-input-modern" min="1" max="100">
                    <p style="color: var(--text-muted); font-size: 0.7rem; margin-top: 0.25rem;">
                        Higher = more likely to be selected
                    </p>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group-modern">
                <label class="form-label-modern">Notes (optional)</label>
                <textarea name="notes" class="form-input-modern" rows="2" 
                          placeholder="Internal notes about this link...">{{ old('notes') }}</textarea>
            </div>

            <!-- Active Status -->
            <div class="form-group-modern">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           style="width: 20px; height: 20px; accent-color: var(--primary);">
                    <span style="color: white; font-weight: 500;">Link is Active</span>
                </label>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 1.5rem 0;">

            <!-- Actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.linkpools.show', $linkpool) }}" 
                   style="padding: 0.875rem 1.5rem; background: rgba(255,255,255,0.05); border-radius: 10px; color: var(--text-secondary); text-decoration: none; font-weight: 500;">
                    Cancel
                </a>
                <button type="submit" 
                        style="padding: 0.875rem 2rem; background: var(--gradient-primary); border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    Add Link
                </button>
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
