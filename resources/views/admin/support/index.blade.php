@extends('layouts.app')

@section('title', 'Admin Support Center')
@section('page-title', 'Support Center')
@section('page-subtitle', 'Simamia maombi ya msaada kutoka kwa watumiaji')

@section('content')
<div class="card glass" style="padding: 1.5rem;">
    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-4">
            <a href="{{ route('admin.support.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-secondary' }} btn-sm">Zote</a>
            <a href="{{ route('admin.support.index', ['status' => 'open']) }}" class="btn {{ request('status') === 'open' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Wazi</a>
            <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" class="btn {{ request('status') === 'closed' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Zilizofungwa</a>
        </div>
    </div>

    @if($tickets->isEmpty())
        <div class="text-center py-12">
            <i data-lucide="message-square" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h4 style="color: var(--text-muted);">Hakuna tiketi zilizopatikana</h4>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mtumiaji</th>
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
                            <div class="flex items-center gap-3">
                                <img src="{{ $ticket->user->getAvatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%;">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.85rem;">{{ $ticket->user->name }}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $ticket->user->email }}</div>
                                </div>
                            </div>
                        </td>
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
                            @if($ticket->unreadAdminMessagesCount() > 0)
                                <span class="badge badge-error" style="font-size: 0.6rem;">{{ $ticket->unreadAdminMessagesCount() }} Mpya</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-secondary btn-sm">
                                Jibu
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
@endsection
