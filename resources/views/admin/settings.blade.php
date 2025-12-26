@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure platform settings')

@section('content')
<div style="max-width: 900px;">
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

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- General Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="settings" style="width: 20px; height: 20px; display: inline; color: var(--primary);"></i>
                        General Settings
                    </div>
                    <div class="chart-subtitle">Basic platform configuration</div>
                </div>
            </div>
            
            <div style="display: grid; gap: 1.25rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Platform Name</label>
                    <input type="text" name="platform_name" class="form-input-modern" 
                           value="{{ $settings['general']['platform_name'] ?? 'SKYpesa' }}"
                           placeholder="SKYpesa">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Default Currency</label>
                        <input type="text" class="form-input-modern" value="{{ $settings['general']['platform_currency'] ?? 'TZS' }}" disabled>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Cannot be changed</p>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Timezone</label>
                        <input type="text" class="form-input-modern" value="{{ $settings['general']['platform_timezone'] ?? 'Africa/Dar_es_Salaam' }}" disabled>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Cannot be changed</p>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="maintenance_mode" 
                               {{ ($settings['general']['maintenance_mode'] ?? false) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <div>
                            <span style="color: white; font-size: 0.9rem;">Maintenance Mode</span>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">Disable public access temporarily</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Referral Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="users" style="width: 20px; height: 20px; display: inline; color: var(--info);"></i>
                        Referral Program
                    </div>
                    <div class="chart-subtitle">Configure referral bonuses</div>
                </div>
            </div>
            
            <div style="display: grid; gap: 1.25rem;">
                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="referral_enabled" 
                               {{ ($settings['referral']['referral_enabled'] ?? true) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Enable Referral Program</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Referrer Bonus (TZS)</label>
                        <input type="number" name="referral_bonus_referrer" class="form-input-modern" 
                               value="{{ $settings['referral']['referral_bonus_referrer'] ?? 500 }}" 
                               min="0" placeholder="500">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Bonus for the user who referred</p>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">New User Bonus (TZS)</label>
                        <input type="number" name="referral_bonus_new_user" class="form-input-modern"
                               value="{{ $settings['referral']['referral_bonus_new_user'] ?? 200 }}" 
                               min="0" placeholder="200">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Bonus for the new user</p>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="referral_require_task_completion" 
                               {{ ($settings['referral']['referral_require_task_completion'] ?? true) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <div>
                            <span style="color: white; font-size: 0.9rem;">Require Task Completion</span>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">New user must complete tasks before referral bonus is paid</p>
                        </div>
                    </label>
                </div>

                <div class="form-group-modern" style="background: rgba(59, 130, 246, 0.1); padding: 1rem; border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.2);">
                    <label class="form-label-modern" style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="shield-check" style="width: 16px; height: 16px; color: var(--info);"></i>
                        Tasks Required for Referral Bonus
                    </label>
                    <input type="number" name="referral_tasks_required" class="form-input-modern" 
                           value="{{ $settings['referral']['referral_tasks_required'] ?? 15 }}" 
                           min="1" max="100" placeholder="15" style="max-width: 150px;">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <strong>🛡️ Anti-Cheat:</strong> Referred user must complete this many tasks before referrer gets bonus. 
                        Higher = less cheating, lower = faster rewards.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Withdrawal Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="credit-card" style="width: 20px; height: 20px; display: inline; color: var(--warning);"></i>
                        Withdrawal Settings
                    </div>
                    <div class="chart-subtitle">Configure withdrawal policies</div>
                </div>
            </div>
            
            <div style="display: grid; gap: 1.25rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Min Withdrawal (TZS)</label>
                        <input type="number" name="withdrawal_min_global" class="form-input-modern" 
                               value="{{ $settings['withdrawal']['withdrawal_min_global'] ?? 5000 }}" 
                               min="0" placeholder="5000">
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Max Daily (TZS)</label>
                        <input type="number" name="withdrawal_max_daily" class="form-input-modern" 
                               value="{{ $settings['withdrawal']['withdrawal_max_daily'] ?? 500000 }}" 
                               min="0" placeholder="500000">
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Per Day Limit</label>
                        <input type="number" name="withdrawal_per_day_limit" class="form-input-modern" 
                               value="{{ $settings['withdrawal']['withdrawal_per_day_limit'] ?? 3 }}" 
                               min="1" max="10" placeholder="3">
                    </div>
                </div>
                
                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="withdrawal_require_phone_verification" 
                               {{ ($settings['withdrawal']['withdrawal_require_phone_verification'] ?? true) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="color: white; font-size: 0.9rem;">Require phone verification for withdrawals</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 10px;">
                    <div class="form-group-modern" style="margin: 0;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="withdrawal_auto_approve" 
                                   {{ ($settings['withdrawal']['withdrawal_auto_approve'] ?? false) ? 'checked' : '' }}
                                   style="width: 18px; height: 18px; accent-color: var(--warning);">
                            <div>
                                <span style="color: white; font-size: 0.9rem;">Auto-Approve Withdrawals</span>
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">Automatically approve small amounts</p>
                            </div>
                        </label>
                    </div>
                    <div class="form-group-modern" style="margin: 0;">
                        <label class="form-label-modern">Max Auto-Approve (TZS)</label>
                        <input type="number" name="withdrawal_auto_approve_max" class="form-input-modern" 
                               value="{{ $settings['withdrawal']['withdrawal_auto_approve_max'] ?? 10000 }}" 
                               min="0" placeholder="10000">
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="clipboard-check" style="width: 20px; height: 20px; display: inline; color: var(--success);"></i>
                        Task Settings
                    </div>
                    <div class="chart-subtitle">Configure task behaviors</div>
                </div>
            </div>
            
            <div style="display: grid; gap: 1.25rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Default Task Duration (seconds)</label>
                    <input type="number" name="task_default_duration" class="form-input-modern" 
                           value="{{ $settings['task']['task_default_duration'] ?? 30 }}" 
                           min="10" max="300" placeholder="30" style="max-width: 200px;">
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">IP Daily Task Limit</label>
                    <input type="number" name="task_ip_daily_limit" class="form-input-modern" 
                           value="{{ $settings['task']['task_ip_daily_limit'] ?? 100 }}" 
                           min="1" placeholder="100" style="max-width: 200px;">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Max tasks allowed from same IP per day</p>
                </div>

                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="task_allow_skip" 
                               {{ ($settings['task']['task_allow_skip'] ?? false) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <div>
                            <span style="color: white; font-size: 0.9rem;">Allow Task Skip</span>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0;">Let users skip tasks (no reward)</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Profit Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="trending-up" style="width: 20px; height: 20px; display: inline; color: #8b5cf6;"></i>
                        Profit Analytics Settings
                    </div>
                    <div class="chart-subtitle">For profit calculations in reports</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Average Ad Revenue Per View (TZS)</label>
                    <input type="number" name="ad_revenue_per_view" class="form-input-modern" 
                           value="{{ $settings['profit']['ad_revenue_per_view'] ?? 8 }}" 
                           min="0" placeholder="8">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">How much you earn per ad view from ad networks</p>
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Platform Profit Margin (%)</label>
                    <input type="number" name="platform_profit_percent" class="form-input-modern" 
                           value="{{ $settings['profit']['platform_profit_percent'] ?? 60 }}" 
                           min="0" max="100" placeholder="60">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Percentage kept as profit (user reward is remainder)</p>
                </div>
            </div>
        <!-- Support Settings -->
        <div class="chart-card" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <div>
                    <div class="chart-title">
                        <i data-lucide="message-square" style="width: 20px; height: 20px; display: inline; color: #25D366;"></i>
                        Support Settings
                    </div>
                    <div class="chart-subtitle">Configure support and community links</div>
                </div>
            </div>
            
            <div style="display: grid; gap: 1.25rem;">
                <div class="form-group-modern">
                    <label class="form-label-modern">WhatsApp Support Number</label>
                    <input type="text" name="whatsapp_support_number" class="form-input-modern" 
                           value="{{ $settings['support']['whatsapp_support_number'] ?? '255700000000' }}"
                           placeholder="255700000000">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Include country code without + (e.g., 255712345678)</p>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 2rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                Save All Settings
            </button>
        </div>
    </form>
        
    <!-- Danger Zone -->
    <div class="chart-card" style="border-color: rgba(239, 68, 68, 0.3);">
        <div class="chart-header">
            <div>
                <div class="chart-title" style="color: var(--error);">
                    <i data-lucide="alert-triangle" style="width: 20px; height: 20px; display: inline;"></i>
                    Danger Zone
                </div>
                <div class="chart-subtitle">Irreversible actions</div>
            </div>
        </div>
        
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                <div>
                    <h4 style="color: white; font-size: 0.9rem; margin-bottom: 0.25rem;">Clear System Cache</h4>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">Clear all cached data including views, configs, and routes</p>
                </div>
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="border-color: var(--error); color: var(--error);">
                        <i data-lucide="trash" style="width: 14px; height: 14px;"></i>
                        Clear Cache
                    </button>
                </form>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                <div>
                    <h4 style="color: white; font-size: 0.9rem; margin-bottom: 0.25rem;">Reset Demo Data</h4>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">Remove all test users and transactions (type DELETE to confirm)</p>
                </div>
                <form action="{{ route('admin.settings.reset-demo') }}" method="POST" onsubmit="return confirm('This will delete demo data. Are you sure?');">
                    @csrf
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="confirm" placeholder="Type DELETE" 
                               style="width: 120px; padding: 0.5rem; background: var(--bg-dark); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; color: white; font-size: 0.8rem;">
                        <button type="submit" class="btn btn-secondary" style="border-color: var(--error); color: var(--error);">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
