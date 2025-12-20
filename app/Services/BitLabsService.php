<?php

namespace App\Services;

use App\Models\User;
use App\Models\SurveyCompletion;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BitLabsService
{
    protected $apiToken;
    protected $secretKey;
    protected $s2sKey;
    protected $offerwallUrl;
    protected $apiUrl;
    protected $enabled;
    protected $demoMode;

    public function __construct()
    {
        $this->apiToken = config('bitlabs.api_token');
        $this->secretKey = config('bitlabs.secret_key');
        $this->s2sKey = config('bitlabs.s2s_key');
        $this->offerwallUrl = config('bitlabs.offerwall_url');
        $this->apiUrl = config('bitlabs.api_url');
        $this->enabled = config('bitlabs.enabled', true);
        $this->demoMode = config('bitlabs.demo_mode', false);
    }

    /**
     * Check if BitLabs is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->secretKey);
    }

    /**
     * Generate the BitLabs Offerwall URL for a user
     */
    public function getOfferwallUrl(User $user): string
    {
        if (!$this->isConfigured()) {
            return '';
        }

        // Build offerwall URL with user parameters
        $params = [
            'token' => $this->apiToken,
            'uid' => $user->id,
        ];

        // Add optional user data for better targeting
        if ($user->name) {
            $params['username'] = $user->name;
        }
        if ($user->email) {
            $params['email'] = $user->email;
        }
        if ($user->birth_date) {
            $birthDate = \Carbon\Carbon::parse($user->birth_date);
            $params['age'] = $birthDate->age;
        }
        if ($user->gender) {
            $params['gender'] = $user->gender === 'male' ? 'm' : 'f';
        }

        // Add country code
        $params['country'] = 'TZ';

        return $this->offerwallUrl . '?' . http_build_query($params);
    }

    /**
     * Get available surveys for a user (API method)
     */
    public function getSurveys(User $user, ?string $ip = null, ?string $userAgent = null): array
    {
        if (!$this->enabled) {
            return [
                'status' => 'error',
                'message' => 'Surveys zimezimwa kwa sasa',
                'surveys' => [],
            ];
        }

        // Demo mode for testing
        if ($this->demoMode) {
            return $this->getDemoSurveys($user);
        }

        if (!$this->isConfigured()) {
            Log::warning('BitLabs not configured');
            return [
                'status' => 'error',
                'message' => 'Survey system haijawekwa vizuri',
                'surveys' => [],
            ];
        }

        // Cache key per user
        $cacheKey = 'bitlabs_surveys_' . $user->id;
        
        return Cache::remember($cacheKey, config('bitlabs.cache_ttl', 120), function () use ($user, $ip, $userAgent) {
            return $this->fetchSurveys($user, $ip, $userAgent);
        });
    }

    /**
     * Fetch surveys from BitLabs API
     */
    protected function fetchSurveys(User $user, ?string $ip, ?string $userAgent): array
    {
        try {
            $headers = [
                'X-Api-Token' => $this->apiToken,
                'X-User-Id' => $user->id,
            ];

            $params = [
                'platform' => 'WEB',
                'os' => 'OTHER',
            ];

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($this->apiUrl . '/client/surveys', $params);

            if (!$response->successful()) {
                Log::error('BitLabs API Error', ['status' => $response->status()]);
                return [
                    'status' => 'error',
                    'message' => 'Hatukuweza kupata surveys',
                    'surveys' => [],
                ];
            }

            $data = $response->json();

            if (!isset($data['data']['surveys'])) {
                return [
                    'status' => 'success',
                    'message' => 'Hakuna surveys kwa sasa',
                    'surveys' => [],
                ];
            }

            // Process and categorize surveys
            $surveys = $this->processSurveys($data['data']['surveys'] ?? [], $user);

            return [
                'status' => 'success',
                'count' => count($surveys),
                'surveys' => $surveys,
            ];

        } catch (\Exception $e) {
            Log::error('BitLabs Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Tatizo la mtandao',
                'surveys' => [],
            ];
        }
    }

    /**
     * Process and categorize surveys
     */
    protected function processSurveys(array $rawSurveys, User $user): array
    {
        $isVip = $this->isVipUser($user);
        $surveys = [];

        foreach ($rawSurveys as $survey) {
            $loi = (int) ($survey['loi'] ?? 5);
            $type = SurveyCompletion::getSurveyType($loi);
            $config = config("bitlabs.rewards.{$type}");

            // Skip long surveys for non-VIP users
            if ($type === 'long' && !$isVip) {
                continue;
            }

            $reward = SurveyCompletion::getRewardForLoi($loi, $user);

            $surveys[] = [
                'id' => $survey['id'] ?? $survey['survey_id'] ?? null,
                'type' => $type,
                'type_label' => $config['label'] ?? 'Survey',
                'loi' => $loi,
                'loi_label' => $loi . ' dakika',
                'reward' => $reward,
                'reward_formatted' => 'TZS ' . number_format($reward, 0),
                'payout' => $survey['value'] ?? $survey['cpi'] ?? 0,
                'rating' => $survey['rating'] ?? 0,
                'is_top' => ($survey['rating'] ?? 0) >= 4,
                'href' => $survey['click_url'] ?? $survey['link'] ?? '',
                'vip_only' => $config['vip_only'] ?? false,
            ];
        }

        // Sort by rating (best first)
        usort($surveys, fn($a, $b) => $b['rating'] <=> $a['rating']);

        return $surveys;
    }

    /**
     * Check if user is VIP
     */
    protected function isVipUser(User $user): bool
    {
        $plan = $user->getCurrentPlan();
        if (!$plan) return false;
        
        return in_array(strtolower($plan->name), config('bitlabs.vip_plans', []));
    }

    /**
     * Handle BitLabs callback/postback
     * 
     * BitLabs sends callback on survey completion with:
     * - user_id: Our user ID (uid)
     * - tx: Unique transaction ID
     * - value: Reward value in USD
     * - status: 'complete' or 'screenout'
     * - hash: Security hash for verification
     */
    public function handleCallback(array $data): array
    {
        try {
            // Map BitLabs fields to our standard format
            $transactionId = $data['tx'] ?? $data['transaction_id'] ?? null;
            $userId = $data['user_id'] ?? $data['uid'] ?? null;
            $surveyId = $data['survey_id'] ?? $data['tx'] ?? null;
            $status = strtolower($data['status'] ?? 'complete');
            $payoutUsd = (float) ($data['value'] ?? $data['payout'] ?? 0);
            $loi = (int) ($data['loi'] ?? 5);

            if (!$transactionId || !$userId) {
                Log::warning('BitLabs Callback: Missing required fields', $data);
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            // Check if already processed
            if (SurveyCompletion::where('transaction_id', $transactionId)->exists()) {
                Log::info('BitLabs Callback: Already processed', ['tx' => $transactionId]);
                return ['success' => true, 'message' => 'Already processed'];
            }

            // Find user
            $user = User::find($userId);
            if (!$user) {
                Log::error('BitLabs Callback: User not found', ['user_id' => $userId]);
                return ['success' => false, 'message' => 'User not found'];
            }

            // Handle screenout
            if ($status === 'screenout' || $status === 'terminated') {
                $this->logScreenout($data);
                return ['success' => true, 'message' => 'Screenout logged'];
            }

            // Handle reversal
            if ($status === 'reversed' || $status === 'chargeback') {
                return $this->handleReversal($transactionId, $user, $data);
            }

            // Calculate reward
            $baseReward = SurveyCompletion::getRewardForLoi($loi, $user);
            $type = SurveyCompletion::getSurveyType($loi);

            // 🌟 Calculate VIP bonus
            $vipBonus = $this->calculateVipBonus($user, $baseReward, $type);
            $totalReward = $baseReward + $vipBonus;

            // 📊 Calculate profit margin
            $usdToTzs = config('bitlabs.usd_to_tzs', 2500);
            $payoutTzs = $payoutUsd * $usdToTzs;
            $profitMargin = $payoutTzs - $totalReward;

            // Create completion record
            $completion = SurveyCompletion::create([
                'user_id' => $user->id,
                'survey_id' => $surveyId,
                'transaction_id' => $transactionId,
                'survey_type' => $type,
                'loi' => $loi,
                'cpx_payout' => $payoutUsd, // Keep field name for compatibility
                'cpx_payout_tzs' => $payoutTzs,
                'user_reward' => $totalReward,
                'vip_bonus' => $vipBonus,
                'profit_margin' => $profitMargin,
                'status' => 'completed',
                'ip_address' => $data['ip'] ?? request()->ip(),
                'cpx_data' => $data, // Store full callback response
                'completed_at' => now(),
            ]);

            // Credit user wallet
            $this->creditUser($user, $completion);

            Log::info('BitLabs Survey completed', [
                'user_id' => $user->id,
                'survey_id' => $surveyId,
                'base_reward' => $baseReward,
                'vip_bonus' => $vipBonus,
                'total_reward' => $totalReward,
                'profit_margin' => $profitMargin,
            ]);

            return [
                'success' => true, 
                'message' => 'Survey credited',
                'reward' => $totalReward,
                'vip_bonus' => $vipBonus,
            ];

        } catch (\Exception $e) {
            Log::error('BitLabs Callback Error: ' . $e->getMessage(), $data);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify callback hash for security
     */
    public function verifyCallbackHash(array $data): bool
    {
        $receivedHash = $data['hash'] ?? '';
        
        if (empty($receivedHash) || empty($this->s2sKey)) {
            // Skip hash validation if not configured
            return true;
        }

        // BitLabs hash: md5(user_id + tx + value + s2s_key)
        $userId = $data['user_id'] ?? $data['uid'] ?? '';
        $tx = $data['tx'] ?? $data['transaction_id'] ?? '';
        $value = $data['value'] ?? '';
        
        $expectedHash = md5($userId . $tx . $value . $this->s2sKey);
        
        return hash_equals($expectedHash, $receivedHash);
    }

    /**
     * Credit user for completed survey
     */
    protected function creditUser(User $user, SurveyCompletion $completion): void
    {
        $wallet = $user->wallet;
        if (!$wallet) {
            $wallet = $user->wallet()->create(['balance' => 0]);
        }

        // Credit wallet using the wallet's credit method
        $wallet->credit(
            $completion->user_reward,
            'survey_reward',
            $completion,
            "Survey {$completion->getTypeLabel()} - #{$completion->survey_id}",
            [
                'transaction_id' => $completion->transaction_id,
                'survey_type' => $completion->survey_type,
                'loi' => $completion->loi,
                'vip_bonus' => $completion->vip_bonus,
                'provider' => 'bitlabs',
            ]
        );

        // Update completion status
        $completion->update([
            'status' => 'credited',
            'credited_at' => now(),
        ]);
    }

    /**
     * Handle survey reversal/chargeback
     */
    protected function handleReversal(string $transactionId, User $user, array $data): array
    {
        $completion = SurveyCompletion::where('transaction_id', $transactionId)->first();
        
        if ($completion && $completion->status === 'credited') {
            $amount = $completion->user_reward;
            $wallet = $user->wallet;
            
            if ($wallet && $wallet->balance >= $amount) {
                // Use wallet debit method for proper transaction creation
                $wallet->debit(
                    $amount,
                    'survey_reversal',
                    $completion,
                    "Survey Reversal - #{$completion->survey_id}",
                    [
                        'transaction_id' => $transactionId,
                        'original_reward' => $amount,
                        'provider' => 'bitlabs',
                    ]
                );

                $completion->update(['status' => 'reversed']);
            }
        }

        return ['success' => true, 'message' => 'Reversal processed'];
    }

    /**
     * Get user's survey statistics
     */
    public function getUserStats(User $user): array
    {
        $completions = SurveyCompletion::where('user_id', $user->id);

        return [
            'total_completed' => $completions->clone()->whereIn('status', ['completed', 'credited'])->count(),
            'today_completed' => $completions->clone()->whereDate('created_at', today())->count(),
            'total_earned' => $completions->clone()->where('status', 'credited')->sum('user_reward'),
            'today_earned' => $completions->clone()->where('status', 'credited')->whereDate('created_at', today())->sum('user_reward'),
            'daily_limit' => config('bitlabs.daily_limit_per_user', 20),
            'remaining_today' => max(0, config('bitlabs.daily_limit_per_user', 20) - $completions->clone()->whereDate('created_at', today())->count()),
        ];
    }

    /**
     * Get demo surveys for testing
     */
    protected function getDemoSurveys(User $user): array
    {
        $isVip = $this->isVipUser($user);
        
        $demoSurveys = [
            [
                'id' => 'demo_1',
                'type' => 'short',
                'type_label' => 'Short Survey (5-7 min)',
                'loi' => 5,
                'loi_label' => '5 dakika',
                'reward' => 200,
                'reward_formatted' => 'TZS 200',
                'payout' => 0.40,
                'rating' => 4.5,
                'is_top' => true,
                'href' => route('surveys.demo.complete', ['id' => 'demo_1']),
                'vip_only' => false,
            ],
            [
                'id' => 'demo_2',
                'type' => 'medium',
                'type_label' => 'Medium Survey (8-12 min)',
                'loi' => 10,
                'loi_label' => '10 dakika',
                'reward' => 300,
                'reward_formatted' => 'TZS 300',
                'payout' => 0.60,
                'rating' => 4.2,
                'is_top' => false,
                'href' => route('surveys.demo.complete', ['id' => 'demo_2']),
                'vip_only' => false,
            ],
        ];

        if ($isVip) {
            $demoSurveys[] = [
                'id' => 'demo_3',
                'type' => 'long',
                'type_label' => 'Long Survey (15+ min)',
                'loi' => 18,
                'loi_label' => '18 dakika',
                'reward' => 500,
                'reward_formatted' => 'TZS 500',
                'payout' => 1.00,
                'rating' => 4.8,
                'is_top' => true,
                'href' => route('surveys.demo.complete', ['id' => 'demo_3']),
                'vip_only' => true,
            ];
        }

        return [
            'status' => 'success',
            'count' => count($demoSurveys),
            'surveys' => $demoSurveys,
            'demo_mode' => true,
        ];
    }

    /**
     * Process demo survey completion
     */
    public function processDemoCompletion(User $user, string $surveyId): array
    {
        if (!$this->demoMode) {
            return ['success' => false, 'message' => 'Demo mode disabled'];
        }

        // Simulate survey completion
        $demoData = [
            'demo_1' => ['loi' => 5, 'payout' => 0.40],
            'demo_2' => ['loi' => 10, 'payout' => 0.60],
            'demo_3' => ['loi' => 18, 'payout' => 1.00],
        ];

        if (!isset($demoData[$surveyId])) {
            return ['success' => false, 'message' => 'Invalid survey'];
        }

        $data = $demoData[$surveyId];
        $transactionId = 'demo_' . $user->id . '_' . time();

        return $this->handleCallback([
            'tx' => $transactionId,
            'user_id' => $user->id,
            'survey_id' => $surveyId,
            'loi' => $data['loi'],
            'value' => $data['payout'],
            'status' => 'complete',
        ]);
    }

    /**
     * Calculate VIP bonus based on user's subscription plan
     */
    public function calculateVipBonus(User $user, float $baseReward, string $surveyType): float
    {
        $plan = $user->getCurrentPlan();
        if (!$plan) {
            return 0;
        }

        // Bonus percentages per plan
        $bonusPercentages = config('bitlabs.vip_bonuses', [
            'diamond' => 25,
            'vip' => 20,
            'premium' => 15,
            'gold' => 10,
            'silver' => 5,
        ]);

        $planSlug = strtolower($plan->name);
        $bonusPercent = $bonusPercentages[$planSlug] ?? 0;

        if ($bonusPercent <= 0) {
            return 0;
        }

        // Calculate bonus
        $bonus = ($baseReward * $bonusPercent) / 100;

        // Round to nearest 10 TZS
        return round($bonus / 10) * 10;
    }

    /**
     * Log screenout (failed/disqualified survey)
     */
    public function logScreenout(array $data): void
    {
        try {
            $userId = $data['user_id'] ?? $data['uid'] ?? null;
            
            SurveyCompletion::create([
                'user_id' => $userId,
                'survey_id' => $data['survey_id'] ?? $data['tx'] ?? null,
                'transaction_id' => $data['tx'] ?? $data['transaction_id'] ?? null,
                'survey_type' => 'screenout',
                'loi' => 0,
                'cpx_payout' => 0,
                'user_reward' => 0,
                'status' => 'screenout',
                'ip_address' => $data['ip'] ?? request()->ip(),
                'cpx_data' => $data,
                'completed_at' => now(),
            ]);

            Log::info('BitLabs Screenout logged', [
                'user_id' => $userId,
                'survey_id' => $data['survey_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('BitLabs Screenout logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Get admin statistics for surveys
     */
    public function getAdminStats(): array
    {
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total' => [
                'completions' => SurveyCompletion::credited()->count(),
                'screenouts' => SurveyCompletion::where('status', 'screenout')->count(),
                'revenue' => SurveyCompletion::credited()->sum('cpx_payout'),
                'payouts' => SurveyCompletion::credited()->sum('user_reward'),
                'profit' => SurveyCompletion::credited()->sum('profit_margin'),
            ],
            'today' => [
                'completions' => SurveyCompletion::credited()->whereDate('created_at', $today)->count(),
                'revenue' => SurveyCompletion::credited()->whereDate('created_at', $today)->sum('cpx_payout'),
                'payouts' => SurveyCompletion::credited()->whereDate('created_at', $today)->sum('user_reward'),
                'profit' => SurveyCompletion::credited()->whereDate('created_at', $today)->sum('profit_margin'),
            ],
            'this_week' => [
                'completions' => SurveyCompletion::credited()->where('created_at', '>=', $thisWeek)->count(),
                'revenue' => SurveyCompletion::credited()->where('created_at', '>=', $thisWeek)->sum('cpx_payout'),
                'payouts' => SurveyCompletion::credited()->where('created_at', '>=', $thisWeek)->sum('user_reward'),
            ],
            'this_month' => [
                'completions' => SurveyCompletion::credited()->where('created_at', '>=', $thisMonth)->count(),
                'revenue' => SurveyCompletion::credited()->where('created_at', '>=', $thisMonth)->sum('cpx_payout'),
                'payouts' => SurveyCompletion::credited()->where('created_at', '>=', $thisMonth)->sum('user_reward'),
            ],
            'by_type' => [
                'short' => SurveyCompletion::credited()->where('survey_type', 'short')->count(),
                'medium' => SurveyCompletion::credited()->where('survey_type', 'medium')->count(),
                'long' => SurveyCompletion::credited()->where('survey_type', 'long')->count(),
            ],
        ];
    }

    /**
     * Get profit analytics for admin dashboard
     */
    public function getProfitAnalytics(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Daily profit
        $dailyProfit = SurveyCompletion::selectRaw('DATE(created_at) as date, 
            SUM(cpx_payout) as revenue_usd,
            SUM(cpx_payout_tzs) as revenue_tzs, 
            SUM(user_reward) as payouts,
            SUM(vip_bonus) as bonuses,
            SUM(profit_margin) as profit,
            COUNT(*) as count')
            ->where('status', 'credited')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $dailyProfit->sum('revenue_usd');
        $totalPayouts = $dailyProfit->sum('payouts');
        $totalProfit = $dailyProfit->sum('profit');
        $avgMargin = $totalRevenue > 0 ? ($totalProfit / ($totalRevenue * config('bitlabs.usd_to_tzs', 2500))) * 100 : 0;

        return [
            'daily' => $dailyProfit,
            'summary' => [
                'total_revenue_usd' => $totalRevenue,
                'total_revenue_tzs' => $totalRevenue * config('bitlabs.usd_to_tzs', 2500),
                'total_payouts' => $totalPayouts,
                'total_bonuses' => $dailyProfit->sum('bonuses'),
                'total_profit' => $totalProfit,
                'average_margin_percent' => round($avgMargin, 2),
                'total_surveys' => $dailyProfit->sum('count'),
            ],
        ];
    }

    /**
     * Get top performing users
     */
    public function getTopPerformers(int $limit = 10): array
    {
        return User::selectRaw('users.*, 
            COUNT(survey_completions.id) as survey_count,
            SUM(survey_completions.user_reward) as total_earned,
            SUM(survey_completions.vip_bonus) as total_bonus')
            ->join('survey_completions', 'users.id', '=', 'survey_completions.user_id')
            ->where('survey_completions.status', 'credited')
            ->groupBy('users.id')
            ->orderByDesc('total_earned')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
