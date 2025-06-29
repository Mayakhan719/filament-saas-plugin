<?php

namespace Maya\FilamentSaasPlugin;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Billing;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy\EditTeamProfile;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy\RegisterTeam;
use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource;
use Maya\FilamentSaasPlugin\Filament\Resources\PlanResource;
use Maya\FilamentSaasPlugin\Filament\Resources\SubscriptionResource;
use Maya\FilamentSaasPlugin\Http\Middleware\DefaultTeamVerify;
use Maya\FilamentSaasPlugin\Http\Middleware\ExtendedAuthenticate;
use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Maya\FilamentSaasPlugin\Models\Team;
use Illuminate\Support\Facades\Auth;

class FilamentSaasClientPlugin implements Plugin
{
    public function getId(): string
    {
        return 'client';
    }

    public function register(Panel $panel): void
    {
        
        $panel
            ->default()
            ->registration()
            ->profile()
            ->resources([
            ])
            ->pages([
                Billing::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url('/admin')
                    ->visible(fn(): bool => Auth::user()->is_super),
            ])
            ->authMiddleware([
                ExtendedAuthenticate::class,
            ])
            ->tenantMiddleware([
                VerifyBillableIsSubscribed::class,
                DefaultTeamVerify::class,
            ], isPersistent: true)
            ->tenant(Team::class, ownershipRelationship: 'team', slugAttribute: 'slug')
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class);
    }

    public function boot(Panel $panel): void
    {
        
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
