@extends('layouts.app')

@section('title', 'Payments & Deposits Management - RishiSMM')
@section('page_header', 'Payments & Deposit History')

@section('styles')
<style>
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 2rem;
    }
    .kpi-card {
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .status-tab-btn {
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-tab-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-primary);
    }
    .status-tab-btn.active {
        background: var(--grad-insta);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(220, 39, 67, 0.35);
    }
    .badge-pending-pulse {
        animation: pulseBadge 1.8s infinite;
    }
    @keyframes pulseBadge {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.85; }
        100% { transform: scale(1); opacity: 1; }
    }
    .copy-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 3px 6px;
        border-radius: 4px;
        transition: color 0.2s;
    }
    .copy-btn:hover {
        color: var(--color-primary);
    }

    /* Responsive Desktop Table vs Mobile Cards */
    .desktop-only-table {
        display: block;
        width: 100%;
        overflow-x: auto;
    }
    .desktop-only-table table {
        min-width: 980px;
        width: 100%;
    }
    .mobile-only-transactions {
        display: none;
    }

    @media (max-width: 768px) {
        .desktop-only-table {
            display: none !important;
        }
        .mobile-only-transactions {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
    }

    .txn-mobile-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
    }
</style>
@endsection

@section('content')
<div style="max-width: 1300px; margin: 0 auto;">

    <!-- Summary KPI Cards -->
    <div class="kpi-grid">
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600;">Total Deposited</span>
                <h3 style="font-size: 1.4rem; font-weight: 800; margin: 2px 0 0; color: #10b981;">
                    ₹{{ number_format($metrics['total_amount'], 2) }}
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ number_format($metrics['total_count']) }} completed payments</span>
            </div>
        </div>

        <div class="glass kpi-card" style="{{ $metrics['pending_count'] > 0 ? 'border: 1px solid rgba(245, 158, 11, 0.5);' : '' }}">
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600;">Pending Approvals</span>
                <h3 style="font-size: 1.4rem; font-weight: 800; margin: 2px 0 0; color: #f59e0b;">
                    {{ number_format($metrics['pending_count']) }}
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-secondary);">Worth ₹{{ number_format($metrics['pending_amount'], 2) }}</span>
            </div>
        </div>

        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600;">Today's Deposits</span>
                <h3 style="font-size: 1.4rem; font-weight: 800; margin: 2px 0 0; color: #3b82f6;">
                    ₹{{ number_format($metrics['today_amount'], 2) }}
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ number_format($metrics['today_count']) }} deposits today</span>
            </div>
        </div>

        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                <i class="fa-solid fa-gift"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600;">Total Bonus Given</span>
                <h3 style="font-size: 1.4rem; font-weight: 800; margin: 2px 0 0; color: #ec4899;">
                    ₹{{ number_format($metrics['total_bonus'], 2) }}
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-secondary);">Automated deposit bonuses</span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="glass custom-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 1.5rem;">
            <h3 class="card-title" style="margin: 0;">
                <i class="fa-solid fa-money-bill-transfer text-gradient"></i> All Payments & Add Funds Log
            </h3>

            <!-- Status Filter Tabs -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('admin.transactions', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
                   class="status-tab-btn {{ $status === 'all' ? 'active' : '' }}">
                    🌐 All ({{ number_format($metrics['total_count'] + $metrics['pending_count']) }})
                </a>
                <a href="{{ route('admin.transactions', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}"
                   class="status-tab-btn {{ $status === 'pending' ? 'active' : '' }}"
                   style="{{ $metrics['pending_count'] > 0 && $status !== 'pending' ? 'border-color: rgba(245,158,11,0.5); color: #f59e0b;' : '' }}">
                    ⏳ Pending
                    @if($metrics['pending_count'] > 0)
                        <span class="badge-pending-pulse" style="background: #f59e0b; color: #000; font-size: 0.7rem; font-weight: 800; padding: 1px 6px; border-radius: 10px;">
                            {{ $metrics['pending_count'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.transactions', array_merge(request()->except(['status', 'page']), ['status' => 'completed'])) }}"
                   class="status-tab-btn {{ $status === 'completed' ? 'active' : '' }}">
                    ✅ Completed
                </a>
                <a href="{{ route('admin.transactions', array_merge(request()->except(['status', 'page']), ['status' => 'failed'])) }}"
                   class="status-tab-btn {{ $status === 'failed' ? 'active' : '' }}">
                    ❌ Rejected / Failed
                </a>
            </div>
        </div>

        <!-- Search & Date Filter Bar -->
        <form action="{{ route('admin.transactions') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 1.5rem;">
            @if($status !== 'all')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div style="flex: 2; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, UTR, amount..." value="{{ $search }}" style="padding: 10px 14px;">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" placeholder="From Date" style="padding: 10px 12px;" title="From Date">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" placeholder="To Date" style="padding: 10px 12px;" title="To Date">
            </div>
            <button type="submit" class="btn-gradient" style="padding: 10px 18px; white-space: nowrap;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($search || $dateFrom || $dateTo || $status !== 'all')
                <a href="{{ route('admin.transactions') }}" class="btn-outline" style="padding: 10px 14px; white-space: nowrap;">
                    Reset
                </a>
            @endif
        </form>

        <!-- Transactions Container -->
        @if($transactions->count() > 0)
            <!-- Desktop Table View (> 768px) -->
            <div class="table-responsive desktop-only-table">
                <table class="custom-table" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th style="width: 70px; white-space: nowrap;">ID</th>
                            <th style="min-width: 170px;">Customer</th>
                            <th style="white-space: nowrap; width: 130px;">Amount</th>
                            <th style="white-space: nowrap; width: 140px;">Gateway / Mode</th>
                            <th style="white-space: nowrap; width: 160px;">UTR / Ref ID</th>
                            <th style="white-space: nowrap; width: 150px;">Date & Time</th>
                            <th style="white-space: nowrap; width: 120px;">Status</th>
                            <th style="text-align: center; width: 160px; white-space: nowrap;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                            @php
                                $isDeposit = ($txn->payment_gateway !== 'System Refund' && !str_starts_with($txn->payment_id, 'REFUND_'));
                                $baseAmt = (float)$txn->amount;
                                $bonusAmt = $isDeposit ? \App\Http\Controllers\PaymentController::calculateDepositBonus($baseAmt) : 0;
                                $totalAmt = $baseAmt + $bonusAmt;
                            @endphp
                            <tr>
                                <td style="font-weight: 700; color: var(--text-muted); white-space: nowrap;">
                                    #{{ $txn->id }}
                                </td>
                                <td>
                                    @if($txn->user)
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            {{ $txn->user->name }}
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-secondary);">
                                            {{ $txn->user->email }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 2px;">
                                            Bal: ₹{{ number_format($txn->user->balance, 2) }}
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted); font-style: italic;">User Deleted (#{{ $txn->user_id }})</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="font-weight: 800; font-size: 1rem; color: #10b981;">
                                        ₹{{ number_format($baseAmt, 2) }}
                                    </div>
                                    @if($bonusAmt > 0)
                                        <span style="font-size: 0.72rem; color: #ec4899; background: rgba(236,72,153,0.1); padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">
                                            +₹{{ number_format($bonusAmt, 2) }} Bonus
                                        </span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <i class="fa-solid fa-building-columns" style="margin-right: 4px; opacity: 0.7;"></i>
                                        {{ $txn->payment_gateway }}
                                    </span>
                                    @if($txn->screenshot)
                                        <div style="margin-top: 4px;">
                                            <a href="{{ route('admin.transactions.screenshot', $txn->id) }}" target="_blank" style="font-size: 0.75rem; color: var(--color-info); text-decoration: none; font-weight: 600;">
                                                <i class="fa-solid fa-image"></i> View Receipt
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                                        <code style="font-family: monospace; font-size: 0.88rem; font-weight: 700; background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 4px; color: var(--text-primary); border: 1px solid var(--border-color);">
                                            {{ $txn->payment_id }}
                                        </code>
                                        <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('{{ $txn->payment_id }}'); alert('UTR copied: {{ $txn->payment_id }}');" title="Copy UTR">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                    @if($txn->notes)
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; max-width: 200px; line-height: 1.3;">
                                            <em>Note: {{ Str::limit($txn->notes, 35) }}</em>
                                        </div>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="font-size: 0.85rem; color: var(--text-primary); font-weight: 600;">
                                        {{ $txn->created_at->format('d M Y, h:i A') }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                        {{ $txn->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">
                                    @if(in_array($txn->status, ['completed', 'approved']))
                                        <span class="badge badge-completed" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; white-space: nowrap;">
                                            <i class="fa-solid fa-circle-check"></i> Completed
                                        </span>
                                    @elseif($txn->status === 'pending')
                                        <span class="badge badge-pending badge-pending-pulse" style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; white-space: nowrap;">
                                            <i class="fa-solid fa-clock"></i> Pending Action
                                        </span>
                                    @else
                                        <span class="badge badge-canceled" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; white-space: nowrap;">
                                            <i class="fa-solid fa-circle-xmark"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    @if($txn->status === 'pending')
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <!-- Approve Form Trigger -->
                                            <button type="button" class="btn-gradient"
                                                    style="padding: 6px 12px; font-size: 0.78rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 6px; box-shadow: none; white-space: nowrap;"
                                                    onclick="openApproveModal({{ $txn->id }}, '{{ addslashes($txn->user ? $txn->user->name : 'User') }}', '{{ number_format($baseAmt, 2) }}', '{{ number_format($bonusAmt, 2) }}', '{{ number_format($totalAmt, 2) }}', '{{ $txn->payment_id }}')">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </button>

                                            <!-- Reject Form Trigger -->
                                            <button type="button" class="btn-outline"
                                                    style="padding: 6px 10px; font-size: 0.78rem; border-color: rgba(239,68,68,0.5); color: #ef4444; border-radius: 6px; white-space: nowrap;"
                                                    onclick="openRejectModal({{ $txn->id }}, '{{ $txn->payment_id }}')">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </button>
                                        </div>
                                    @elseif(in_array($txn->status, ['completed', 'approved']))
                                        <span style="font-size: 0.78rem; color: #10b981; font-weight: 600;">
                                            <i class="fa-solid fa-check-double"></i> Credited
                                        </span>
                                    @else
                                        <span style="font-size: 0.78rem; color: #ef4444;">
                                            <i class="fa-solid fa-ban"></i> Declined
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards View (<= 768px) -->
            <div class="mobile-only-transactions">
                @foreach($transactions as $txn)
                    @php
                        $isDeposit = ($txn->payment_gateway !== 'System Refund' && !str_starts_with($txn->payment_id, 'REFUND_'));
                        $baseAmt = (float)$txn->amount;
                        $bonusAmt = $isDeposit ? \App\Http\Controllers\PaymentController::calculateDepositBonus($baseAmt) : 0;
                        $totalAmt = $baseAmt + $bonusAmt;
                    @endphp
                    <div class="txn-mobile-card">
                        <!-- Card Top Bar -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: 700; font-size: 0.88rem; color: var(--text-muted);">
                                #{{ $txn->id }}
                            </span>
                            <div>
                                @if($txn->status === 'completed')
                                    <span class="badge badge-completed" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-weight: 700; font-size: 0.75rem; padding: 3px 8px; border-radius: 20px;">
                                        <i class="fa-solid fa-circle-check"></i> Completed
                                    </span>
                                @elseif($txn->status === 'pending')
                                    <span class="badge badge-pending badge-pending-pulse" style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-weight: 700; font-size: 0.75rem; padding: 3px 8px; border-radius: 20px;">
                                        <i class="fa-solid fa-clock"></i> Pending Action
                                    </span>
                                @else
                                    <span class="badge badge-canceled" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-weight: 700; font-size: 0.75rem; padding: 3px 8px; border-radius: 20px;">
                                        <i class="fa-solid fa-circle-xmark"></i> Rejected
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Details -->
                        <div style="margin-bottom: 10px;">
                            @if($txn->user)
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                                    {{ $txn->user->name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $txn->user->email }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--color-primary); margin-top: 2px;">
                                    User Balance: ₹{{ number_format($txn->user->balance, 2) }}
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">User Deleted (#{{ $txn->user_id }})</span>
                            @endif
                        </div>

                        <!-- Amount & UTR Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; background: rgba(0,0,0,0.15); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">Amount</span>
                                <strong style="font-size: 1.1rem; color: #10b981;">₹{{ number_format($baseAmt, 2) }}</strong>
                                @if($bonusAmt > 0)
                                    <span style="font-size: 0.7rem; color: #ec4899; display: block;">+₹{{ number_format($bonusAmt, 2) }} Bonus</span>
                                @endif
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">Mode</span>
                                <span style="font-size: 0.82rem; color: var(--text-primary);">{{ $txn->payment_gateway }}</span>
                            </div>
                        </div>

                        <!-- UTR & Date -->
                        <div style="font-size: 0.82rem; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="color: var(--text-secondary);">UTR:</span>
                                <code style="font-family: monospace; font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">{{ $txn->payment_id }}</code>
                                <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('{{ $txn->payment_id }}'); alert('UTR copied: {{ $txn->payment_id }}');">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.78rem;">
                                <i class="fa-regular fa-clock" style="margin-right: 4px;"></i> {{ $txn->created_at->format('d M Y, h:i A') }} ({{ $txn->created_at->diffForHumans() }})
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        @if($txn->status === 'pending')
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                <button type="button" class="btn-gradient"
                                        style="padding: 8px 12px; font-size: 0.82rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 6px; width: 100%;"
                                        onclick="openApproveModal({{ $txn->id }}, '{{ addslashes($txn->user ? $txn->user->name : 'User') }}', '{{ number_format($baseAmt, 2) }}', '{{ number_format($bonusAmt, 2) }}', '{{ number_format($totalAmt, 2) }}', '{{ $txn->payment_id }}')">
                                    <i class="fa-solid fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn-outline"
                                        style="padding: 8px 12px; font-size: 0.82rem; border-color: rgba(239,68,68,0.5); color: #ef4444; border-radius: 6px; width: 100%;"
                                        onclick="openRejectModal({{ $txn->id }}, '{{ $txn->payment_id }}')">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="margin-top: 1.5rem;">
                {{ $transactions->links() }}
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 4rem 1rem;">
                <i class="fa-solid fa-receipt" style="font-size: 3rem; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                <h4 style="color: var(--text-secondary); margin-bottom: 4px;">No deposit transactions found</h4>
                <p style="font-size: 0.85rem;">Try adjusting your filters or search query.</p>
            </div>
        @endif
    </div>
</div>

@push('modals')
<!-- Modal: Approve Confirmation -->
<div id="approveModal" class="custom-modal" onclick="if(event.target===this) closeApproveModal()">
    <div class="custom-modal-content animate-fade-in" style="max-width: 500px;">
        <div class="custom-modal-header">
            <div class="custom-modal-header-text">
                <h3 class="custom-modal-title" style="color: #10b981;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> Approve Deposit
                </h3>
                <p class="custom-modal-subtitle">Credit customer's wallet balance</p>
            </div>
            <button type="button" class="custom-modal-close" onclick="closeApproveModal()" title="Close">&times;</button>
        </div>

        <form id="approveForm" action="" method="POST">
            @csrf
            <div class="custom-modal-body">
                <!-- Summary Data Card -->
                <div class="modal-info-summary">
                    <div class="modal-info-row">
                        <span class="modal-info-label">Customer</span>
                        <span class="modal-info-value" id="approveClientName"></span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-info-label">UTR / Ref ID</span>
                        <code class="modal-info-code" id="approveUtr"></code>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-info-label">Base Amount</span>
                        <span class="modal-info-value" id="approveBaseAmt"></span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-info-label">Deposit Bonus</span>
                        <span class="modal-info-value" id="approveBonusAmt" style="color: #ec4899;"></span>
                    </div>
                    <div class="modal-info-row modal-info-total">
                        <span class="modal-info-total-label">Total Credit</span>
                        <span class="modal-info-total-value" id="approveTotalAmt"></span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="approveNotes" class="form-label" style="font-size: 0.8rem; font-weight: 600;">Admin Remarks (Optional)</label>
                    <textarea name="notes" id="approveNotes" class="form-control" rows="2" placeholder="e.g. Bank SMS / UTR verified manually" style="font-size: 0.85rem; resize: none;"></textarea>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-outline" onclick="closeApproveModal()" style="height: 42px; padding: 0 18px; font-size: 0.88rem;">Cancel</button>
                <button type="submit" class="btn-gradient" style="height: 42px; padding: 0 22px; font-size: 0.88rem; background: linear-gradient(135deg, #10b981, #059669); font-weight: 700;">
                    <i class="fa-solid fa-check" style="margin-right: 6px;"></i> Approve Deposit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Confirmation -->
<div id="rejectModal" class="custom-modal" onclick="if(event.target===this) closeRejectModal()">
    <div class="custom-modal-content modal-compact animate-fade-in">
        <div class="custom-modal-header">
            <div class="custom-modal-header-text">
                <h3 class="custom-modal-title" style="color: #ef4444;">
                    <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> Reject Deposit
                </h3>
                <p class="custom-modal-subtitle">Decline payment request</p>
            </div>
            <button type="button" class="custom-modal-close" onclick="closeRejectModal()" title="Close">&times;</button>
        </div>

        <form id="rejectForm" action="" method="POST">
            @csrf
            <div class="custom-modal-body">
                <div class="modal-info-summary" style="margin-bottom: 1rem;">
                    <div class="modal-info-row">
                        <span class="modal-info-label">UTR / Ref ID</span>
                        <code class="modal-info-code" id="rejectUtr"></code>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="rejectNotes" class="form-label" style="font-size: 0.8rem; font-weight: 600;">Rejection Reason (Optional)</label>
                    <textarea name="notes" id="rejectNotes" class="form-control" rows="2" placeholder="e.g. UTR not found in bank statement / Invalid reference" style="font-size: 0.85rem; resize: none;"></textarea>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-outline" onclick="closeRejectModal()" style="height: 42px; padding: 0 18px; font-size: 0.88rem;">Cancel</button>
                <button type="submit" class="btn-gradient" style="height: 42px; padding: 0 20px; font-size: 0.88rem; background: linear-gradient(135deg, #ef4444, #dc2626); font-weight: 700;">
                    <i class="fa-solid fa-ban" style="margin-right: 6px;"></i> Reject Deposit
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

<script>
    // Restore scroll position after approving or rejecting a deposit
    const savedScrollY = sessionStorage.getItem('admin_transactions_scroll_y');
    if (savedScrollY !== null) {
        window.scrollTo(0, parseInt(savedScrollY, 10));
        sessionStorage.removeItem('admin_transactions_scroll_y');
    }

    document.addEventListener('submit', function(e) {
        if (e.target && (e.target.id === 'approveForm' || e.target.id === 'rejectForm')) {
            sessionStorage.setItem('admin_transactions_scroll_y', window.scrollY);
        }
    });

    function openApproveModal(id, client, baseAmt, bonusAmt, totalAmt, utr) {
        document.getElementById('approveClientName').innerText = client;
        document.getElementById('approveUtr').innerText = utr;
        document.getElementById('approveBaseAmt').innerText = '₹' + baseAmt;
        document.getElementById('approveBonusAmt').innerText = bonusAmt > 0 ? '+₹' + bonusAmt : 'No Bonus';
        document.getElementById('approveTotalAmt').innerText = '₹' + totalAmt;
        document.getElementById('approveForm').action = '/admin/transactions/' + id + '/approve';
        
        const modal = document.getElementById('approveModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function openRejectModal(id, utr) {
        document.getElementById('rejectUtr').innerText = utr;
        document.getElementById('rejectForm').action = '/admin/transactions/' + id + '/reject';
        
        const modal = document.getElementById('rejectModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
</script>
@endsection
