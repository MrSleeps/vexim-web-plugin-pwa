<?php

namespace VEximweb\Plugin\PWA\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use VEximweb\Core\Data\Repositories\SettingRepository;

class PWAServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \VEximweb\Core\Data\Repositories\Interfaces\SettingRepositoryInterface::class,
            SettingRepository::class
        );
    }

    public function boot(): void
    {
        \Log::info('PWA: Service provider booting');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/Migrations');

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

        // Register the plugin with the Filament panel
        $this->registerWithFilamentPanel();
    }

    /**
     * Register the PWA plugin with the Filament panel.
     */
    protected function registerWithFilamentPanel(): void
    {
        // Method 1: Register when the app is fully booted
        $this->app->booted(function () {
            \Log::info('PWA: App booted, registering with Filament panel...');
            $this->registerPluginWithFilament();
        });

        // Method 2: Also try when filament is being resolved
        $this->app->resolving('filament', function ($app) {
            \Log::info('PWA: Filament resolving, registering plugin...');
            $this->registerPluginWithFilament();
        });

        // Method 3: Try to find the panel directly
        $this->registerPluginWithFilament();
    }

    /**
     * Register the plugin with Filament panels.
     */
    protected function registerPluginWithFilament(): void
    {
        try {
            // Check if Filament is available
            if (!class_exists('Filament\Facades\Filament')) {
                \Log::warning('PWA: Filament not available');
                return;
            }

            // Try to get the 'vexim' panel directly (since that's your panel's ID)
            try {
                $panel = \Filament\Facades\Filament::getPanel('vexim');
                if ($panel) {
                    \Log::info('PWA: Found vexim panel, registering plugin...');
                    $this->registerPluginWithPanel($panel);
                    return;
                }
            } catch (\Exception $e) {
                \Log::warning('PWA: Could not get vexim panel: ' . $e->getMessage());
            }

            // If that fails, try to get all panels
            $panels = \Filament\Facades\Filament::getPanels();
            
            if (empty($panels)) {
                \Log::warning('PWA: No Filament panels found');
                return;
            }

            \Log::info('PWA: Found ' . count($panels) . ' Filament panels');
            
            foreach ($panels as $panel) {
                $this->registerPluginWithPanel($panel);
            }
        } catch (\Exception $e) {
            \Log::error('PWA: Failed to register with Filament: ' . $e->getMessage());
        }
    }

    /**
     * Register the plugin with a specific panel.
     */
    protected function registerPluginWithPanel($panel): void
    {
        try {
            // Check if PWA is enabled
            $settings = app(SettingRepository::class);
            if (!filter_var($settings->get('pwa_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
                \Log::info('PWA: PWA is disabled, skipping plugin registration');
                return;
            }

            // Check if plugin is already registered
            $plugins = $panel->getPlugins();
            foreach ($plugins as $plugin) {
                if ($plugin->getId() === 'pwa') {
                    \Log::info('PWA: Plugin already registered with panel');
                    return;
                }
            }

            // Register the plugin
            $panel->plugin(new PWAFilamentPlugin(
                app(SettingRepository::class)
            ));
            
            \Log::info('PWA: Plugin registered successfully with panel: ' . $panel->getId());
        } catch (\Exception $e) {
            \Log::error('PWA: Failed to register plugin with panel: ' . $e->getMessage());
        }
    }

    protected function registerBladeDirectives(): void
    {
        \Illuminate\Support\Facades\Blade::directive('pwaHead', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderHead(); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('pwaBody', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderBody(); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('pwaScripts', function () {
            return "<?php echo app(\VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin::class)->renderScripts(); ?>";
        });
    }
}