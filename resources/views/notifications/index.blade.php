@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Taarifa zako na matukio muhimu')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12" style="max-width: 800px; margin: 0 auto;">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0;">Taarifa Zako</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Fuatilia matukio na bonus zako zote hapa.</p>
                </div>
                @if(auth()->user()->notifications()->where('is_read', false)->exists())
                    <form action="{{ route('notifications.read', 'all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <i data-lucide="check-check" style="width: 14px; height: 14px;"></i>
                            Soma Zote
                        </button>
                    </form>
                @endif
            </div>

            <div class="card" style="overflow: hidden; border: none; background: var(--bg-card);">
                <div class="card-body p-0">
                    @if($notifications->isEmpty())
                        <div class="text-center py-16">
                            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i data-lucide="bell-off" style="width: 32px; height: 32px; color: var(--text-muted);"></i>
                            </div>
                            <h3 style="color: white; margin-bottom: 0.5rem;">Huna taarifa yoyote</h3>
                            <p style="color: var(--text-muted); max-width: 300px; margin: 0 auto;">Tutakujulisha pindi utakapopata bonus au mualiko mpya.</p>
                            <a href="{{ route('tasks.index') }}" class="btn btn-primary mt-6">Anza Kazi Sasa</a>
                        </div>
                    @else
                        <div class="notification-list">
                            @foreach($notifications as $notification)
                                <div class="notification-item {{ $notification->is_read ? '' : 'unread' }}" 
                                     style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; gap: 1rem; transition: all 0.2s ease; position: relative;">
                                    
                                    @if(!$notification->is_read)
                                        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--primary);"></div>
                                    @endif

                                    <div class="notification-icon" style="flex-shrink: 0;">
                                        @php
                                            $icon = match($notification->type) {
                                                'referral' => 'users',
                                                'bonus' => 'gift',
                                                'task' => 'clipboard-check',
                                                'system' => 'shield-info',
                                                default => 'bell'
                                            };
                                            $color = match($notification->type) {
                                                'referral' => 'var(--primary)',
                                                'bonus' => 'var(--success)',
                                                'task' => 'var(--info)',
                                                'system' => 'var(--warning)',
                                                default => 'var(--primary)'
                                            };
                                        @endphp
                                        <div style="width: 48px; height: 48px; border-radius: 14px; background: {{ $color }}15; display: flex; align-items: center; justify-content: center; color: {{ $color }}; border: 1px solid {{ $color }}20;">
                                            <i data-lucide="{{ $icon }}" style="width: 22px; height: 22px;"></i>
                                        </div>
                                    </div>
                                    <div class="notification-content" style="flex: 1;">
                                        <div class="flex justify-between items-start mb-1">
                                            <h6 style="font-weight: 700; margin: 0; font-size: 1rem; color: {{ $notification->is_read ? 'var(--text-primary)' : 'white' }};">
                                                {{ $notification->title }}
                                            </h6>
                                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 500;">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                                            {{ $notification->message }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="p-6" style="background: rgba(0,0,0,0.1);">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .notification-item.unread {
        background: rgba(16, 185, 129, 0.03);
    }
    .notification-item:hover {
        background: rgba(255,255,255,0.02);
    }
    .notification-item:last-child {
        border-bottom: none;
    }
    /* Pagination styling */
    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    .page-link {
        background: var(--bg-elevated);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
    }
    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
    }
</style>
@endsection
