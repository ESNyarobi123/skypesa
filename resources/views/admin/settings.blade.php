@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure platform settings')

@section('content')
<div style="max-width: 800px;">
    <!-- General Settings -->
    <div class="chart-card" style="margin-bottom: 1.5rem;">
        <div class="chart-header">
            <div>
                <div class="chart-title">General Settings</div>
                <div class="chart-subtitle">Basic platform configuration</div>
            </div>
        </div>
        
        <div style="display: grid; gap: 1.25rem;">
            <div class="form-group-modern">
                <label class="form-label-modern">Platform Name</label>
                <input type="text" class="form-input-modern" value="SKYpesa" disabled>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Default Currency</label>
                    <input type="text" class="form-input-modern" value="TZS" disabled>
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Timezone</label>
                    <input type="text" class="form-input-modern" value="Africa/Dar_es_Salaam" disabled>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Referral Settings -->
    <div class="chart-card" style="margin-bottom: 1.5rem;">
        <div class="chart-header">
            <div>
                <div class="chart-title">Referral Program</div>
                <div class="chart-subtitle">Configure referral bonuses</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group-modern">
                <label class="form-label-modern">Referrer Bonus (TZS)</label>
                <input type="number" class="form-input-modern" value="{{ config('app.referral_bonus_referrer', 500) }}" placeholder="500">
                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Bonus for the user who referred</p>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern">Referral Bonus (TZS)</label>
                <input type="number" class="form-input-modern" value="{{ config('app.referral_bonus_new_user', 200) }}" placeholder="200">
                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Bonus for the new user</p>
            </div>
        </div>
    </div>
    
    <!-- Withdrawal Settings -->
    <div class="chart-card" style="margin-bottom: 1.5rem;">
        <div class="chart-header">
            <div>
                <div class="chart-title">Withdrawal Settings</div>
                <div class="chart-subtitle">Configure withdrawal policies</div>
            </div>
        </div>
        
        <div style="display: grid; gap: 1rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Min Withdrawal Global (TZS)</label>
                    <input type="number" class="form-input-modern" value="5000" placeholder="5000">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Max Withdrawal/Day (TZS)</label>
                    <input type="number" class="form-input-modern" value="500000" placeholder="500000">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Withdrawals Per Day</label>
                    <input type="number" class="form-input-modern" value="3" placeholder="3">
                </div>
            </div>
            
            <div class="form-group-modern">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span style="color: white; font-size: 0.9rem;">Require phone verification for withdrawals</span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Danger Zone -->
    <div class="chart-card" style="border-color: rgba(239, 68, 68, 0.3);">
        <div class="chart-header">
            <div>
                <div class="chart-title" style="color: var(--error);">Danger Zone</div>
                <div class="chart-subtitle">Irreversible actions</div>
            </div>
        </div>
        
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                <div>
                    <h4 style="color: white; font-size: 0.9rem; margin-bottom: 0.25rem;">Clear System Cache</h4>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">Clear all cached data including views and configs</p>
                </div>
                <button class="btn btn-secondary" style="border-color: var(--error); color: var(--error);">
                    Clear Cache
                </button>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                <div>
                    <h4 style="color: white; font-size: 0.9rem; margin-bottom: 0.25rem;">Reset Demo Data</h4>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">Remove all test users and transactions</p>
                </div>
                <button class="btn btn-secondary" style="border-color: var(--error); color: var(--error);">
                    Reset Data
                </button>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 2rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
        <p>Settings are currently read-only. Contact developer to modify core settings.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
