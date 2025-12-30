@extends('layouts.admin')

@section('title', 'Upload App Version')
@section('page-title', 'Upload App Version')
@section('page-subtitle', 'Upload a new APK version for users')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('admin.app-versions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-modern">
                <label class="form-label-modern">Version Code (e.g., 1.0.0)</label>
                <input type="text" name="version_code" class="form-input-modern" required value="{{ old('version_code') }}">
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Version Name (e.g., Initial Release)</label>
                <input type="text" name="version_name" class="form-input-modern" required value="{{ old('version_name') }}">
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">APK File</label>
                <input type="file" name="apk_file" class="form-input-modern" accept=".apk,.zip" required>
                <small class="text-muted">Max size: 100MB</small>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Description</label>
                <textarea name="description" class="form-input-modern" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Features (One per line)</label>
                <textarea name="features" class="form-input-modern" rows="5" placeholder="Amazing Feature 1&#10;Incredible Feature 2">{{ old('features') }}</textarea>
            </div>

            <div class="form-group-modern">
                <label class="form-label-modern">Screenshots (Select multiple)</label>
                <input type="file" name="screenshots[]" class="form-input-modern" accept="image/*" multiple>
            </div>

            <div class="form-group-modern">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span class="text-white">Set as Active Version</span>
                </label>
            </div>

            <div class="form-group-modern">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="force_update" value="1">
                    <span class="text-white">Force Update (Users must update to continue)</span>
                </label>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('admin.app-versions.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Upload Version</button>
            </div>
        </form>
    </div>
</div>
@endsection
