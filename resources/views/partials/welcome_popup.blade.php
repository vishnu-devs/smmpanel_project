@php
    $popupStatus = App\Models\Setting::get('popup_status', 'enabled');
    $popupMode = App\Models\Setting::get('popup_mode', 'image_and_content'); // 'image_and_content', 'image_only', 'content_only'
    $popupTitle = App\Models\Setting::get('popup_title', '🔥 Special Deposit Offers & Launch Your Own SMM Panel!');
    $popupImage = App\Models\Setting::get('popup_image', '');
    $popupContent = App\Models\Setting::get('popup_content');

    // Default fallback content if empty
    if (empty($popupContent)) {
        $popupContent = '
                    <div style="display: flex; flex-direction: column; gap: 10px; text-align: left;">
                        <div style="background: rgba(220, 39, 67, 0.08); border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 8px; padding: 10px 14px;">
                            <strong style="color: var(--color-primary); display: block; margin-bottom: 2px;">🎁 VIP Deposit Cashback Active:</strong>
                            <span style="font-size: 0.88rem; color: var(--text-secondary);">Get up to <strong>5% Extra Bonus Balance</strong> automatically credited on UPI deposits above ₹100!</span>
                        </div>

                        <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 10px 14px;">
                            <strong style="color: #10b981; display: block; margin-bottom: 2px;">💼 Start Your Own SMM Panel Business:</strong>
                            <span style="font-size: 0.88rem; color: var(--text-secondary);">Want to sell SMM services under your own brand? We provide <strong>Ready-to-use SMM Panels</strong> with Domain & High-Speed Hosting or Without Domain/Hosting, with 24/7 dedicated support & wholesale root API pricing.</span>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-top: 4px;">
                            ⚡ 250+ Instant Delivery Services | 🔒 100% Safe & Secure | 💬 24/7 Live Support
                        </div>
                    </div>';
    }

    $waNum = App\Models\Setting::get('whatsapp_number', '');
    $waClean = preg_replace('/[^0-9]/', '', $waNum);
    $defaultWaLink = $waClean ? "https://wa.me/{$waClean}?text=" . urlencode("Hello, I am interested in buying my own SMM Panel / Reseller setup.") : route('resellers');

    $popupBtn1Text = App\Models\Setting::get('popup_btn1_text', '💬 Get Your Own SMM Panel');
    $popupBtn1Link = App\Models\Setting::get('popup_btn1_link', $defaultWaLink);
    $popupBtn2Text = App\Models\Setting::get('popup_btn2_text', '💰 Add Funds & Get Bonus');
    $popupBtn2Link = App\Models\Setting::get('popup_btn2_link', route('add_funds'));
    $popupFrequency = App\Models\Setting::get('popup_frequency', 'once_per_session');
@endphp

