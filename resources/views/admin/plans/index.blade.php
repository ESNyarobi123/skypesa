@extends('layouts.admin')

@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')
@section('page-subtitle', 'Manage pricing and features for each plan')

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card-modern">
        <div class="stat-icon purple">
            <i data-lucide="crown" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Total Plans</div>
        <div class="stat-number">{{ $plans->count() }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon green">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Active Subscriptions</div>
        <div class="stat-number">{{ number_format($totalActiveSubscriptions) }}</div>
    </div>
    <div class="stat-card-modern">
        <div class="stat-icon yellow">
            <i data-lucide="banknote" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="stat-title">Subscription Revenue</div>
        <div class="stat-number">TZS {{ number_format($totalRevenue, 0) }}</div>
    </div>
</div>

<!-- Add Plan Button -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
        <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
        Add New Plan
    </a>
</div>

<!-- Plans Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
    @foreach($plans as $plan)
    <div class="chart-card" style="position: relative; {{ !$plan->is_active ? 'opacity: 0.6;' : '' }}">
        <!-- Status Badge -->
        @if(!$plan->is_active)
        <div style="position: absolute; top: 1rem; right: 1rem;">
            <span class="status-badge inactive">Inactive</span>
        </div>
        @elseif($plan->is_featured)
        <div style="position: absolute; top: 1rem; right: 1rem;">
            <span class="badge badge-primary">Featured</span>
        </div>
        @endif
        
        <!-- Plan Header -->
        <div style="text-align: center; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 1.5rem;">
            <div style="width: 60px; height: 60px; background: {{ $plan->badge_color ?? 'var(--primary)' }}20; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="{{ $plan->icon ?? 'crown' }}" style="width: 28px; height: 28px; color: {{ $plan->badge_color ?? 'var(--primary)' }};"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: white; margin-bottom: 0.25rem;">{{ $plan->display_name }}</h3>
            <div style="font-size: 2rem; font-weight: 800; color: {{ $plan->badge_color ?? 'var(--primary)' }};">
                @if($plan->price > 0)
                TZS {{ number_format($plan->price, 0) }}
                <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-muted);">/ {{ $plan->duration_days }} days</span>
                @else
                Free
                @endif
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
                {{ $plan->subscriptions_count }} active users
            </div>
        </div>
        
        <!-- Plan Features -->
        <div style="margin-bottom: 1.5rem;">
            <div style="display: grid; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px; color: var(--success);"></i>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ $plan->daily_task_limit ? $plan->daily_task_limit . ' tasks/day' : 'Unlimited tasks' }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="coins" style="width: 16px; height: 16px; color: var(--success);"></i>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        TZS {{ number_format($plan->reward_per_task, 0) }} per task
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px; color: var(--success);"></i>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        Min withdrawal: TZS {{ number_format($plan->min_withdrawal, 0) }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="percent" style="width: 16px; height: 16px; color: var(--success);"></i>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ $plan->withdrawal_fee_percent }}% withdrawal fee
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="clock" style="width: 16px; height: 16px; color: var(--success);"></i>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ $plan->processing_days }} day{{ $plan->processing_days != 1 ? 's' : '' }} processing
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">
                <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                Edit
            </a>
            <form action="{{ route('admin.plans.toggle-status', $plan) }}" method="POST" style="flex: 1;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $plan->is_active ? 'btn-secondary' : 'btn-primary' }}" style="width: 100%; justify-content: center;">
                    <i data-lucide="{{ $plan->is_active ? 'eye-off' : 'eye' }}" style="width: 14px; height: 14px;"></i>
                    {{ $plan->is_active ? 'Disable' : 'Enable' }}
                </button>
            </form>
            @if($plan->name !== 'free' && $plan->subscriptions_count == 0)
            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Delete this plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary" style="padding: 0.75rem;">
                    <i data-lucide="trash-2" style="width: 14px; height: 14px; color: var(--error);"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
