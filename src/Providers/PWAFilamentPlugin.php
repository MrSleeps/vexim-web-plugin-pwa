<?php

namespace VEximweb\Plugin\PWA\Providers;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\View;
use VEximweb\Core\Data\Repositories\SettingRepository;
use VEximweb\Plugin\PWA\Filament\Resources\PwaSettingsResource;

class PWAFilamentPlugin implements Plugin
{
    protected SettingRepository $settingRepository;
    protected array $settings = [];
    protected static bool $registered = false;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->loadSettings();
    }

    public function getId(): string
    {
        return 'pwa';
    }

    public function register(Panel $panel): void
    {
        if (static::$registered) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        \Log::info('PWA: Registering plugin with panel: ' . $panel->getId());

        // Register the settings resource
        $panel->resources([
            PwaSettingsResource::class,
        ]);

        // Register render hooks
        $this->registerRenderHooks($panel);

        static::$registered = true;
        \Log::info('PWA: Plugin registration complete');
    }

    public function boot(Panel $panel): void
    {
        // No additional boot logic needed
    }

    protected function loadSettings(): void
    {
        try {
            $this->settings = $this->settingRepository->getAll();
        } catch (\Throwable $e) {
            \Log::error('PWA: Failed to load settings: ' . $e->getMessage());
            $this->settings = [];
        }
    }

    protected function isEnabled(): bool
    {
        return filter_var($this->settings['pwa_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    protected function registerRenderHooks(Panel $panel): void
    {
        \Log::info('PWA: Registering render hooks for panel: ' . $panel->getId());

        // Head section
        $panel->renderHook(
            'panels::head.start',
            fn () => $this->renderHead()
        );

        // Body end section
        $panel->renderHook(
            'panels::body.end',
            fn () => $this->renderBody()
        );

        // Scripts
        $panel->renderHook(
            'panels::body.end',
            fn () => $this->renderScripts()
        );
    }

    public function renderHead(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->loadSettings();

        // Note: catches \Throwable, not just \Exception. PHP 8+ errors like
        // "Undefined constant" are thrown as \Error, which does NOT extend
        // \Exception — a catch(\Exception) here would let them propagate
        // uncaught straight out of this render hook, potentially crashing
        // the entire panel page mid-render. \Throwable covers both.
        try {
            return view('pwa::filament.head', ['settings' => $this->settings])->render();
        } catch (\Throwable $e) {
            \Log::error('PWA: Failed to render head: ' . $e->getMessage());
            return '';
        }
    }

    public function renderBody(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->loadSettings();

        try {
            return view('pwa::filament.body', ['settings' => $this->settings])->render();
        } catch (\Throwable $e) {
            \Log::error('PWA: Failed to render body: ' . $e->getMessage());
            return '';
        }
    }

    public function renderScripts(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->loadSettings();

        try {
            return view('pwa::filament.scripts', ['settings' => $this->settings])->render();
        } catch (\Throwable $e) {
            \Log::error('PWA: Failed to render scripts: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get all PWA settings (useful for other parts of the plugin)
     */
    public function getSettings(): array
    {
        $this->loadSettings();
        return $this->settings;
    }

    /**
     * Get a specific setting
     */
    public function getSetting(string $key, $default = null)
    {
        $this->loadSettings();
        return $this->settings[$key] ?? $default;
    }

    /**
     * Clear the settings cache
     */
    public function clearCache(): void
    {
        $this->settingRepository->clearCache();
        $this->loadSettings();
    }
}