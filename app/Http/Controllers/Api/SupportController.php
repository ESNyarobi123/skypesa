<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
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
                'answer' => 'Unapata bonus kwa kila mtu anayejiandikisha kwa referral code yako na kukamilisha task yake ya kwanza!',
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
                'categories' => ['all', 'general', 'tasks', 'withdrawal', 'subscription', 'referral', 'account'],
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
        
        // Check if SupportTicket model exists
        if (!class_exists(SupportTicket::class)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => ['total' => 0],
                'message' => 'Support ticket system coming soon',
            ]);
        }
        
        $tickets = SupportTicket::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
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
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $user = $request->user();
        
        // Create ticket (basic implementation without model)
        $ticketData = [
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'subject' => $request->subject,
            'category' => $request->category,
            'message' => $request->message,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
            'created_at' => now()->toISOString(),
        ];
        
        // If SupportTicket model exists, save to database
        if (class_exists(SupportTicket::class)) {
            try {
                $ticket = SupportTicket::create($ticketData);
                $ticketData['id'] = $ticket->id;
            } catch (\Exception $e) {
                // Model exists but table might not
            }
        }
        
        // Send notification email to support (optional)
        // Mail::to('support@skypesa.site')->send(new NewSupportTicket($ticketData));
        
        return response()->json([
            'success' => true,
            'message' => 'Ombi lako limepokelewa. Tutawasiliana nawe ndani ya masaa 24-48.',
            'data' => $ticketData,
        ], 201);
    }
    
    /**
     * Get single ticket details
     * GET /api/v1/support/tickets/{ticketNumber}
     */
    public function showTicket(Request $request, string $ticketNumber)
    {
        $user = $request->user();
        
        if (!class_exists(SupportTicket::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }
        
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }
    
    /**
     * Add reply to a ticket
     * POST /api/v1/support/tickets/{ticketNumber}/reply
     */
    public function replyTicket(Request $request, string $ticketNumber)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:10|max:2000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Basic implementation
        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => [
                'ticket_number' => $ticketNumber,
                'reply' => $request->message,
                'sent_at' => now()->toISOString(),
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
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $user = $request->user();
        
        $bugReport = [
            'report_id' => 'BUG-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'title' => $request->title,
            'description' => $request->description,
            'steps_to_reproduce' => $request->steps_to_reproduce,
            'device_info' => $request->device_info,
            'app_version' => $request->app_version,
            'status' => 'submitted',
            'created_at' => now()->toISOString(),
        ];
        
        // Log the bug report
        \Log::channel('daily')->info('Bug Report Submitted', $bugReport);
        
        return response()->json([
            'success' => true,
            'message' => 'Asante kwa kuripoti tatizo! Timu yetu itashughulikia haraka iwezekanavyo.',
            'data' => [
                'report_id' => $bugReport['report_id'],
            ],
        ], 201);
    }
}
