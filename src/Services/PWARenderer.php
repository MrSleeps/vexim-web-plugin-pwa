<?php

namespace VEximweb\Plugin\PWA\Services;

use VEximweb\Core\Data\Repositories\SettingRepository;

class PWARenderer
{
    protected SettingRepository $settings;
    protected array $cachedSettings = [];

    public function __construct(SettingRepository $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Check if PWA is enabled.
     */
    public function isEnabled(): bool
    {
        return filter_var($this->settings->get('pwa_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Render PWA head section.
     */
    public function renderHead(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $settings = $this->getSettings();
        $manifestUrl = route('pwa.manifest');
        $themeColor = $settings['pwa_theme_color'] ?? '#1976D2';
        $shortName = $settings['pwa_short_name'] ?? 'PWA';
        $iconPath = $settings['pwa_icon_path'] ?? '/vendor/pwa/logo.png';

        return <<<HTML
<!-- PWA Head -->
<link rel="manifest" href="{$manifestUrl}">
<meta name="theme-color" content="{$themeColor}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{$shortName}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="{$shortName}">
<link rel="apple-touch-icon" href="{$iconPath}">
HTML;
    }

    /**
     * Render PWA body section.
     */
    public function renderBody(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return <<<HTML
<div id="pwa-install-container" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button id="pwa-install-btn" 
            class="fi-btn fi-btn-color-primary fi-btn-size-md fi-btn-label"
            style="padding: 12px 24px; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <span class="fi-btn-label">📱 Install App</span>
    </button>
</div>
HTML;
    }

    /**
     * Render PWA install button.
     */
    public function renderInstallButton(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        // Same as renderBody but separate for flexibility
        return $this->renderBody();
    }

    /**
     * Get settings with caching.
     */
    protected function getSettings(): array
    {
        if (empty($this->cachedSettings)) {
            try {
                $this->cachedSettings = $this->settings->getAll();
            } catch (\Exception $e) {
                $this->cachedSettings = [];
            }
        }
        return $this->cachedSettings;
    }
}