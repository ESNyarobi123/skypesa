@extends('layouts.app')

@section('title', 'Ongeza Kazi')
@section('page-title', 'Ongeza Kazi Mpya')
@section('page-subtitle', 'Ongeza task kwa watumiaji')

@section('content')
<div style="max-width: 600px;">
    <div class="card card-body">
        <form action="{{ route('admin.tasks.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Jina la Kazi</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Mfano: Tazama Tangazo la Bidhaa">
            </div>
            
            <div class="form-group">
                <label class="form-label">Maelezo</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Maelezo mafupi ya kazi...">{{ old('description') }}</textarea>
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Aina</label>
                    <select name="type" class="form-control" required>
                        <option value="view_ad" {{ old('type') == 'view_ad' ? 'selected' : '' }}>View Ad</option>
                        <option value="share_link" {{ old('type') == 'share_link' ? 'selected' : '' }}>Share Link</option>
                        <option value="survey" {{ old('type') == 'survey' ? 'selected' : '' }}>Survey</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Provider</label>
                    <select name="provider" class="form-control" required>
                        <option value="monetag" {{ old('provider') == 'monetag' ? 'selected' : '' }}>Monetag</option>
                        <option value="adsterra" {{ old('provider') == 'adsterra' ? 'selected' : '' }}>Adsterra</option>
                        <option value="custom" {{ old('provider') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">URL (Smartlink au Direct Link)</label>
                <input type="url" name="url" class="form-control" value="{{ old('url') }}" required placeholder="https://...">
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Muda (sekunde)</label>
                    <input type="number" name="duration_seconds" class="form-control" value="{{ old('duration_seconds', 30) }}" min="5" max="300" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reward Override (TZS)</label>
                    <input type="number" name="reward_override" class="form-control" value="{{ old('reward_override') }}" min="0" placeholder="Acha tupu kutumia plan default">
                </div>
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4);">
                <div class="form-group">
                    <label class="form-label">Daily Limit (per user)</label>
                    <input type="number" name="daily_limit" class="form-control" value="{{ old('daily_limit') }}" min="1" placeholder="Acha tupu = unlimited">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Total Limit</label>
                    <input type="number" name="total_limit" class="form-control" value="{{ old('total_limit') }}" min="1" placeholder="Acha tupu = unlimited">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
            </div>
            
            <div class="grid grid-2" style="gap: var(--space-4); margin-bottom: var(--space-6);">
                <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="accent-color: var(--primary);">
                    <span>Active</span>
                </label>
                
                <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="accent-color: var(--primary);">
                    <span>Featured</span>
                </label>
            </div>
            
            <div class="flex gap-4">
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary" style="flex: 1;">Ghairi</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="plus"></i>
                    Ongeza Kazi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
