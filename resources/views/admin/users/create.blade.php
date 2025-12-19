@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create New User')
@section('page-subtitle', 'Add a new user to the system')

@section('content')
<div style="max-width: 700px;">
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">User Details</div>
                <div class="chart-subtitle">Enter the new user's information</div>
            </div>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; gap: 1.25rem;">
                <!-- Name -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Full Name *</label>
                    <input type="text" name="name" class="form-input-modern" value="{{ old('name') }}" required placeholder="Enter full name">
                    @error('name')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Email -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Email Address *</label>
                    <input type="email" name="email" class="form-input-modern" value="{{ old('email') }}" required placeholder="user@example.com">
                    @error('email')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Phone -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Phone Number</label>
                    <input type="text" name="phone" class="form-input-modern" value="{{ old('phone') }}" placeholder="+255...">
                    @error('phone')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Password -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Password *</label>
                        <input type="password" name="password" class="form-input-modern" required placeholder="Min 8 characters">
                        @error('password')
                        <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-input-modern" required placeholder="Repeat password">
                    </div>
                </div>
                
                <!-- Subscription Plan -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Subscription Plan *</label>
                    <select name="plan_id" class="form-input-modern form-select-modern" required>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id || ($loop->first && $plan->name == 'free') ? 'selected' : '' }}>
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
                
                <!-- Initial Balance -->
                <div class="form-group-modern">
                    <label class="form-label-modern">Initial Balance (TZS)</label>
                    <input type="number" name="initial_balance" class="form-input-modern" value="{{ old('initial_balance', 0) }}" min="0" step="0.01" placeholder="0">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Optional: Add starting balance to user's wallet</p>
                    @error('initial_balance')
                    <span style="color: var(--error); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Status -->
                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Account is active</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                    Create User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
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
