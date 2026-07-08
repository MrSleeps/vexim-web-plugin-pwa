<?php

namespace VEximweb\Plugin\PWA\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Filament\Panel;
use VEximweb\Core\Data\Repositories\SettingRepository;

class PWAServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \VEximweb\Core\Data\Repositories\Interfaces\SettingRepositoryInterface::class,
            SettingRepository::class
        );

        // Register the plugin with the 'vexim' panel BEFORE Filament builds
        // that panel's routes. This must happen in register(), not boot(),
        // because Filament's own provider builds resource routes during its
        // boot() phase — by the time our boot() (or app->booted()) runs,
        // the panel's route group has already executed and adding the
        // resource afterwards has no effect on routing.
        Panel::configureUsing(function (Panel $panel) {
            if ($panel->getId() !== 'vexim') {
                return;
            }

            // Resolve settings lazily inside the closure — this runs later,
            // when Filament actually constructs the panel, so it's safe even
            // if config/DB state isn't fully ready during our own register().
            $settingRepository = $this->app->make(SettingRepository::class);

            if (!filter_var($settingRepository->get('pwa_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
                \Log::info('PWA: PWA is disabled, skipping plugin registration');
                return;
            }

            \Log::info('PWA: Registering plugin with panel via configureUsing: ' . $panel->getId());

            $panel->plugin(new PWAFilamentPlugin($settingRepository));
        });
    }

    public function boot(): void
    {
        \Log::info('PWA: Service provider booting');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $viewPath = __DIR__ . '/../resources/views';
        \Log::info('PWA: Loading views from: ' . $viewPath);

        if (is_dir($viewPath)) {
            $this->loadViewsFrom($viewPath, 'pwa');
            \Log::info('PWA: Views loaded successfully');
        }

        // Publish views
        $this->publishes([
            $viewPath => resource_path('views/vendor/pwa'),
        ], 'pwa-views');

        // Register Blade directives
        $this->registerBladeDirectives();

        // NOTE: Filament panel/plugin registration now happens in register()
        // via Panel::configureUsing(). Do not re-add booted()/resolving()
        // hooks here — see the comment above for why that doesn't work.
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('pwaHead', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderHead(); ?>";
        });

        Blade::directive('pwaBody', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderBody(); ?>";
        });

        Blade::directive('pwaScripts', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderScripts(); ?>";
        });
    }
}