<?php

namespace App\Services;

use App\Models\User;
use App\Models\TaskCompletion;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * Fraud Detection Service
 * 
 * Analyzes user behavior and task completions to detect potential fraud.
 * Used for withdrawal risk assessment and automatic flagging.
 */
class FraudDetectionService
{
    /**
     * Risk thresholds
     */
    protected const HIGH_RISK_THRESHOLD = 60;
    protected const MEDIUM_RISK_THRESHOLD = 30;
    protected const AUTO_FREEZE_THRESHOLD = 80;

    /**
     * Calculate risk score for a withdrawal request
     */
    public function assessWithdrawalRisk(Withdrawal $withdrawal): array
    {
        $user = $withdrawal->user;
        $riskFactors = [];
        $totalScore = 0;

        // ===== Factor 1: Account Age =====
        $accountAgeDays = $user->created_at->diffInDays(now());
        if ($accountAgeDays < 1) {
            $riskFactors[] = ['factor' => 'new_account', 'score' => 30, 'detail' => 'Account < 1 day old'];
            $totalScore += 30;
        } elseif ($accountAgeDays < 3) {
            $riskFactors[] = ['factor' => 'young_account', 'score' => 15, 'detail' => 'Account < 3 days old'];
            $totalScore += 15;
        } elseif ($accountAgeDays < 7) {
            $riskFactors[] = ['factor' => 'recent_account', 'score' => 5, 'detail' => 'Account < 7 days old'];
            $totalScore += 5;
        }

        // ===== Factor 2: Withdrawal Amount vs Earnings Pattern =====
        $avgDailyEarnings = $this->getAverageDailyEarnings($user);
        $withdrawalDays = max(1, $avgDailyEarnings > 0 ? $withdrawal->amount / $avgDailyEarnings : 999);
        
        if ($withdrawalDays > 30) {
            $riskFactors[] = ['factor' => 'large_withdrawal', 'score' => 20, 'detail' => "Amount exceeds 30 days of average earnings"];
            $totalScore += 20;
        } elseif ($withdrawalDays > 14) {
            $riskFactors[] = ['factor' => 'above_average_withdrawal', 'score' => 10, 'detail' => "Amount exceeds 14 days of average earnings"];
            $totalScore += 10;
        }

        // ===== Factor 3: IP Diversity =====
        $distinctIPs = $this->getDistinctIPsCount($user, 7);
        if ($distinctIPs > 10) {
            $riskFactors[] = ['factor' => 'many_ips', 'score' => 25, 'detail' => "{$distinctIPs} different IPs in 7 days"];
            $totalScore += 25;
        } elseif ($distinctIPs > 5) {
            $riskFactors[] = ['factor' => 'varied_ips', 'score' => 10, 'detail' => "{$distinctIPs} different IPs in 7 days"];
            $totalScore += 10;
        }

        // ===== Factor 4: Task Completion Velocity =====
        $avgTasksPerDay = $this->getAverageTasksPerDay($user, 7);
        $dailyLimit = $user->getDailyTaskLimit();
        
        if ($avgTasksPerDay > $dailyLimit * 0.9) {
            $riskFactors[] = ['factor' => 'max_velocity', 'score' => 15, 'detail' => "Consistently hitting daily limit"];
            $totalScore += 15;
        }

        // ===== Factor 5: Fraud/Rejected Task Ratio =====
        $fraudRatio = $this->getFraudTaskRatio($user, 30);
        if ($fraudRatio > 0.1) {
            $riskFactors[] = ['factor' => 'high_fraud_ratio', 'score' => 30, 'detail' => number_format($fraudRatio * 100, 1) . "% fraud/rejected tasks"];
            $totalScore += 30;
        } elseif ($fraudRatio > 0.05) {
            $riskFactors[] = ['factor' => 'some_fraud', 'score' => 15, 'detail' => number_format($fraudRatio * 100, 1) . "% fraud/rejected tasks"];
            $totalScore += 15;
        }

        // ===== Factor 6: Device Fingerprint Diversity =====
        $distinctFingerprints = $this->getDistinctFingerprintCount($user, 7);
        if ($distinctFingerprints > 5) {
            $riskFactors[] = ['factor' => 'many_devices', 'score' => 20, 'detail' => "{$distinctFingerprints} devices in 7 days"];
            $totalScore += 20;
        }

        // ===== Factor 7: Referral Abuse =====
        $suspiciousReferrals = $this->getSuspiciousReferralCount($user);
        if ($suspiciousReferrals > 3) {
            $riskFactors[] = ['factor' => 'referral_abuse', 'score' => 25, 'detail' => "{$suspiciousReferrals} suspicious referrals"];
            $totalScore += 25;
        }

        // ===== Factor 8: Previous Withdrawals =====
        $previousWithdrawals = $user->withdrawals()
            ->where('status', 'paid')
            ->count();
        
        if ($previousWithdrawals === 0) {
            $riskFactors[] = ['factor' => 'first_withdrawal', 'score' => 10, 'detail' => 'First withdrawal attempt'];
            $totalScore += 10;
        }

        // Cap score at 100
        $totalScore = min(100, $totalScore);

        return [
            'score' => $totalScore,
            'factors' => $riskFactors,
            'risk_level' => $this->getRiskLevel($totalScore),
            'should_freeze' => $totalScore >= self::AUTO_FREEZE_THRESHOLD,
            'delay_hours' => $this->calculateDelay($totalScore),
        ];
    }

