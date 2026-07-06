<?php

namespace VEximweb\Plugin\PWA\Http\Controllers;

use Illuminate\Routing\Controller;
use VEximweb\Core\Data\Repositories\SettingRepository;
use Illuminate\Http\Response;

class PWAServiceWorkerController extends Controller
{
    protected SettingRepository $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    public function serviceWorker(): Response
    {
        $settings = $this->settingRepository->getAll();
        
        // Views are now in filament/ directory
        $content = view('pwa::filament.sw-js', [
            'settings' => $settings
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}