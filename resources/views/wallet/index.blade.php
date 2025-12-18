@extends('layouts.app')

@section('title', 'Wallet')
@section('page-title', 'Wallet Yako')
@section('page-subtitle', 'Angalia salio na historia ya shughuli')

@section('content')
<!-- Wallet Overview -->
<div class="grid grid-3 mb-8">
    <!-- Main Balance -->
    <div class="wallet-card">
        <div style="position: relative; z-index: 10;">
            <div class="wallet-label">Salio Lako</div>
            <div class="wallet-balance">TZS {{ number_format($wallet->balance ?? 0, 0) }}</div>
            <div class="flex gap-4 mt-4">
                <a href="{{ route('withdrawals.create') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                    <i data-lucide="send"></i>
                    Toa Pesa
                </a>
            </div>
        </div>
    </div>
    
    <!-- Total Earned -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.2); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="trending-up" style="color: var(--success);"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted);">Jumla Umepata</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">TZS {{ number_format($wallet->total_earned ?? 0, 0) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Total Withdrawn -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.2); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="arrow-up-right" style="color: var(--error);"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted);">Jumla Umetoa</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--error);">TZS {{ number_format($wallet->total_withdrawn ?? 0, 0) }}</div>
            </div>
        </div>
    </div>
</div>

@if($wallet->pending_withdrawal > 0)
<div class="alert alert-warning mb-8">
    <i data-lucide="clock"></i>
    <span>Una TZS {{ number_format($wallet->pending_withdrawal, 0) }} inasubiri kulipwa.</span>
</div>
@endif

<!-- Transaction History -->
<div class="flex justify-between items-center mb-4">
    <h3>Historia ya Shughuli</h3>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Tarehe</th>
                <th>Reference</th>
                <th>Maelezo</th>
                <th>Aina</th>
                <th style="text-align: right;">Kiasi</th>
                <th style="text-align: right;">Salio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td style="color: var(--text-muted); white-space: nowrap;">
                    {{ $transaction->created_at->format('d/m/Y') }}
                    <br>
                    <small>{{ $transaction->created_at->format('H:i') }}</small>
                </td>
                <td style="font-family: monospace; font-size: 0.75rem;">{{ $transaction->reference }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $transaction->getCategoryLabel() }}</div>
                    @if($transaction->description)
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $transaction->description }}</div>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $transaction->isCredit() ? 'badge-success' : 'badge-error' }}">
                        {{ $transaction->isCredit() ? 'Credit' : 'Debit' }}
                    </span>
                </td>
                <td style="text-align: right; font-weight: 600; color: {{ $transaction->isCredit() ? 'var(--success)' : 'var(--error)' }};">
                    {{ $transaction->getFormattedAmount() }}
                </td>
                <td style="text-align: right; color: var(--text-muted);">
                    TZS {{ number_format($transaction->balance_after, 0) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <i data-lucide="inbox" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                    Hakuna shughuli bado. Anza kufanya tasks kupata pesa!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($transactions->hasPages())
<div class="flex justify-center mt-6">
    {{ $transactions->links() }}
</div>
@endif
@endsection