    /**
     * Calculate withdrawal delay based on risk score
     */
    protected function calculateDelay(int $riskScore): int
    {
        if ($riskScore >= self::AUTO_FREEZE_THRESHOLD) {
            return 72; // 3 days for high risk
        }
        
        if ($riskScore >= self::HIGH_RISK_THRESHOLD) {
            return 48; // 2 days
        }
        
        if ($riskScore >= self::MEDIUM_RISK_THRESHOLD) {
            return 24; // 1 day
        }
        
        return 12; // Minimum 12 hours even for low risk
    }

    /**
     * Get risk level label
     */
    protected function getRiskLevel(int $score): string
    {
        if ($score >= self::AUTO_FREEZE_THRESHOLD) {
            return 'critical';
        }
        if ($score >= self::HIGH_RISK_THRESHOLD) {
            return 'high';
        }
        if ($score >= self::MEDIUM_RISK_THRESHOLD) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Get average daily earnings for user
     */
    protected function getAverageDailyEarnings(User $user): float
    {
        $accountDays = max(1, $user->created_at->diffInDays(now()));
        $totalEarned = $user->wallet?->total_earned ?? 0;
        
        return $totalEarned / $accountDays;
    }

    /**
     * Get count of distinct IPs used
     */
    protected function getDistinctIPsCount(User $user, int $days): int
    {
        return TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('ip_address')
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Get average tasks completed per day
     */
    protected function getAverageTasksPerDay(User $user, int $days): float
    {
        $completions = TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->count();
        
        return $completions / max(1, $days);
    }

    /**
     * Get ratio of fraud/rejected tasks
     */
    protected function getFraudTaskRatio(User $user, int $days): float
    {
        $total = TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $fraudCount = TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('status', ['fraud', 'rejected'])
            ->count();
        
        return $fraudCount / $total;
    }

    /**
     * Get count of distinct device fingerprints
     */
    protected function getDistinctFingerprintCount(User $user, int $days): int
    {
        return TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('device_fingerprint')
            ->distinct('device_fingerprint')
            ->count('device_fingerprint');
    }

    /**
     * Get count of suspicious referrals
     * (referrals from same IP or that never completed tasks)
     */
    protected function getSuspiciousReferralCount(User $user): int
    {
        // Get referrals who never completed any tasks
        $inactiveReferrals = User::where('referred_by', $user->id)
            ->whereDoesntHave('taskCompletions', function ($q) {
                $q->where('status', 'completed');
            })
            ->count();
        
        return $inactiveReferrals;
    }

    /**
     * Run fraud check on user and update their fraud score
     */
    public function updateUserFraudScore(User $user): void
    {
        $score = 0;

        // Check IP diversity
        $distinctIPs = $this->getDistinctIPsCount($user, 30);
        if ($distinctIPs > 15) {
            $score += 20;
        }

        // Check fraud task ratio
        $fraudRatio = $this->getFraudTaskRatio($user, 30);
        $score += (int) ($fraudRatio * 100);

        // Check referral abuse
        $suspiciousReferrals = $this->getSuspiciousReferralCount($user);
        $score += min(30, $suspiciousReferrals * 5);

        // Update user
        $user->update([
            'fraud_score' => min(100, $score),
            'is_suspicious' => $score >= 50,
            'last_fraud_check' => now(),
        ]);

        Log::info('User fraud score updated', [
            'user_id' => $user->id,
            'score' => $score,
            'is_suspicious' => $score >= 50,
        ]);
    }

    /**
     * Get withdrawal processing recommendation
     */
    public function getWithdrawalRecommendation(Withdrawal $withdrawal): string
    {
        $assessment = $this->assessWithdrawalRisk($withdrawal);

        if ($assessment['should_freeze']) {
            return 'FREEZE: High fraud risk detected. Manual review required.';
        }

        if ($assessment['risk_level'] === 'high') {
            return 'DELAY: Apply ' . $assessment['delay_hours'] . ' hour delay and review before processing.';
        }

        if ($assessment['risk_level'] === 'medium') {
            return 'PROCEED WITH CAUTION: Apply standard delay, monitor for patterns.';
        }

        return 'APPROVE: Low risk, can proceed after minimum delay.';
    }
}
