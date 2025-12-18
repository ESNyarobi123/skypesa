<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MonetagService
{
    protected string $domain;
    protected int $zoneId;
    protected ?string $smartlinkBase;

    public function __construct()
    {
        $this->domain = config('monetag.domain');
        $this->zoneId = config('monetag.zone_id');
        $this->smartlinkBase = config('monetag.smartlink_base');
    }

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
     * Generate a smartlink URL with tracking parameters
     */
    public function generateSmartlink(string $baseUrl, ?int $userId = null, ?string $taskId = null): string
    {
        $params = [];
        
        if ($userId) {
            $params['subid'] = $userId;
        }
        
        if ($taskId) {
            $params['subid2'] = $taskId;
        }
        
        if (empty($params)) {
            return $baseUrl;
        }
        
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        return $baseUrl . $separator . http_build_query($params);
    }

    /**
     * Create a task-ready Monetag smartlink
     */
    public function createTaskUrl(?string $smartlinkUrl = null): string
    {
        $url = $smartlinkUrl ?? $this->smartlinkBase;
        
        if (empty($url)) {
            // Return a placeholder or throw exception
            Log::warning('Monetag smartlink base not configured');
            return '#';
        }
        
        return $url;
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
     * Check if push is enabled
     */
    public function isPushEnabled(): bool
    {
        return config('monetag.enable_push', true);
    }

    /**
     * Check if IPN is enabled
     */
    public function isIPNEnabled(): bool
    {
        return config('monetag.enable_ipn', false);
    }

    /**
     * Convert to task data array
     */
    public function createTaskData(string $title, string $url, int $durationSeconds = 30): array
    {
        return [
            'title' => $title,
            'description' => 'Tazama tangazo hili kwa sekunde ' . $durationSeconds . ' na upate malipo.',
            'type' => 'view_ad',
            'url' => $url,
            'provider' => 'monetag',
            'duration_seconds' => $durationSeconds,
            'daily_limit' => 5,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 5,
            'requirements' => [
                'source' => 'monetag',
                'zone_id' => $this->zoneId,
            ],
        ];
    }
}
