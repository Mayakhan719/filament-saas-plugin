<?php

namespace Maya\FilamentSaasPlugin;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Maya\FilamentSaasPlugin\Livewire\PaymentProcess;
use Maya\FilamentSaasPlugin\Services\FilamentPaymentsServices;
use Maya\FilamentSaasPlugin\Services\FilamentSubscriptionServices;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Livewire\Livewire;
/**
 * Class FilamentSaasPluginServiceProvider
 *
 * This service provider registers the Filament SaaS plugin, including assets, commands,
 * migrations, translations, views, and Livewire components.
 */
class FilamentSaasPluginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-saas-plugin';

    public static string $viewNamespace = 'filament-saas-plugin';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('maya/filament-saas-plugin');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {
        Livewire::component('payment-process',PaymentProcess::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    public function packageBooted(): void
    {
        // Asset registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
        $this->app->bind('filament-payments', function () {
            return new FilamentPaymentsServices();
        });
        $this->app->singleton('filament-subscriptions', function () {
            return new FilamentSubscriptionServices();
        });
        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon registration (if you have icons)
        FilamentIcon::register($this->getIcons());

        // Publish stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-saas-plugin/{$file->getFilename()}"),
                ], 'filament-saas-plugin-stubs');
            }

            // Publish views manually (for safety)
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/' . static::$viewNamespace),
            ], 'filament-saas-plugin-views');
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'maya/filament-saas-plugin';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            // Add artisan commands if any
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [
            // Register custom icons if needed
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2023_09_04_100358_create_teams_table',
            '2023_09_04_100361_create_team_user_table',
            '0001_01_00_000001_add_columns_to_users_table',
            '2025_06_02_075842_create_payment_gateways_table',
            '2025_06_02_075843_create_payments_table',
            '2025_06_02_075844_payment_logs_table',
        ];
    }
}
