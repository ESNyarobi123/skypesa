<?php

namespace App\Services;

use App\Models\User;
use App\Models\SurveyCompletion;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CpxResearchService
{
    protected $appId;
    protected $secureHash;
    protected $apiUrl;
    protected $enabled;
    protected $demoMode;

    public function __construct()
    {
        $this->appId = config('cpx.app_id');
        $this->secureHash = config('cpx.secure_hash');
        $this->apiUrl = config('cpx.api_url');
        $this->enabled = config('cpx.enabled', true);
        $this->demoMode = config('cpx.demo_mode', false);
    }

    /**
     * Check if CPX is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->secureHash);
    }

    /**
     * Get available surveys for a user
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
            Log::warning('CPX Research not configured');
            return [
                'status' => 'error',
                'message' => 'Survey system haijawekwa vizuri',
                'surveys' => [],
            ];
        }

        // Cache key per user
        $cacheKey = 'cpx_surveys_' . $user->id;
        
        return Cache::remember($cacheKey, config('cpx.cache_ttl', 120), function () use ($user, $ip, $userAgent) {
            return $this->fetchSurveys($user, $ip, $userAgent);
        });
    }

    /**
     * Fetch surveys from CPX API
     */
    protected function fetchSurveys(User $user, ?string $ip, ?string $userAgent): array
    {
        try {
            $extUserId = $user->id;
            $secureHashMd5 = md5($extUserId . '-' . $this->secureHash);

            $params = [
                'app_id' => $this->appId,
                'ext_user_id' => $extUserId,
                'output_method' => 'api',
                'ip_user' => $ip ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'limit' => config('cpx.default_limit', 12),
                'secure_hash' => $secureHashMd5,
            ];

            // Add user profiling if available
            if ($user->birth_date) {
                $birthDate = \Carbon\Carbon::parse($user->birth_date);
                $params['main_info'] = 'true';
                $params['birthday_day'] = $birthDate->day;
                $params['birthday_month'] = $birthDate->month;
                $params['birthday_year'] = $birthDate->year;
            }
            if ($user->gender) {
                $params['gender'] = $user->gender === 'male' ? 'm' : 'f';
            }
            $params['user_country_code'] = 'TZ'; // Tanzania

            $response = Http::timeout(30)->get($this->apiUrl, $params);

            if (!$response->successful()) {
                Log::error('CPX API Error', ['status' => $response->status()]);
                return [
                    'status' => 'error',
                    'message' => 'Hatukuweza kupata surveys',
                    'surveys' => [],
                ];
            }

            $data = $response->json();

            if ($data['status'] !== 'success') {
                return [
                    'status' => 'error',
                    'message' => 'Hakuna surveys kwa sasa',
                    'surveys' => [],
                ];
            }

            // Process and categorize surveys
            $surveys = $this->processSurveys($data['surveys'] ?? [], $user);

            return [
                'status' => 'success',
                'count' => count($surveys),
                'surveys' => $surveys,
            ];

        } catch (\Exception $e) {
            Log::error('CPX Research Error: ' . $e->getMessage());
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
            $config = config("cpx.rewards.{$type}");

            // Skip long surveys for non-VIP users
            if ($type === 'long' && !$isVip) {
                continue;
            }

            $reward = SurveyCompletion::getRewardForLoi($loi, $user);

            $surveys[] = [
                'id' => $survey['id'],
                'type' => $type,
                'type_label' => $config['label'] ?? 'Survey',
                'loi' => $loi,
                'loi_label' => $loi . ' dakika',
                'reward' => $reward,
                'reward_formatted' => 'TZS ' . number_format($reward, 0),
                'cpx_payout' => $survey['payout'] ?? 0,
                'conversion_rate' => $survey['conversion_rate'] ?? 0,
                'score' => $survey['score'] ?? 0,
                'is_top' => ($survey['top'] ?? 0) == 1,
                'href' => $survey['href'] ?? '',
                'vip_only' => $config['vip_only'] ?? false,
            ];
        }

        // Sort by score (best first)
        usort($surveys, fn($a, $b) => $b['score'] <=> $a['score']);

        return $surveys;
    }

    /**
     * Check if user is VIP
     */
    protected function isVipUser(User $user): bool
    {
        $plan = $user->getCurrentPlan();
        if (!$plan) return false;
        
        return in_array($plan->name, config('cpx.vip_plans', []));
    }

    /**
     * Handle CPX postback/callback
     */
    public function handlePostback(array $data): array
    {
        try {
            // Validate required fields
            $transactionId = $data['trans_id'] ?? $data['transaction_id'] ?? null;
            $extUserId = $data['ext_user_id'] ?? $data['user_id'] ?? null;
            $surveyId = $data['survey_id'] ?? null;
            $status = $data['status'] ?? 'complete';

            if (!$transactionId || !$extUserId) {
                Log::warning('CPX Postback: Missing required fields', $data);
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            // Check if already processed
            if (SurveyCompletion::where('transaction_id', $transactionId)->exists()) {
                Log::info('CPX Postback: Already processed', ['trans_id' => $transactionId]);
                return ['success' => true, 'message' => 'Already processed'];
            }

            // Find user
            $user = User::find($extUserId);
            if (!$user) {
                Log::error('CPX Postback: User not found', ['user_id' => $extUserId]);
                return ['success' => false, 'message' => 'User not found'];
            }

            // Get LOI and calculate reward
            $loi = (int) ($data['loi'] ?? 5);
            $cpxPayout = (float) ($data['payout'] ?? $data['payout_publisher_usd'] ?? 0);
            $baseReward = SurveyCompletion::getRewardForLoi($loi, $user);
            $type = SurveyCompletion::getSurveyType($loi);

            // 🌟 Calculate VIP bonus
            $vipBonus = $this->calculateVipBonus($user, $baseReward, $type);
            $totalReward = $baseReward + $vipBonus;

            // Handle reversal
            if ($status === 'reversed' || $status === 'chargeback' || $status == 3) {
                return $this->handleReversal($transactionId, $user, $totalReward);
            }

            // 📊 Calculate profit margin
            $usdToTzs = config('cpx.usd_to_tzs', 2500);
            $cpxPayoutTzs = $cpxPayout * $usdToTzs;
            $profitMargin = $cpxPayoutTzs - $totalReward;

            // Create completion record
            $completion = SurveyCompletion::create([
                'user_id' => $user->id,
                'survey_id' => $surveyId,
                'transaction_id' => $transactionId,
                'survey_type' => $type,
                'loi' => $loi,
                'cpx_payout' => $cpxPayout,
                'cpx_payout_tzs' => $cpxPayoutTzs,
                'user_reward' => $totalReward,
                'vip_bonus' => $vipBonus,
                'profit_margin' => $profitMargin,
                'status' => 'completed',
                'ip_address' => $data['ip_click'] ?? $data['ip'] ?? request()->ip(),
                'cpx_data' => $data,
                'completed_at' => now(),
            ]);

            // Credit user wallet
            $this->creditUser($user, $completion);

            Log::info('CPX Survey completed', [
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
            Log::error('CPX Postback Error: ' . $e->getMessage(), $data);
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

        // Add to wallet
        $wallet->increment('balance', $completion->user_reward);

        // Create transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'survey_reward',
            'amount' => $completion->user_reward,
            'balance_after' => $wallet->balance,
            'description' => "Survey {$completion->getTypeLabel()} - #{$completion->survey_id}",
            'reference_type' => SurveyCompletion::class,
            'reference_id' => $completion->id,
            'status' => 'completed',
        ]);

        // Update completion status
        $completion->update([
            'status' => 'credited',
            'credited_at' => now(),
        ]);
    }

    /**
     * Handle survey reversal/chargeback
     */
    protected function handleReversal(string $transactionId, User $user, float $amount): array
    {
        $completion = SurveyCompletion::where('transaction_id', $transactionId)->first();
        
        if ($completion && $completion->status === 'credited') {
            // Deduct from wallet
            $wallet = $user->wallet;
            if ($wallet && $wallet->balance >= $amount) {
                $wallet->decrement('balance', $amount);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'survey_reversal',
                    'amount' => -$amount,
                    'balance_after' => $wallet->balance,
                    'description' => "Survey Reversal - #{$completion->survey_id}",
                    'reference_type' => SurveyCompletion::class,
                    'reference_id' => $completion->id,
                    'status' => 'completed',
                ]);

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
            'daily_limit' => config('cpx.daily_limit_per_user', 20),
            'remaining_today' => max(0, config('cpx.daily_limit_per_user', 20) - $completions->clone()->whereDate('created_at', today())->count()),
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
                'cpx_payout' => 0.40,
                'conversion_rate' => 85,
                'score' => 15,
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
                'cpx_payout' => 0.60,
                'conversion_rate' => 78,
                'score' => 12,
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
                'cpx_payout' => 1.00,
                'conversion_rate' => 92,
                'score' => 18,
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

        return $this->handlePostback([
            'trans_id' => $transactionId,
            'ext_user_id' => $user->id,
            'survey_id' => $surveyId,
            'loi' => $data['loi'],
            'payout' => $data['payout'],
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
        $bonusPercentages = config('cpx.vip_bonuses', [
            'diamond' => 25, // 25% bonus
            'vip' => 20,     // 20% bonus
            'premium' => 15, // 15% bonus
            'gold' => 10,    // 10% bonus
            'silver' => 5,   // 5% bonus
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
            $userId = $data['ext_user_id'] ?? $data['user_id'] ?? null;
            
            SurveyCompletion::create([
                'user_id' => $userId,
                'survey_id' => $data['survey_id'] ?? null,
                'transaction_id' => $data['trans_id'] ?? $data['transaction_id'] ?? null,
                'survey_type' => 'screenout',
                'loi' => 0,
                'cpx_payout' => 0,
                'user_reward' => 0,
                'status' => 'screenout',
                'ip_address' => $data['ip_click'] ?? $data['ip'] ?? request()->ip(),
                'cpx_data' => $data,
                'completed_at' => now(),
            ]);

            Log::info('CPX Screenout logged', [
                'user_id' => $userId,
                'survey_id' => $data['survey_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('CPX Screenout logging failed: ' . $e->getMessage());
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
        $avgMargin = $totalRevenue > 0 ? ($totalProfit / ($totalRevenue * config('cpx.usd_to_tzs', 2500))) * 100 : 0;

        return [
            'daily' => $dailyProfit,
            'summary' => [
                'total_revenue_usd' => $totalRevenue,
                'total_revenue_tzs' => $totalRevenue * config('cpx.usd_to_tzs', 2500),
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

