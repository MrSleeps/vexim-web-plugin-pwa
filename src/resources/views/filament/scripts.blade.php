<script>
    @php
    echo config('app.debug');
    @endphp
    console.log('=== START TEST ===');
@php
    
    $testOutput = pwa_debug('Test message');
    echo "console.log('Helper output: " . addslashes($testOutput) . "');";
    echo $testOutput;
@endphp
console.log('=== END TEST ===');
// PWA Install Button
(function() {
    {!! pwa_debug('PWA Install script loaded') !!}
    
    let deferredPrompt;
    let installContainer = document.getElementById('pwa-install-container');
    let installBtn = document.getElementById('pwa-install-btn');
    let closeBtn = document.getElementById('pwa-close-btn');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

    // Check if already installed via localStorage
    const hasInstalledFlag = localStorage.getItem('pwa_installed') === 'true';
    const isInstalled = isStandalone || hasInstalledFlag;

    // Check if user has dismissed
    const hasDismissedPWA = localStorage.getItem('pwa_dismissed') === 'true';
    const pwaDismissedTime = parseInt(localStorage.getItem('pwa_dismissed_time') || '0');
    const dismissExpiry = 7 * 24 * 60 * 60 * 1000; // 7 days
    const isDismissed = hasDismissedPWA && (Date.now() - pwaDismissedTime < dismissExpiry);

    {!! pwa_debug('Standalone:', ['isStandalone' => isStandalone]) !!}
    {!! pwa_debug('Installed flag:', ['hasInstalledFlag' => hasInstalledFlag]) !!}
    {!! pwa_debug('Is Installed:', ['isInstalled' => isInstalled]) !!}
    {!! pwa_debug('Dismissed:', ['isDismissed' => isDismissed]) !!}

    // If installed or dismissed - hide everything
    if (isInstalled || isDismissed) {
        {!! pwa_debug('Installed or dismissed - hiding') !!}
        if (installContainer) installContainer.style.display = 'none';
        return;
    }

    function dismissPWA() {
        localStorage.setItem('pwa_dismissed', 'true');
        localStorage.setItem('pwa_dismissed_time', Date.now().toString());
        if (installContainer) installContainer.style.display = 'none';
        {!! pwa_debug('Dismissed for 7 days') !!}
    }

    function markAsInstalled() {
        localStorage.setItem('pwa_installed', 'true');
        if (installContainer) installContainer.style.display = 'none';
        {!! pwa_debug('Marked as installed') !!}
    }

    // Listen for native install prompt
    window.addEventListener('beforeinstallprompt', function(e) {
        if (isInstalled || localStorage.getItem('pwa_dismissed') === 'true') {
            return;
        }

        {!! pwa_debug('beforeinstallprompt FIRED!') !!}
        e.preventDefault();
        deferredPrompt = e;
        
        // Show native install button
        if (installContainer) {
            installContainer.style.display = 'block';
            {!! pwa_debug('Native install button shown') !!}
        }
    });

    // Install button click
    if (installBtn) {
        installBtn.addEventListener('click', function() {
            {!! pwa_debug('Install button clicked') !!}
            
            if (!deferredPrompt) {
                {!! pwa_debug('No deferredPrompt available', ['warning' => true]) !!}
                // Just hide the button
                if (installContainer) installContainer.style.display = 'none';
                return;
            }

            // Show native Chrome install dialog
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    {!! pwa_debug('Installed!') !!}
                    markAsInstalled();
                    localStorage.removeItem('pwa_dismissed');
                    localStorage.removeItem('pwa_dismissed_time');
                    if (installContainer) installContainer.style.display = 'none';
                } else {
                    {!! pwa_debug('User declined') !!}
                    dismissPWA();
                }
                deferredPrompt = null;
            });
        });
    }

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            {!! pwa_debug('User closed') !!}
            dismissPWA();
        });
    }

    // App installed event
    window.addEventListener('appinstalled', function() {
        {!! pwa_debug('appinstalled event fired!') !!}
        markAsInstalled();
        if (installContainer) installContainer.style.display = 'none';
    });

    {!! pwa_debug('Status:', [
        'isInstalled' => isInstalled,
        'isDismissed' => isDismissed,
        'hasDeferredPrompt' => !!deferredPrompt,
        'hasContainer' => !!installContainer,
        'hasButton' => !!installBtn
    ]) !!}
})();
</script>