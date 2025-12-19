@extends('layouts.admin')

@section('title', 'Transactions')
@section('page-title', 'All Transactions')
@section('page-subtitle', 'Complete transaction history')

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="arrow-up-right" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Credits</div>
        <div class="stat-number">TZS {{ number_format($statistics['total_credits'], 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon red">
            <i data-lucide="arrow-down-right" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Debits</div>
        <div class="stat-number">TZS {{ number_format($statistics['total_debits'], 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Transactions Today</div>
        <div class="stat-number">{{ number_format($statistics['transactions_today']) }}</div>
    </div>
</div>

<!-- Transactions Table -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Transaction History</h3>
        <div class="table-actions">
            <!-- Filter by Type -->
            <select class="form-input-modern form-select-modern" style="width: 130px; padding: 0.6rem 1rem;"
                    onchange="window.location.href='{{ route('admin.transactions') }}?type=' + this.value + '&category={{ request('category') }}'">
                <option value="">All Types</option>
                <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credits</option>
                <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debits</option>
            </select>
            
            <!-- Filter by Category -->
            <select class="form-input-modern form-select-modern" style="width: 160px; padding: 0.6rem 1rem;"
                    onchange="window.location.href='{{ route('admin.transactions') }}?category=' + this.value + '&type={{ request('type') }}'">
                <option value="">All Categories</option>
                <option value="task_reward" {{ request('category') === 'task_reward' ? 'selected' : '' }}>Task Reward</option>
                <option value="withdrawal" {{ request('category') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                <option value="withdrawal_fee" {{ request('category') === 'withdrawal_fee' ? 'selected' : '' }}>Withdrawal Fee</option>
                <option value="subscription" {{ request('category') === 'subscription' ? 'selected' : '' }}>Subscription</option>
                <option value="bonus" {{ request('category') === 'bonus' ? 'selected' : '' }}>Bonus</option>
                <option value="referral_bonus" {{ request('category') === 'referral_bonus' ? 'selected' : '' }}>Referral Bonus</option>
                <option value="adjustment" {{ request('category') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
            </select>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Reference</th>
                <th>User</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
            <tr>
                <td>
                    <code style="background: rgba(255,255,255,0.05); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                        {{ $txn->reference }}
                    </code>
                </td>
                <td>
                    <div class="user-cell">
                        <img src="{{ $txn->user->getAvatarUrl() }}" class="user-avatar">
                        <div class="user-details">
                            <div class="user-name">{{ $txn->user->name }}</div>
                            <div class="user-email">{{ $txn->user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="status-badge {{ $txn->type === 'credit' ? 'active' : 'inactive' }}">
                        <i data-lucide="{{ $txn->type === 'credit' ? 'arrow-up-right' : 'arrow-down-right' }}" style="width: 12px; height: 12px;"></i>
                        {{ ucfirst($txn->type) }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-primary" style="font-size: 0.65rem;">
                        {{ $txn->getCategoryLabel() }}
                    </span>
                </td>
                <td style="font-weight: 700; color: {{ $txn->type === 'credit' ? 'var(--success)' : 'var(--error)' }};">
                    {{ $txn->type === 'credit' ? '+' : '-' }}TZS {{ number_format($txn->amount, 0) }}
                </td>
                <td style="color: var(--text-muted);">
                    <div style="font-size: 0.75rem;">
                        {{ number_format($txn->balance_before, 0) }} → {{ number_format($txn->balance_after, 0) }}
                    </div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8rem;">
                    {{ $txn->created_at->format('M d, Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i data-lucide="receipt" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>No transactions found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($transactions->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
