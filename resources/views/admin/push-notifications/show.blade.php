@extends('layouts.admin')

@section('title', 'Push Notification Details')
@section('page-title', 'Notification Details')
@section('page-subtitle', 'ID: #{{ $pushNotification->id }}')

@section('content')
<div class="notification-details-page">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.push-notifications.index') }}" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Rudi kwenye orodha
        </a>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Details -->
        <div>
            <!-- Notification Content -->
            <div class="chart-card" style="margin-bottom: 1.5rem;">
                <div class="chart-header">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="bell" style="width: 24px; height: 24px; color: white;"></i>
                        </div>
                        <div>
                            <h3 class="chart-title">{{ $pushNotification->title }}</h3>
                            <p class="chart-subtitle">Sent {{ $pushNotification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="status-badge" style="background: {{ $pushNotification->status_color }}20; color: {{ $pushNotification->status_color }};">
                        {{ ucfirst($pushNotification->status) }}
                    </span>
                </div>
                
                <div style="padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 10px; margin-bottom: 1.5rem;">
                    <p style="color: var(--text-secondary); line-height: 1.6; font-size: 0.9375rem;">
                        {{ $pushNotification->body }}
                    </p>
                </div>
                
                @if($pushNotification->image_url)
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Image</label>
                    <div style="margin-top: 0.5rem;">
                        <img src="{{ $pushNotification->image_url }}" alt="Notification Image" style="max-width: 300px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>
                @endif
                
                @if($pushNotification->data)
                <div>
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Extra Data</label>
                    <pre style="margin-top: 0.5rem; padding: 1rem; background: rgba(0,0,0,0.3); border-radius: 8px; color: var(--primary); font-size: 0.8rem; overflow-x: auto;">{{ json_encode($pushNotification->data, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
            
            <!-- Delivery Stats -->
            <div class="chart-card" style="margin-bottom: 1.5rem;">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">📊 Delivery Statistics</h3>
                        <p class="chart-subtitle">Matokeo ya kutuma</p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="text-align: center; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: white;">{{ $pushNotification->total_tokens }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Total Tokens</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);">{{ $pushNotification->success_count }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Success</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--error);">{{ $pushNotification->failure_count }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Failed</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: rgba(59, 130, 246, 0.1); border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--info);">{{ $pushNotification->success_rate }}%</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Success Rate</div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 6px; overflow: hidden;">
                    <div style="height: 100%; background: linear-gradient(90deg, var(--success), #059669); width: {{ $pushNotification->success_rate }}%; transition: width 0.5s ease;"></div>
                </div>
            </div>
            
            <!-- Error Details -->
            @if($pushNotification->error_details && count($pushNotification->error_details) > 0)
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title" style="color: var(--error);">⚠️ Error Details</h3>
                        <p class="chart-subtitle">Makosa yaliyotokea wakati wa kutuma</p>
                    </div>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($pushNotification->error_details as $error)
                    <div style="padding: 0.75rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; margin-bottom: 0.5rem; border-left: 3px solid var(--error);">
                        <div style="font-size: 0.8rem; color: var(--error); font-weight: 500;">{{ $error['error'] ?? 'Unknown error' }}</div>
                        @if(isset($error['token']))
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Token: {{ $error['token'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Metadata -->
            <div class="chart-card" style="margin-bottom: 1.5rem;">
                <div class="chart-header">
                    <h3 class="chart-title">📋 Details</h3>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Target</label>
                        <div style="margin-top: 0.25rem;">
                            <span style="padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 500; background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                                {{ $pushNotification->target_type_label }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Sent By</label>
                        <div style="margin-top: 0.25rem; color: white; font-weight: 500;">
                            {{ $pushNotification->sender?->name ?? 'System' }}
                        </div>
                    </div>
                    
                    <div>
                        <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Sent At</label>
                        <div style="margin-top: 0.25rem; color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $pushNotification->sent_at?->format('d M Y, H:i:s') ?? '-' }}
                        </div>
                    </div>
                    
                    <div>
                        <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Completed At</label>
                        <div style="margin-top: 0.25rem; color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $pushNotification->completed_at?->format('d M Y, H:i:s') ?? '-' }}
                        </div>
                    </div>
                    
                    @if($pushNotification->completed_at && $pushNotification->sent_at)
                    <div>
                        <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Duration</label>
                        <div style="margin-top: 0.25rem; color: var(--primary); font-weight: 600;">
                            {{ $pushNotification->sent_at->diffForHumans($pushNotification->completed_at, true) }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Targeted Users (if specific) -->
            @if($targetedUsers && $targetedUsers->count() > 0)
            <div class="chart-card" style="margin-bottom: 1.5rem;">
                <div class="chart-header">
                    <h3 class="chart-title">👥 Targeted Users</h3>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($targetedUsers as $user)
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 600; font-size: 0.75rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.8rem; color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $user->name }}
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">
                                {{ $user->device_type }}
                            </div>
                        </div>
                        @if($user->fcm_token)
                        <i data-lucide="check-circle" style="width: 16px; height: 16px; color: var(--success);"></i>
                        @else
                        <i data-lucide="x-circle" style="width: 16px; height: 16px; color: var(--error);" title="No FCM token"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">⚡ Actions</h3>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @if($pushNotification->status === 'failed')
                    <form action="{{ route('admin.push-notifications.resend', $pushNotification) }}" method="POST">
                        @csrf
                        <button type="submit" style="width: 100%; padding: 0.75rem; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; color: var(--success); font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                            Tuma Tena
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('admin.push-notifications.create') }}" style="width: 100%; padding: 0.75rem; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 10px; color: var(--info); font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i data-lucide="copy" style="width: 16px; height: 16px;"></i>
                        Duplicate & Send
                    </a>
                    
                    <form action="{{ route('admin.push-notifications.destroy', $pushNotification) }}" method="POST" onsubmit="return confirm('Una uhakika?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="width: 100%; padding: 0.75rem; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; color: var(--error); font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                            Delete Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
