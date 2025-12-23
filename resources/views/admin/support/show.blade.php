@extends('layouts.app')

@section('title', 'Admin Support Chat')
@section('page-title', 'Support Chat')
@section('page-subtitle', 'Tiketi #' . $ticket->id . ' - ' . $ticket->user->name)

@section('content')
<style>
    .chat-container {
        height: calc(100vh - 220px);
        min-height: 500px;
        display: flex;
        flex-direction: column;
        background: #0b141a;
        border-radius: var(--radius-xl);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.05);
        position: relative;
    }

    .chat-container::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-repeat: repeat;
        opacity: 0.05;
        pointer-events: none;
    }

    .chat-header {
        padding: 0.75rem 1.25rem;
        background: #202c33;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        z-index: 1;
    }

    .message-wrapper {
        display: flex;
        width: 100%;
        margin-bottom: 2px;
    }

    .message-wrapper.sent {
        justify-content: flex-end;
    }

    .message-wrapper.received {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 75%;
        padding: 0.5rem 0.75rem 0.4rem 0.75rem;
        font-size: 0.9375rem;
        position: relative;
        line-height: 1.4;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }

    .sent .message-bubble {
        background: #005c4b;
        color: #e9edef;
        border-radius: 8px 0 8px 8px;
    }

    .received .message-bubble {
        background: #202c33;
        color: #e9edef;
        border-radius: 0 8px 8px 8px;
    }

    .message-info {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-top: 2px;
        font-size: 0.6875rem;
        color: rgba(233, 237, 239, 0.6);
    }

    .chat-input-area {
        padding: 0.5rem 1rem;
        background: #202c33;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10;
    }

    .chat-input-area input {
        flex: 1;
        background: #2a3942;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1rem;
        color: #d1d7db;
        outline: none;
        font-size: 0.9375rem;
    }

    .send-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: transparent;
        border: none;
        color: #8696a0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.2s;
    }

    .send-btn:hover {
        color: #00a884;
    }

    .date-separator {
        display: flex;
        justify-content: center;
        margin: 1rem 0;
        position: relative;
        z-index: 1;
    }

    .date-badge {
        background: #182229;
        color: #8696a0;
        font-size: 0.75rem;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .user-sidebar {
        background: #111b21;
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        height: fit-content;
        border: 1px solid rgba(255,255,255,0.05);
    }

    /* Scrollbar */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    .chat-messages::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }
</style>

<div class="grid grid-responsive-3 gap-6">
    <!-- Left: User Info -->
    <div class="user-sidebar">
        <div class="flex flex-col items-center text-center mb-6">
            <div style="position: relative;">
                <img src="{{ $ticket->user->getAvatarUrl() }}" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem; border: 3px solid #00a884;">
                <div style="position: absolute; bottom: 15px; right: 5px; width: 15px; height: 15px; background: #00a884; border-radius: 50%; border: 2px solid #111b21;"></div>
            </div>
            <h3 style="margin: 0; color: white;">{{ $ticket->user->name }}</h3>
            <p style="font-size: 0.85rem; color: #8696a0;">{{ $ticket->user->email }}</p>
            <div class="mt-2">
                <span class="badge badge-primary" style="background: #00a884;">{{ $ticket->user->getPlanName() }}</span>
            </div>
        </div>
        
        <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.5rem;">
            <div class="flex justify-between mb-2">
                <span style="font-size: 0.8rem; color: #8696a0;">Salio:</span>
                <span style="font-weight: 700; color: #00a884;">TZS {{ number_format($ticket->user->wallet?->balance ?? 0) }}</span>
            </div>
            <div class="flex justify-between mb-4">
                <span style="font-size: 0.8rem; color: #8696a0;">Hali ya Tiketi:</span>
                <span class="badge {{ $ticket->status === 'open' ? 'badge-success' : '' }}" style="{{ $ticket->status === 'open' ? 'background: #00a884;' : '' }}">
                    {{ $ticket->status === 'open' ? 'Wazi' : 'Imefungwa' }}
                </span>
            </div>
            
            @if($ticket->status === 'open')
                <form action="{{ route('admin.support.close', $ticket->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary btn-sm w-full" style="border-color: #ef4444; color: #ef4444;" onclick="return confirm('Je, una uhakika unataka kufunga tiketi hii?')">
                        <i data-lucide="lock"></i>
                        Funga Tiketi
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Right: Chat Interface -->
    <div style="grid-column: span 2;">
        <div class="chat-container">
            <!-- Header -->
            <div class="chat-header">
                <div class="flex items-center gap-3">
                    <img src="{{ $ticket->user->getAvatarUrl() }}" style="width: 40px; height: 40px; border-radius: 50%;">
                    <div>
                        <h4 style="margin: 0; color: #e9edef; font-size: 1rem;">{{ $ticket->user->name }}</h4>
                        <div style="font-size: 0.75rem; color: #8696a0;">Tiketi #{{ $ticket->id }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.support.index') }}" class="header-btn" title="Rudi">
                        <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <div class="chat-messages" id="chatMessages">
                <div class="date-separator">
                    <span class="date-badge">Somo: {{ $ticket->subject }}</span>
                </div>

                @php $lastDate = null; @endphp
                @foreach($ticket->messages as $message)
                    @php 
                        $currentDate = $message->created_at->format('d M, Y');
                    @endphp
                    
                    @if($lastDate !== $currentDate)
                        <div class="date-separator">
                            <span class="date-badge">
                                @if($message->created_at->isToday()) Leo
                                @elseif($message->created_at->isYesterday()) Jana
                                @else {{ $currentDate }}
                                @endif
                            </span>
                        </div>
                        @php $lastDate = $currentDate; @endphp
                    @endif

                    <div class="message-wrapper {{ $message->is_admin ? 'sent' : 'received' }}">
                        <div class="message-bubble">
                            {{ $message->message }}
                            <div class="message-info">
                                {{ $message->created_at->format('H:i') }}
                                @if($message->is_admin)
                                    <i data-lucide="{{ $message->is_read ? 'check-check' : 'check' }}" 
                                       style="width: 14px; height: 14px; {{ $message->is_read ? 'color: #53bdeb;' : '' }}"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input Area -->
            @if($ticket->status === 'open')
                <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST" class="chat-input-area">
                    @csrf
                    <button type="button" class="send-btn">
                        <i data-lucide="smile"></i>
                    </button>
                    <button type="button" class="send-btn">
                        <i data-lucide="paperclip"></i>
                    </button>
                    <input type="text" name="message" placeholder="Andika jibu..." required autocomplete="off">
                    <button type="submit" class="send-btn" style="color: #00a884;">
                        <i data-lucide="send-horizontal"></i>
                    </button>
                </form>
            @else
                <div style="padding: 1rem; text-align: center; background: #111b21; color: #8696a0; font-size: 0.875rem;">
                    <i data-lucide="lock" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                    Tiketi hii imefungwa. Huwezi kutuma ujumbe zaidi.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
</script>
@endsection
