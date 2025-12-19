@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', $user->name)

@section('content')
<div style="max-width: 700px;">
    <div class="chart-card">
        <div class="chart-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="{{ $user->getAvatarUrl() }}" style="width: 50px; height: 50px; border-radius: 12px;">
                <div>
                    <div class="chart-title">Edit User Details</div>
                    <div class="chart-subtitle">Joined {{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; gap: 1.25rem;">
                <!-- Name -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Full Name *</label>
                    <input type="text" name="name" class="form-input-modern" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Email -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Email Address *</label>
                    <input type="email" name="email" class="form-input-modern" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Phone -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Phone Number</label>
                    <input type="text" name="phone" class="form-input-modern" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Password -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">New Password</label>
                        <input type="password" name="password" class="form-input-modern" placeholder="Leave blank to keep current">
                        @error('password')
                        <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input-modern" placeholder="Confirm new password">
                    </div>
                </div>
                
                <!-- Subscription Plan -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Subscription Plan *</label>
                    <select name="plan_id" class="form-input-modern form-select-modern" required>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $user->activeSubscription?->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->display_name }}
                            @if($plan->price > 0)
                            (TZS {{ number_format($plan->price, 0) }})
                            @else
                            (Free)
                            @endif
                        </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Status -->
                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Account is active</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Save Changes
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                    View Profile
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    
    <!-- Wallet Adjustment -->
    <div class="chart-card" style="margin-top: 1.5rem;">
        <div class="chart-header">
            <div>
                <div class="chart-title">Wallet Balance</div>
                <div class="chart-subtitle">Current: TZS {{ number_format($user->wallet?->balance ?? 0, 0) }}</div>
            </div>
        </div>
        
        <form action="{{ route('admin.users.adjust-balance', $user) }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group-modern" style="margin-bottom: 0;">
                    <label class="form-label-modern">Amount (TZS)</label>
                    <input type="number" name="amount" class="form-input-modern" min="0.01" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group-modern" style="margin-bottom: 0;">
                    <label class="form-label-modern">Reason</label>
                    <input type="text" name="reason" class="form-input-modern" required placeholder="Reason for adjustment">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" name="type" value="credit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Credit
                    </button>
                    <button type="submit" name="type" value="debit" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">
                        <i data-lucide="minus" style="width: 16px; height: 16px;"></i>
                        Debit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
