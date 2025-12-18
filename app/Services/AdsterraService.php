<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdsterraService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $format;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('adsterra.api_key');
        $this->baseUrl = config('adsterra.base_url');
        $this->format = config('adsterra.format', 'json');
        $this->timeout = config('adsterra.timeout', 30);
    }

    /**
     * Make API request to Adsterra
     */
    protected function request(string $endpoint): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
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
     * Get all domains
     */
    public function getDomains(): array
    {
        $cacheKey = 'adsterra_domains';
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->request("/publisher/domains.{$this->format}");
            return $response['items'] ?? [];
        });
    }

    /**
     * Get all placements
     */
    public function getPlacements(): array
    {
        $cacheKey = 'adsterra_placements';
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->request("/publisher/placements.{$this->format}");
            return $response['items'] ?? [];
        });
    }

    /**
     * Get placements for a specific domain
     */
    public function getDomainPlacements(int $domainId): array
    {
        $cacheKey = "adsterra_domain_{$domainId}_placements";
        
        return Cache::remember($cacheKey, 3600, function () use ($domainId) {
            $response = $this->request("/publisher/domain/{$domainId}/placements.{$this->format}");
            return $response['items'] ?? [];
        });
    }

    /**
     * Get placements with direct URLs only (usable for tasks)
     */
    public function getTaskablePlacements(): array
    {
        $placements = $this->getPlacements();
        
        // Filter only placements that have direct_url
        return array_filter($placements, function ($placement) {
            return !empty($placement['direct_url']);
        });
    }

    /**
     * Clear cached data
     */
    public function clearCache(): void
    {
        Cache::forget('adsterra_domains');
        Cache::forget('adsterra_placements');
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
                ->get("{$this->baseUrl}/publisher/domains.{$this->format}");

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
                'error' => $response->body(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection Error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get placement type from alias/title
     */
    public function getPlacementType(string $title): string
    {
        $title = strtolower($title);
        
        if (str_contains($title, 'popunder')) {
            return 'view_ad';
        }
        
        if (str_contains($title, 'native') || str_contains($title, 'banner')) {
            return 'view_ad';
        }
        
        if (str_contains($title, 'vast') || str_contains($title, 'video')) {
            return 'view_ad';
        }
        
        if (str_contains($title, 'social') || str_contains($title, 'share')) {
            return 'share_link';
        }
        
        return 'view_ad';
    }

    /**
     * Convert placement to task data
     */
    public function placementToTaskData(array $placement): array
    {
        return [
            'title' => $this->formatPlacementTitle($placement['title'] ?? $placement['alias'] ?? 'Adsterra Task'),
            'description' => 'Tazama tangazo hili kwa sekunde 30 na upate malipo.',
            'type' => $this->getPlacementType($placement['title'] ?? ''),
            'url' => $placement['direct_url'] ?? '',
            'provider' => 'adsterra',
            'duration_seconds' => 30,
            'daily_limit' => 3,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 10,
            'requirements' => [
                'adsterra_placement_id' => $placement['id'] ?? null,
                'adsterra_domain_id' => $placement['domain_id'] ?? null,
            ],
        ];
    }

    /**
     * Format placement title to be user-friendly
     */
    protected function formatPlacementTitle(string $title): string
    {
        // Remove underscores and numbers
        $title = preg_replace('/[_\d]+/', ' ', $title);
        $title = trim($title);
        
        // Map common types to Swahili titles
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
        
        if (str_contains($lowerTitle, 'vast') || str_contains($lowerTitle, 'video')) {
            return 'Tazama Video Tangazo';
        }
        
        return 'Tazama Tangazo - ' . ucwords($title);
    }
}
