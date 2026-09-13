<!-- PWA Manager & 1-Click Install System (Android & iOS / Apple) -->
<div id="pwaInstallBanner" class="pwa-floating-banner" style="display: none;">
    <div class="pwa-banner-content">
        <div class="pwa-banner-icon">
            <img src="{{ App\Models\Setting::getLogoUrl() }}" onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';" alt="App Icon">
        </div>
        <div class="pwa-banner-text">
            <strong>Install {{ App\Models\Setting::getSiteName() }} App</strong>
            <p id="pwaBannerSubtitle">Faster loading, full-screen mode & 1-tap access</p>
        </div>
    </div>
    <div class="pwa-banner-actions">
        <button type="button" id="pwaMainInstallBtn" class="btn-gradient pwa-install-btn" onclick="triggerPWAInstall()">
            <i id="pwaBtnIcon" class="fa-brands fa-android"></i> <span id="pwaBtnLabel">Install</span>
        </button>
        <button type="button" class="pwa-close-btn" onclick="dismissPWABanner()" aria-label="Dismiss">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<!-- Universal / Android Step-by-Step Installation Modal -->
<div id="androidInstallModal" class="pwa-modal-overlay" style="display: none;">
    <div class="pwa-modal-content">
        <button type="button" class="pwa-modal-close" onclick="closeAndroidModal()" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="text-align: center; margin-bottom: 1.25rem;">
            <div style="width: 68px; height: 68px; margin: 0 auto 12px auto; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px rgba(220, 39, 67, 0.45); border: 2px solid rgba(220, 39, 67, 0.6); background: #1e1b4b; display: flex; align-items: center; justify-content: center;">
                <img src="{{ App\Models\Setting::getLogoUrl() }}" onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';" alt="App Icon" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #ffffff !important; margin-bottom: 6px;">
                Install <span class="text-gradient">{{ App\Models\Setting::getSiteName() }}</span> App
            </h3>
            <p style="font-size: 0.85rem; color: #cbd5e1 !important; margin: 0; line-height: 1.5;">
                Get instant 1-tap order placement & faster loading on your Android phone.
            </p>
        </div>

        <div class="pwa-steps-list">
            <div class="pwa-step-item">
                <div class="pwa-step-num"><i class="fa-solid fa-bolt"></i></div>
                <div class="pwa-step-text">
                    <strong>1-Tap Instant Access</strong>
                    <span>Faster order placement & live notifications.</span>
                </div>
            </div>
            <div class="pwa-step-item">
                <div class="pwa-step-num"><i class="fa-solid fa-mobile-screen"></i></div>
                <div class="pwa-step-text">
                    <strong>Full-Screen App Mode</strong>
                    <span>Runs standalone with no browser address bar.</span>
                </div>
            </div>
        </div>

        <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 10px;">
            <button type="button" id="modalDirectInstallBtn" class="btn-gradient" onclick="executeNativeInstallPrompt()" style="width: 100%; padding: 13px; border-radius: var(--radius-md); font-weight: 800; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 8px 20px rgba(220, 39, 67, 0.4); border: none; cursor: pointer; color: #ffffff !important;">
                <i class="fa-solid fa-bolt"></i> 1-Click Instant Install
            </button>
            <a href="{{ route('app.download') }}" style="width: 100%; padding: 13px; border-radius: var(--radius-md); font-weight: 700; font-size: 0.92rem; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-sizing: border-box; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff !important; transition: all 0.2s ease;">
                <i class="fa-solid fa-download" style="color: #38bdf8; font-size: 1rem;"></i> Download APK File Directly
            </a>
        </div>
    </div>
</div>

