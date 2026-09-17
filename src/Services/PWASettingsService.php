<?php

namespace VEximweb\Plugin\PWA\Services;

use VEximweb\Core\Data\Repositories\SettingRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PWASettingsService
{
    protected SettingRepository $repository;
    protected const CACHE_KEY = 'pwa_settings';
    protected const CACHE_DURATION = 3600;

    public function __construct(SettingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all PWA settings
     */
    public function getAll(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            // Get all settings with category 'pwa'
            $settings = $this->repository->getByPattern('pwa_%');
            
            return $settings->toArray();
        });
    }

    /**
     * Get a specific PWA setting
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->getAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a PWA setting
     */
    public function set(string $key, $value, string $type = null, string $description = null): void
    {
        // Auto-detect type if not specified
        if (!$type) {
            $type = match(true) {
                is_int($value) => 'integer',
                is_bool($value) => 'boolean',
                is_array($value) => 'json',
                default => 'string'
            };
        }
        
        // Ensure category is set to 'pwa'
        $setting = $this->repository->getModel($key);
        
        if ($setting) {
            $setting->value = $value;
            $setting->type = $type;
            $setting->category = 'pwa';
            if ($description) {
                $setting->description = $description;
            }
            $setting->save();
        } else {
            // Create new setting with category 'pwa'
            $this->repository->set($key, $value, $type, $description);
            
            // Update the category to 'pwa'
            $setting = $this->repository->getModel($key);
            if ($setting) {
                $setting->category = 'pwa';
                $setting->save();
            }
        }
        
        $this->clearCache();
    }

    /**
     * Update multiple PWA settings at once
     */
    public function updateMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (is_array($value) && isset($value['value'])) {
                $this->set(
                    $key,
                    $value['value'],
                    $value['type'] ?? null,
                    $value['description'] ?? null
                );
            } else {
                $this->set($key, $value);
            }
        }
        $this->clearCache();
    }

    /**
     * Clear the PWA settings cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->repository->clearCache();
    }

    /**
     * Get default PWA settings
     */
    public function getDefaults(): array
    {
        return [
            'pwa_long_name' => config('app.name', 'Laravel PWA'),
            'pwa_short_name' => 'PWA',
            'pwa_description' => 'A Progressive Web Application',
            'pwa_lang' => 'en',
            'pwa_theme_color' => '#ffffff',
            'pwa_background_color' => '#000000',
            'pwa_icon_path' => '/images/pwa-logo.png',
            'pwa_icon_sizes' => '512x512,192x192,144x144,96x96,72x72',
            'pwa_display' => 'standalone',
            'pwa_orientation' => 'portrait',
            'pwa_scope' => '/',
            'pwa_categories' => 'web,app,productivity',
            'pwa_allow_offline' => '1',
            'pwa_cache_duration' => 3600,
            'pwa_routes_to_cache' => '/,api,/images,/css,/js',
        ];
    }
    
    /**
     * Get all PWA settings as a collection
     */
    public function getAllAsCollection(): Collection
    {
        return collect($this->getAll());
    }
    
    /**
     * Get manifest-specific settings
     */
    public function getManifestSettings(): array
    {
        $all = $this->getAll();
        $manifestKeys = [
            'pwa_long_name',
            'pwa_short_name',
            'pwa_description',
            'pwa_lang',
            'pwa_theme_color',
            'pwa_background_color',
            'pwa_icon_path',
            'pwa_icon_sizes',
            'pwa_display',
            'pwa_orientation',
            'pwa_scope',
            'pwa_categories'
        ];

        return array_intersect_key($all, array_flip($manifestKeys));
    }
}
