<!-- PWA Native Install Container -->
<div id="pwa-install-container" style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 99999; background: #1a1a1a; padding: 20px 24px; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); max-width: 90%; width: 400px; border: 1px solid rgba(255,255,255,0.1);">
    <!-- Top section: Icon + App Name + Close button -->
    <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
        <!-- PWA Icon -->
        <div style="width: 48px; height: 48px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            @php
                $iconPath = $settings['pwa_icon_path'] ?? '/images/pwa-logo.png';
                $shortName = $settings['pwa_short_name'] ?? 'APP';
                $initials = '';
                $words = explode(' ', $shortName);
                foreach ($words as $word) {
                    $initials .= strtoupper(substr($word, 0, 1));
                }
                $initials = $initials ?: substr($shortName, 0, 2);
            @endphp
            <img src="{{ $iconPath }}" 
                 alt="App Icon" 
                 style="width: 100%; height: 100%; object-fit: contain;"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'color: white; font-weight: 700; font-size: 18px;\'>{{ $initials }}</span>';">
        </div>

        <!-- App Info -->
        <div style="flex: 1;">
            <div style="color: white; font-weight: 600; font-size: 16px;">
                {{ $settings['site_name'] ?? 'Install App' }}
            </div>
            <div style="color: rgba(255,255,255,0.6); font-size: 13px; margin-top: 2px;">
                Add to home screen for the best experience
            </div>
        </div>

        <!-- Close Button -->
        <button id="pwa-close-btn" 
                style="background: none; border: none; color: rgba(255,255,255,0.3); cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Bottom section: Install Button -->
    <button id="pwa-install-btn" 
            style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 8px; font-weight: 600; background: #1976D2; color: white; border: none; cursor: pointer; font-size: 14px; transition: background 0.2s;"
            onmouseover="this.style.background='#1565C0'"
            onmouseout="this.style.background='#1976D2'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Install App
    </button>
</div>

<style>
    /* Only show on mobile */
    @media (min-width: 769px) {
        #pwa-install-container {
            display: none !important;
        }
    }
    
    @media (max-width: 768px) {
        #pwa-install-container {
            bottom: 20px !important;
            padding: 16px 20px !important;
            width: 90% !important;
        }
    }
</style>