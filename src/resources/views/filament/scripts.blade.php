<script>
// PWA Install Button
(function() {
    
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

    // If installed or dismissed - hide everything
    if (isInstalled || isDismissed) {
        if (installContainer) installContainer.style.display = 'none';
        return;
    }

    function dismissPWA() {
        localStorage.setItem('pwa_dismissed', 'true');
        localStorage.setItem('pwa_dismissed_time', Date.now().toString());
        if (installContainer) installContainer.style.display = 'none';
    }

    function markAsInstalled() {
        localStorage.setItem('pwa_installed', 'true');
        if (installContainer) installContainer.style.display = 'none';
    }

    // Listen for native install prompt
    window.addEventListener('beforeinstallprompt', function(e) {
        if (isInstalled || localStorage.getItem('pwa_dismissed') === 'true') {
            return;
        }

        e.preventDefault();
        deferredPrompt = e;
        
        // Show native install button
        if (installContainer) {
            installContainer.style.display = 'block';
        }
    });

    // Install button click
    if (installBtn) {
        installBtn.addEventListener('click', function() {
            
            if (!deferredPrompt) {
                // Just hide the button
                if (installContainer) installContainer.style.display = 'none';
                return;
            }

            // Show native Chrome install dialog
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    markAsInstalled();
                    localStorage.removeItem('pwa_dismissed');
                    localStorage.removeItem('pwa_dismissed_time');
                    if (installContainer) installContainer.style.display = 'none';
                } else {
                    dismissPWA();
                }
                deferredPrompt = null;
            });
        });
    }

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            dismissPWA();
        });
    }

    // App installed event
    window.addEventListener('appinstalled', function() {
        markAsInstalled();
        if (installContainer) installContainer.style.display = 'none';
    });

})();
</script>