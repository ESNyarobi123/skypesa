<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->supportTickets()->latest('last_message_at')->paginate(10);
        $whatsappNumber = Setting::get('whatsapp_support_number', '255700000000');
        
        return view('support.index', compact('tickets', 'whatsappNumber'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_admin' => false,
            ]);

            // Notify admin (you might want to notify specific admins or all)
            // For now, we just log or create a system notification if needed
        });

        return redirect()->route('support.index')->with('success', 'Tiketi yako imefunguliwa. Tutajibu hivi punde.');
    }

    public function show($id)
    {
        $ticket = auth()->user()->supportTickets()->with('messages.user')->findOrFail($id);
        
        // Mark admin messages as read
        $ticket->messages()->where('is_admin', true)->where('is_read', false)->update(['is_read' => true]);
        
        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = auth()->user()->supportTickets()->findOrFail($id);
        
        $request->validate([
            'message' => 'required|string',
        ]);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Tiketi hii imeshafungwa.');
        }

        DB::transaction(function () use ($request, $ticket) {
            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_admin' => false,
            ]);

            $ticket->update(['last_message_at' => now()]);
        });

        return back()->with('success', 'Ujumbe umetumwa.');
    }
}
