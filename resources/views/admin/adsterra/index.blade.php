@extends('layouts.app')

@section('title', 'Adsterra Integration')
@section('page-title', 'Adsterra Integration')
@section('page-subtitle', 'Simamia muunganisho na Adsterra API')

@section('content')
<!-- Connection Status -->
<div class="card mb-8" style="padding: var(--space-6); {{ $connectionTest['success'] ? 'background: rgba(16, 185, 129, 0.1); border-color: var(--success);' : 'background: rgba(239, 68, 68, 0.1); border-color: var(--error);' }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($connectionTest['success'])
            <div style="width: 50px; height: 50px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check" style="color: white;"></i>
            </div>
            <div>
                <h4 style="color: var(--success);">Umeunganishwa na Adsterra!</h4>
                <p style="font-size: 0.875rem;">Domains: {{ $connectionTest['domains_count'] ?? 0 }}</p>
            </div>
            @else
            <div style="width: 50px; height: 50px; background: var(--error); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="color: white;"></i>
            </div>
            <div>
                <h4 style="color: var(--error);">Haujaunganishwa!</h4>
                <p style="font-size: 0.875rem;">{{ $connectionTest['message'] ?? 'API key imekosea' }}</p>
            </div>
            @endif
        </div>
        
        <form action="{{ route('admin.adsterra.refresh') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary">
                <i data-lucide="refresh-cw"></i>
                Sasisha Data
            </button>
        </form>
    </div>
</div>

@if($connectionTest['success'])
<!-- Quick Actions -->
<div class="grid grid-3 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ count($domains) }}</div>
        <div class="stat-label">Domains</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ count($placements) }}</div>
        <div class="stat-label">Placements Zote</div>
    </div>
    <div class="stat-card" style="border-color: var(--primary);">
        <div class="stat-value" style="color: var(--primary);">{{ count($taskablePlacements) }}</div>
        <div class="stat-label">Direct Links (Taskable)</div>
    </div>
</div>

<!-- Import Actions -->
<div class="card card-body mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4>Ingiza Placements Kama Tasks</h4>
            <p style="font-size: 0.875rem; color: var(--text-muted);">
                Unazo placements {{ count($taskablePlacements) }} zenye direct URLs zinazoweza kuwa tasks
            </p>
        </div>
        <div class="flex gap-4">
            <form action="{{ route('admin.adsterra.sync') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i data-lucide="refresh-cw"></i>
                    Sync URLs
                </button>
            </form>
            <form action="{{ route('admin.adsterra.import-all') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Ingiza placements zote kama tasks?')">
                    <i data-lucide="download"></i>
                    Import Zote
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Existing Adsterra Tasks -->
@if($existingTasks->count() > 0)
<h3 class="mb-4">Tasks za Adsterra Zilizo Mfumo ({{ $existingTasks->count() }})</h3>
<div class="card mb-8">
    <table class="table">
        <thead>
            <tr>
                <th>Task</th>
                <th>URL</th>
                <th>Hali</th>
                <th>Completions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($existingTasks as $task)
            <tr>
                <td>
                    <div style="font-weight: 500;">{{ $task->title }}</div>
                    @if($task->is_featured)
                    <span class="badge badge-warning">FEATURED</span>
                    @endif
                </td>
                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <a href="{{ $task->url }}" target="_blank" style="font-size: 0.75rem;">
                        {{ Str::limit($task->url, 50) }}
                    </a>
                </td>
                <td>
                    @if($task->is_active)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-error">Inactive</span>
                    @endif
                </td>
                <td>{{ number_format($task->completions_count) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Available Placements -->
<h3 class="mb-4">Placements Zinazopatikana ({{ count($taskablePlacements) }})</h3>
@if(count($taskablePlacements) > 0)
<div class="grid grid-2" style="gap: var(--space-4);">
    @foreach($taskablePlacements as $placement)
    @php
        $isImported = $existingTasks->contains(function($task) use ($placement) {
            $requirements = $task->requirements;
            return ($requirements['adsterra_placement_id'] ?? null) == $placement['id'];
        });
    @endphp
    <div class="card card-body {{ $isImported ? 'opacity-50' : '' }}">
        <div class="flex justify-between items-start">
            <div>
                <h5 class="mb-1">{{ $placement['title'] ?? $placement['alias'] }}</h5>
                <p style="font-size: 0.75rem; color: var(--text-muted);">ID: {{ $placement['id'] }}</p>
                @if(isset($placement['domain_id']))
                <p style="font-size: 0.75rem; color: var(--text-muted);">Domain: {{ $placement['domain_id'] }}</p>
                @endif
            </div>
            
            @if($isImported)
            <span class="badge badge-success">Imeingizwa</span>
            @else
            <form action="{{ route('admin.adsterra.import-placement') }}" method="POST">
                @csrf
                <input type="hidden" name="placement_id" value="{{ $placement['id'] }}">
                <input type="hidden" name="direct_url" value="{{ $placement['direct_url'] }}">
                <input type="hidden" name="title" value="{{ app(App\Services\AdsterraService::class)->placementToTaskData($placement)['title'] }}">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i data-lucide="plus"></i>
                    Import
                </button>
            </form>
            @endif
        </div>
        
        <div style="margin-top: var(--space-3); padding: var(--space-2); background: var(--bg-dark); border-radius: var(--radius-md); overflow: hidden;">
            <code style="font-size: 0.7rem; word-break: break-all;">{{ $placement['direct_url'] }}</code>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card card-body text-center">
    <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
    <h4 class="mb-2">Hakuna Placements za Direct URL</h4>
    <p>Unda placements na direct URLs kwenye Adsterra dashboard yako.</p>
</div>
@endif

<!-- All Domains -->
@if(count($domains) > 0)
<h3 class="mt-8 mb-4">Domains Zako ({{ count($domains) }})</h3>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Domain</th>
            </tr>
        </thead>
        <tbody>
            @foreach($domains as $domain)
            <tr>
                <td>{{ $domain['id'] }}</td>
                <td>{{ $domain['title'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@else
<!-- Not Connected -->
<div class="card card-body text-center">
    <i data-lucide="unplug" style="width: 64px; height: 64px; color: var(--text-muted); margin: 0 auto var(--space-6);"></i>
    <h3 class="mb-2">Hakuna Muunganisho</h3>
    <p class="mb-6">Weka ADSTERRA_API_KEY kwenye faili ya .env yako</p>
    
    <div class="card" style="background: var(--bg-dark); padding: var(--space-4); text-align: left; max-width: 500px; margin: 0 auto;">
        <code style="font-size: 0.875rem;">
            ADSTERRA_API_KEY=af010595347dd08420ff2070362e0d1e
        </code>
    </div>
    
    <p class="mt-6" style="font-size: 0.875rem; color: var(--text-muted);">
        Baada ya kuweka API key, endesha <code>php artisan config:clear</code> na usasishe page hii.
    </p>
</div>
@endif
@endsection
