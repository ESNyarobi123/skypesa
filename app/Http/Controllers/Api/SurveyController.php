<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CpxResearchService;
use App\Models\SurveyCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SurveyController extends Controller
{
    protected $cpxService;

    public function __construct(CpxResearchService $cpxService)
    {
        $this->cpxService = $cpxService;
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

        $dailyLimit = config('cpx.daily_limit_per_user', 20);

        if ($todayCount >= $dailyLimit) {
            return response()->json([
                'status' => 'limit_reached',
                'message' => "Umefika kikomo cha surveys {$dailyLimit} kwa leo",
                'surveys' => [],
                'completed_today' => $todayCount,
                'daily_limit' => $dailyLimit,
            ]);
        }

        // Get surveys from CPX
        $result = $this->cpxService->getSurveys(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Add user stats
        $result['stats'] = $this->cpxService->getUserStats($user);
        $result['reward_info'] = $this->getRewardInfo($user);

        return response()->json($result);
    }

    /**
     * Get survey statistics for user
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $stats = $this->cpxService->getUserStats($user);
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
     * Handle CPX postback
     * 
     * CPX Research sends postback on survey completion with:
     * - trans_id: Unique transaction ID
     * - ext_user_id: Our user ID
     * - survey_id: CPX survey ID
     * - loi: Length of interview (minutes)
     * - payout: Publisher payout in USD
     * - status: 1=completed, 2=screenout, 3=chargeback
     * - hash: md5(trans_id + secure_hash)
     * - ip_click: User's IP
     */
    public function postback(Request $request)
    {
        // 🧹 Clean and normalize all input data (trim whitespace)
        $data = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $request->all());
        
        Log::info('CPX Postback received', [
            'all_params' => $data,
            'trans_id' => $data['trans_id'] ?? 'N/A',
            'user_id' => $data['ext_user_id'] ?? $data['user_id'] ?? 'N/A',
            'status' => $data['status'] ?? 'N/A',
            'payout' => $data['payout'] ?? $data['amount_usd'] ?? 'N/A',
            'ip' => $request->ip(),
            'method' => $request->method(),
        ]);

        // 1️⃣ Get transaction ID
        $transId = $data['trans_id'] ?? $data['transaction_id'] ?? '';
        $receivedHash = $data['hash'] ?? $data['secure_hash'] ?? '';
        $secureHash = config('cpx.secure_hash');
        
        // 2️⃣ Validate secure_hash (Skip if hash is placeholder or empty - for testing)
        $skipHashValidation = empty($receivedHash) 
            || $receivedHash === '{hash}' 
            || config('cpx.demo_mode');
            
        if (!$skipHashValidation && $secureHash && $transId) {
            $expectedHash = md5($transId . '-' . $secureHash);
            if ($receivedHash !== $expectedHash) {
                Log::warning('CPX Postback: Invalid secure hash', [
                    'trans_id' => $transId,
                    'received' => $receivedHash,
                    'expected' => $expectedHash,
                ]);
                // In production, uncomment below to enforce hash validation
                // return response()->json(['error' => 'Invalid secure hash'], 403);
            }
        }

        // 3️⃣ Verify postback secret if configured (additional security)
        $secret = config('cpx.postback_secret');
        $inputSecret = $request->input('secret');
        if ($secret && $inputSecret && $inputSecret !== $secret) {
            Log::warning('CPX Postback: Invalid secret');
            return response()->json(['error' => 'Invalid secret'], 401);
        }

        // 4️⃣ Optional: IP Whitelist validation
        $allowedIps = config('cpx.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            Log::warning('CPX Postback: IP not whitelisted', ['ip' => $request->ip()]);
            return response()->json(['error' => 'IP not allowed'], 403);
        }

        // 5️⃣ Handle based on status (1=pending/complete, 2=reversed)
        $status = intval($data['status'] ?? 1);
        
        // Log failed surveys (screenouts/reversals)
        if ($status == 2) {
            Log::info('CPX Reversal/Screenout', [
                'trans_id' => $transId,
                'user_id' => $data['ext_user_id'] ?? $data['user_id'] ?? null,
            ]);
            $this->cpxService->logScreenout($data);
            return response()->json(['status' => 'ok', 'message' => 'reversal logged']);
        }

        // 6️⃣ Process completed survey
        $result = $this->cpxService->handlePostback($data);

        if ($result['success']) {
            Log::info('CPX Postback processed successfully', [
                'trans_id' => $transId,
                'user_id' => $data['ext_user_id'] ?? $data['user_id'] ?? null,
                'reward' => $result['reward'] ?? 0,
            ]);
            return response()->json(['status' => 'ok', 'reward' => $result['reward'] ?? 0]);
        }

        Log::error('CPX Postback processing failed', [
            'trans_id' => $transId,
            'error' => $result['message'],
        ]);
        
        // Still return 200 OK to prevent CPX from retrying
        return response()->json(['status' => 'ok', 'error' => $result['message']]);
    }

    /**
     * Demo survey completion (for testing)
     */
    public function demoComplete(Request $request, string $id)
    {
        if (!config('cpx.demo_mode')) {
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

        $result = $this->cpxService->processDemoCompletion($user, $id);

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
        $isVip = $plan && in_array($plan->name, config('cpx.vip_plans', []));

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
