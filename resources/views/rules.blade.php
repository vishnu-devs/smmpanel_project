@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Platform Rules & Ordering Guidelines - ' . App\Models\Setting::get('site_name', 'SMM Panel'))
@section('page_header', 'Platform Rules & Terms')

@section('styles')
<style>
    .rules-card {
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .flag-box {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.08));
        border: 1px solid rgba(239, 68, 68, 0.4);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin: 1.5rem 0;
        position: relative;
    }

    .flag-box h4 {
        color: #ef4444;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .rules-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rule-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: var(--radius-sm);
        font-size: 0.92rem;
        line-height: 1.5;
        color: var(--text-primary);
    }

    .rule-number {
        background: var(--grad-insta);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        min-width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .refill-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .refill-card {
        border-radius: var(--radius-md);
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        background: rgba(0, 0, 0, 0.2);
    }

    .refill-card.no-refill {
        border-color: rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.03);
    }

    .refill-card.can-refill {
        border-color: rgba(16, 185, 129, 0.3);
        background: rgba(16, 185, 129, 0.03);
    }

    .refill-card h4 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .refill-card.no-refill h4 {
        color: #ef4444;
    }

    .refill-card.can-refill h4 {
        color: #10b981;
    }

    .bullet-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .bullet-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.88rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .bullet-list li i {
        margin-top: 3px;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div style="padding: {{ Auth::check() ? '0' : '3rem 5%' }}; max-width: 950px; margin: 0 auto;">

    @if(!Auth::check())
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">Platform Rules & <span class="text-gradient">Ordering Terms</span></h2>
            <p style="color: var(--text-secondary);">Please read all terms carefully before placing orders for smooth & guaranteed fulfillment.</p>
        </div>
    @endif

    <!-- Critical Alert: Flag For Review -->
    <div class="flag-box animate-fade-in">
        <h4><i class="fa-solid fa-flag-checkered"></i> 🏴 FLAG FOR REVIEW OPTION - INSTAGRAM RULE</h4>
        <p style="margin: 0 0 10px 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">
            Before placing any Instagram Followers order, you MUST turn <strong>OFF</strong> the "Flag for Review" option in your Instagram app settings!
        </p>
        <p style="margin: 0 0 12px 0; font-size: 0.92rem; color: #f87171; font-weight: 500;">
            ⚠️ इंस्टाग्राम फॉलोअर्स ऑर्डर करने से पहले अपने इंस्टाग्राम अकाउंट को <strong>Flag for Review option OFF</strong> जरूर बनाएं।
        </p>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="https://www.youtube.com/results?search_query=how+to+turn+off+flag+for+review+instagram" target="_blank" class="btn-gradient" style="padding: 8px 16px; font-size: 0.85rem; background: linear-gradient(135deg, #ef4444, #dc2626);">
                <i class="fa-brands fa-youtube"></i> Watch How to Flag OFF Video Guide
            </a>
            <span style="font-size: 0.82rem; color: #fca5a5; font-weight: 600;">
                ❌ If you do not turn OFF the Flag option and followers get flagged, NO guarantee/refill/refund will be provided!
            </span>
        </div>
    </div>

    <!-- Main Terms & Rules Card -->
    <div class="rules-card glass">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-gavel text-gradient"></i> Terms of Ordering & Fulfillment Rules
        </h3>

        <ul class="rules-list">
            <li class="rule-item">
                <span class="rule-number">1</span>
                <div>
                    <strong>Duplicate Orders Policy:</strong> If you place a duplicate order on a link while one order is already in process, it will be at your own loss. No refunds will be made for such orders.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">2</span>
                <div>
                    <strong>Username Change:</strong> Refills are strictly NOT available after changing your username.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">3</span>
                <div>
                    <strong>Refill Order Sequence:</strong> Always take refill before placing a new order on the same link. We do not refill old drops after a new order is placed.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">4</span>
                <div>
                    <strong>Start Count & End Count:</strong> New orders and refills always finish according to Start Count and End Count. Your own organic new followers/views are also included into it.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">5</span>
                <div>
                    <strong>Wrong / Changed Link:</strong> If you change link or submit a wrong link in any running order, no refill or refund will be provided at any case.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">6</span>
                <div>
                    <strong>Refund Method:</strong> No refund will be provided in Bank Accounts (all refunds are credited back to your panel account balance).
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">7</span>
                <div>
                    <strong>Client Commitment:</strong> We always take care of our clients. Please follow the rules for best results.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">8</span>
                <div>
                    <strong>Responsible Usage:</strong> We recommend using our service responsibly and within the guidelines set by Instagram. We are not responsible for any actions taken by Instagram against your account.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">9</span>
                <div>
                    <strong>No Refill (♻️NR) & Cheapest Services:</strong> Orders placed on ♻️NR (No Refill) services and cheapest services may drop after order is completed, while being completed, or before order delivery is started. There is no refund or no refill if not mentioned in that service; these services depend on algorithm luck.
                </div>
            </li>
            <li class="rule-item">
                <span class="rule-number">10</span>
                <div>
                    <strong>Flag OFF Rules:</strong> Before Instagram Followers Order, you must turn OFF Flag for Review Option.
                </div>
            </li>
        </ul>
    </div>

    <!-- Refill Eligibility Rules Section -->
    <div class="rules-card glass">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-arrows-rotate text-gradient"></i> Refill Guarantee Eligibility Guide
        </h3>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
            Quick reference guide on which orders can or cannot be refilled:
        </p>

        <div class="refill-grid">
            <!-- Non-Refillable Conditions -->
            <div class="refill-card no-refill">
                <h4><i class="fa-solid fa-circle-xmark"></i> Which Orders CANNOT Be Refilled (Even on Guarantee Services)</h4>
                <ul class="bullet-list">
                    <li><i class="fa-solid fa-xmark text-danger"></i> If current count is less than start count or more than final count</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> If Username is changed</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> If it's not mentioned in Title or description</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> If ordered service is hidden</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> If account/channel is private</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> If Refill date/period is completed</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> On Cheapest and No refill (♻️NR) orders</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> On Wrong or broken link</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> Multiple orders on Same link simultaneously</li>
                    <li><i class="fa-solid fa-xmark text-danger"></i> On partial cancel / partial complete orders</li>
                </ul>
                <div style="margin-top: 10px; font-size: 0.8rem; color: #f87171; font-weight: 600;">
                    Note: These cannot be partial, cancelled, or refunded.
                </div>
            </div>

            <!-- Refillable Conditions -->
            <div class="refill-card can-refill">
                <h4><i class="fa-solid fa-circle-check"></i> Which Orders CAN Be Refilled</h4>
                <ul class="bullet-list">
                    <li><i class="fa-solid fa-check text-success"></i> Refill only possible if current count is more than start count and less than final count</li>
                    <li><i class="fa-solid fa-check text-success"></i> Username must NOT be changed</li>
                    <li><i class="fa-solid fa-check text-success"></i> Needs to be Public account/channel</li>
                    <li><i class="fa-solid fa-check text-success"></i> Ordered service should not be hidden (e.g. if likes is ordered then likes count must be Unhidden)</li>
                    <li><i class="fa-solid fa-check text-success"></i> You can press Refill button every 24 hours from Website</li>
                    <li><i class="fa-solid fa-check text-success"></i> Manual refilling takes approx 0-72 hours to start and process (may take longer if issues occur)</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
