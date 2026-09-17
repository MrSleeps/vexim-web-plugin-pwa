<?php

namespace VEximweb\Plugin\PWA\Tests;

use Filament\Panel;
use Illuminate\Support\Facades\Route;
use VEximweb\Core\Data\Repositories\SettingRepository;
use VEximweb\Plugin\PWA\Filament\Resources\PwaSettingsResource;
use VEximweb\Plugin\PWA\Providers\PWAFilamentPlugin;

class RouteRegistrationTest extends TestCase
{
    public function test_settings_resource_is_registered_when_pwa_is_disabled(): void
    {
        $settings = $this->createMock(SettingRepository::class);
        $settings->method('getAll')->willReturn([
            'pwa_enabled' => false,
        ]);

        $panel = Panel::make()
            ->id('vexim')
            ->path('');

        (new PWAFilamentPlugin($settings))->register($panel);

        $this->assertContains(PwaSettingsResource::class, $panel->getResources());
    }

    public function test_pwa_settings_index_route_is_registered_with_expected_name(): void
    {
        $panel = Panel::make()
            ->id('vexim')
            ->path('');

        Route::name('filament.vexim.')->group(function () use ($panel): void {
            PwaSettingsResource::registerRoutes($panel);
        });

        Route::getRoutes()->refreshNameLookups();

        $this->assertTrue(Route::has('filament.vexim.resources.pwa-settings.index'));
    }
}
