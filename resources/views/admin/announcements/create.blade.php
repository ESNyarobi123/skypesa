@extends('layouts.admin')

@section('title', 'Create Announcement')
@section('page-title', 'Create Announcement')
@section('page-subtitle', 'Broadcast a new message or video to all users')

@section('content')
<div style="max-width: 800px;">
    <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data" id="announcementForm">
        @csrf

        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="edit-3" style="width: 20px; height: 20px; display: inline; color: var(--primary);"></i>
                        Announcement Details
                    </div>
                    <div class="chart-subtitle">Create text or video announcement for users</div>
                </div>
            </div>

            <div style="display: grid; gap: 1.25rem;">
                <!-- Media Type Selector -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Announcement Type *</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--bg-elevated); border-radius: var(--radius-lg); cursor: pointer; border: 2px solid transparent; transition: all 0.2s;" class="media-type-option" data-type="text">
                            <input type="radio" name="media_type" value="text" {{ old('media_type', 'text') === 'text' ? 'checked' : '' }} style="display: none;">
                            <span style="font-size: 1.5rem;">📝</span>
                            <div>
                                <div style="font-weight: 600; color: white;">Text</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Title + Message</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--bg-elevated); border-radius: var(--radius-lg); cursor: pointer; border: 2px solid transparent; transition: all 0.2s;" class="media-type-option" data-type="video">
                            <input type="radio" name="media_type" value="video" {{ old('media_type') === 'video' ? 'checked' : '' }} style="display: none;">
                            <span style="font-size: 1.5rem;">🎬</span>
                            <div>
                                <div style="font-weight: 600; color: white;">Video</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">10-15 seconds MP4</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Title *</label>
                    <input type="text" name="title" class="form-input-modern" 
                           value="{{ old('title') }}" 
                           placeholder="e.g., System Update Notice" required>
                    @error('title')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Text Content Section -->
                <div class="form-group-modern" id="textContentSection">
                    <label class="form-label-modern">Message Body <span id="bodyRequired">*</span></label>
                    <textarea name="body" class="form-input-modern" rows="5" 
                              placeholder="Write your announcement message here..." id="bodyInput">{{ old('body') }}</textarea>
                    @error('body')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Video Upload Section -->
                <div class="form-group-modern" id="videoUploadSection" style="display: none;">
                    <label class="form-label-modern">Video File * <span style="font-weight: 400; color: var(--text-muted);">(MP4, max 15MB, 10-15 seconds)</span></label>
                    
                    <div id="videoDropZone" style="border: 2px dashed rgba(255,255,255,0.2); border-radius: var(--radius-lg); padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s;">
                        <div id="videoPreviewContainer" style="display: none; margin-bottom: 1rem;">
                            <video id="videoPreview" style="max-width: 100%; max-height: 200px; border-radius: var(--radius-md);" controls></video>
                            <div id="videoDurationDisplay" style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary);"></div>
                        </div>
                        <div id="uploadPlaceholder">
                            <i data-lucide="film" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Click or drag video file here</p>
                            <p style="font-size: 0.7rem; color: var(--text-muted);">MP4 format • Max 15MB • 10-15 seconds recommended</p>
                        </div>
                        <input type="file" name="video" id="videoInput" accept="video/mp4" style="display: none;">
                        <input type="hidden" name="video_duration" id="videoDuration" value="15">
                    </div>
                    
                    @error('video')
                    <p style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                    
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <span style="color: var(--warning);">⚠️</span> Video announcements auto-play in user popups. Keep them short and impactful!
                    </p>
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

<style>
    .media-type-option.selected {
        border-color: var(--primary) !important;
        background: rgba(16, 185, 129, 0.1) !important;
    }
    
    #videoDropZone:hover {
        border-color: var(--primary);
        background: rgba(16, 185, 129, 0.05);
    }
    
    #videoDropZone.dragover {
        border-color: var(--primary);
        background: rgba(16, 185, 129, 0.1);
    }
</style>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Media type toggle
    const mediaTypeOptions = document.querySelectorAll('.media-type-option');
    const textSection = document.getElementById('textContentSection');
    const videoSection = document.getElementById('videoUploadSection');
    const bodyInput = document.getElementById('bodyInput');
    const bodyRequired = document.getElementById('bodyRequired');
    
    function updateMediaType(type) {
        mediaTypeOptions.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.dataset.type === type) {
                opt.classList.add('selected');
                opt.querySelector('input').checked = true;
            }
        });
        
        if (type === 'video') {
            videoSection.style.display = 'block';
            bodyRequired.style.display = 'none';
            bodyInput.removeAttribute('required');
        } else {
            videoSection.style.display = 'none';
            bodyRequired.style.display = 'inline';
            bodyInput.setAttribute('required', 'required');
        }
    }
    
    mediaTypeOptions.forEach(opt => {
        opt.addEventListener('click', () => updateMediaType(opt.dataset.type));
    });
    
    // Initialize
    updateMediaType(document.querySelector('input[name="media_type"]:checked')?.value || 'text');
    
    // Video upload handling
    const videoDropZone = document.getElementById('videoDropZone');
    const videoInput = document.getElementById('videoInput');
    const videoPreview = document.getElementById('videoPreview');
    const videoPreviewContainer = document.getElementById('videoPreviewContainer');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const videoDuration = document.getElementById('videoDuration');
    const videoDurationDisplay = document.getElementById('videoDurationDisplay');
    
    videoDropZone.addEventListener('click', () => videoInput.click());
    
    videoDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        videoDropZone.classList.add('dragover');
    });
    
    videoDropZone.addEventListener('dragleave', () => {
        videoDropZone.classList.remove('dragover');
    });
    
    videoDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        videoDropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type === 'video/mp4') {
            handleVideoFile(file);
        }
    });
    
    videoInput.addEventListener('change', (e) => {
        if (e.target.files[0]) {
            handleVideoFile(e.target.files[0]);
        }
    });
    
    function handleVideoFile(file) {
        // Check file size (15MB max)
        if (file.size > 15 * 1024 * 1024) {
            alert('Video file too large! Maximum size is 15MB.');
            return;
        }
        
        const url = URL.createObjectURL(file);
        videoPreview.src = url;
        videoPreviewContainer.style.display = 'block';
        uploadPlaceholder.style.display = 'none';
        
        // Get video duration
        videoPreview.addEventListener('loadedmetadata', () => {
            const duration = Math.round(videoPreview.duration);
            videoDuration.value = duration;
            videoDurationDisplay.textContent = `Duration: ${duration} seconds`;
            
            if (duration > 15) {
                videoDurationDisplay.innerHTML = `<span style="color: var(--warning);">⚠️ Duration: ${duration}s (recommended: 10-15s)</span>`;
            } else {
                videoDurationDisplay.innerHTML = `<span style="color: var(--success);">✅ Duration: ${duration} seconds</span>`;
            }
        });
        
        // Update file input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        videoInput.files = dataTransfer.files;
    }
</script>
@endpush
