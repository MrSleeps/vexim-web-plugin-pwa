<!-- PWA  -->
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="{{ $settings['pwa_theme_color'] ?? '#1976D2' }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $settings['pwa_short_name'] ?? 'PWA' }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="{{ $settings['pwa_short_name'] ?? 'PWA' }}">
<link rel="apple-touch-icon" href="{{ $settings['pwa_icon_path'] ?? '/vendor/pwa/logo.png' }}">
