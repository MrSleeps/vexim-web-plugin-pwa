<?php

namespace VEximweb\Plugin\PWA\Providers;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\View;
use VEximweb\Core\Data\Repositories\SettingRepository;

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
        } catch (\Exception $e) {
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

        try {
            return view('pwa::filament.head', ['settings' => $this->settings])->render();
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            \Log::error('PWA: Failed to render scripts: ' . $e->getMessage());
            return '';
        }
    }
}