<?php

namespace App\Providers\AdProviders;

use App\Contracts\Providers\BaseAdProvider;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Monetag Ad Provider Implementation
 * 
 * Handles Monetag Smartlinks, Push Notifications, and In-Page Push.
 * 
 * IMPORTANT: For web-based ads (Push, In-Page Push, Smartlink):
 * - NO postback support - postbacks are ONLY for SDK-based Rewarded Interstitial/Popup
 * - We use `ymid` parameter for custom tracking
 * - Payment to user is TIMER-BASED
 * - We track clicks via /go/monetag/{task}
 * 
 * For SDK-based Rewarded ads (mobile apps only):
 * - Postbacks ARE supported via ymid
 * - @see https://docs.monetag.com/sdk-for-telegram-mini-apps/rewarded-popup
 */
class MonetagProvider extends BaseAdProvider
{
    protected string $domain;
    protected int $zoneId;
    protected ?string $smartlinkBase;

    public function __construct()
    {
        parent::__construct();
        
        $this->domain = config('monetag.domain', '3nbf4.com');
        $this->zoneId = (int) config('monetag.zone_id', 10345364);
        $this->smartlinkBase = config('monetag.smartlink_base');
    }

    /**
     * Provider name
     */
    public function getName(): string
    {
        return 'monetag';
    }

    /**
     * Generate task link with ymid tracking
     * 
     * Monetag uses ymid for custom identifier.
     * We embed user_id and task_id in ymid for tracking.
     */
    public function generateTaskLink(User $user, Task $task): string
    {
        $baseUrl = $task->url ?: $this->smartlinkBase;
        
        if (empty($baseUrl)) {
            Log::warning('Monetag: No URL configured for task', ['task_id' => $task->id]);
            return '#';
        }

        // Build ymid with tracking data
        // Format: U{user_id}_T{task_id}_S{short_signature}
        $sig = substr($this->generateSignature($user->id, $task->id), 0, 8);
        $ymid = "U{$user->id}_T{$task->id}_S{$sig}";

        // Add ymid and zone to URL
        $params = [
            'ymid' => $ymid,
            'zone' => $this->zoneId,
        ];

        return $this->appendTrackingParams($baseUrl, $params);
    }

    /**
     * Verify Monetag postback
     * 
     * NOTE: For web (Push/IPN), postbacks don't exist.
     * This method is for SDK-based Rewarded ads ONLY.
     */
    public function verifyPostback(Request $request): bool
    {
        $this->logPostback($request);

        // Get ymid from postback
        $ymid = $request->input('ymid') ?? $request->input('custom');

        if (!$ymid) {
            Log::warning('Monetag postback: Missing ymid');
            return false;
        }

        // Parse our tracking format: U{user_id}_T{task_id}_S{sig}
        if (!preg_match('/U(\d+)_T(\d+)_S(\w+)/', $ymid, $matches)) {
            Log::warning('Monetag postback: Invalid ymid format', ['ymid' => $ymid]);
            return false;
        }

        $userId = (int) $matches[1];
        $taskId = (int) $matches[2];
        $receivedSig = $matches[3];

        // Verify signature
        $expectedSig = substr($this->generateSignature($userId, $taskId), 0, 8);
        if (!hash_equals($expectedSig, $receivedSig)) {
            Log::warning('Monetag postback: Invalid signature', [
                'ymid' => $ymid,
                'user_id' => $userId,
                'task_id' => $taskId,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Parse Monetag postback data
     */
    public function parsePostback(Request $request): array
    {
        $ymid = $request->input('ymid') ?? $request->input('custom') ?? '';
        $userId = null;
        $taskId = null;

        // Parse our format
        if (preg_match('/U(\d+)_T(\d+)/', $ymid, $matches)) {
            $userId = (int) $matches[1];
            $taskId = (int) $matches[2];
        }

        return [
            'user_id' => $userId,
            'task_id' => $taskId,
            'payout' => (float) ($request->input('payout') ?? $request->input('revenue') ?? 0),
            'provider_ref' => $request->input('transaction_id') 
                ?? $request->input('click_id') 
                ?? 'monetag_' . time(),
            'status' => $request->input('status') ?? 'completed',
            'ip' => $request->input('user_ip') ?? $request->ip(),
            'country' => $request->input('country') ?? $request->input('geo'),
            'raw' => $request->all(),
        ];
    }

    /**
     * Monetag payout policy
     * 
     * For web-based ads: TIMER-BASED (no postback)
     * For SDK Rewarded: Postback supported
     */
    public function getPayoutPolicy(): array
    {
        return [
            'type' => 'traffic', // Traffic-based for web
            'min_duration' => 30,
            'daily_limit' => 5,
            'ip_limit' => 10,
            'cooldown' => 60,
            'require_postback' => false, // Web ads don't have postback
        ];
    }

    /**
     * Web-based Monetag ads don't support postback
     * (only SDK Rewarded Interstitial/Popup do)
     */
    public function supportsPostback(): bool
    {
        // Return false for web implementation
        // If you implement SDK Rewarded, this would be true
        return false;
    }

    /**
     * Only traffic tasks for web ads
     */
    public function getSupportedCategories(): array
    {
        return ['traffic_task'];
    }

    // ==========================================
    // Monetag-specific methods
    // ==========================================

    /**
     * Get domain for scripts
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get zone ID
     */
    public function getZoneId(): int
    {
        return $this->zoneId;
    }

    /**
     * Check if push notifications are enabled
     */
    public function isPushEnabled(): bool
    {
        return config('monetag.enable_push', true);
    }

    /**
     * Check if in-page push is enabled
     */
    public function isIPNEnabled(): bool
    {
        return config('monetag.enable_ipn', true);
    }

    /**
     * Generate service worker registration script
     */
    public function getServiceWorkerScript(): string
    {
        return <<<JS
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function(registration) {
                    console.log('Monetag SW registered');
                })
                .catch(function(error) {
                    console.log('Monetag SW registration failed:', error);
                });
        }
        JS;
    }

    /**
     * Get the In-Page Push script tag
     */
    public function getIPNScript(): string
    {
        return sprintf(
            '<script src="https://%s/pfe/current/tag.min.js?z=%d" data-cfasync="false" async></script>',
            $this->domain,
            $this->zoneId
        );
    }

    /**
     * Create task data from smartlink config
     */
    public function createTaskData(string $title, string $url, int $durationSeconds = 30): array
    {
        return [
            'title' => $title,
            'description' => 'Tazama tangazo hili kwa sekunde ' . $durationSeconds . ' na upate malipo.',
            'type' => 'view_ad',
            'category' => 'traffic_task', // TRAFFIC - timer-based
            'require_postback' => false,
            'url' => $url,
            'provider' => 'monetag',
            'duration_seconds' => $durationSeconds,
            'cooldown_seconds' => 60,
            'daily_limit' => 5,
            'ip_daily_limit' => 10,
            'is_active' => true,
            'requirements' => [
                'source' => 'monetag',
                'zone_id' => $this->zoneId,
            ],
        ];
    }
}

