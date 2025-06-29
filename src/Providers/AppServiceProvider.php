<?php

namespace Maya\FilamentSaasPlugin\Providers;

use Maya\FilamentSaasPlugin\Facades\FilamentSubscriptions;
use Maya\FilamentSaasPlugin\Services\FilamentNotificationService;
use Maya\FilamentSaasPlugin\Services\HelperService;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('filament-subscriptions', function () {
            return new \Maya\FilamentSaasPlugin\Services\FilamentSubscriptionServices();
        });
        
        $this->app->singleton('filament-notify', function ($app) {
            return new FilamentNotificationService();
        });

        $this->app->singleton('helper', function ($app) {
            return new HelperService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentSubscriptions::register(
            \Maya\FilamentSaasPlugin\Services\Contracts\Subscriber::make('Team')->model(\Maya\FilamentSaasPlugin\Models\Team::class)
        );
    }
}
