@extends('layouts.admin')

@section('title', 'Edit App Version')
@section('page-title', 'Edit App Version')
@section('page-subtitle', 'Edit details for version {{ $appVersion->version_code }}')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('admin.app-versions.update', $appVersion) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group-modern">
                <label class="form-label-modern">Version Code</label>
                <input type="text" name="version_code" class="form-input-modern" required value="{{ old('version_code', $appVersion->version_code) }}">
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Version Name</label>
                <input type="text" name="version_name" class="form-input-modern" required value="{{ old('version_name', $appVersion->version_name) }}">
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">APK File (Leave empty to keep current)</label>
                <input type="file" name="apk_file" class="form-input-modern" accept=".apk,.zip">
                @if($appVersion->apk_path)
                    <small class="text-success">Current file: {{ basename($appVersion->apk_path) }}</small>
                @endif
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">OR Manual Filename (Update manually)</label>
                <input type="text" name="manual_file_name" class="form-input-modern" placeholder="e.g. app-release.apk" value="{{ old('manual_file_name') }}">
                <small class="text-muted">Upload file to <code>public/storage/apks/</code> and enter name here to update.</small>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Description</label>
                <textarea name="description" class="form-input-modern" rows="4">{{ old('description', $appVersion->description) }}</textarea>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Features (One per line)</label>
                <textarea name="features" class="form-input-modern" rows="5">{{ old('features', $appVersion->features ? implode("\n", $appVersion->features) : '') }}</textarea>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Add More Screenshots</label>
                <input type="file" name="screenshots[]" class="form-input-modern" accept="image/*" multiple>
                @if($appVersion->screenshots)
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        @foreach($appVersion->screenshots as $screenshot)
                            <img src="{{ Storage::url($screenshot) }}" alt="Screenshot" style="height: 60px; border-radius: 4px;">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="form-group-modern">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $appVersion->is_active) ? 'checked' : '' }}>
                    <span class="text-white">Set as Active Version</span>
                </label>
            </div>

            <div class="form-group-modern">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="force_update" value="1" {{ old('force_update', $appVersion->force_update) ? 'checked' : '' }}>
                    <span class="text-white">Force Update</span>
                </label>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('admin.app-versions.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Version</button>
            </div>
        </form>
    </div>
</div>
@endsection
