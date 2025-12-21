<?php

namespace App\Services;

use App\Contracts\Providers\AdProviderInterface;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Postback Handler Service
 * 
 * Centralized service for processing postbacks from all ad providers.
 * Implements idempotency, fraud detection, and proper payout logic.
 */
class PostbackHandlerService
{
    protected AdProviderManager $providerManager;

    public function __construct(AdProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * Process a postback from a provider
     */
    public function handlePostback(string $providerName, Request $request): array
    {
        $provider = $this->providerManager->provider($providerName);

        if (!$provider) {
            Log::error("Postback received for unknown provider: {$providerName}");
            return [
                'success' => false,
                'error' => 'unknown_provider',
                'message' => 'Provider not found',
            ];
        }

        // Verify postback authenticity
        if (!$provider->verifyPostback($request)) {
            return [
                'success' => false,
                'error' => 'verification_failed',
                'message' => 'Postback verification failed',
            ];
        }

        // Parse postback data
        $data = $provider->parsePostback($request);

        // Validate required data
        if (!$data['user_id']) {
            Log::warning("{$providerName} postback: Missing user_id", $data);
            return [
                'success' => false,
                'error' => 'missing_user_id',
                'message' => 'User ID not found in postback',
            ];
        }

        // Check idempotency (prevent duplicate payouts)
        if ($this->isDuplicatePostback($providerName, $data['provider_ref'])) {
            Log::info("{$providerName} postback: Duplicate detected", [
                'ref' => $data['provider_ref'],
            ]);
            return [
                'success' => true,
                'error' => 'duplicate',
                'message' => 'Postback already processed',
            ];
        }

        // Process the postback
        try {
            $result = $this->processPostback($provider, $data, $request);
            
            // Mark as processed for idempotency
            $this->markPostbackProcessed($providerName, $data['provider_ref']);
            
            return $result;

        } catch (\Exception $e) {
            Log::error("{$providerName} postback processing error", [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'processing_error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process verified postback data
     */
    protected function processPostback(AdProviderInterface $provider, array $data, Request $request): array
    {
        return DB::transaction(function () use ($provider, $data, $request) {
            // Find user
            $user = User::find($data['user_id']);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'user_not_found',
                    'message' => 'User not found',
                ];
            }

            // Find or create task completion
            $completion = $this->findOrCreateCompletion($user, $data, $provider, $request);

            if (!$completion) {
                return [
                    'success' => false,
                    'error' => 'completion_not_found',
                    'message' => 'Could not find or create task completion',
                ];
            }

            // Check if already paid
            if ($completion->status === 'completed' && $completion->reward_earned > 0) {
                return [
                    'success' => true,
                    'message' => 'Already paid',
                    'completion_id' => $completion->id,
                ];
            }

            // Apply fraud checks
            $fraudCheck = $this->checkForFraud($user, $data, $request);
            if ($fraudCheck['is_fraud']) {
                $completion->update([
                    'status' => 'fraud',
                    'rejection_reason' => $fraudCheck['reason'],
                ]);

                Log::warning('Postback fraud detected', [
                    'user_id' => $user->id,
                    'reason' => $fraudCheck['reason'],
                    'data' => $data,
                ]);

                return [
                    'success' => false,
                    'error' => 'fraud_detected',
                    'message' => $fraudCheck['reason'],
                ];
            }

            // Calculate reward
            $reward = $this->calculateReward($user, $completion->task, $data);

            // Update completion and pay user
            $completion->update([
                'status' => 'completed',
                'reward_earned' => $reward,
                'completed_at' => now(),
                'metadata' => array_merge($completion->metadata ?? [], [
                    'postback_ref' => $data['provider_ref'],
                    'postback_payout' => $data['payout'],
                    'processed_at' => now()->toISOString(),
                ]),
            ]);

            // Credit wallet
            if ($reward > 0) {
                $user->wallet->credit(
                    $reward,
                    'task_reward',
                    $completion,
                    'Malipo ya task: ' . ($completion->task?->title ?? 'Provider Task'),
                    [
                        'provider' => $provider->getName(),
                        'provider_ref' => $data['provider_ref'],
                    ]
                );

                // Update task completions count
                if ($completion->task) {
                    $completion->task->increment('completions_count');
                }
            }

            Log::info('Postback processed successfully', [
                'provider' => $provider->getName(),
                'user_id' => $user->id,
                'reward' => $reward,
                'completion_id' => $completion->id,
            ]);

            return [
                'success' => true,
                'message' => 'Postback processed',
                'completion_id' => $completion->id,
                'reward' => $reward,
            ];
        });
    }

    /**
     * Find existing completion or create from postback
     */
    protected function findOrCreateCompletion(User $user, array $data, AdProviderInterface $provider, Request $request): ?TaskCompletion
    {
        // Try to find existing in-progress completion
        if ($data['task_id']) {
            $completion = TaskCompletion::where('user_id', $user->id)
                ->where('task_id', $data['task_id'])
                ->where('status', 'in_progress')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($completion) {
                return $completion;
            }
        }

        // Try to find by provider reference
        $completion = TaskCompletion::where('user_id', $user->id)
            ->whereJsonContains('metadata->postback_ref', $data['provider_ref'])
            ->first();

        if ($completion) {
            return $completion;
        }

        // Create new completion if we have task_id
        if ($data['task_id']) {
            $task = Task::find($data['task_id']);
            
            if ($task) {
                return TaskCompletion::create([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'reward_earned' => 0, // Will be set when confirmed
                    'status' => 'pending', // Pending provider confirmation
                    'started_at' => now(),
                    'ip_address' => $data['ip'] ?? $request->ip(),
                    'metadata' => [
                        'provider' => $provider->getName(),
                        'provider_ref' => $data['provider_ref'],
                        'from_postback' => true,
                    ],
                ]);
            }
        }

        // Create generic provider completion without specific task
        return TaskCompletion::create([
            'user_id' => $user->id,
            'task_id' => null, // No specific task
            'reward_earned' => 0,
            'status' => 'pending',
            'started_at' => now(),
            'ip_address' => $data['ip'] ?? $request->ip(),
            'metadata' => [
                'provider' => $provider->getName(),
                'provider_ref' => $data['provider_ref'],
                'from_postback' => true,
                'raw_data' => $data['raw'] ?? [],
            ],
        ]);
    }

    /**
     * Calculate reward for the completion
     */
    protected function calculateReward(User $user, ?Task $task, array $data): float
    {
        // If task has override, use it
        if ($task && $task->reward_override) {
            return (float) $task->reward_override;
        }

        // Use user's subscription reward rate
        $baseReward = $user->getRewardPerTask();

        // If provider sent payout, we could use it to adjust
        // But for safety, we use our own calculated rewards
        // Provider payout is just for tracking/analytics

        return $baseReward;
    }

    /**
     * Check for potential fraud
     */
    protected function checkForFraud(User $user, array $data, Request $request): array
    {
        $ip = $data['ip'] ?? $request->ip();

        // Check IP abuse (too many completions from same IP today)
        $ipCompletions = TaskCompletion::where('ip_address', $ip)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        if ($ipCompletions >= 20) { // Max 20 completions per IP per day
            return [
                'is_fraud' => true,
                'reason' => 'IP limit exceeded: ' . $ipCompletions,
            ];
        }

        // Check user abuse (too many completions today)
        $userCompletions = TaskCompletion::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        $dailyLimit = $user->getDailyTaskLimit();
        if ($userCompletions >= $dailyLimit * 2) { // Allow some buffer for postback delays
            return [
                'is_fraud' => true,
                'reason' => 'Daily limit exceeded: ' . $userCompletions,
            ];
        }

        // Check for velocity abuse (too many in short time)
        $recentCompletions = TaskCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->where('status', 'completed')
            ->count();

        if ($recentCompletions >= 10) { // Max 10 in 5 minutes
            return [
                'is_fraud' => true,
                'reason' => 'Velocity limit exceeded: ' . $recentCompletions . ' in 5 min',
            ];
        }

        return ['is_fraud' => false];
    }

    /**
     * Check if postback was already processed (idempotency)
     */
    protected function isDuplicatePostback(string $provider, string $reference): bool
    {
        $key = "postback:{$provider}:{$reference}";
        return Cache::has($key);
    }

    /**
     * Mark postback as processed
     */
    protected function markPostbackProcessed(string $provider, string $reference): void
    {
        $key = "postback:{$provider}:{$reference}";
        Cache::put($key, true, now()->addDays(7)); // Keep for 7 days
    }
}
