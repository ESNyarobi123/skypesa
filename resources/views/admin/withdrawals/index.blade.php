@extends('layouts.admin')

@section('title', 'Manage Withdrawals')
@section('page-title', 'Maombi ya Kutoa Pesa')
@section('page-subtitle', 'Thibitisha na ulipe maombi')

@section('content')
<!-- Stats -->
<div class="grid grid-3 mb-8">
    <div class="stat-card" style="border-color: var(--warning);">
        <div class="stat-value" style="color: var(--warning);">{{ $pendingCount }}</div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card" style="border-color: var(--warning);">
        <div class="stat-value" style="color: var(--warning);">TZS {{ number_format($pendingAmount, 0) }}</div>
        <div class="stat-label">Kiasi Kinasubiri</div>
    </div>
    <div class="card card-body flex items-center justify-center">
        <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="btn btn-primary">
            <i data-lucide="filter"></i>
            Pending Tu
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-body mb-8">
    <form method="GET" class="flex gap-4 items-center">
        <select name="status" class="form-control" style="max-width: 200px;">
            <option value="">Hali Zote</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn btn-secondary">
            <i data-lucide="filter"></i>
            Filter
        </button>
        @if(request()->hasAny(['status']))
        <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary">
            <i data-lucide="x"></i>
            Clear
        </a>
        @endif
    </form>
</div>

<!-- Withdrawals Table -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Tarehe</th>
                <th>Mtumiaji</th>
                <th>Kiasi</th>
                <th>Ada</th>
                <th>Net</th>
                <th>Malipo</th>
                <th>Hali</th>
                <th style="text-align: right;">Actions</th>
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
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $withdrawal->user->getAvatarUrl() }}" style="width: 36px; height: 36px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 500;">{{ $withdrawal->user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $withdrawal->user->getPlanName() }}</div>
                        </div>
                    </div>
                </td>
                <td>TZS {{ number_format($withdrawal->amount, 0) }}</td>
                <td style="color: var(--text-muted);">TZS {{ number_format($withdrawal->fee, 0) }}</td>
                <td style="font-weight: 600; color: var(--success);">TZS {{ number_format($withdrawal->net_amount, 0) }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $withdrawal->payment_number }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst($withdrawal->payment_provider) }}</div>
                </td>
                <td>
                    @php
                        $colors = [
                            'pending' => 'badge-warning',
                            'processing' => 'badge-primary',
                            'approved' => 'badge-success',
                            'paid' => 'badge-success',
                            'rejected' => 'badge-error',
                        ];
                    @endphp
                    <span class="badge {{ $colors[$withdrawal->status] ?? '' }}">
                        {{ $withdrawal->getStatusLabel() }}
                    </span>
                </td>
                <td style="text-align: right;">
                    @if($withdrawal->isPending())
                    <div class="flex gap-2 justify-end">
                        <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Kubali ombi hili?')">
                                <i data-lucide="check"></i>
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="showRejectModal({{ $withdrawal->id }})" style="color: var(--error);">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    @elseif($withdrawal->isApproved())
                    <form action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Weka kama limelipwa?')">
                            <i data-lucide="banknote"></i>
                            Lipwa
                        </button>
                    </form>
                    @elseif($withdrawal->isPaid())
                    <span style="color: var(--success); font-size: 0.875rem;">
                        <i data-lucide="check-circle" style="width: 16px; height: 16px; display: inline;"></i>
                        {{ $withdrawal->paid_at->format('d/m H:i') }}
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                    <i data-lucide="inbox" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                    Hakuna maombi
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

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 400px; width: 100%; padding: var(--space-6);">
        <h4 class="mb-4">Kataa Ombi</h4>
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label class="form-label">Sababu ya Kukataa</label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="Eleza kwa nini unakataa..."></textarea>
            </div>
            <div class="flex gap-4">
                <button type="button" onclick="hideRejectModal()" class="btn btn-secondary" style="flex: 1;">Ghairi</button>
                <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--error);">Kataa</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function showRejectModal(id) {
        document.getElementById('rejectForm').action = '/admin/withdrawals/' + id + '/reject';
        document.getElementById('rejectModal').style.display = 'flex';
    }
    
    function hideRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
</script>
@endpush
@endsection
