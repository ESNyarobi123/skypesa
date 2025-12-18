@extends('layouts.app')

@section('title', 'Withdrawals')
@section('page-title', 'Maombi ya Kutoa Pesa')
@section('page-subtitle', 'Historia na hali ya maombi yako')

@section('content')
<!-- Quick Actions -->
<div class="flex justify-between items-center mb-8">
    <div class="flex gap-4">
        <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i>
            Omba Kutoa Pesa
        </a>
    </div>
    
    <div class="card card-body" style="padding: var(--space-3) var(--space-4);">
        <div class="flex items-center gap-3">
            <i data-lucide="wallet" style="color: var(--primary);"></i>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Salio</div>
                <div style="font-weight: 700;">TZS {{ number_format(auth()->user()->wallet->balance ?? 0, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawals Table -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Tarehe</th>
                <th>Reference</th>
                <th>Kiasi</th>
                <th>Ada</th>
                <th>Utapata</th>
                <th>Malipo</th>
                <th>Hali</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $withdrawal)
            <tr>
                <td style="white-space: nowrap;">
                    {{ $withdrawal->created_at->format('d/m/Y') }}
                    <br>
                    <small style="color: var(--text-muted);">{{ $withdrawal->created_at->format('H:i') }}</small>
                </td>
                <td style="font-family: monospace; font-size: 0.75rem;">{{ $withdrawal->reference }}</td>
                <td style="font-weight: 600;">TZS {{ number_format($withdrawal->amount, 0) }}</td>
                <td style="color: var(--text-muted);">TZS {{ number_format($withdrawal->fee, 0) }}</td>
                <td style="font-weight: 600; color: var(--success);">TZS {{ number_format($withdrawal->net_amount, 0) }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $withdrawal->payment_number }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst($withdrawal->payment_provider) }}</div>
                </td>
                <td>
                    @php
                        $statusColors = [
                            'pending' => 'badge-warning',
                            'processing' => 'badge-primary',
                            'approved' => 'badge-success',
                            'paid' => 'badge-success',
                            'rejected' => 'badge-error',
                            'cancelled' => 'badge-error',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$withdrawal->status] ?? '' }}">
                        {{ $withdrawal->getStatusLabel() }}
                    </span>
                    @if($withdrawal->isRejected() && $withdrawal->rejection_reason)
                    <div style="font-size: 0.75rem; color: var(--error); margin-top: var(--space-1);">
                        {{ $withdrawal->rejection_reason }}
                    </div>
                    @endif
                    @if($withdrawal->isPaid())
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-1);">
                        {{ $withdrawal->paid_at->format('d/m/Y H:i') }}
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <i data-lucide="banknote" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                    Hujatoa pesa bado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($withdrawals->hasPages())
<div class="flex justify-center mt-6">
    {{ $withdrawals->links() }}
</div>
@endif

<!-- Info Section -->
<div class="card mt-8" style="padding: var(--space-6); background: var(--gradient-glow);">
    <h4 class="mb-4">
        <i data-lucide="info" style="color: var(--primary); display: inline;"></i>
        Maelezo ya Kutoa Pesa
    </h4>
    <div class="grid grid-2" style="gap: var(--space-6);">
        <div>
            <h5 class="mb-2" style="color: var(--text-secondary);">Mpango Wako</h5>
            <ul style="list-style: none; font-size: 0.875rem; color: var(--text-muted);">
                <li class="flex justify-between mb-2">
                    <span>Min. Withdrawal:</span>
                    <strong>TZS {{ number_format(auth()->user()->getMinWithdrawal(), 0) }}</strong>
                </li>
                <li class="flex justify-between mb-2">
                    <span>Ada:</span>
                    <strong>{{ auth()->user()->getWithdrawalFeePercent() }}%</strong>
                </li>
                <li class="flex justify-between">
                    <span>Muda wa Processing:</span>
                    <strong>Siku {{ auth()->user()->getCurrentPlan()?->processing_days ?? 7 }}</strong>
                </li>
            </ul>
        </div>
        <div>
            <h5 class="mb-2" style="color: var(--text-secondary);">Njia za Malipo</h5>
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <span class="badge" style="background: var(--bg-dark);">M-Pesa</span>
                <span class="badge" style="background: var(--bg-dark);">Tigo Pesa</span>
                <span class="badge" style="background: var(--bg-dark);">Airtel Money</span>
                <span class="badge" style="background: var(--bg-dark);">Halo Pesa</span>
            </div>
        </div>
    </div>
</div>
@endsection
