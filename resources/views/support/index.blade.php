@extends('layouts.app')

@section('title', 'Support Center')
@section('page-title', 'Support Center')
@section('page-subtitle', 'Wasiliana nasi kwa msaada zaidi')

@section('content')
<div class="grid grid-responsive-3 gap-6">
    <!-- Left: WhatsApp & Community -->
    <div class="card-stack">
        <div class="card glass" style="padding: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.2);">
            <div class="flex flex-col items-center text-center">
                <div style="width: 60px; height: 60px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 1rem; box-shadow: 0 0 20px rgba(37, 211, 102, 0.3);">
                    <i data-lucide="phone" style="width: 30px; height: 30px;"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">WhatsApp Community</h3>
                <p style="font-size: 0.85rem; margin-bottom: 1.5rem;">Jiunge na jamii yetu ya WhatsApp kupata updates za haraka na msaada kutoka kwa wanachama wengine.</p>
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="btn btn-primary w-full" style="background: #25D366; border: none;">
                    <i data-lucide="external-link"></i>
                    Fungua WhatsApp
                </a>
            </div>
        </div>

        <div class="card glass" style="padding: 1.5rem;">
            <h4 style="margin-bottom: 1rem;">Miongozo ya Haraka</h4>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="help-circle" style="width: 16px; height: 16px; color: var(--primary);"></i>
                    <span style="font-size: 0.85rem;">Jinsi ya kutoa pesa</span>
                </li>
                <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="help-circle" style="width: 16px; height: 16px; color: var(--primary);"></i>
                    <span style="font-size: 0.85rem;">Jinsi ya kukamilisha tasks</span>
                </li>
                <li style="padding: 0.75rem 0; display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="help-circle" style="width: 16px; height: 16px; color: var(--primary);"></i>
                    <span style="font-size: 0.85rem;">Sheria za Referral</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right: Support Tickets -->
    <div class="grid-responsive-2" style="grid-column: span 2;">
        <div class="card glass" style="padding: 1.5rem;">
            <div class="flex justify-between items-center mb-6">
                <h3 style="margin: 0;">Tiketi Zako</h3>
                <a href="{{ route('support.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus"></i>
                    Tiketi Mpya
                </a>
            </div>

            @if($tickets->isEmpty())
                <div class="text-center py-12">
                    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-muted);">
                        <i data-lucide="message-square" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h4 style="color: var(--text-muted);">Huna tiketi yoyote kwa sasa</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Ukipata shida yoyote, fungua tiketi hapa.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Somo</th>
                                <th>Hali</th>
                                <th>Ujumbe wa Mwisho</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $ticket->subject }}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">#{{ $ticket->id }} • {{ $ticket->created_at->format('d M, Y') }}</div>
                                </td>
                                <td>
                                    @if($ticket->status === 'open')
                                        <span class="badge badge-success">Wazi</span>
                                    @else
                                        <span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-muted);">Imefungwa</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem;">{{ $ticket->last_message_at->diffForHumans() }}</div>
                                    @if($ticket->unreadMessagesCount() > 0)
                                        <span class="badge badge-primary" style="font-size: 0.6rem;">{{ $ticket->unreadMessagesCount() }} Mpya</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('support.show', $ticket->id) }}" class="btn btn-secondary btn-sm">
                                        Fungua
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
