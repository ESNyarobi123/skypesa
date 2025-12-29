@extends('layouts.admin')

@section('title', 'Tuma Push Notification')
@section('page-title', 'Tuma Push Notification')
@section('page-subtitle', 'Tuma arifa kwa watumiaji wa app')

@section('content')
<div class="create-notification-page">
    @if(!$isConfigured)
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.2); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-triangle" style="color: var(--error); width: 24px; height: 24px;"></i>
        </div>
        <div>
            <h4 style="color: var(--error); font-weight: 600; margin-bottom: 0.25rem;">Firebase Haijasanidiwa</h4>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Push notifications hazitafanya kazi bila Firebase.</p>
        </div>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Form -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Compose Notification</h3>
                    <p class="chart-subtitle">Andika ujumbe wako wa push notification</p>
                </div>
            </div>
            
            <form action="{{ route('admin.push-notifications.store') }}" method="POST" id="notificationForm">
                @csrf
                
                <!-- Title -->
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i data-lucide="type" style="width: 14px; height: 14px; display: inline;"></i>
                        Title *
                    </label>
                    <input type="text" 
                           name="title" 
                           class="form-input-modern" 
                           placeholder="Mfano: 🎉 Bonus Mpya!" 
                           value="{{ old('title') }}"
                           maxlength="100"
                           required>
                    @error('title')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Body -->
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i data-lucide="message-square" style="width: 14px; height: 14px; display: inline;"></i>
                        Message *
                    </label>
                    <textarea name="body" 
                              class="form-input-modern" 
                              rows="4" 
                              placeholder="Andika ujumbe wako hapa..."
                              maxlength="500"
                              required>{{ old('body') }}</textarea>
                    @error('body')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Target Type -->
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i data-lucide="users" style="width: 14px; height: 14px; display: inline;"></i>
                        Target Audience *
                    </label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.1); border-radius: 12px; cursor: pointer; flex: 1; min-width: 150px;" class="target-option" data-target="all">
                            <input type="radio" name="target_type" value="all" {{ old('target_type', 'all') === 'all' ? 'checked' : '' }}>
                            <div>
                                <div style="font-weight: 600; color: white;">Wote ({{ $tokenStats['active_users_with_tokens'] }})</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Tuma kwa watumiaji wote</div>
                            </div>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.1); border-radius: 12px; cursor: pointer; flex: 1; min-width: 150px;" class="target-option" data-target="segment">
                            <input type="radio" name="target_type" value="segment" {{ old('target_type') === 'segment' ? 'checked' : '' }}>
                            <div>
                                <div style="font-weight: 600; color: white;">Segment</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Chagua kundi maalum</div>
                            </div>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.1); border-radius: 12px; cursor: pointer; flex: 1; min-width: 150px;" class="target-option" data-target="specific">
                            <input type="radio" name="target_type" value="specific" {{ old('target_type') === 'specific' ? 'checked' : '' }}>
                            <div>
                                <div style="font-weight: 600; color: white;">Watumiaji Maalum</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Chagua manually</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Segment Selection (hidden by default) -->
                <div class="form-group-modern" id="segmentGroup" style="display: none;">
                    <label class="form-label-modern">Chagua Segment</label>
                    <select name="segment" class="form-input-modern form-select-modern">
                        @foreach($segments as $key => $segment)
                        <option value="{{ $key }}" {{ old('segment') === $key ? 'selected' : '' }}>
                            {{ $segment['label'] }} ({{ $segment['count'] }} watumiaji)
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Specific Users Selection (hidden by default) -->
                <div class="form-group-modern" id="usersGroup" style="display: none;">
                    <label class="form-label-modern">Chagua Watumiaji</label>
                    <div style="max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 0.5rem;">
                        @foreach($users as $user)
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border-radius: 6px; cursor: pointer;" class="user-checkbox-label">
                            <input type="checkbox" name="target_users[]" value="{{ $user->id }}" {{ in_array($user->id, old('target_users', [])) ? 'checked' : '' }}>
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                <span style="font-size: 0.875rem; color: white;">{{ $user->name }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</span>
                                <span style="margin-left: auto; font-size: 0.65rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                                    {{ $user->device_type }}
                                </span>
                            </div>
                        </label>
                        @endforeach
                        
                        @if($users->isEmpty())
                        <p style="text-align: center; color: var(--text-muted); padding: 1rem;">
                            Hakuna watumiaji wenye FCM tokens.
                        </p>
                        @endif
                    </div>
                </div>
                
                <!-- Optional: Image URL -->
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i data-lucide="image" style="width: 14px; height: 14px; display: inline;"></i>
                        Image URL (Optional)
                    </label>
                    <input type="url" 
                           name="image_url" 
                           class="form-input-modern" 
                           placeholder="https://example.com/image.png" 
                           value="{{ old('image_url') }}">
                    <span style="font-size: 0.7rem; color: var(--text-muted);">Picha itaonyeshwa kwenye notification</span>
                </div>
                
                <!-- Optional: Action URL -->
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i data-lucide="link" style="width: 14px; height: 14px; display: inline;"></i>
                        Action URL (Optional)
                    </label>
                    <input type="text" 
                           name="action_url" 
                           class="form-input-modern" 
                           placeholder="/dashboard au /tasks" 
                           value="{{ old('action_url') }}">
                    <span style="font-size: 0.7rem; color: var(--text-muted);">Deep link ndani ya app</span>
                </div>
                
                <!-- Submit -->
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem; background: var(--gradient-primary); border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                        Tuma Notification
                    </button>
                    <a href="{{ route('admin.push-notifications.index') }}" style="padding: 1rem 2rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: var(--text-secondary); font-weight: 500; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Preview & Info -->
        <div>
            <!-- Phone Preview -->
            <div class="chart-card" style="margin-bottom: 1.5rem;">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">📱 Preview</h3>
                        <p class="chart-subtitle">Jinsi notification itakavyoonekana</p>
                    </div>
                </div>
                
                <div style="background: #1a1a1a; border-radius: 16px; padding: 1rem; border: 2px solid #333;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 12px;">
                        <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="zap" style="width: 20px; height: 20px; color: white;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-size: 0.7rem; font-weight: 600; color: var(--primary);">SKYpesa</span>
                                <span style="font-size: 0.65rem; color: var(--text-muted);">now</span>
                            </div>
                            <div id="previewTitle" style="font-size: 0.8rem; font-weight: 600; color: white; margin-bottom: 0.25rem;">
                                🎉 Title ya Notification
                            </div>
                            <div id="previewBody" style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                                Ujumbe wako utaonekana hapa...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">📊 Token Stats</h3>
                        <p class="chart-subtitle">Vifaa vilivyosajiliwa</p>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 10px;">
                        <span style="color: var(--text-secondary);">Android</span>
                        <span style="color: var(--success); font-weight: 600;">{{ $tokenStats['android_tokens'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(59, 130, 246, 0.1); border-radius: 10px;">
                        <span style="color: var(--text-secondary);">iOS</span>
                        <span style="color: var(--info); font-weight: 600;">{{ $tokenStats['ios_tokens'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(139, 92, 246, 0.1); border-radius: 10px;">
                        <span style="color: var(--text-secondary);">Web</span>
                        <span style="color: #8b5cf6; font-weight: 600;">{{ $tokenStats['web_tokens'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: 600;">Jumla</span>
                        <span style="color: white; font-weight: 700;">{{ $tokenStats['total_tokens'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.target-option:has(input:checked) {
    border-color: var(--primary) !important;
    background: rgba(16, 185, 129, 0.1) !important;
}

.user-checkbox-label:hover {
    background: rgba(255,255,255,0.05);
}

.user-checkbox-label:has(input:checked) {
    background: rgba(16, 185, 129, 0.1);
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetRadios = document.querySelectorAll('input[name="target_type"]');
    const segmentGroup = document.getElementById('segmentGroup');
    const usersGroup = document.getElementById('usersGroup');
    const titleInput = document.querySelector('input[name="title"]');
    const bodyInput = document.querySelector('textarea[name="body"]');
    const previewTitle = document.getElementById('previewTitle');
    const previewBody = document.getElementById('previewBody');
    
    // Handle target type changes
    function updateTargetVisibility() {
        const selected = document.querySelector('input[name="target_type"]:checked')?.value;
        segmentGroup.style.display = selected === 'segment' ? 'block' : 'none';
        usersGroup.style.display = selected === 'specific' ? 'block' : 'none';
    }
    
    targetRadios.forEach(radio => {
        radio.addEventListener('change', updateTargetVisibility);
    });
    
    // Initialize
    updateTargetVisibility();
    
    // Live preview
    function updatePreview() {
        previewTitle.textContent = titleInput.value || '🎉 Title ya Notification';
        previewBody.textContent = bodyInput.value || 'Ujumbe wako utaonekana hapa...';
    }
    
    titleInput.addEventListener('input', updatePreview);
    bodyInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>
@endpush
@endsection
