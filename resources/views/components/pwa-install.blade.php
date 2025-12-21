{{-- PWA Install Prompt Component --}}
<div id="pwa-install-prompt" class="pwa-install-prompt" style="display: none;">
    <div class="pwa-install-content">
        <div class="pwa-install-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" x2="12" y1="15" y2="3"/>
            </svg>
        </div>
        <div class="pwa-install-text">
            <h4>Install SKYpesa</h4>
            <p>Pakua app kwenye simu yako kwa urahisi zaidi</p>
        </div>
        <div class="pwa-install-actions">
            <button id="pwa-install-btn" class="pwa-btn-install">Install</button>
            <button id="pwa-dismiss-btn" class="pwa-btn-dismiss">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" x2="6" y1="6" y2="18"/>
                    <line x1="6" x2="18" y1="6" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- iOS Install Instructions Modal --}}
<div id="ios-install-modal" class="ios-install-modal" style="display: none;">
    <div class="ios-install-backdrop"></div>
    <div class="ios-install-sheet">
        <div class="ios-install-header">
            <h3>Install SKYpesa</h3>
            <button class="ios-close-btn" onclick="closeIOSModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" x2="6" y1="6" y2="18"/>
                    <line x1="6" x2="18" y1="6" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="ios-install-body">
            <p>Ili kupakua SKYpesa kwenye iPhone yako:</p>
            <div class="ios-step">
                <div class="ios-step-number">1</div>
                <div class="ios-step-content">
                    <span>Bonyeza</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" x2="12" y1="2" y2="15"/>
                    </svg>
                    <span>hapo chini</span>
                </div>
            </div>
            <div class="ios-step">
                <div class="ios-step-number">2</div>
                <div class="ios-step-content">
                    <span>Tafuta "Add to Home Screen"</span>
                </div>
            </div>
            <div class="ios-step">
                <div class="ios-step-number">3</div>
                <div class="ios-step-content">
                    <span>Bonyeza "Add" juu kulia</span>
                </div>
            </div>
        </div>
        <button class="ios-got-it-btn" onclick="closeIOSModal()">Nimeelewa!</button>
    </div>
</div>

<style>
/* PWA Install Prompt Styles */
.pwa-install-prompt {
    position: fixed;
    bottom: 80px;
    left: 1rem;
    right: 1rem;
    z-index: 9999;
    animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.pwa-install-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #1a1a22 0%, #111116 100%);
    border: 1px solid rgba(240, 180, 41, 0.2);
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.pwa-install-icon {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f0b429, #d4a025);
    border-radius: 12px;
    color: #000;
}

.pwa-install-text {
    flex: 1;
    min-width: 0;
}

.pwa-install-text h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.pwa-install-text p {
    font-size: 0.8rem;
    color: #b0b0b8;
    margin: 0.25rem 0 0;
}

.pwa-install-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.pwa-btn-install {
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    background: linear-gradient(135deg, #f0b429, #d4a025);
    color: #000;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pwa-btn-install:hover {
    transform: scale(1.05);
}

.pwa-btn-dismiss {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    color: #6b6b75;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pwa-btn-dismiss:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}

/* iOS Install Modal */
.ios-install-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
}

.ios-install-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.ios-install-sheet {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1a1a22;
    border-radius: 24px 24px 0 0;
    padding: 1.5rem;
    animation: sheetUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes sheetUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.ios-install-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.ios-install-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.ios-close-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
}

.ios-install-body {
    margin-bottom: 1.5rem;
}

.ios-install-body > p {
    font-size: 0.9rem;
    color: #b0b0b8;
    margin: 0 0 1.25rem;
}

.ios-step {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    margin-bottom: 0.75rem;
}

.ios-step-number {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f0b429, #d4a025);
    color: #000;
    font-weight: 700;
    font-size: 0.875rem;
    border-radius: 50%;
    flex-shrink: 0;
}

.ios-step-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #fff;
}

.ios-step-content svg {
    color: #f0b429;
}

.ios-got-it-btn {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 600;
    background: linear-gradient(135deg, #f0b429, #d4a025);
    color: #000;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.ios-got-it-btn:hover {
    transform: scale(1.02);
}

/* Desktop adjustments */
@media (min-width: 768px) {
    .pwa-install-prompt {
        left: auto;
        right: 1.5rem;
        bottom: 1.5rem;
        max-width: 400px;
    }
}
</style>

<script>
// PWA Installation Handler
let deferredPrompt = null;
const pwaPrompt = document.getElementById('pwa-install-prompt');
const pwaInstallBtn = document.getElementById('pwa-install-btn');
const pwaDismissBtn = document.getElementById('pwa-dismiss-btn');
const iosModal = document.getElementById('ios-install-modal');

// Check if already installed
function isInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true ||
           localStorage.getItem('pwa-installed') === 'true';
}

// Check if iOS
function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
}

// Show install prompt
function showInstallPrompt() {
    if (isInstalled()) return;
    if (localStorage.getItem('pwa-dismissed')) return;
    
    // Wait a bit before showing
    setTimeout(() => {
        if (pwaPrompt) {
            pwaPrompt.style.display = 'block';
        }
    }, 3000);
}

// Close iOS modal
function closeIOSModal() {
    if (iosModal) {
        iosModal.style.display = 'none';
    }
}

// Listen for install prompt
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallPrompt();
});

// Handle install click
if (pwaInstallBtn) {
    pwaInstallBtn.addEventListener('click', async () => {
        if (isIOS()) {
            // Show iOS instructions
            if (iosModal) {
                iosModal.style.display = 'block';
            }
            pwaPrompt.style.display = 'none';
            return;
        }
        
        if (!deferredPrompt) {
            console.log('No install prompt available');
            return;
        }
        
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            localStorage.setItem('pwa-installed', 'true');
            pwaPrompt.style.display = 'none';
        }
        
        deferredPrompt = null;
    });
}

// Handle dismiss
if (pwaDismissBtn) {
    pwaDismissBtn.addEventListener('click', () => {
        pwaPrompt.style.display = 'none';
        // Don't show again for 7 days
        localStorage.setItem('pwa-dismissed', Date.now().toString());
    });
}

// iOS specific handling
if (isIOS() && !isInstalled()) {
    // Show prompt after delay
    setTimeout(() => {
        if (!localStorage.getItem('pwa-dismissed')) {
            pwaPrompt.style.display = 'block';
        }
    }, 5000);
}

// Track when app was installed
window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    localStorage.setItem('pwa-installed', 'true');
    pwaPrompt.style.display = 'none';
    deferredPrompt = null;
});

// Check dismissed expiry (7 days)
const dismissedTime = localStorage.getItem('pwa-dismissed');
if (dismissedTime) {
    const daysSinceDismissed = (Date.now() - parseInt(dismissedTime)) / (1000 * 60 * 60 * 24);
    if (daysSinceDismissed > 7) {
        localStorage.removeItem('pwa-dismissed');
    }
}
</script>
