<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'lastMessage'])->latest('last_message_at');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(20);
        
        return view('admin.support.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])->findOrFail($id);
        
        // Mark user messages as read
        $ticket->messages()->where('is_admin', false)->where('is_read', false)->update(['is_read' => true]);
        
        return view('admin.support.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $request->validate([
            'message' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $ticket) {
            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_admin' => true,
            ]);

            $ticket->update(['last_message_at' => now()]);

            // Notify user
            Notification::notify(
                $ticket->user,
                'system',
                '💬 Jibu la Support',
                'Umekuwa na jibu jipya kwenye tiketi yako: ' . $ticket->subject
            );
        });

        return back()->with('success', 'Jibu limetumwa kwa mtumiaji.');
    }

    public function close($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => 'closed']);
        
        return back()->with('success', 'Tiketi imefungwa.');
    }
}
