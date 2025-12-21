<?php

namespace App\Providers\AdProviders;

use App\Contracts\Providers\BaseAdProvider;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Adsterra Ad Provider Implementation
 * 
 * Handles Adsterra Smartlinks and Direct Links.
 * 
 * IMPORTANT: Adsterra Smartlink (publisher side) does NOT have postback.
 * - It's traffic-based monetization
 * - We use `psid` parameter for SubID tracking (for Adsterra reporting)
 * - Payment to user is TIMER-BASED (user views ad for X seconds)
 * - We track clicks ourselves via /go/adsterra/{task}
 * 
 * @see https://adsterra.com/blog/how-to-add-subid-to-smartlink/
 */
class AdsterraProvider extends BaseAdProvider
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        parent::__construct();
        
        $this->apiKey = config('adsterra.api_key', '');
        $this->baseUrl = config('adsterra.base_url', 'https://api3.adsterratools.com');
    }

    /**
     * Provider name
     */
    public function getName(): string
    {
        return 'adsterra';
    }

    /**
     * Generate task link with psid tracking for Adsterra reporting
     * 
     * Adsterra Smartlink supports: &psid=your_sub_id
     * We use format: U{user_id}_T{task_id} for easy parsing in reports
     */
    public function generateTaskLink(User $user, Task $task): string
    {
        $baseUrl = $task->url;
        
        if (empty($baseUrl)) {
            return '#';
        }

        // Build psid (Publisher SubID) for Adsterra reporting
        // Format: U{user_id}_T{task_id} - easy to parse in Adsterra stats
        $psid = "U{$user->id}_T{$task->id}";

        // Add psid to URL
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        return $baseUrl . $separator . 'psid=' . urlencode($psid);
    }

    /**
     * Adsterra Smartlink does NOT send postbacks
     * 
     * This method exists for interface compliance but will rarely be called.
     * If Adsterra ever sends something, we log it but don't process payment.
     */
    public function verifyPostback(Request $request): bool
    {
        // Log any unexpected calls for debugging
        $this->logPostback($request, 'unexpected');
        
        Log::warning('Adsterra postback received but Smartlink has no postback', [
            'ip' => $request->ip(),
            'data' => $request->all(),
        ]);
        
        // Always return false - we don't process Adsterra postbacks
        return false;
    }

    /**
     * Parse would-be postback data (for interface compliance)
     */
    public function parsePostback(Request $request): array
    {
        // Parse psid if present (unlikely from actual postback)
        $psid = $request->input('psid') ?? $request->input('sub_id') ?? '';
        $userId = null;
        $taskId = null;

        // Try to parse our U{id}_T{id} format
        if (preg_match('/U(\d+)_T(\d+)/', $psid, $matches)) {
            $userId = (int) $matches[1];
            $taskId = (int) $matches[2];
        }

        return [
            'user_id' => $userId,
            'task_id' => $taskId,
            'payout' => 0, // Adsterra doesn't send payout in Smartlink
            'provider_ref' => $request->input('click_id') ?? 'adsterra_' . time(),
            'status' => 'click', // Not a conversion, just tracking
            'ip' => $request->ip(),
            'raw' => $request->all(),
        ];
    }

    /**
     * Adsterra Smartlink payout policy
     * 
     * Since there's no postback, payout is TIMER-BASED.
     * User must view ad for minimum duration, then we pay.
     */
    public function getPayoutPolicy(): array
    {
        return [
            'type' => 'traffic', // Traffic-based, not conversion
            'min_duration' => 30, // User must view for 30 seconds
            'daily_limit' => 3, // Max per task per day
            'ip_limit' => 5, // Max from same IP per day
            'cooldown' => 120, // 2 minutes between starts
            'require_postback' => false, // NO POSTBACK - timer-based
        ];
    }

    /**
     * Adsterra Smartlink does NOT support postback
     */
    public function supportsPostback(): bool
    {
        return false; // Important: Smartlink has no postback!
    }

    /**
     * Only traffic tasks for Smartlink
     */
    public function getSupportedCategories(): array
    {
        return ['traffic_task']; // Only traffic, no conversions
    }

    // ==========================================
    // Adsterra API methods (for admin/stats)
    // ==========================================

    /**
     * Make API request to Adsterra Publisher API
     */
    protected function request(string $endpoint): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Adsterra API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Adsterra API Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all placements from Adsterra
     */
    public function getPlacements(): array
    {
        return Cache::remember('adsterra_placements', 3600, function () {
            $response = $this->request('/publisher/placements.json');
            return $response['items'] ?? [];
        });
    }

    /**
     * Get placements with direct URLs (usable for tasks)
     */
    public function getTaskablePlacements(): array
    {
        $placements = $this->getPlacements();
        
        return array_filter($placements, function ($placement) {
            return !empty($placement['direct_url']);
        });
    }

    /**
     * Convert placement to task data
     */
    public function placementToTaskData(array $placement): array
    {
        return [
            'title' => $this->formatPlacementTitle($placement['title'] ?? $placement['alias'] ?? 'Adsterra Task'),
            'description' => 'Tazama tangazo hili kwa sekunde 30 na upate malipo.',
            'type' => 'view_ad',
            'category' => 'traffic_task', // TRAFFIC - no postback
            'require_postback' => false, // Timer-based payment
            'url' => $placement['direct_url'] ?? '',
            'provider' => 'adsterra',
            'duration_seconds' => 30,
            'cooldown_seconds' => 120,
            'daily_limit' => 3,
            'ip_daily_limit' => 5,
            'is_active' => true,
            'requirements' => [
                'adsterra_placement_id' => $placement['id'] ?? null,
                'adsterra_domain_id' => $placement['domain_id'] ?? null,
            ],
        ];
    }

    /**
     * Format placement title to user-friendly Swahili
     */
    protected function formatPlacementTitle(string $title): string
    {
        $title = preg_replace('/[_\d]+/', ' ', $title);
        $title = trim($title);
        $lowerTitle = strtolower($title);

        if (str_contains($lowerTitle, 'popunder')) {
            return 'Tazama Tangazo Maalum';
        }

        if (str_contains($lowerTitle, 'native')) {
            return 'Tazama Tangazo la Bidhaa';
        }

        if (str_contains($lowerTitle, 'banner')) {
            return 'Tazama Banner Tangazo';
        }

        if (str_contains($lowerTitle, 'video')) {
            return 'Tazama Video Tangazo';
        }

        return 'Tazama Tangazo - ' . ucwords($title);
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/publisher/domains.json");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Umeunganishwa na Adsterra!',
                    'domains_count' => $data['itemCount'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'message' => 'Adsterra API Error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Clear cached data
     */
    public function clearCache(): void
    {
        Cache::forget('adsterra_placements');
        Cache::forget('adsterra_domains');
    }
}