<!-- iOS / Apple Safari Step-by-Step Installation Modal -->
<div id="iosInstallModal" class="pwa-modal-overlay" style="display: none;">
    <div class="pwa-modal-content">
        <button type="button" class="pwa-modal-close" onclick="closeIosModal()" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="text-align: center; margin-bottom: 1.25rem;">
            <div style="width: 68px; height: 68px; margin: 0 auto 12px auto; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.25); background: #1e1b4b; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('images/icons/icon-192x192.png') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';" alt="App Icon" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #ffffff !important; margin-bottom: 6px;">
                Install on <span class="text-gradient">iPhone / iPad</span>
            </h3>
            <p style="font-size: 0.85rem; color: #cbd5e1 !important; margin: 0; line-height: 1.5;">
                Add {{ App\Models\Setting::get('site_name', 'RishiSMM') }} to your Home Screen in 3 simple steps:
            </p>
        </div>

        <div class="pwa-steps-list">
            <div class="pwa-step-item">
                <div class="pwa-step-num">1</div>
                <div class="pwa-step-text">
                    Tap the <strong>Share</strong> button <span class="pwa-icon-badge"><i class="fa-solid fa-arrow-up-from-bracket"></i></span> at the bottom of your Safari browser bar.
                </div>
            </div>
            <div class="pwa-step-item">
                <div class="pwa-step-num">2</div>
                <div class="pwa-step-text">
                    Scroll down and tap <strong>"Add to Home Screen"</strong> <span class="pwa-icon-badge"><i class="fa-regular fa-square-plus"></i></span>.
                </div>
            </div>
            <div class="pwa-step-item">
                <div class="pwa-step-num">3</div>
                <div class="pwa-step-text">
                    Tap <strong>"Add"</strong> in the top right corner. The App icon will appear on your iPhone!
                </div>
            </div>
        </div>

        <button type="button" class="btn-gradient" onclick="closeIosModal()" style="width: 100%; padding: 13px; border-radius: var(--radius-md); font-weight: 700; font-size: 0.95rem; margin-top: 1.25rem; border: none; cursor: pointer; color: #ffffff !important;">
            Got It!
        </button>
    </div>
</div>

<style>
    .pwa-floating-banner {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(120px);
        width: calc(100% - 30px);
        max-width: 480px;
        background: rgba(15, 23, 42, 0.98);
        border: 1px solid rgba(220, 39, 67, 0.45);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(220, 39, 67, 0.25);
        border-radius: var(--radius-lg);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        z-index: 99999;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .pwa-floating-banner.show {
        transform: translateX(-50%) translateY(0);
    }
    .pwa-banner-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }
    .pwa-banner-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
        background: #1e1b4b;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .pwa-banner-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pwa-banner-text {
        min-width: 0;
    }
    .pwa-banner-text strong {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: 0.2px;
    }
    .pwa-banner-text p {
        margin: 2px 0 0 0;
        font-size: 0.8rem;
        color: #e2e8f0 !important;
        opacity: 0.9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pwa-banner-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .pwa-install-btn {
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 800;
        border-radius: 20px;
        cursor: pointer;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(220, 39, 67, 0.4);
    }
    .pwa-close-btn {
        background: transparent;
        border: none;
        color: #cbd5e1 !important;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 6px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .pwa-close-btn:hover {
        color: #ef4444 !important;
        transform: rotate(90deg);
    }

    /* Universal Modal Styles */
    .pwa-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(10, 15, 30, 0.92);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        box-sizing: border-box;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .pwa-modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .pwa-modal-content {
        max-width: 420px;
        width: 100%;
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%) !important;
        color: #ffffff !important;
        border: 1px solid rgba(220, 39, 67, 0.45) !important;
        border-radius: 24px;
        padding: 2rem 1.5rem 1.5rem 1.5rem;
        position: relative;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 35px rgba(220, 39, 67, 0.2);
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-sizing: border-box;
    }
    .pwa-modal-overlay.show .pwa-modal-content {
        transform: scale(1);
    }
    .pwa-modal-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pwa-modal-close:hover {
        background: #ef4444;
        color: #ffffff !important;
    }
    .pwa-steps-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .pwa-step-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 12px 14px;
        border-radius: 12px;
    }
    .pwa-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--grad-insta);
        color: white;
        font-size: 0.82rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .pwa-step-text {
        font-size: 0.85rem;
        color: #e2e8f0 !important;
        line-height: 1.45;
        display: flex;
        flex-direction: column;
        gap: 2px;
        text-align: left;
    }
    .pwa-step-text strong {
        color: #ffffff !important;
        font-size: 0.9rem;
    }
    .pwa-step-text span {
        color: #cbd5e1 !important;
        font-size: 0.8rem;
    }
    .pwa-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 7px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 6px;
        color: #38bdf8;
        font-size: 0.82rem;
        margin: 0 2px;
    }

    /* Standalone APK / PWA Mode - Hide All App Install UI inside the App */
    @media (display-mode: standalone), (display-mode: fullscreen) {
        .app-install-only-web,
        .app-showcase-section,
        .pwa-floating-banner,
        #pwaInstallBanner,
        #androidInstallModal,
        #iosInstallModal,
        .sidebar-install-app,
        .landing-install-app {
            display: none !important;
        }
    }
    .in-standalone-app .app-install-only-web,
    .in-standalone-app .app-showcase-section,
    .in-standalone-app .pwa-floating-banner,
    .in-standalone-app #pwaInstallBanner,
    .in-standalone-app #androidInstallModal,
    .in-standalone-app #iosInstallModal,
    .in-standalone-app .sidebar-install-app,
    .in-standalone-app .landing-install-app {
        display: none !important;
    }
