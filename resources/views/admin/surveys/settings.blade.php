@extends('layouts.admin')

@section('title', 'Survey Settings')
@section('page-title', 'Survey Settings')
@section('page-subtitle', 'CPX Research Configuration')

@section('content')
<div class="grid grid-2" style="gap: var(--space-6);">
    <!-- Configuration Status -->
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">📊 CPX Research Status</h4>
            
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center" style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <span>Surveys Enabled</span>
                    @if($config['enabled'])
                        <span class="badge badge-success">✓ Active</span>
                    @else
                        <span class="badge badge-error">✕ Disabled</span>
                    @endif
                </div>
                
                <div class="flex justify-between items-center" style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <span>Demo Mode</span>
                    @if($config['demo_mode'])
                        <span class="badge badge-warning">⚡ Demo Active</span>
                    @else
                        <span class="badge badge-success">🔴 Live Mode</span>
                    @endif
                </div>
                
                <div class="flex justify-between items-center" style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <span>App ID</span>
                    <code>{{ $config['app_id'] ?: 'Not configured' }}</code>
                </div>
                
                <div class="flex justify-between items-center" style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <span>Daily Limit per User</span>
                    <strong>{{ $config['daily_limit'] }} surveys</strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Postback URL -->
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">🔗 Postback Configuration</h4>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-4);">
                Weka URL hii kwenye CPX Research dashboard yako chini ya "Postback URL":
            </p>
            
            <div style="background: var(--bg-tertiary); padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
                <code style="font-size: 0.75rem; word-break: break-all; color: var(--primary);">
                    {{ $config['postback_url'] }}?trans_id=[trans_id]&ext_user_id=[user_id]&survey_id=[survey_id]&loi=[loi]&payout=[payout_usd]&status=[status]
                </code>
            </div>
            
            <button onclick="navigator.clipboard.writeText('{{ $config['postback_url'] }}?trans_id=[trans_id]&ext_user_id=[user_id]&survey_id=[survey_id]&loi=[loi]&payout=[payout_usd]&status=[status]')" class="btn btn-sm btn-secondary">
                📋 Copy URL
            </button>
        </div>
    </div>
</div>

<!-- Reward Structure -->
<div class="card mt-6">
    <div class="card-body">
        <h4 class="mb-4">💰 Reward Structure</h4>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-4);">
            Hizi ni rewards ambazo watumiaji wanapata kwa kukamilisha surveys:
        </p>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Aina ya Survey</th>
                    <th>Muda (LOI)</th>
                    <th>User Reward</th>
                    <th>VIP Only</th>
                </tr>
            </thead>
            <tbody>
                @foreach($config['rewards'] as $type => $reward)
                <tr>
                    <td>
                        <span class="badge badge-{{ $type === 'short' ? 'info' : ($type === 'medium' ? 'primary' : 'warning') }}">
                            {{ $reward['label'] }}
                        </span>
                    </td>
                    <td>{{ $reward['min_loi'] }}-{{ $reward['max_loi'] }} dakika</td>
                    <td style="font-weight: 700; color: var(--success);">TZS {{ number_format($reward['reward'], 0) }}</td>
                    <td>
                        @if($reward['vip_only'])
                            <span class="badge badge-warning">VIP Only</span>
                        @else
                            <span class="badge badge-success">All Users</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Environment Variables -->
<div class="card mt-6">
    <div class="card-body">
        <h4 class="mb-4">⚙️ Environment Variables</h4>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--space-4);">
            Ongeza hizi kwenye <code>.env</code> file yako:
        </p>
        
        <div style="background: #1a1a1a; padding: var(--space-4); border-radius: var(--radius-md); font-family: monospace; font-size: 0.8rem; color: #10b981;">
            <pre style="margin: 0; white-space: pre-wrap;">
# CPX Research Surveys
CPX_APP_ID=your_app_id_here
CPX_SECURE_HASH=your_secure_hash_here
CPX_POSTBACK_SECRET=your_secret_here
CPX_ENABLED=true
CPX_DEMO_MODE=false
            </pre>
        </div>
        
        <div style="margin-top: var(--space-4); padding: var(--space-4); background: rgba(59, 130, 246, 0.1); border: 1px solid var(--info); border-radius: var(--radius-md);">
            <strong style="color: var(--info);">ℹ️ Jinsi ya Kupata Credentials:</strong>
            <ol style="margin-top: var(--space-2); padding-left: var(--space-4); font-size: 0.875rem; color: var(--text-secondary);">
                <li>Nenda <a href="https://publisher.cpx-research.com" target="_blank" style="color: var(--primary);">publisher.cpx-research.com</a></li>
                <li>Jiandikishe au ingia kama tayari una akaunti</li>
                <li>Tengeneza App mpya</li>
                <li>Nakili App ID na Secure Hash</li>
                <li>Weka Postback URL kwenye settings za App</li>
            </ol>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="flex gap-4 mt-6">
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-primary">
        ← Rudi Surveys
    </a>
    <a href="{{ route('admin.surveys.analytics') }}" class="btn btn-secondary">
        📈 Analytics
    </a>
</div>
@endsection
