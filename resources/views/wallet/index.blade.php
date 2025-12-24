@extends('layouts.app')

@section('title', __('messages.wallet.title'))
@section('page-title', __('messages.wallet.title'))
@section('page-subtitle', __('messages.wallet.subtitle'))

@push('styles')
<style>
    /* Mobile-first responsive wallet cards */
    @media (max-width: 768px) {
        .wallet-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .wallet-buttons .btn {
            width: 100%;
            justify-content: center;
        }
        
        .transaction-card {
            background: var(--bg-elevated);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: var(--space-3);
        }
        
        .transaction-card:last-child {
            margin-bottom: 0;
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-3);
        }
        
        .transaction-amount {
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        .transaction-details {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }
        
        .transaction-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }
        
        .transaction-label {
            color: var(--text-muted);
        }
    }
</style>
@endpush

@section('content')
<!-- Wallet Overview -->
<div class="grid grid-3 mb-8">
    <!-- Main Balance -->
    <div class="wallet-card">
        <div style="position: relative; z-index: 10;">
            <div class="wallet-label">{{ __('messages.wallet.current_balance') }}</div>
            <div class="wallet-balance">TZS {{ number_format($wallet->balance ?? 0, 0) }}</div>
            <div class="flex gap-4 mt-4 wallet-buttons">
                <a href="{{ route('withdrawals.create') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                    <i data-lucide="send"></i>
                    {{ __('messages.wallet.withdraw') }}
                </a>
                <a href="{{ route('tasks.index') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                    <i data-lucide="briefcase"></i>
                    {{ __('messages.tasks.title') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Total Earned -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.2); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="trending-up" style="color: var(--success);"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 0.875rem; color: var(--text-muted);">{{ __('messages.wallet.total_earned') }}</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--success);">TZS {{ number_format($wallet->total_earned ?? 0, 0) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Total Withdrawn -->
    <div class="card card-body">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.2); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="arrow-up-right" style="color: var(--error);"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 0.875rem; color: var(--text-muted);">{{ __('messages.wallet.total_withdrawn') }}</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--error);">TZS {{ number_format($wallet->total_withdrawn ?? 0, 0) }}</div>
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
    <h3>{{ __('messages.wallet.transaction_history') }}</h3>
</div>

<!-- Desktop Table View -->
<div class="card hide-mobile">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('messages.common.date') }}</th>
                    <th>Reference</th>
                    <th>{{ __('messages.common.description') }}</th>
                    <th>{{ __('messages.common.status') }}</th>
                    <th style="text-align: right;">{{ __('messages.common.amount') }}</th>
                    <th style="text-align: right;">{{ __('messages.common.balance') }}</th>
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
                        {{ __('messages.wallet.no_transactions') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Card View -->
<div class="show-mobile" style="display: none;">
    @forelse($transactions as $transaction)
    <div class="transaction-card">
        <div class="transaction-header">
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $transaction->getCategoryLabel() }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            <div class="transaction-amount" style="color: {{ $transaction->isCredit() ? 'var(--success)' : 'var(--error)' }};">
                {{ $transaction->getFormattedAmount() }}
            </div>
        </div>
        <div class="transaction-details">
            <div class="transaction-row">
                <span class="transaction-label">Aina</span>
                <span class="badge {{ $transaction->isCredit() ? 'badge-success' : 'badge-error' }}">
                    {{ $transaction->isCredit() ? 'Credit' : 'Debit' }}
                </span>
            </div>
            <div class="transaction-row">
                <span class="transaction-label">Salio Baada</span>
                <span style="font-weight: 500;">TZS {{ number_format($transaction->balance_after, 0) }}</span>
            </div>
            @if($transaction->description)
            <div class="transaction-row">
                <span class="transaction-label">Maelezo</span>
                <span style="text-align: right; max-width: 60%;">{{ Str::limit($transaction->description, 30) }}</span>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="card card-body text-center">
        <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
        <p style="color: var(--text-muted);">{{ __('messages.wallet.no_transactions') }}</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($transactions->hasPages())
<div class="flex justify-center mt-6">
    {{ $transactions->links() }}
</div>
@endif
@endsection
