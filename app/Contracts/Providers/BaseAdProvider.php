<?php

namespace App\Contracts\Providers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Base implementation for Ad Providers
 * 
 * Provides common functionality that all providers share.
 */
abstract class BaseAdProvider implements AdProviderInterface
{
    /**
     * Secret key for signature generation
     */
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.key');
    }

    /**
     * Generate HMAC signature for tracking
     */
    public function generateSignature(int $userId, int $taskId): string
    {
        $data = "{$userId}:{$taskId}:{$this->getName()}";
        return hash_hmac('sha256', $data, $this->secretKey);
    }

    /**
     * Verify tracking signature
     */
    public function verifySignature(string $signature, int $userId, int $taskId): bool
    {
        $expected = $this->generateSignature($userId, $taskId);
        return hash_equals($expected, $signature);
    }

    /**
     * Default payout policy - providers should override this
     */
    public function getPayoutPolicy(): array
    {
        return [
            'type' => 'traffic', // traffic or conversion
            'min_duration' => 30, // minimum seconds before completion
            'daily_limit' => 5, // max completions per day per user
            'ip_limit' => 10, // max completions per day per IP
            'cooldown' => 60, // seconds between task starts
            'require_postback' => false, // whether postback is required for payout
        ];
    }

    /**
     * Log postback for debugging
     */
    protected function logPostback(Request $request, string $status = 'received'): void
    {
        Log::channel('postbacks')->info("{$this->getName()} postback", [
            'status' => $status,
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'data' => $request->all(),
        ]);
    }

    /**
     * Default postback support - most providers support it
     */
    public function supportsPostback(): bool
    {
        return true;
    }

    /**
     * Default supported categories
     */
    public function getSupportedCategories(): array
    {
        return ['traffic_task', 'conversion_task'];
    }

    /**
     * Build subid string for tracking
     */
    protected function buildSubId(User $user, Task $task): string
    {
        return json_encode([
            'u' => $user->id,
            't' => $task->id,
            's' => substr($this->generateSignature($user->id, $task->id), 0, 16),
        ]);
    }

    /**
     * Parse subid string
     */
    protected function parseSubId(string $subId): ?array
    {
        $data = json_decode($subId, true);
        
        if (!$data || !isset($data['u'], $data['t'], $data['s'])) {
            return null;
        }

        // Verify signature prefix
        $expectedPrefix = substr($this->generateSignature($data['u'], $data['t']), 0, 16);
        if (!hash_equals($expectedPrefix, $data['s'])) {
            return null;
        }

        return [
            'user_id' => (int) $data['u'],
            'task_id' => (int) $data['t'],
        ];
    }

    /**
     * Build tracking URL with parameters
     */
    protected function appendTrackingParams(string $url, array $params): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . http_build_query($params);
    }
}
