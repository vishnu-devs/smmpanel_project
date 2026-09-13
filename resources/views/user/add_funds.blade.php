@extends('layouts.app')

@section('title', 'Add Funds - Growinsta')
@section('page_header', 'Add Funds')

@section('content')
    <div style="max-width: 700px; margin: 0 auto;">

        <!-- Option 1: QR & Manual Deposit -->
        <div class="glass custom-card">
            <h3 class="card-title"><i class="fa-solid fa-qrcode text-gradient"></i>
                {{ !empty($bankDetails) ? 'UPI Scan / Bank Transfer' : 'UPI Scan Payment' }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Scan QR or pay directly to details below, then submit your transaction reference number.
            </p>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 2rem; align-items: start;">
                <!-- QR Code representation -->
                @if($upiQr)
                    <div style="background: white; padding: 10px; border-radius: var(--radius-md); max-width: 180px;">
                        <img src="{{ $upiQr }}" alt="UPI QR Code" style="width: 100%; display: block;">
                    </div>
                @else
                    <!-- Elegant CSS placeholder QR scan card if setting is missing -->
                    <div class="glass"
                        style="width: 160px; height: 160px; border-radius: var(--radius-md); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border: 2px dashed var(--text-secondary); background: rgba(255,255,255,0.01);">
                        <i class="fa-solid fa-qrcode"
                            style="font-size: 3rem; margin-bottom: 8px; color: var(--color-primary); opacity: 0.8;"></i>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 500;">Pay to UPI ID</span>
                    </div>
                @endif

                <div style="flex-grow: 1; min-width: 250px;">
                    @if(!empty($upiId))
                        <div style="margin-bottom: 12px;">
                            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">UPI
                                ID:</span>
                            <strong style="display: block; font-size: 1.1rem; color: var(--text-primary);">
                                {{ $upiId }}
                            </strong>
                        </div>
                    @endif
                    @if(!empty($bankDetails))
                        <div>
                            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Bank
                                Account Details:</span>
                            <p
                                style="font-size: 0.9rem; color: var(--text-secondary); white-space: pre-line; line-height: 1.4;">
                                {{ $bankDetails }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Step Instructions & Warning Box -->
            <div
                style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: var(--grad-insta); color: white; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">1</span>
                        <strong style="font-size: 0.9rem; color: var(--text-primary);">STEP 1 : SCAN ANY BARCODE</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: var(--grad-insta); color: white; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">2</span>
                        <strong style="font-size: 0.9rem; color: var(--text-primary);">STEP 2 : PAY AMOUNT (MINIMUM
                            ₹1)</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: var(--grad-insta); color: white; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">3</span>
                        <strong style="font-size: 0.9rem; color: var(--text-primary);">STEP 3 : PUT UTR ID & AMOUNT</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: var(--grad-insta); color: white; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">4</span>
                        <strong style="font-size: 0.9rem; color: var(--text-primary);">STEP 4 : CLICK ON PAY BUTTON</strong>
                    </div>
                </div>

                <!-- Indian Fraud Warning Box -->
                <div
                    style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: var(--radius-sm); padding: 15px; font-size: 0.82rem; line-height: 1.5; color: #fecaca;">
                    <strong style="display: block; margin-bottom: 8px; color: #ef4444; font-size: 0.88rem;">
                        ⚠️ *महत्वपूर्ण सूचना सभी भारतीय 🇮🇳 के लिए
                    </strong>
                    <ul style="margin: 0; padding-left: 15px; display: flex; flex-direction: column; gap: 6px;">
                        <li>किसी भी तरह के Fraud, Scam, Money Doubler, Investment, BGMI, Amazon, Carder, चैनल्स के Order की
                            हम अनुमति नहीं देते।</li>
                        <li>ऐसे Scam, Fraud से जुड़े चैनल देखने पर हम उन अकाउंट को Block करेंगे और उनके IP Address पर Cyber
                            🚨 Police को सूचित करेंगे।</li>
                    </ul>
                </div>
            </div>

            <!-- Submit UTR Form -->
            <form action="{{ route('add_funds.manual') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="manual_amount" class="form-label">Deposited Amount (INR)</label>
                    <input type="number" name="amount" id="manual_amount" class="form-control"
                        placeholder="Enter amount paid (Minimum ₹1)" min="1" step="any" required>
                    <div id="bonusPreviewBox" style="display: none; margin-top: 8px; font-size: 0.82rem; background: rgba(2, 132, 199, 0.1); border: 1px solid rgba(2, 132, 199, 0.25); padding: 8px 12px; border-radius: var(--radius-sm); color: #0284c7; font-weight: 500;">
                        <i class="fa-solid fa-gift"></i> <span id="bonusText"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment_id" class="form-label">Transaction Ref ID / UTR Number <span class="text-danger">*</span></label>
                    <input type="text" name="payment_id" id="payment_id" class="form-control"
                        placeholder="Enter 12-digit UTR (e.g. 123456789012)"
                        maxlength="12"
                        inputmode="numeric"
                        pattern="\d{12}"
                        autocomplete="off"
                        required>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-secondary); margin-top: 5px;">
                        <span>Only 12 numeric digits allowed (no letters/emojis).</span>
                        <span id="utrCharCounter" style="font-weight: 600;">0/12 digits</span>
                    </div>
                </div>
                <!-- <div class="form-group"> 
                        <label for="notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" placeholder="Sender name or transfer remarks..." rows="2"></textarea>
                    </div>-->
                <button type="submit" class="btn-gradient"
                    style="width: 100%; padding: 14px; font-weight: 700; font-size: 1.05rem; color: #ffffff !important;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Submit Verification Request
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const amountInput = document.getElementById('manual_amount');
            const bonusBox = document.getElementById('bonusPreviewBox');
            const bonusText = document.getElementById('bonusText');

            const t1Min = {{ \App\Models\Setting::get('bonus_t1_min', 100) }};
            const t1Pct = {{ \App\Models\Setting::get('bonus_t1_percent', 1) }};
            const t2Min = {{ \App\Models\Setting::get('bonus_t2_min', 1000) }};
            const t2Pct = {{ \App\Models\Setting::get('bonus_t2_percent', 2) }};
            const t3Min = {{ \App\Models\Setting::get('bonus_t3_min', 5000) }};
            const t3Pct = {{ \App\Models\Setting::get('bonus_t3_percent', 5) }};
            const status = "{{ \App\Models\Setting::get('deposit_bonus_status', 'enabled') }}";

            if (amountInput && bonusBox && bonusText && status === 'enabled') {
                amountInput.addEventListener('input', function () {
                    const amt = parseFloat(this.value) || 0;
                    let pct = 0;
                    if (t3Min > 0 && amt >= t3Min && t3Pct > 0) pct = t3Pct;
                    else if (t2Min > 0 && amt >= t2Min && t2Pct > 0) pct = t2Pct;
                    else if (t1Min > 0 && amt >= t1Min && t1Pct > 0) pct = t1Pct;

                    if (pct > 0) {
                        const bonusVal = (amt * pct) / 100;
                        const totalVal = amt + bonusVal;
                        bonusText.innerHTML = `<strong>Bonus Applicable (${pct}%):</strong> +₹${bonusVal.toFixed(2)} bonus! Total Wallet Credit: <strong>₹${totalVal.toFixed(2)}</strong>`;
                        bonusBox.style.display = 'block';
                    } else {
                        bonusBox.style.display = 'none';
                    }
                });
            }

            // Strict 12-digit UTR Input Restriction (No special characters, letters, or emojis)
            const utrInput = document.getElementById('payment_id');
            const utrCounter = document.getElementById('utrCharCounter');
            if (utrInput) {
                function cleanUtr() {
                    utrInput.value = utrInput.value.replace(/\D/g, '').slice(0, 12);
                    if (utrCounter) {
                        const len = utrInput.value.length;
                        utrCounter.innerText = len + '/12 digits';
                        if (len === 12) {
                            utrCounter.style.color = '#10b981';
                        } else {
                            utrCounter.style.color = 'var(--text-secondary)';
                        }
                    }
                }

                utrInput.addEventListener('input', cleanUtr);
                utrInput.addEventListener('paste', function () {
                    setTimeout(cleanUtr, 0);
                });
            }
        });
    </script>

    <!-- Deposit Transaction History List -->
    <div class="glass custom-card" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-money-bill-transfer text-gradient"></i> Deposit Transaction History</h3>
            <span style="font-size: 0.8rem; color: var(--text-secondary);">Showing recent wallet reloads</span>
        </div>

        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="custom-table" style="font-size: 0.9rem; min-width: 620px; width: 100%;">
                    <thead>
                        <tr>
                            <th style="white-space: nowrap;">Date & Time</th>
                            <th style="white-space: nowrap;">Mode / Gateway</th>
                            <th style="white-space: nowrap;">Reference / UTR ID</th>
                            <th style="white-space: nowrap;">Amount Paid</th>
                            <th style="white-space: nowrap;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                            @php
                                $isDeposit = ($txn->payment_gateway !== 'System Refund' && !str_starts_with($txn->payment_id, 'REFUND_'));
                                $baseAmt = (float)$txn->amount;
                                $bonusAmt = $isDeposit ? \App\Http\Controllers\PaymentController::calculateDepositBonus($baseAmt) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">
                                        {{ $txn->created_at->format('d M Y, h:i A') }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                        {{ $txn->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <i class="fa-solid fa-building-columns" style="margin-right: 4px; opacity: 0.7;"></i>
                                        {{ $txn->payment_gateway }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <code style="font-family: monospace; font-size: 0.88rem; font-weight: 700; background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 4px; color: var(--text-primary); border: 1px solid var(--border-color);">
                                            {{ $txn->payment_id }}
                                        </code>
                                        <button type="button" class="btn-outline" style="padding: 2px 6px; font-size: 0.75rem; border: none; color: var(--text-secondary);" onclick="navigator.clipboard.writeText('{{ $txn->payment_id }}'); alert('UTR copied: {{ $txn->payment_id }}');" title="Copy UTR">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <strong style="font-size: 0.95rem; color: #10b981;">₹{{ number_format($baseAmt, 2) }}</strong>
                                    @if($bonusAmt > 0 && $txn->status === 'completed')
                                        <span style="font-size: 0.72rem; color: #ec4899; background: rgba(236,72,153,0.1); padding: 1px 6px; border-radius: 4px; display: block; margin-top: 2px; width: fit-content;">
                                            +₹{{ number_format($bonusAmt, 2) }} Bonus
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($txn->status === 'completed')
                                        <span class="badge badge-completed" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px;">
                                            <i class="fa-solid fa-circle-check"></i> Success
                                        </span>
                                    @elseif($txn->status === 'pending')
                                        <span class="badge badge-pending" style="background: rgba(2, 132, 199, 0.15); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px;" title="Verifying your payment safely...">
                                            <i class="fa-solid fa-arrows-rotate fa-spin" style="margin-right: 4px;"></i> Verifying Payment...
                                        </span>
                                    @else
                                        <span class="badge badge-canceled" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px;">
                                            <i class="fa-solid fa-circle-xmark"></i> Failed / Rejected
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div style="margin-top: 1.5rem;">
                {{ $transactions->links() }}
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 3rem;">
                <i class="fa-solid fa-receipt" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                No deposits recorded yet. Use options above to add funds.
            </div>
        @endif
    </div>

    </div>
@endsection