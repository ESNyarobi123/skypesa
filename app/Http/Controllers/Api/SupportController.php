<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Get support contact info
     * GET /api/v1/support/contact
     */
    public function contact()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'email' => config('app.support_email', 'support@skypesa.site'),
                'phone' => config('app.support_phone', '+255 700 000 000'),
                'whatsapp' => config('app.support_whatsapp', '+255700000000'),
                'working_hours' => 'Mon-Fri: 9:00 AM - 6:00 PM (EAT)',
                'response_time' => 'Within 24-48 hours',
                'social' => [
                    'telegram' => config('app.telegram_channel', 'https://t.me/skypesa'),
                    'instagram' => config('app.instagram', 'https://instagram.com/skypesa'),
                    'facebook' => config('app.facebook', 'https://facebook.com/skypesa'),
                ],
            ],
        ]);
    }
    
    /**
     * Get FAQs
     * GET /api/v1/support/faq
     */
    public function faq()
    {
        $faqs = [
            [
                'id' => 1,
                'category' => 'general',
                'question' => 'SKYpesa ni nini?',
                'answer' => 'SKYpesa ni jukwaa la kipekee linalowaruhusu watu kupata pesa kwa kutazama matangazo. Unaweza kupata hadi TZS 200,000+ kwa mwezi!',
            ],
            [
                'id' => 2,
                'category' => 'general',
                'question' => 'Je, ni bure kujisajili?',
                'answer' => 'Ndio! Kujisajili ni bure kabisa. Mpango wa bure unakupa tasks 5 kwa siku.',
            ],
            [
                'id' => 3,
                'category' => 'tasks',
                'question' => 'Jinsi gani nakamilisha task?',
                'answer' => 'Bofya task, tazama ad au video hadi mwisho, na pesa itaingia moja kwa moja kwenye wallet yako!',
            ],
            [
                'id' => 4,
                'category' => 'tasks',
                'question' => 'Kwa nini napata tasks chache?',
                'answer' => 'Idadi ya tasks inategemea mpango wako. Upgrade kwa Silver au VIP kupata tasks nyingi zaidi na pesa kubwa!',
            ],
            [
                'id' => 5,
                'category' => 'withdrawal',
                'question' => 'Naweza kutoa pesa lini?',
                'answer' => 'Unaweza kutoa pesa unapofikia kiwango cha chini (TZS 5,000 kwa Silver/VIP, TZS 10,000 kwa Free).',
            ],
            [
                'id' => 6,
                'category' => 'withdrawal',
                'question' => 'Inachukua muda gani kupata pesa?',
                'answer' => 'Kawaida masaa 24-48. Pesa huenda moja kwa moja M-Pesa, Tigo Pesa, Airtel Money, au Halo Pesa.',
            ],
            [
                'id' => 7,
                'category' => 'subscription',
                'question' => 'Tofauti ya mipango ni ipi?',
                'answer' => 'Free: 5 tasks/day @ TZS 100. Silver: 10 tasks/day @ TZS 500. VIP: 200 tasks/day @ TZS 1,000. Upgrade upate zaidi!',
            ],
            [
                'id' => 8,
                'category' => 'referral',
                'question' => 'Napata nini nikishirikisha watu?',
                'answer' => 'Unapata bonus kwa kila mtu anayejiandikisha kwa referral code yako na kukamilisha tasks 15 za kwanza!',
            ],
            [
                'id' => 9,
                'category' => 'account',
                'question' => 'Nimesahau password yangu',
                'answer' => 'Bofya "Forgot Password" kwenye login page. Utapata OTP kwenye email yako kubadilisha password.',
            ],
            [
                'id' => 10,
                'category' => 'account',
                'question' => 'Naweza kubadilisha namba ya simu?',
                'answer' => 'Ndio, nenda Settings > Profile > Edit Phone Number.',
            ],
        ];
        
        $category = request('category');
        
        if ($category && $category !== 'all') {
            $faqs = array_filter($faqs, fn($faq) => $faq['category'] === $category);
            $faqs = array_values($faqs); // Re-index
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => [
                    ['id' => 'all', 'name' => 'Zote', 'icon' => 'list'],
                    ['id' => 'general', 'name' => 'Jumla', 'icon' => 'info'],
                    ['id' => 'tasks', 'name' => 'Tasks', 'icon' => 'play'],
                    ['id' => 'withdrawal', 'name' => 'Kutoa Pesa', 'icon' => 'wallet'],
                    ['id' => 'subscription', 'name' => 'Subscription', 'icon' => 'crown'],
                    ['id' => 'referral', 'name' => 'Referral', 'icon' => 'users'],
                    ['id' => 'account', 'name' => 'Akaunti', 'icon' => 'user'],
                ],
                'faqs' => $faqs,
            ],
        ]);
    }
    
    /**
     * Get user's support tickets
     * GET /api/v1/support/tickets
     */
    public function tickets(Request $request)
    {
        $user = $request->user();
        
        $status = $request->query('status');
        
        $query = SupportTicket::where('user_id', $user->id)
            ->with(['lastMessage', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->latest();
        
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        $tickets = $query->paginate(20);
        
        $formattedTickets = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'category_label' => $ticket->getCategoryLabel(),
                'priority' => $ticket->priority,
                'priority_label' => $ticket->getPriorityLabel(),
                'priority_color' => $ticket->getPriorityColor(),
                'status' => $ticket->status,
                'status_label' => $ticket->getStatusLabel(),
                'status_color' => $ticket->getStatusColor(),
                'unread_count' => $ticket->unreadMessagesCount(),
                'last_message_at' => $ticket->last_message_at?->toIso8601String(),
                'created_at' => $ticket->created_at->toIso8601String(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedTickets,
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }
    
    /**
     * Create a new support ticket
     * POST /api/v1/support/tickets
     */
    public function createTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:200',
            'category' => 'required|string|in:general,task,withdrawal,subscription,account,bug,other',
            'message' => 'required|string|min:20|max:2000',
            'priority' => 'sometimes|string|in:low,medium,high',
        ], [
            'subject.required' => 'Tafadhali weka mada.',
            'subject.max' => 'Mada isizidi herufi 200.',
            'category.required' => 'Tafadhali chagua kategoria.',
            'category.in' => 'Kategoria uliochagua haipo.',
            'message.required' => 'Tafadhali andika ujumbe wako.',
            'message.min' => 'Ujumbe uwe na angalau herufi 20.',
            'message.max' => 'Ujumbe usizidi herufi 2000.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $user = $request->user();
        
        try {
            DB::beginTransaction();
            
            // Create ticket
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'category' => $request->category,
                'priority' => $request->priority ?? 'medium',
                'initial_message' => $request->message,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
            
            // Create initial message
            $message = SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'is_admin' => false,
                'is_read' => true, // User's own message is already "read"
            ]);
            
            DB::commit();
            
            // Log for admin notification
            Log::info('New support ticket created', [
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Ombi lako limepokelewa. Tutawasiliana nawe ndani ya masaa 24-48.',
                'data' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'category' => $ticket->category,
                    'category_label' => $ticket->getCategoryLabel(),
                    'priority' => $ticket->priority,
                    'priority_label' => $ticket->getPriorityLabel(),
                    'status' => $ticket->status,
                    'status_label' => $ticket->getStatusLabel(),
                    'created_at' => $ticket->created_at->toIso8601String(),
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Support ticket creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena baadaye.',
            ], 500);
        }
    }
    
    /**
     * Get single ticket details with messages
     * GET /api/v1/support/tickets/{ticketNumber}
     */
    public function showTicket(Request $request, string $ticketNumber)
    {
        $user = $request->user();
        
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->with(['messages.user:id,name,avatar', 'assignedTo:id,name'])
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi haijapatikana.',
            ], 404);
        }
        
        // Mark admin messages as read
        $ticket->messages()
            ->where('is_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        $formattedMessages = $ticket->messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'is_admin' => $msg->is_admin,
                'sender_name' => $msg->is_admin ? 'Support Team' : $msg->user->name,
                'sender_avatar' => $msg->is_admin ? null : $msg->user->avatar,
                'is_read' => $msg->is_read,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'category_label' => $ticket->getCategoryLabel(),
                'priority' => $ticket->priority,
                'priority_label' => $ticket->getPriorityLabel(),
                'priority_color' => $ticket->getPriorityColor(),
                'status' => $ticket->status,
                'status_label' => $ticket->getStatusLabel(),
                'status_color' => $ticket->getStatusColor(),
                'assigned_to' => $ticket->assignedTo?->name,
                'messages' => $formattedMessages,
                'can_reply' => in_array($ticket->status, ['open', 'in_progress']),
                'last_message_at' => $ticket->last_message_at?->toIso8601String(),
                'resolved_at' => $ticket->resolved_at?->toIso8601String(),
                'created_at' => $ticket->created_at->toIso8601String(),
            ],
        ]);
    }
    
    /**
     * Add reply to a ticket
     * POST /api/v1/support/tickets/{ticketNumber}/reply
     */
    public function replyTicket(Request $request, string $ticketNumber)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:2000',
        ], [
            'message.required' => 'Tafadhali andika ujumbe.',
            'message.min' => 'Ujumbe uwe na angalau herufi 5.',
            'message.max' => 'Ujumbe usizidi herufi 2000.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $user = $request->user();
        
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi haijapatikana.',
            ], 404);
        }
        
        // Check if ticket can be replied to
        if (!in_array($ticket->status, ['open', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi hii haiwezi kujibiwa. Ilifungwa.',
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create message
            $message = SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'is_admin' => false,
                'is_read' => true,
            ]);
            
            // Update ticket last_message_at
            $ticket->update(['last_message_at' => now()]);
            
            // Reopen if it was resolved
            if ($ticket->status === 'resolved') {
                $ticket->reopen();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Ujumbe umetumwa.',
                'data' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_admin' => false,
                    'sender_name' => $user->name,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Support reply failed', [
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena baadaye.',
            ], 500);
        }
    }
    
    /**
     * Close a ticket (user action)
     * POST /api/v1/support/tickets/{ticketNumber}/close
     */
    public function closeTicket(Request $request, string $ticketNumber)
    {
        $user = $request->user();
        
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi haijapatikana.',
            ], 404);
        }
        
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi hii tayari imefungwa.',
            ], 400);
        }
        
        $ticket->close();
        
        return response()->json([
            'success' => true,
            'message' => 'Tiketi imefungwa.',
            'data' => [
                'ticket_number' => $ticket->ticket_number,
                'status' => 'closed',
                'status_label' => 'Imefungwa',
            ],
        ]);
    }
    
    /**
     * Reopen a ticket (user action)
     * POST /api/v1/support/tickets/{ticketNumber}/reopen
     */
    public function reopenTicket(Request $request, string $ticketNumber)
    {
        $user = $request->user();
        
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi haijapatikana.',
            ], 404);
        }
        
        if (!in_array($ticket->status, ['closed', 'resolved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tiketi hii haiwezi kufunguliwa tena.',
            ], 400);
        }
        
        $ticket->reopen();
        
        return response()->json([
            'success' => true,
            'message' => 'Tiketi imefunguliwa tena.',
            'data' => [
                'ticket_number' => $ticket->ticket_number,
                'status' => 'open',
                'status_label' => 'Wazi',
            ],
        ]);
    }
    
    /**
     * Report a bug
     * POST /api/v1/support/bug-report
     */
    public function bugReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'required|string|min:20|max:2000',
            'steps_to_reproduce' => 'sometimes|string|max:2000',
            'device_info' => 'sometimes|string|max:500',
            'app_version' => 'sometimes|string|max:20',
        ], [
            'title.required' => 'Tafadhali weka kichwa.',
            'description.required' => 'Tafadhali eleza tatizo.',
            'description.min' => 'Maelezo yawe na angalau herufi 20.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kuna makosa kwenye fomu.',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $user = $request->user();
        
        try {
            // Create a support ticket with bug category and high priority
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'subject' => '[BUG] ' . $request->title,
                'category' => 'bug',
                'priority' => 'high',
                'initial_message' => $this->formatBugReport($request),
                'status' => 'open',
                'last_message_at' => now(),
            ]);
            
            // Create initial message
            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $this->formatBugReport($request),
                'is_admin' => false,
                'is_read' => true,
            ]);
            
            // Log the bug report
            Log::channel('daily')->warning('Bug Report Submitted', [
                'report_id' => $ticket->ticket_number,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'title' => $request->title,
                'device_info' => $request->device_info,
                'app_version' => $request->app_version,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Asante kwa kuripoti tatizo! Timu yetu itashughulikia haraka iwezekanavyo.',
                'data' => [
                    'report_id' => $ticket->ticket_number,
                    'ticket_number' => $ticket->ticket_number,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Bug report failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kuna tatizo. Jaribu tena baadaye.',
            ], 500);
        }
    }
    
    /**
     * Format bug report message
     */
    private function formatBugReport(Request $request): string
    {
        $message = "**Maelezo ya Tatizo:**\n" . $request->description;
        
        if ($request->steps_to_reproduce) {
            $message .= "\n\n**Hatua za Kufanya Tatizo Litokee:**\n" . $request->steps_to_reproduce;
        }
        
        if ($request->device_info) {
            $message .= "\n\n**Taarifa za Kifaa:**\n" . $request->device_info;
        }
        
        if ($request->app_version) {
            $message .= "\n\n**Version ya App:** " . $request->app_version;
        }
        
        return $message;
    }
    
    /**
     * Get ticket statistics for user
     * GET /api/v1/support/stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total' => SupportTicket::where('user_id', $user->id)->count(),
            'open' => SupportTicket::where('user_id', $user->id)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('user_id', $user->id)->where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('user_id', $user->id)->where('status', 'closed')->count(),
            'unread_messages' => 0,
        ];
        
        // Count unread admin messages across all tickets
        $tickets = SupportTicket::where('user_id', $user->id)->get();
        foreach ($tickets as $ticket) {
            $stats['unread_messages'] += $ticket->unreadMessagesCount();
        }
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
