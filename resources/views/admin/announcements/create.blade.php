@extends('layouts.admin')

@section('title', 'Create Announcement')
@section('page-title', 'Create Announcement')
@section('page-subtitle', 'Broadcast a new message to all users')

@section('content')
<div style="max-width: 800px;">
    <form action="{{ route('admin.announcements.store') }}" method="POST">
        @csrf

        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="edit-3" style="width: 20px; height: 20px; display: inline; color: var(--primary);"></i>
                        Announcement Details
                    </div>
                    <div class="chart-subtitle">Write your message for users</div>
                </div>
            </div>

            <div style="display: grid; gap: 1.25rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Title *</label>
                    <input type="text" name="title" class="form-input-modern" 
                           value="{{ old('title') }}" 
                           placeholder="e.g., System Update Notice" required>
                    @error('title')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Message Body *</label>
                    <textarea name="body" class="form-input-modern" rows="5" 
                              placeholder="Write your announcement message here..." required>{{ old('body') }}</textarea>
                    @error('body')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Type</label>
                        <select name="type" class="form-input-modern form-select-modern">
                            <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>ℹ️ Info</option>
                            <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>✅ Success</option>
                            <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>⚠️ Warning</option>
                            <option value="urgent" {{ old('type') === 'urgent' ? 'selected' : '' }}>🚨 Urgent</option>
                        </select>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Icon (Lucide)</label>
                        <input type="text" name="icon" class="form-input-modern" 
                               value="{{ old('icon') }}" 
                               placeholder="e.g., bell, gift, alert-circle">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">
                            <a href="https://lucide.dev/icons/" target="_blank" style="color: var(--primary);">Browse icons →</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="settings-2" style="width: 20px; height: 20px; display: inline; color: #8b5cf6;"></i>
                        Display Settings
                    </div>
                    <div class="chart-subtitle">Control how and when users see this</div>
                </div>
            </div>

            <div style="display: grid; gap: 1.25rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" checked
                                   style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <div>
                                <span style="color: white; font-size: 0.9rem;">Active</span>
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">Show to users immediately</p>
                            </div>
                        </label>
                    </div>

                    <div class="form-group-modern">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="show_as_popup" checked
                                   style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <div>
                                <span style="color: white; font-size: 0.9rem;">Show as Popup</span>
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">Display as dialog on dashboard</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Max Popup Views</label>
                    <input type="number" name="max_popup_views" class="form-input-modern" 
                           value="{{ old('max_popup_views', 2) }}" 
                           min="1" max="10" style="max-width: 150px;">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">
                        How many times user sees popup before it goes to notification history
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            Start Date (Optional)
                            <span style="font-weight: 400; color: var(--text-muted);">— EAT Timezone</span>
                        </label>
                        <input type="datetime-local" name="starts_at" class="form-input-modern" 
                               value="{{ old('starts_at') }}">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">
                            Leave empty to start immediately. Current time: {{ now()->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            End Date (Optional)
                            <span style="font-weight: 400; color: var(--text-muted);">— EAT Timezone</span>
                        </label>
                        <input type="datetime-local" name="expires_at" class="form-input-modern" 
                               value="{{ old('expires_at') }}">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Leave empty for no expiry</p>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; gap: 1rem;">
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                Send Announcement
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
