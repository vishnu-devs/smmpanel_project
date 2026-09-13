@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Frequently Asked Questions - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Frequently Asked Questions')

@section('styles')
<style>
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 4rem;
    }
    .faq-hero-card {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2.5rem 1.5rem;
        border-radius: var(--radius-lg);
        background: radial-gradient(circle at 50% 20%, rgba(220, 39, 67, 0.15) 0%, transparent 70%),
                    var(--bg-card);
        border: 1px solid var(--border-color);
    }
    .faq-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(220, 39, 67, 0.1);
        border: 1px solid rgba(220, 39, 67, 0.3);
        border-radius: 30px;
        color: var(--color-primary);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .faq-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .faq-card-item {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .faq-card-item:hover {
        border-color: rgba(255, 255, 255, 0.15);
    }

    .faq-card-item.active {
        border-color: rgba(220, 39, 67, 0.4);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .faq-header-btn {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        user-select: none;
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
        gap: 15px;
    }

    .faq-icon-rotator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.04);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        color: var(--color-primary);
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .faq-card-item.active .faq-icon-rotator {
        transform: rotate(180deg);
        background: var(--grad-insta);
        color: white;
    }

    .faq-content-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0 1.5rem;
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.7;
        background: rgba(0, 0, 0, 0.1);
    }

    .faq-card-item.active .faq-content-body {
        padding: 1.25rem 1.5rem 1.5rem 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .faq-support-box {
        margin-top: 3.5rem;
        padding: 2.5rem;
        border-radius: var(--radius-lg);
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="faq-container animate-fade-in">

    <!-- Hero Header -->
    <div class="glass faq-hero-card">
        <div class="faq-badge">
            <i class="fa-solid fa-circle-question"></i> Help & Knowledge Base
        </div>
        <h1 style="font-size: 2.3rem; font-weight: 800; margin-bottom: 10px;">
            Frequently Asked <span class="text-gradient">Questions</span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 1rem; max-width: 650px; margin: 0 auto;">
            Find quick answers to common questions regarding ordering, wallet deposits, delivery speeds, and the Reseller API.
        </p>
    </div>

    <!-- FAQ Accordion List -->
    <div class="faq-list">

        <!-- Q1 -->
        <div class="glass faq-card-item active">
            <button type="button" class="faq-header-btn">
                <span>What is an SMM Panel and how does it work?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body" style="max-height: 300px;">
                An SMM (Social Media Marketing) Panel is an automated platform where individuals, creators, and digital marketing agencies buy social media engagement—such as Instagram followers, reels likes, YouTube watch time, Telegram channel members, and Twitter retweets—to boost social proof and brand credibility instantly.
            </div>
        </div>

        <!-- Q2 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>How do I deposit funds using UPI QR code?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                Go to the <strong>Add Funds</strong> page, scan our dynamic QR code with Google Pay, PhonePe, Paytm, or BHIM, make the payment, and copy the <strong>12-digit numeric UTR / Ref number</strong> from your payment receipt. Paste the UTR in the form and click Submit. Your funds will be credited to your wallet balance.
            </div>
        </div>

        <!-- Q3 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>Are these services safe? Will my social media account get banned?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                <strong>100% Safe.</strong> We never ask for your account password or private login credentials. All deliveries follow organic delivery limits and algorithmic pacing so that your account remains completely secure.
            </div>
        </div>

        <!-- Q4 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>How fast will my order start after placing it?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                Most services with an <em>"Instant Start"</em> badge begin processing within 0 to 5 minutes. You can monitor the live progress (Pending, Processing, In Progress, Completed) anytime on your <strong>My Orders</strong> history page.
            </div>
        </div>

        <!-- Q5 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>What is a "Refill" and how do I use it?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                If a service has a 30-Day, 60-Day, or Lifetime Refill guarantee and you notice a drop in count, a <strong>Refill</strong> button will appear in your Orders History. Clicking it triggers our automated system to send free replacement quantity to restore your count.
            </div>
        </div>

        <!-- Q6 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>What do "Partial" and "Canceled" order statuses mean?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <li><strong>Canceled:</strong> The order could not be fulfilled (e.g., private profile or wrong link). Your wallet balance is 100% refunded immediately.</li>
                    <li><strong>Partial:</strong> The service delivered a portion of the order (e.g. 800 out of 1000). The cost of the remaining 200 undelivered units is calculated and refunded back to your wallet instantly.</li>
                </ul>
            </div>
        </div>

        <!-- Q7 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>Can I connect my Child Panel or SMM website using API?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                Yes! We offer a full standard REST API v2 compatible with PerfectPanel, SmartPanel, RentASMM, and custom code. You can find your private API key in your Profile and view full endpoint parameters on the <strong>Developer API</strong> documentation page.
            </div>
        </div>

        <!-- Q8 -->
        <div class="glass faq-card-item">
            <button type="button" class="faq-header-btn">
                <span>What is the Instagram "Flag for Review" rule?</span>
                <div class="faq-icon-rotator"><i class="fa-solid fa-chevron-down"></i></div>
            </button>
            <div class="faq-content-body">
                Instagram has an in-app setting called "Flag for review" in <em>Settings > Follow and invite friends</em>. You must turn this option <strong>OFF</strong> before ordering followers; otherwise Instagram will hold new followers in pending review without adding them to your public follower count.
            </div>
        </div>

    </div>

    <!-- Still Need Help Box -->
    <div class="glass faq-support-box">
        <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 8px;">Still have questions?</h3>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.75rem;">
            Our support team is available 24/7 to assist you with order inquiries, payment verification, and custom API integrations.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            @auth
                <a href="{{ route('tickets.index') }}" class="btn-gradient" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-ticket" style="margin-right: 8px;"></i> Open Support Ticket
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-gradient" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-right-to-bracket" style="margin-right: 8px;"></i> Sign In for Support
                </a>
            @endauth
            @php
                $waNum = App\Models\Setting::get('whatsapp_number');
                $waClean = preg_replace('/[^0-9]/', '', $waNum);
            @endphp
            @if($waClean)
                <a href="https://wa.me/{{ $waClean }}" target="_blank" class="btn-outline" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md); background: rgba(37, 211, 102, 0.1); border-color: #25d366; color: #25d366;">
                    <i class="fa-brands fa-whatsapp" style="margin-right: 8px;"></i> WhatsApp Live Chat
                </a>
            @endif
        </div>
    </div>

</div>

<!-- Self-executing robust script for FAQ Accordion -->
<script>
    (function initFaqAccordion() {
        function setupFaq() {
            const faqItems = document.querySelectorAll('.faq-card-item');
            faqItems.forEach(item => {
                const btn = item.querySelector('.faq-header-btn');
                const body = item.querySelector('.faq-content-body');

                if (btn && body) {
                    btn.onclick = function(e) {
                        e.preventDefault();
                        const isActive = item.classList.contains('active');

                        // Close other items smoothly
                        faqItems.forEach(otherItem => {
                            if (otherItem !== item) {
                                otherItem.classList.remove('active');
                                const otherBody = otherItem.querySelector('.faq-content-body');
                                if (otherBody) otherBody.style.maxHeight = null;
                            }
                        });

                        // Toggle current item
                        if (isActive) {
                            item.classList.remove('active');
                            body.style.maxHeight = null;
                        } else {
                            item.classList.add('active');
                            body.style.maxHeight = (body.scrollHeight + 40) + 'px';
                        }
                    };
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFaq);
        } else {
            setupFaq();
        }
    })();
</script>
@endsection
