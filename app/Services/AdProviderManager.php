<?php

namespace App\Services;

use App\Contracts\Providers\AdProviderInterface;
use App\Providers\AdProviders\AdsterraProvider;
use App\Providers\AdProviders\MonetagProvider;
use Illuminate\Support\Facades\Log;

/**
 * Ad Provider Manager
 * 
 * Central manager for all ad providers. Handles provider registration,
 * retrieval, and provides unified access to provider functionality.
 */
class AdProviderManager
{
    /**
     * Registered provider instances
     */
    protected array $providers = [];

    /**
     * Provider class mappings
     */
    protected array $providerClasses = [
        'adsterra' => AdsterraProvider::class,
        'monetag' => MonetagProvider::class,
        // Add more providers here as needed:
        // 'cpx' => CpxResearchProvider::class,
        // 'bitlabs' => BitLabsProvider::class,
    ];

    /**
     * Get a provider instance by name
     */
    public function provider(string $name): ?AdProviderInterface
    {
        $name = strtolower($name);

        // Return cached instance if exists
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        // Create new instance if class exists
        if (isset($this->providerClasses[$name])) {
            $this->providers[$name] = app($this->providerClasses[$name]);
            return $this->providers[$name];
        }

        Log::warning("Ad provider not found: {$name}");
        return null;
    }

    /**
     * Get Adsterra provider
     */
    public function adsterra(): AdsterraProvider
    {
        return $this->provider('adsterra');
    }

    /**
     * Get Monetag provider
     */
    public function monetag(): MonetagProvider
    {
        return $this->provider('monetag');
    }

    /**
     * Get all registered provider names
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providerClasses);
    }

    /**
     * Check if a provider is registered
     */
    public function hasProvider(string $name): bool
    {
        return isset($this->providerClasses[strtolower($name)]);
    }

    /**
     * Register a custom provider
     */
    public function registerProvider(string $name, string $class): self
    {
        if (!is_subclass_of($class, AdProviderInterface::class)) {
            throw new \InvalidArgumentException(
                "Provider class must implement AdProviderInterface"
            );
        }

        $this->providerClasses[strtolower($name)] = $class;
        
        // Clear cached instance if exists
        unset($this->providers[strtolower($name)]);

        return $this;
    }

    /**
     * Get all providers that support a specific category
     */
    public function getProvidersForCategory(string $category): array
    {
        $matching = [];

        foreach ($this->providerClasses as $name => $class) {
            $provider = $this->provider($name);
            if ($provider && in_array($category, $provider->getSupportedCategories())) {
                $matching[$name] = $provider;
            }
        }

        return $matching;
    }

    /**
     * Get all providers that support postbacks
     */
    public function getPostbackProviders(): array
    {
        $matching = [];

        foreach ($this->providerClasses as $name => $class) {
            $provider = $this->provider($name);
            if ($provider && $provider->supportsPostback()) {
                $matching[$name] = $provider;
            }
        }

        return $matching;
    }

    /**
     * Test connection to all providers
     */
    public function testAllConnections(): array
    {
        $results = [];

        foreach ($this->providerClasses as $name => $class) {
            $provider = $this->provider($name);
            
            if ($provider && method_exists($provider, 'testConnection')) {
                $results[$name] = $provider->testConnection();
            } else {
                $results[$name] = [
                    'success' => true,
                    'message' => 'Provider loaded successfully',
                ];
            }
        }

        return $results;
    }
}
