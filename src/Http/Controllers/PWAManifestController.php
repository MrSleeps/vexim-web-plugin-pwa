<?php

namespace VEximweb\Plugin\PWA\Http\Controllers;

use VEximweb\Core\Data\Repositories\SettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PWAManifestController extends Controller
{
    protected SettingRepository $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    public function manifest(): JsonResponse
    {
        $settings = $this->settingRepository->getAll();
        
        // Get the logo path
        $logoPath = $settings['pwa_icon_path'] ?? '/images/pwa-logo.png';
        
        // Check if logo exists
        $logoExists = file_exists(public_path(str_replace('/images/', '', $logoPath)));
        
        $icons = [];
        
        if ($logoExists) {
            $iconSizes = ['512x512', '192x192', '144x144', '96x96', '72x72'];
            foreach ($iconSizes as $size) {
                $icons[] = [
                    'src' => $logoPath,
                    'sizes' => $size,
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ];
            }
        }

        $manifest = [
            'name' => $settings['site_name'] ?? config('app.name', 'Laravel PWA'),
            'short_name' => $settings['pwa_short_name'] ?? $this->getInitials($settings['site_name'] ?? 'APP'),
            'start_url' => '/',
            'background_color' => $settings['pwa_background_color'] ?? '#ffffff',
            'description' => $settings['pwa_description'] ?? 'A Progressive Web Application.',
            'display' => $settings['pwa_display'] ?? 'standalone',
            'theme_color' => $settings['pwa_theme_color'] ?? '#1976D2',
            'icons' => $icons,
            'orientation' => $settings['pwa_orientation'] ?? 'portrait',
            'scope' => '/',
            'lang' => $settings['pwa_lang'] ?? 'en',
            'categories' => ['web', 'app', 'productivity'],
            'dir' => 'ltr',
            'prefer_related_applications' => false,
        ];

        return response()->json(array_filter($manifest, function ($value) {
            return !empty($value);
        }));
    }

    private function getInitials(string $string): string
    {
        $words = preg_split('/\s+/', trim($string));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        
        return $initials ?: 'APP';
    }
}