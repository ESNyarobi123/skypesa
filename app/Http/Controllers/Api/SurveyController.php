<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitLabsService;
use App\Models\SurveyCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SurveyController extends Controller
{
    protected $bitLabsService;

    public function __construct(BitLabsService $bitLabsService)
    {
        $this->bitLabsService = $bitLabsService;
    }

    /**
     * Get available surveys for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check daily limit
        $todayCount = SurveyCompletion::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        $dailyLimit = config('bitlabs.daily_limit_per_user', 20);

        if ($todayCount >= $dailyLimit) {
            return response()->json([
                'status' => 'limit_reached',
                'message' => "Umefika kikomo cha surveys {$dailyLimit} kwa leo",
                'surveys' => [],
                'completed_today' => $todayCount,
                'daily_limit' => $dailyLimit,
            ]);
        }

        // Get surveys from BitLabs
        $result = $this->bitLabsService->getSurveys(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Add user stats
        $result['stats'] = $this->bitLabsService->getUserStats($user);
        $result['reward_info'] = $this->getRewardInfo($user);

        return response()->json($result);
    }

    /**
     * Get survey statistics for user
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $stats = $this->bitLabsService->getUserStats($user);
        $stats['reward_info'] = $this->getRewardInfo($user);

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * Get user's survey history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $completions = SurveyCompletion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $completions->map(function ($completion) {
                return [
                    'id' => $completion->id,
                    'survey_id' => $completion->survey_id,
                    'type' => $completion->survey_type,
                    'type_label' => $completion->getTypeLabel(),
                    'loi' => $completion->loi,
                    'reward' => $completion->user_reward,
                    'reward_formatted' => 'TZS ' . number_format($completion->user_reward, 0),
                    'status' => $completion->status,
                    'status_label' => $completion->getStatusLabel(),
                    'completed_at' => $completion->completed_at?->toISOString(),
                    'created_at' => $completion->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $completions->currentPage(),
                'last_page' => $completions->lastPage(),
                'per_page' => $completions->perPage(),
                'total' => $completions->total(),
            ],
        ]);
    }

    /**
     * Handle BitLabs callback/postback
     * 
     * BitLabs sends callback on survey completion with:
     * - user_id/uid: Our user ID
     * - tx: Unique transaction ID
     * - value: Publisher payout in USD
     * - status: 'complete', 'screenout', 'reversed'
     * - hash: Security hash for verification
     * - loi: Length of interview (minutes)
     */
    public function postback(Request $request)
    {
        // 🧹 Clean and normalize all input data (trim whitespace)
        $data = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $request->all());
        
        Log::info('BitLabs Callback received', [
            'all_params' => $data,
            'tx' => $data['tx'] ?? 'N/A',
            'user_id' => $data['user_id'] ?? $data['uid'] ?? 'N/A',
            'status' => $data['status'] ?? 'N/A',
            'value' => $data['value'] ?? 'N/A',
            'ip' => $request->ip(),
            'method' => $request->method(),
        ]);

        // 1️⃣ Verify callback hash for security
        if (!config('bitlabs.demo_mode') && !$this->bitLabsService->verifyCallbackHash($data)) {
            Log::warning('BitLabs Callback: Invalid hash', [
                'tx' => $data['tx'] ?? null,
                'received_hash' => $data['hash'] ?? null,
            ]);
            // Still return 200 to prevent retries, but log the issue
            // In production, you may want to return 403
        }

        // 2️⃣ Optional: IP Whitelist validation
        $allowedIps = config('bitlabs.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            Log::warning('BitLabs Callback: IP not whitelisted', ['ip' => $request->ip()]);
            return response()->json(['error' => 'IP not allowed'], 403);
        }

        // 3️⃣ Process the callback
        $result = $this->bitLabsService->handleCallback($data);

        if ($result['success']) {
            Log::info('BitLabs Callback processed successfully', [
                'tx' => $data['tx'] ?? $data['transaction_id'] ?? null,
                'user_id' => $data['user_id'] ?? $data['uid'] ?? null,
                'reward' => $result['reward'] ?? 0,
            ]);
            return response()->json([
                'status' => 'ok', 
                'reward' => $result['reward'] ?? 0,
                'message' => $result['message'] ?? 'Success',
            ]);
        }

        Log::error('BitLabs Callback processing failed', [
            'tx' => $data['tx'] ?? $data['transaction_id'] ?? null,
            'error' => $result['message'],
        ]);
        
        // Still return 200 OK to prevent BitLabs from retrying
        return response()->json(['status' => 'ok', 'error' => $result['message']]);
    }

    /**
     * Demo survey completion (for testing)
     */
    public function demoComplete(Request $request, string $id)
    {
        if (!config('bitlabs.demo_mode')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Demo mode disabled',
            ], 400);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $result = $this->bitLabsService->processDemoCompletion($user, $id);

        if ($result['success']) {
            return response()->json([
                'status' => 'success',
                'message' => 'Survey imekamilika! Pesa imewekwa kwenye wallet yako.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Get reward info based on user's subscription
     */
    protected function getRewardInfo($user): array
    {
        $plan = $user->getCurrentPlan();
        $isVip = $plan && in_array(strtolower($plan->name), config('bitlabs.vip_plans', []));

        return [
            'is_vip' => $isVip,
            'plan_name' => $plan?->display_name ?? 'Free',
            'rewards' => [
                'short' => [
                    'loi' => '5-7 dakika',
                    'reward' => 200,
                    'reward_formatted' => 'TZS 200',
                    'available' => true,
                ],
                'medium' => [
                    'loi' => '8-12 dakika',
                    'reward' => 300,
                    'reward_formatted' => 'TZS 300',
                    'available' => true,
                ],
                'long' => [
                    'loi' => '15+ dakika',
                    'reward' => 500,
                    'reward_formatted' => 'TZS 500',
                    'available' => $isVip,
                    'vip_only' => true,
                    'upgrade_message' => $isVip ? null : 'Upgrade kwa VIP kupata TZS 500 surveys',
                ],
            ],
        ];
    }
}