@if($popupStatus === 'enabled')
    <!-- Welcome Announcement Popup Modal -->
    <div id="welcomePromoModal" class="welcome-modal-overlay" style="display: none;">
        <div class="glass welcome-modal-content {{ $popupMode === 'image_only' ? 'modal-image-mode' : '' }}">
            <!-- Close Button (X) -->
            <button type="button" class="welcome-modal-close" onclick="closeWelcomePopup()" aria-label="Close Popup">
                <i class="fa-solid fa-xmark"></i>
            </button>

            @if($popupMode !== 'image_only')
                <!-- Top Announcement Badge -->
                <div style="text-align: center; margin-bottom: 12px;">
                    <span class="welcome-badge">
                        <i class="fa-solid fa-bullhorn animate-pulse"></i> Special Deals & Announcement
                    </span>
                </div>
            @endif

            <!-- Image Display (If Mode is image_only or image_and_content and image exists) -->
            @if(($popupMode === 'image_only' || $popupMode === 'image_and_content') && !empty($popupImage))
                <div class="welcome-modal-image-wrap {{ $popupMode === 'image_only' ? 'full-banner' : '' }}">
                    @if(!empty($popupBtn1Link))
                        <a href="{{ $popupBtn1Link }}" target="{{ str_starts_with($popupBtn1Link, 'http') ? '_blank' : '_self' }}">
                            <img src="{{ $popupImage }}" alt="Announcement Banner">
                        </a>
                    @else
                        <img src="{{ $popupImage }}" alt="Announcement Banner">
                    @endif
                </div>
            @endif

            @if($popupMode !== 'image_only')
                <!-- Title -->
                @if(!empty($popupTitle))
                    <h3 class="welcome-modal-title">
                        {{ $popupTitle }}
                    </h3>
                @endif

                <!-- Body Content -->
                <div class="welcome-modal-body">
                    {!! $popupContent !!}
                </div>
            @endif

            <!-- Action CTA Buttons -->
            @if(!empty($popupBtn1Text) || !empty($popupBtn2Text))
                <div class="welcome-modal-actions" style="{{ $popupMode === 'image_only' ? 'margin-top: 15px;' : '' }}">
                    @if(!empty($popupBtn1Text) && !empty($popupBtn1Link))
                        <a href="{{ $popupBtn1Link }}" target="{{ str_starts_with($popupBtn1Link, 'http') ? '_blank' : '_self' }}"
                            class="btn-gradient welcome-cta-btn" style="background: #25d366; color: white;">
                            <i class="fa-brands fa-whatsapp"></i> {{ $popupBtn1Text }}
                        </a>
                    @endif
                    @if(!empty($popupBtn2Text) && !empty($popupBtn2Link))
                        <a href="{{ $popupBtn2Link }}" class="btn-gradient welcome-cta-btn">
                            <i class="fa-solid fa-wallet"></i> {{ $popupBtn2Text }}
                        </a>
                    @endif
                </div>
            @endif

            <!-- Dismiss Checkbox Option -->
            <div style="text-align: center; margin-top: 14px;">
                <label
                    style="font-size: 0.78rem; color: var(--text-muted); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                    <input type="checkbox" id="dontShowPopupAgain" style="accent-color: var(--color-primary);"> Don't show
                    this announcement again today
                </label>
            </div>
        </div>
    </div>

    <style>
        .welcome-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.85);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            box-sizing: border-box;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .welcome-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .welcome-modal-content {
            max-width: 560px;
            width: 100%;
            max-height: 91vh;
            border-radius: var(--radius-lg);
            padding: 0.75rem .50rem;
            background: var(--bg-card);
            border: 1px solid rgba(220, 39, 67, 0.35);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 30px rgba(220, 39, 67, 0.2);
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-sizing: border-box;
        }

        .welcome-modal-content.modal-image-mode {
            padding: .5rem .25rem;
        }

        .welcome-modal-overlay.show .welcome-modal-content {
            transform: scale(1);
        }

        .welcome-modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 20;
        }

        .welcome-modal-close:hover {
            background: #ef4444;
            border-color: #ef4444;
            transform: rotate(90deg);
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(220, 39, 67, 0.12);
            border: 1px solid rgba(220, 39, 67, 0.35);
            border-radius: 20px;
            color: var(--color-primary);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .welcome-modal-image-wrap {
            width: 100%;
            max-height: 220px;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 1.25rem;
            border: 1px solid var(--border-color);
        }

        .welcome-modal-image-wrap.full-banner {
            max-height: 480px;
            margin-bottom: 0;
        }

        .welcome-modal-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .welcome-modal-title {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 1rem;
            text-align: center;
            color: var(--text-primary);
        }

        .welcome-modal-body {
            font-size: 0.92rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .welcome-modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .welcome-cta-btn {
            flex: 1;
            min-width: 180px;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: var(--radius-md);
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-sizing: border-box;
        }

        @media (max-width: 576px) {
            .welcome-modal-content {
                padding: 1.75rem 1.25rem;
            }

            .welcome-modal-title {
                font-size: 1.15rem;
            }

            .welcome-modal-actions {
                flex-direction: column;
            }

            .welcome-cta-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        (function initWelcomePopupScript() {
            const modal = document.getElementById('welcomePromoModal');
            if (!modal) return;

            const frequency = "{{ $popupFrequency }}"; // 'always', 'once_per_session', 'once_per_day'
            const storageKey = 'rishismm_welcome_popup_seen';
            const dayStorageKey = 'rishismm_welcome_popup_dismissed_day';

            function shouldShowPopup() {
                // Check if user dismissed today
                const dismissedDate = localStorage.getItem(dayStorageKey);
                const today = new Date().toISOString().slice(0, 10);
                if (dismissedDate === today) {
                    return false;
                }

                if (frequency === 'always') {
                    return true;
                } else if (frequency === 'once_per_session') {
                    return !sessionStorage.getItem(storageKey);
                } else if (frequency === 'once_per_day') {
                    return localStorage.getItem(storageKey) !== today;
                }
                return true;
            }

            function showPopup() {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.classList.add('show');
                }, 50);

                const today = new Date().toISOString().slice(0, 10);
                sessionStorage.setItem(storageKey, '1');
                localStorage.setItem(storageKey, today);
            }

            window.closeWelcomePopup = function () {
                const checkbox = document.getElementById('dontShowPopupAgain');
                if (checkbox && checkbox.checked) {
                    const today = new Date().toISOString().slice(0, 10);
                    localStorage.setItem(dayStorageKey, today);
                }

                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            };

            // Close on clicking outside modal
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    window.closeWelcomePopup();
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    window.closeWelcomePopup();
                }
            });

            // Trigger popup with slight delay for smooth page load
            if (shouldShowPopup()) {
                setTimeout(showPopup, 800);
            }
        })();
    </script>
@endif