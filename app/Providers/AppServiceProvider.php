<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AdProviderManager;
use App\Services\PostbackHandlerService;
use App\Services\FraudDetectionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AdProviderManager as singleton
        $this->app->singleton(AdProviderManager::class, function ($app) {
            return new AdProviderManager();
        });

        // Register PostbackHandlerService
        $this->app->singleton(PostbackHandlerService::class, function ($app) {
            return new PostbackHandlerService(
                $app->make(AdProviderManager::class)
            );
        });

        // Register FraudDetectionService
        $this->app->singleton(FraudDetectionService::class, function ($app) {
            return new FraudDetectionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

