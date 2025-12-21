<?php

namespace App\Contracts\Providers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * Interface for Ad Provider implementations
 * 
 * This provides a unified contract for all ad providers (Adsterra, Monetag, etc.)
 * allowing easy addition of new providers without modifying core logic.
 */
interface AdProviderInterface
{
    /**
     * Get the provider name/identifier
     */
    public function getName(): string;

    /**
     * Generate task link with tracking parameters
     * 
     * @param User $user The user who will complete the task
     * @param Task $task The task being started
     * @return string The URL with tracking parameters
     */
    public function generateTaskLink(User $user, Task $task): string;

    /**
     * Verify postback signature/authenticity
     * 
     * @param Request $request The incoming postback request
     * @return bool True if postback is valid
     */
    public function verifyPostback(Request $request): bool;

    /**
     * Parse postback data into standardized format
     * 
     * @param Request $request The incoming postback request
     * @return array{user_id: int|null, task_id: int|null, payout: float, provider_ref: string, status: string, raw: array}
     */
    public function parsePostback(Request $request): array;

    /**
     * Get the payout policy for this provider
     * 
     * @return array{type: string, min_duration: int, daily_limit: int, ip_limit: int, cooldown: int}
     */
    public function getPayoutPolicy(): array;

    /**
     * Check if the provider supports postback-driven payouts
     */
    public function supportsPostback(): bool;

    /**
     * Get task categories this provider supports
     * 
     * @return array List of supported task categories (traffic_task, conversion_task)
     */
    public function getSupportedCategories(): array;

    /**
     * Generate signature for tracking
     * 
     * @param int $userId
     * @param int $taskId
     * @return string
     */
    public function generateSignature(int $userId, int $taskId): string;

    /**
     * Verify tracking signature
     * 
     * @param string $signature
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function verifySignature(string $signature, int $userId, int $taskId): bool;
}
