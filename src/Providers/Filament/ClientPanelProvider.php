<?php

namespace Maya\FilamentSaasPlugin\Providers\Filament;

use Maya\FilamentSaasPlugin\Facades\Helper;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy\EditTeamProfile;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy\RegisterTeam;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\Billing;
use Maya\FilamentSaasPlugin\Http\Middleware\DefaultTeamVerify;
use Maya\FilamentSaasPlugin\Http\Middleware\ExtendedAuthenticate;
use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Maya\FilamentSaasPlugin\Models\Team;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('client')
            ->path('client')
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'secondary' => Color::Purple,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->login()
            ->registration()
            ->profile()
            ->unsavedChangesAlerts()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url('/admin')
                    ->visible(fn(): bool => Auth::user()->is_super),

            ])
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'Maya\FilamentSaasPlugin\\Filament\\Client\\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'Maya\FilamentSaasPlugin\\Filament\\Client\\Pages')
            ->pages([
                Pages\Dashboard::class,
                Billing::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'Maya\FilamentSaasPlugin\\Filament\\Client\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                ExtendedAuthenticate::class,
            ])
            ->tenantMiddleware([
                VerifyBillableIsSubscribed::class,
                DefaultTeamVerify::class,
            ], isPersistent: true)
            ->databaseNotificationsPolling('1s')
            ->databaseNotifications()
            ->tenant(Team::class, ownershipRelationship: 'team', slugAttribute: 'slug')
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class);
    }
}
