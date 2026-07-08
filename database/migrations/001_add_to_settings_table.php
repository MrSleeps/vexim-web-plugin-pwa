<?php

use Illuminate\Database\Migrations\Migration;
use VEximweb\Core\Data\Models\Setting;

return new class extends Migration
{
    private function getInitials(string $string): string
    {
        $words = preg_split('/\s+/', trim($string));
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper($word[0] ?? '');
        }

        return $initials ?: 'APP';
    }

    public function up(): void
    {
        $appName = env('APP_NAME', 'Laravel PWA');
        $shortName = $this->getInitials($appName);

        // Core PWA Settings
        Setting::set('pwa_long_name', $appName, 'string', 'Full application name', 'pwa');
        Setting::set('pwa_short_name', $shortName, 'string', 'Short name for PWA (max 12 chars)', 'pwa');
        Setting::set('pwa_description', 'A Progressive Web Application setup for Laravel projects.', 'string', 'Description shown in PWA', 'pwa');

        // PWA Display Settings
        Setting::set('pwa_display', 'standalone', 'string', 'Display mode: fullscreen, standalone, minimal-ui, browser', 'pwa');
        Setting::set('pwa_orientation', 'portrait', 'string', 'Orientation: any, portrait, landscape', 'pwa');
        Setting::set('pwa_scope', '/', 'string', 'Navigation scope', 'pwa');
        Setting::set('pwa_lang', 'en', 'string', 'Language code', 'pwa');
        Setting::set('pwa_categories', 'web,app,productivity', 'string', 'Categories for app store', 'pwa');

        // Color Settings
        Setting::set('pwa_theme_color', '#ffffff', 'string', 'Theme color (hex)', 'pwa');
        Setting::set('pwa_background_color', '#000000', 'string', 'Background color (hex)', 'pwa');

        // Icon Settings
        Setting::set('pwa_icon_path', '/images/pwa-logo.png', 'string', 'Path to PWA icon', 'pwa');
        Setting::set('pwa_icon_sizes', '512x512,192x192,144x144,96x96,72x72', 'string', 'Comma-separated icon sizes', 'pwa');

        // Advanced Settings
        Setting::set('pwa_allow_offline', 'true', 'boolean', 'Enable offline support', 'pwa');
        Setting::set('pwa_cache_duration', '3600', 'integer', 'Cache duration in seconds', 'pwa');
        Setting::set('pwa_routes_to_cache', '/,api,/images,/css,/js', 'string', 'Comma-separated routes to cache', 'pwa');
    }

    public function down(): void
    {
        $keys = [
            'pwa_long_name',
            'pwa_short_name',
            'pwa_description',
            'pwa_display',
            'pwa_orientation',
            'pwa_scope',
            'pwa_lang',
            'pwa_categories',
            'pwa_theme_color',
            'pwa_background_color',
            'pwa_icon_path',
            'pwa_icon_sizes',
            'pwa_allow_offline',
            'pwa_cache_duration',
            'pwa_routes_to_cache'
        ];

        Setting::whereIn('key', $keys)->delete();
        Setting::clearCache();
    }
};
