<?php

namespace Maya\FilamentSaasPlugin;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Maya\FilamentSaasPlugin\Filament\Pages\PaymentGateway;
use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource;
use Maya\FilamentSaasPlugin\Filament\Resources\PlanResource;
use Maya\FilamentSaasPlugin\Filament\Resources\SubscriptionResource;
use Maya\FilamentSaasPlugin\Http\Middleware\ExtendedAuthenticate;
use Maya\FilamentSaasPlugin\Http\Middleware\VerifyIsAdmin;

class FilamentSaasAdminPlugin implements Plugin
{
    public function getId(): string
    {
        return 'admin';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PaymentResource::class,
                PlanResource::class,
                SubscriptionResource::class,
            ])
            ->pages([
                PaymentGateway::class
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Dashboard')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url('/client')
            ])
            ->middleware([
                VerifyIsAdmin::class
            ])
            ->authMiddleware([
                ExtendedAuthenticate::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