</style>

<script>
    (function initPWASystem() {
        window.deferredPWAEvent = null;
        const banner = document.getElementById('pwaInstallBanner');
        const iosModal = document.getElementById('iosInstallModal');
        const androidModal = document.getElementById('androidInstallModal');
        const dismissKey = 'rishismm_pwa_dismissed_time';

        // Device & Standalone APK Detection
        const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isInStandaloneMode = window.matchMedia('(display-mode: standalone)').matches ||
            window.matchMedia('(display-mode: fullscreen)').matches ||
            ('standalone' in window.navigator && window.navigator.standalone) ||
            document.referrer.includes('android-app://') ||
            window.location.search.includes('source=app') ||
            window.location.search.includes('source=pwa') ||
            navigator.userAgent.includes('RishiSMM') ||
            navigator.userAgent.includes('wv');

        if (isInStandaloneMode) {
            document.documentElement.classList.add('in-standalone-app');
            return; // In standalone APK, do not initialize install prompt listeners or banners
        }

        // Update UI for iOS if detected
        if (isIos && !isInStandaloneMode) {
            const btnIcon = document.getElementById('pwaBtnIcon');
            const btnLabel = document.getElementById('pwaBtnLabel');
            const subtitle = document.getElementById('pwaBannerSubtitle');
            if (btnIcon) btnIcon.className = 'fa-brands fa-apple';
            if (btnLabel) btnLabel.textContent = 'Add to iPhone';
            if (subtitle) subtitle.textContent = 'Tap to add icon to iPhone Home Screen';
        }

        // 1. Register Service Worker immediately
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function(reg) {
                // Service worker registered
            }).catch(function(err) {
                console.log('SW Registration error:', err);
            });
        }

        // Check if banner should be displayed
        function shouldShowBanner() {
            if (isInStandaloneMode) return false;
            const lastDismissed = localStorage.getItem(dismissKey);
            const now = Date.now();
            if (lastDismissed && (now - parseInt(lastDismissed, 10)) < 86400000) {
                return false;
            }
            return true;
        }

        // 2. Capture Android install prompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPWAEvent = e;

            if (shouldShowBanner()) {
                setTimeout(() => {
                    if (banner) {
                        banner.style.display = 'flex';
                        setTimeout(() => banner.classList.add('show'), 50);
                    }
                }, 1500);
            }
        });

        // If on iOS and not installed, show banner after 2s
        if (isIos && !isInStandaloneMode && shouldShowBanner()) {
            setTimeout(() => {
                if (banner) {
                    banner.style.display = 'flex';
                    setTimeout(() => banner.classList.add('show'), 50);
                }
            }, 2000);
        }

        // 3. Direct Native Install Trigger
        window.executeNativeInstallPrompt = function() {
            if (window.deferredPWAEvent) {
                window.deferredPWAEvent.prompt();
                window.deferredPWAEvent.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User installed PWA App');
                    }
                    window.deferredPWAEvent = null;
                    if (androidModal) closeAndroidModal();
                    if (banner) {
                        banner.classList.remove('show');
                        setTimeout(() => banner.style.display = 'none', 400);
                    }
                });
            } else {
                // Directly trigger APK download without any 3-dots messages
                window.location.href = "{{ route('app.download') }}";
            }
        };

        // 4. Global install trigger function
        window.triggerPWAInstall = function() {
            if (isIos) {
                if (iosModal) {
                    iosModal.style.display = 'flex';
                    setTimeout(() => iosModal.classList.add('show'), 50);
                }
                return;
            }

            if (window.deferredPWAEvent) {
                window.executeNativeInstallPrompt();
            } else {
                if (androidModal) {
                    androidModal.style.display = 'flex';
                    setTimeout(() => androidModal.classList.add('show'), 50);
                } else {
                    window.location.href = "{{ route('app.download') }}";
                }
            }
        };

        window.closeAndroidModal = function() {
            if (androidModal) {
                androidModal.classList.remove('show');
                setTimeout(() => androidModal.style.display = 'none', 300);
            }
        };

        window.closeIosModal = function() {
            if (iosModal) {
                iosModal.classList.remove('show');
                setTimeout(() => iosModal.style.display = 'none', 300);
            }
        };

        window.dismissPWABanner = function() {
            if (banner) {
                banner.classList.remove('show');
                setTimeout(() => banner.style.display = 'none', 400);
            }
            localStorage.setItem(dismissKey, Date.now().toString());
        };

        window.addEventListener('appinstalled', () => {
            if (banner) banner.style.display = 'none';
            if (androidModal) closeAndroidModal();
            window.deferredPWAEvent = null;
        });
    })();
</script>
