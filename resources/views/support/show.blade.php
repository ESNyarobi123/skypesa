@extends('layouts.app')

@section('title', 'Support Chat')
@section('page-title', 'Support Chat')
@section('page-subtitle', 'Tiketi #' . $ticket->id . ' - ' . $ticket->subject)

@section('content')
<style>
    .chat-container {
        height: calc(100vh - 220px);
        min-height: 500px;
        display: flex;
        flex-direction: column;
        background: #0b141a; /* WhatsApp Dark Background */
        border-radius: var(--radius-xl);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.05);
        position: relative;
    }

    /* WhatsApp-like background pattern */
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
        background: #005c4b; /* WhatsApp Sent Green */
        color: #e9edef;
        border-radius: 8px 0 8px 8px;
    }

    .received .message-bubble {
        background: #202c33; /* WhatsApp Received Grey */
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

    /* Scrollbar */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    .chat-messages::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <div class="flex items-center gap-3">
                <div style="width: 40px; height: 40px; background: #00a884; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i data-lucide="shield-check" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #e9edef; font-size: 1rem;">SKYpesa Support</h4>
                    <div style="font-size: 0.75rem; color: #8696a0;">
                        @if($ticket->status === 'open')
                            <span style="color: #00a884;">●</span> Online
                        @else
                            Tiketi Imefungwa
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('support.index') }}" class="header-btn" title="Rudi">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </a>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="date-separator">
                <span class="date-badge">Tiketi: {{ $ticket->subject }}</span>
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

                <div class="message-wrapper {{ $message->is_admin ? 'received' : 'sent' }}">
                    <div class="message-bubble">
                        {{ $message->message }}
                        <div class="message-info">
                            {{ $message->created_at->format('H:i') }}
                            @if(!$message->is_admin)
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
            <form action="{{ route('support.reply', $ticket->id) }}" method="POST" class="chat-input-area">
                @csrf
                <button type="button" class="send-btn">
                    <i data-lucide="smile"></i>
                </button>
                <button type="button" class="send-btn">
                    <i data-lucide="plus"></i>
                </button>
                <input type="text" name="message" placeholder="Andika ujumbe..." required autocomplete="off">
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

<script>
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Auto-scroll on image load if any
    window.onload = () => {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };
</script>
@endsection
