<?php

namespace VEximweb\Plugin\PWA\Filament\Resources\PwaSettingsResource\Pages;

use VEximweb\Plugin\PWA\Filament\Resources\PwaSettingsResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TagsInput;
use VEximweb\Plugin\PWA\Models\PWASettings;

class EditPWASettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    
    protected static string $resource = PwaSettingsResource::class;
    
    protected string $view = 'pwa::filament.resources.settings.pages.edit-pwa-settings';
    
    public ?array $data = [];

    public function mount(): void
    {
        $settings = PWASettings::where('key', 'LIKE', 'pwa_%')->get();
        
        $formData = [];
        foreach ($settings as $setting) {
            $formData[$setting->key] = $setting->value;
        }

        $this->data = $formData;
        $this->form->fill($formData);
    }	
	
    protected function getHeaderActions(): array
    {
        return [];
    }
	
	public function getBreadcrumbs(): array
	{
		return [];
	}
	
    public function getTitle(): string
    {
        return 'Edit PWA Settings';
    }	
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema($this->getFormSchema())
            ->statePath('data');
    }
    
    protected function getFormSchema(): array
    {
        return [
            Tabs::make('PWA Settings Tabs')
                ->tabs([
                    Tab::make('general')
                        ->label('General')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('General PWA Settings')
                                ->description('Configure basic Progressive Web App information')
                                ->schema([
                                    TextInput::make('pwa_long_name')
                                        ->label('App Long Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->default(config('app.name'))
                                        ->helperText('The full name of your PWA application')
                                        ->placeholder('My Progressive Web App'),
                                    
                                    TextInput::make('pwa_short_name')
                                        ->label('Short Name')
                                        ->required()
                                        ->maxLength(12)
                                        ->helperText('Short name for the app (max 12 characters)')
                                        ->placeholder('PWA'),

                                    Textarea::make('pwa_description')
                                        ->label('Description')
                                        ->maxLength(500)
                                        ->rows(3)
                                        ->helperText('Brief description of your PWA')
                                        ->placeholder('A modern progressive web application...'),

                                    TextInput::make('pwa_lang')
                                        ->label('Language')
                                        ->required()
                                        ->default('en')
                                        ->maxLength(2)
                                        ->helperText('Language code (e.g., en, fr, es)')
                                        ->placeholder('en'),
                                ])->columns(2),
                        ]),

                    Tab::make('branding')
                        ->label('Branding & Appearance')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            Section::make('Branding & Appearance')
                                ->description('Customize the look and feel of your PWA')
                                ->schema([
                                    ColorPicker::make('pwa_theme_color')
                                        ->label('Theme Color')
                                        ->required()
                                        ->default('#ffffff')
                                        ->helperText('Color of the browser UI elements'),

                                    ColorPicker::make('pwa_background_color')
                                        ->label('Background Color')
                                        ->required()
                                        ->default('#000000')
                                        ->helperText('Background color of the splash screen'),

                                    TextInput::make('pwa_icon_path')
                                        ->label('Icon Path')
                                        ->required()
                                        ->default('/images/pwa-logo.png')
                                        ->helperText('Path to the PWA icon (relative to public directory)')
                                        ->placeholder('/images/pwa-logo.png'),

                                    TagsInput::make('pwa_icon_sizes')
                                        ->label('Icon Sizes')
                                        ->required()
                                        ->default(['512x512', '192x192', '144x144', '96x96', '72x72'])
                                        ->helperText('Add icon sizes (e.g., 512x512)')
                                        ->placeholder('Add icon size')
                                        ->separator(','),
                                ])->columns(2),
                        ]),

                    Tab::make('display')
                        ->label('Display & Navigation')
                        ->icon('heroicon-o-device-phone-mobile')
                        ->schema([
                            Section::make('Display & Navigation')
                                ->description('Configure how your PWA is displayed')
                                ->schema([
                                    Select::make('pwa_display')
                                        ->label('Display Mode')
                                        ->required()
                                        ->options([
                                            'fullscreen' => 'Fullscreen',
                                            'standalone' => 'Standalone',
                                            'minimal-ui' => 'Minimal UI',
                                            'browser' => 'Browser',
                                        ])
                                        ->default('standalone')
                                        ->helperText('How the PWA should be displayed'),

                                    Select::make('pwa_orientation')
                                        ->label('Orientation')
                                        ->options([
                                            'any' => 'Any',
                                            'natural' => 'Natural',
                                            'landscape' => 'Landscape',
                                            'landscape-primary' => 'Landscape Primary',
                                            'landscape-secondary' => 'Landscape Secondary',
                                            'portrait' => 'Portrait',
                                            'portrait-primary' => 'Portrait Primary',
                                            'portrait-secondary' => 'Portrait Secondary',
                                        ])
                                        ->default('portrait')
                                        ->helperText('Preferred screen orientation'),

                                    TextInput::make('pwa_scope')
                                        ->label('Scope')
                                        ->required()
                                        ->default('/')
                                        ->helperText('Navigation scope of the service worker')
                                        ->placeholder('/'),

                                    TagsInput::make('pwa_categories')
                                        ->label('Categories')
                                        ->default(['web', 'app', 'productivity'])
                                        ->helperText('App store categories')
                                        ->placeholder('Add category')
                                        ->separator(','),
                                ])->columns(2),
                        ]),

                    Tab::make('offline')
                        ->label('Offline & Caching')
                        ->icon('heroicon-o-cloud')
                        ->schema([
                            Section::make('Offline & Caching')
                                ->description('Configure offline support and caching behavior')
                                ->schema([
                                    Toggle::make('pwa_allow_offline')
                                        ->label('Enable Offline Support')
                                        ->required()
                                        ->default(true)
                                        ->helperText('Enable Service Worker for offline caching')
                                        ->onColor('success')
                                        ->offColor('danger'),

                                    TextInput::make('pwa_cache_duration')
                                        ->label('Cache Duration (seconds)')
                                        ->required()
                                        ->numeric()
                                        ->default(3600)
                                        ->minValue(60)
                                        ->maxValue(86400)
                                        ->helperText('How long to cache assets in seconds (1 hour = 3600, 1 day = 86400)')
                                        ->suffix('seconds'),

                                    TagsInput::make('pwa_routes_to_cache')
                                        ->label('Routes to Cache')
                                        ->default(['/', 'api', '/images', '/css', '/js'])
                                        ->helperText('Routes/URLs to cache for offline access')
                                        ->placeholder('Add route (e.g., /, /api)')
                                        ->separator(','),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
    
    public function save(): void
    {
        try {
            $state = $this->form->getState();
            
            foreach ($state as $key => $value) {
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
                
                // Use updateOrCreate to ensure the record exists
                PWASettings::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }
            
            Notification::make()
                ->title('All settings saved successfully')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save All Settings')
                ->action('save')
                ->color('primary'),
                
            Action::make('back')
                ->label('Back to List')
                ->url(PwaSettingsResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}