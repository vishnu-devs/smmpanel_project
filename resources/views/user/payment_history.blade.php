@extends('layouts.app')

@section('title', 'Payment & Wallet History - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Payment & Wallet History')

@section('styles')
<style>
    /* Container Box Sizing Security */
    .ph-container {
        max-width: 1100px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    /* Filter Form Responsiveness */
    .payment-filter-grid {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .payment-filter-grid .filter-item {
        flex: 1;
        min-width: 160px;
    }
    .payment-filter-grid .filter-actions {
        display: flex;
        gap: 8px;
    }

    /* Responsive Display Toggles */
    .ph-desktop-view {
        display: block;
    }
    .ph-mobile-view {
        display: none;
    }

    /* Mobile Responsive Card Styling (< 768px) */
    @media (max-width: 768px) {
        .ph-desktop-view {
            display: none !important;
        }
        .ph-mobile-view {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            box-sizing: border-box;
        }

        .payment-filter-grid {
            flex-direction: column;
            align-items: stretch;
        }
        .payment-filter-grid .filter-item {
            min-width: 100% !important;
            width: 100%;
        }
        .payment-filter-grid .filter-actions {
            width: 100%;
            margin-top: 6px;
        }
        .payment-filter-grid .filter-actions button,
        .payment-filter-grid .filter-actions a {
            flex: 1;
            text-align: center;
            justify-content: center;
        }

        /* Compact Card Styling */
        .ph-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
            border-radius: var(--radius-md, 12px);
            padding: 14px;
            width: 100%;
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .ph-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .ph-card-type {
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ph-card-amount {
            font-size: 1.05rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .ph-card-date {
            font-size: 0.78rem;
            color: var(--text-muted, #94a3b8);
            margin-top: 3px;
        }
        .ph-card-divider {
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            margin: 10px 0;
            height: 0;
        }
        .ph-card-balance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            gap: 8px;
        }
        .ph-card-balance-item {
            display: flex;
            flex-direction: column;
        }
        .ph-card-lbl {
            font-size: 0.72rem;
            color: var(--text-muted, #94a3b8);
            text-transform: uppercase;
            font-weight: 600;
        }
        .ph-card-val {
            font-weight: 700;
            color: var(--text-primary, #ffffff);
            font-size: 0.88rem;
        }
        .ph-card-ref {
            font-size: 0.8rem;
            font-family: monospace;
            color: var(--text-secondary, #cbd5e1);
            word-break: break-all;
        }
        .ph-card-status {
            display: inline-block;
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="ph-container">

    <!-- Filter Card -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <h3 class="card-title" style="margin-bottom: 1rem;"><i class="fa-solid fa-filter text-gradient"></i> Filter Wallet Ledger</h3>
        
        <form action="{{ route('payment.history') }}" method="GET" class="payment-filter-grid">
            <div class="filter-item">
                <label class="form-label" style="font-size: 0.8rem;">Transaction Type</label>
                <select name="type" class="form-control" style="font-size: 0.9rem;">
                    <option value="">All Types</option>
                    <option value="deposit_approve" {{ request('type') == 'deposit_approve' ? 'selected' : '' }}>Deposit</option>
                    <option value="deposit_bonus" {{ request('type') == 'deposit_bonus' ? 'selected' : '' }}>Deposit Bonus</option>
                    <option value="order_place" {{ request('type') == 'order_place' ? 'selected' : '' }}>Order Payment</option>
                    <option value="order_refund" {{ request('type') == 'order_refund' ? 'selected' : '' }}>Refund</option>
                    <option value="referral_commission" {{ request('type') == 'referral_commission' ? 'selected' : '' }}>Referral Commission</option>
                    <option value="admin_adjustment" {{ request('type') == 'admin_adjustment' ? 'selected' : '' }}>Admin Adjustment</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="form-label" style="font-size: 0.8rem;">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="font-size: 0.9rem;">
            </div>

            <div class="filter-item">
                <label class="form-label" style="font-size: 0.8rem;">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="font-size: 0.9rem;">
            </div>

            <div class="filter-item">
                <label class="form-label" style="font-size: 0.8rem;">Search Reference / Txn ID</label>
                <input type="text" name="search" class="form-control" placeholder="e.g. Order ID, UTR..." value="{{ request('search') }}" style="font-size: 0.9rem;">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-gradient" style="padding: 10px 18px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                <a href="{{ route('payment.history') }}" class="btn-outline" style="padding: 10px 14px; text-decoration: none; color: var(--text-primary);">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- History Wrapper -->
    <div class="glass custom-card animate-fade-in" style="padding: 1.25rem;">
        <h3 class="card-title" style="margin-bottom: 1rem;"><i class="fa-solid fa-receipt text-gradient"></i> Financial Ledger</h3>

        <!-- DESKTOP TABLE VIEW (> 768px) -->
        <div class="ph-desktop-view">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type / Action</th>
                            <th>Amount</th>
                            <th>Previous Balance</th>
                            <th>New Balance</th>
                            <th>Reference ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                            <tr>
                                <td style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap;">
                                    {{ $txn->created_at ? \Carbon\Carbon::parse($txn->created_at)->format('d M Y, h:i A') : '-' }}
                                </td>
                                <td style="white-space: nowrap;">
                                    @switch($txn->action)
                                        @case('deposit_approve')
                                            <span style="font-weight: 700; color: #22c55e;"><i class="fa-solid fa-wallet"></i> Deposit</span>
                                            @break
                                        @case('deposit_bonus')
                                            <span style="font-weight: 700; color: #10b981;"><i class="fa-solid fa-gift"></i> Deposit Bonus</span>
                                            @break
                                        @case('order_place')
                                            <span style="font-weight: 700; color: #ef4444;"><i class="fa-solid fa-cart-shopping"></i> Order Payment</span>
                                            @break
                                        @case('order_refund')
                                            <span style="font-weight: 700; color: #3b82f6;"><i class="fa-solid fa-rotate-left"></i> Refund</span>
                                            @break
                                        @case('referral_commission')
                                            <span style="font-weight: 700; color: #a855f7;"><i class="fa-solid fa-users"></i> Referral Commission</span>
                                            @break
                                        @default
                                            <span style="font-weight: 600; color: var(--text-primary);">{{ ucfirst(str_replace('_', ' ', $txn->action)) }}</span>
                                    @endswitch
                                </td>
                                <td style="font-weight: 800; white-space: nowrap; {{ (float)$txn->amount >= 0 ? 'color: #22c55e;' : 'color: #ef4444;' }}">
                                    {{ (float)$txn->amount >= 0 ? '+' : '' }}₹{{ number_format((float)$txn->amount, 2) }}
                                </td>
                                <td style="color: var(--text-muted); white-space: nowrap;">₹{{ number_format((float)$txn->previous_balance, 2) }}</td>
                                <td style="font-weight: 700; color: var(--text-primary); white-space: nowrap;">₹{{ number_format((float)$txn->new_balance, 2) }}</td>
                                <td style="font-size: 0.85rem; font-family: monospace; word-break: break-all;">{{ $txn->reference_id ?? '-' }}</td>
                                <td>
                                    <span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">Completed</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                                    No payment transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MOBILE CARDS VIEW (<= 768px) -->
        <div class="ph-mobile-view">
            @forelse($transactions as $txn)
                <div class="ph-card">
                    <!-- Top Row: Type & Amount -->
                    <div class="ph-card-header">
                        <div class="ph-card-type">
                            @switch($txn->action)
                                @case('deposit_approve')
                                    <span style="color: #22c55e;"><i class="fa-solid fa-wallet"></i> Deposit</span>
                                    @break
                                @case('deposit_bonus')
                                    <span style="color: #10b981;"><i class="fa-solid fa-gift"></i> Deposit Bonus</span>
                                    @break
                                @case('order_place')
                                    <span style="color: #ef4444;"><i class="fa-solid fa-cart-shopping"></i> Order Payment</span>
                                    @break
                                @case('order_refund')
                                    <span style="color: #3b82f6;"><i class="fa-solid fa-rotate-left"></i> Refund</span>
                                    @break
                                @case('referral_commission')
                                    <span style="color: #a855f7;"><i class="fa-solid fa-users"></i> Referral Commission</span>
                                    @break
                                @default
                                    <span style="color: var(--text-primary);">{{ ucfirst(str_replace('_', ' ', $txn->action)) }}</span>
                            @endswitch
                        </div>
                        <div class="ph-card-amount" style="{{ (float)$txn->amount >= 0 ? 'color: #22c55e;' : 'color: #ef4444;' }}">
                            {{ (float)$txn->amount >= 0 ? '+' : '' }}₹{{ number_format((float)$txn->amount, 2) }}
                        </div>
                    </div>

                    <!-- Sub-row: Date & Time -->
                    <div class="ph-card-date">
                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                        {{ $txn->created_at ? \Carbon\Carbon::parse($txn->created_at)->format('d M Y • h:i A') : '-' }}
                    </div>

                    <div class="ph-card-divider"></div>

                    <!-- Balance Row: Previous & New -->
                    <div class="ph-card-balance-row">
                        <div class="ph-card-balance-item">
                            <span class="ph-card-lbl">Previous Balance</span>
                            <span class="ph-card-val" style="color: var(--text-muted);">₹{{ number_format((float)$txn->previous_balance, 2) }}</span>
                        </div>
                        <div class="ph-card-balance-item" style="text-align: right;">
                            <span class="ph-card-lbl">New Balance</span>
                            <span class="ph-card-val" style="color: #22c55e;">₹{{ number_format((float)$txn->new_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="ph-card-divider"></div>

                    <!-- Bottom Row: Reference ID & Status Badge -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 8px;">
                        <div style="flex: 1; min-width: 0;">
                            <div class="ph-card-lbl">Reference / Txn ID</div>
                            <div class="ph-card-ref">{{ $txn->reference_id ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="ph-card-status">Completed</span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 2rem; background: rgba(255,255,255,0.02); border-radius: 12px;">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                    No payment transactions found.
                </div>
            @endforelse
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $transactions->links('partials.pagination') }}
        </div>
    </div>

</div>
@endsection
