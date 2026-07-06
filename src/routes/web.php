<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use VEximweb\Plugin\PWA\Http\Controllers\PWAManifestController;
use VEximweb\Plugin\PWA\Http\Controllers\PWAServiceWorkerController;

// PWA Manifest
Route::get('/manifest.json', [PWAManifestController::class, 'manifest'])->name('pwa.manifest');

// PWA Service Worker
Route::get('/sw.js', [PWAServiceWorkerController::class, 'serviceWorker'])->name('pwa.service-worker');

// Optional: PWA Offline Fallback
Route::get('/offline', function () {
    return view('pwa.offline');
})->name('pwa.offline');


Route::get('/pwa-test', function() {
    try {
        $settings = app(\VEximweb\Core\Data\Repositories\SettingRepository::class)->getAll();
        return view('pwa::filament.body', ['settings' => $settings]);
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    }
});