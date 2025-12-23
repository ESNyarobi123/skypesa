@extends('layouts.admin')

@section('title', 'Manage Withdrawals')
@section('page-title', 'Maombi ya Kutoa Pesa')
@section('page-subtitle', 'Thibitisha na ulipe maombi')

@section('content')
@if(session('success'))
<div class="alert alert-success mb-6" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 1rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem;">
    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error mb-6" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--error); padding: 1rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem;">
    <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
    {{ session('error') }}
</div>
@endif

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Pending</div>
        <div class="stat-number">{{ $pendingCount }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Kiasi Kinasubiri</div>
        <div class="stat-number">TZS {{ number_format($pendingAmount, 0) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Approved Today</div>
        <div class="stat-number">{{ \App\Models\Withdrawal::where('status', 'approved')->whereDate('updated_at', today())->count() }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon blue">
            <i data-lucide="banknote" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Paid Today</div>
        <div class="stat-number">{{ \App\Models\Withdrawal::where('status', 'paid')->whereDate('paid_at', today())->count() }}</div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="chart-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; justify-content: space-between;">
        <!-- Filters -->
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group-modern" style="margin: 0;">
                <label class="form-label-modern">Status</label>
                <select name="status" class="form-input-modern" style="min-width: 150px;">
                    <option value="">Wote</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group-modern" style="margin: 0;">
                <label class="form-label-modern">From Date</label>
                <input type="date" name="from_date" class="form-input-modern" value="{{ request('from_date') }}">
            </div>
            <div class="form-group-modern" style="margin: 0;">
                <label class="form-label-modern">To Date</label>
                <input type="date" name="to_date" class="form-input-modern" value="{{ request('to_date') }}">
            </div>
            <div class="form-group-modern" style="margin: 0;">
                <label class="form-label-modern">Search</label>
                <input type="text" name="search" class="form-input-modern" placeholder="Jina au namba..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                Filter
            </button>
            @if(request()->hasAny(['status', 'from_date', 'to_date', 'search']))
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                Clear
            </a>
            @endif
        </form>

        <!-- Export -->
        <a href="{{ route('admin.withdrawals.export', request()->query()) }}" class="btn btn-secondary">
            <i data-lucide="download" style="width: 16px; height: 16px;"></i>
            Export CSV
        </a>
    </div>
</div>

<!-- Bulk Actions -->
<form id="bulkForm" method="POST">
    @csrf
    <div id="bulkActions" style="display: none; margin-bottom: 1rem; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.3);">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span style="color: var(--primary); font-weight: 600;">
                <span id="selectedCount">0</span> selected
            </span>
            <button type="button" onclick="bulkApprove()" class="btn btn-primary btn-sm">
                <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                Approve All
            </button>
            <button type="button" onclick="showBulkRejectModal()" class="btn btn-sm" style="background: var(--error);">
                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                Reject All
            </button>
            <button type="button" onclick="clearSelection()" class="btn btn-secondary btn-sm">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="data-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" style="width: 18px; height: 18px; accent-color: var(--primary);">
                    </th>
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
                    <td>
                        @if($withdrawal->isPending())
                        <input type="checkbox" name="withdrawal_ids[]" value="{{ $withdrawal->id }}" class="withdrawal-checkbox" onchange="updateBulkActions()" style="width: 18px; height: 18px; accent-color: var(--primary);">
                        @endif
                    </td>
                    <td style="white-space: nowrap;">
                        {{ $withdrawal->created_at->format('d/m/Y') }}
                        <br>
                        <small style="color: var(--text-muted);">{{ $withdrawal->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <div class="user-cell">
                            <img src="{{ $withdrawal->user->getAvatarUrl() }}" alt="{{ $withdrawal->user->name }}" class="user-avatar">
                            <div class="user-details">
                                <div class="user-name">{{ $withdrawal->user->name }}</div>
                                <div class="user-email">{{ $withdrawal->user->getPlanName() }}</div>
                            </div>
                        </div>
                    </td>
                    <td>TZS {{ number_format($withdrawal->amount, 0) }}</td>
                    <td style="color: var(--text-muted);">TZS {{ number_format($withdrawal->fee, 0) }}</td>
                    <td style="font-weight: 600; color: var(--success);">TZS {{ number_format($withdrawal->net_amount, 0) }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $withdrawal->payment_number }}</div>
                        @if($withdrawal->payment_name)
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $withdrawal->payment_name }}</div>
                        @endif
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst($withdrawal->payment_provider) }}</div>
                    </td>
                    <td>
                        @php
                            $statusClasses = [
                                'pending' => 'pending',
                                'processing' => 'active',
                                'approved' => 'active',
                                'paid' => 'active',
                                'rejected' => 'inactive',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusClasses[$withdrawal->status] ?? 'pending' }}">
                            {{ $withdrawal->getStatusLabel() }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        @if($withdrawal->isPending())
                        <div class="action-btns">
                            <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn" title="Approve" onclick="return confirm('Kubali ombi hili?')">
                                    <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                            <button type="button" class="action-btn danger" onclick="showRejectModal({{ $withdrawal->id }})" title="Reject">
                                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                        @elseif($withdrawal->isApproved())
                        <form action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Weka kama limelipwa?')">
                                <i data-lucide="banknote" style="width: 14px; height: 14px;"></i>
                                Lipwa
                            </button>
                        </form>
                        @elseif($withdrawal->isPaid())
                        <span style="color: var(--success); font-size: 0.75rem;">
                            <i data-lucide="check-circle" style="width: 14px; height: 14px; display: inline;"></i>
                            {{ $withdrawal->paid_at->format('d/m H:i') }}
                        </span>
                        @elseif($withdrawal->status === 'rejected')
                        <span style="color: var(--error); font-size: 0.75rem;" title="{{ $withdrawal->rejection_reason }}">
                            <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
                            Rejected
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Hakuna maombi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<!-- Pagination -->
@if($withdrawals->hasPages())
<div style="display: flex; justify-content: center; margin-top: 1.5rem;">
    {{ $withdrawals->links() }}
</div>
@endif

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="chart-card" style="max-width: 400px; width: 90%;">
        <h4 style="color: white; margin-bottom: 1rem;">
            <i data-lucide="x-circle" style="width: 20px; height: 20px; display: inline; color: var(--error);"></i>
            Kataa Ombi
        </h4>
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="form-group-modern">
                <label class="form-label-modern">Sababu ya Kukataa</label>
                <textarea name="reason" class="form-input-modern" rows="3" required placeholder="Eleza kwa nini unakataa..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="hideRejectModal()" class="btn btn-secondary" style="flex: 1;">Ghairi</button>
                <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--error);">Kataa</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div id="bulkRejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="chart-card" style="max-width: 400px; width: 90%;">
        <h4 style="color: white; margin-bottom: 1rem;">
            <i data-lucide="x-circle" style="width: 20px; height: 20px; display: inline; color: var(--error);"></i>
            Kataa Maombi Yote (<span id="bulkRejectCount">0</span>)
        </h4>
        <form id="bulkRejectForm" action="{{ route('admin.withdrawals.bulk-reject') }}" method="POST">
            @csrf
            <div id="bulkRejectIds"></div>
            <div class="form-group-modern">
                <label class="form-label-modern">Sababu ya Kukataa</label>
                <textarea name="reason" class="form-input-modern" rows="3" required placeholder="Sababu sawa kwa wote..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="hideBulkRejectModal()" class="btn btn-secondary" style="flex: 1;">Ghairi</button>
                <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--error);">Kataa Wote</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    lucide.createIcons();

    function showRejectModal(id) {
        document.getElementById('rejectForm').action = '/admin/withdrawals/' + id + '/reject';
        document.getElementById('rejectModal').style.display = 'flex';
    }
    
    function hideRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActions').style.display = count > 0 ? 'block' : 'none';
    }

    function clearSelection() {
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.withdrawal-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkApprove() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Tafadhali chagua maombi kwanza');
            return;
        }
        
        if (!confirm('Kubali maombi ' + ids.length + '?')) {
            return;
        }

        const form = document.getElementById('bulkForm');
        form.action = '{{ route("admin.withdrawals.bulk-approve") }}';
        form.submit();
    }

    function showBulkRejectModal() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Tafadhali chagua maombi kwanza');
            return;
        }

        document.getElementById('bulkRejectCount').textContent = ids.length;
        
        // Add hidden inputs for IDs
        const container = document.getElementById('bulkRejectIds');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'withdrawal_ids[]';
            input.value = id;
            container.appendChild(input);
        });

        document.getElementById('bulkRejectModal').style.display = 'flex';
    }

    function hideBulkRejectModal() {
        document.getElementById('bulkRejectModal').style.display = 'none';
    }
</script>
@endpush
@endsection
