@extends('layouts.app')

@section('title', 'Historia ya Surveys')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="page-header" style="margin-bottom: var(--space-6);">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="page-title">📜 Historia ya Surveys</h1>
                <p class="page-subtitle">Surveys zote ulizokamilisha</p>
            </div>
            <a href="{{ route('surveys.index') }}" class="btn btn-primary">
                ← Rudi Surveys
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-3 mb-6" style="gap: var(--space-4);">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_completed'] ?? 0 }}</div>
            <div class="stat-label">Jumla Surveys</div>
        </div>
        <div class="stat-card" style="border-color: var(--success);">
            <div class="stat-value" style="color: var(--success);">TZS {{ number_format($stats['total_earned'] ?? 0, 0) }}</div>
            <div class="stat-label">Jumla Umepata</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['today_completed'] ?? 0 }}</div>
            <div class="stat-label">Surveys Leo</div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card">
        <div class="card-body" style="padding-bottom: 0;">
            <h4>Surveys Zilizokamilika</h4>
        </div>
        
        @if($completions->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Aina</th>
                    <th>Muda</th>
                    <th>Malipo</th>
                    <th>Status</th>
                    <th>Tarehe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completions as $completion)
                <tr>
                    <td>
                        <code style="font-size: 0.75rem;">#{{ $completion->survey_id }}</code>
                    </td>
                    <td>
                        @php
                            $typeColors = [
                                'short' => 'badge-info',
                                'medium' => 'badge-primary',
                                'long' => 'badge-warning',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$completion->survey_type] ?? '' }}">
                            {{ $completion->getTypeLabel() }}
                        </span>
                    </td>
                    <td>{{ $completion->loi }} min</td>
                    <td style="font-weight: 700; color: var(--success);">
                        TZS {{ number_format($completion->user_reward, 0) }}
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'badge-warning',
                                'completed' => 'badge-info',
                                'credited' => 'badge-success',
                                'rejected' => 'badge-error',
                                'reversed' => 'badge-error',
                            ];
                        @endphp
                        <span class="badge {{ $statusColors[$completion->status] ?? '' }}">
                            {{ $completion->getStatusLabel() }}
                        </span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.875rem;">
                        {{ $completion->created_at->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($completions->hasPages())
        <div class="card-body">
            {{ $completions->links() }}
        </div>
        @endif
        
        @else
        <div class="card-body text-center" style="padding: var(--space-12);">
            <div style="font-size: 4rem; margin-bottom: var(--space-4);">📊</div>
            <h3 style="margin-bottom: var(--space-2);">Hakuna Historia</h3>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto var(--space-6);">
                Hujakamilisha survey yoyote bado. Anza sasa kupata pesa!
            </p>
            <a href="{{ route('surveys.index') }}" class="btn btn-primary">
                Anza Survey
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
