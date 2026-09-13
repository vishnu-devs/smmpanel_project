@extends('layouts.app')

@section('title', 'Admin Panel - Growinsta')
@section('page_header', 'System Overview')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- System Stats Grid -->
    <div class="stats-container animate-fade-in">
        <div class="glass stats-card" style="border-left: 4px solid var(--color-info);">
            <div>
                <div class="stats-lbl">Total Clients</div>
                <div class="stats-val text-gradient">{{ number_format($stats['users']) }}</div>
            </div>
            <div class="stats-icon" style="color: var(--color-info);"><i class="fa-solid fa-users"></i></div>
        </div>

        <div class="glass stats-card" style="border-left: 4px solid var(--color-primary);">
            <div>
                <div class="stats-lbl">Total Orders</div>
                <div class="stats-val text-gradient">{{ number_format($stats['orders']) }}</div>
            </div>
            <div class="stats-icon" style="color: var(--color-primary);"><i class="fa-solid fa-cart-shopping"></i></div>
        </div>

        <div class="glass stats-card" style="border-left: 4px solid #3b82f6;">
            <div>
                <div class="stats-lbl">Total Earnings (Revenue)</div>
                <div class="stats-val text-gradient" style="background: linear-gradient(135deg, #3b82f6, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">₹{{ number_format($stats['revenue'], 2) }}</div>
            </div>
            <div class="stats-icon" style="color: #3b82f6;"><i class="fa-solid fa-money-bill-wave"></i></div>
        </div>

        <div class="glass stats-card" style="border-left: 4px solid #10b981; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.02));">
            <div>
                <div class="stats-lbl" style="display: flex; align-items: center; gap: 6px;">
                    <span>Net Profit</span>
                    @if($stats['profit_margin'] > 0)
                        <span style="background: rgba(16,185,129,0.2); color: #10b981; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 10px;">{{ $stats['profit_margin'] }}% Margin</span>
                    @endif
                </div>
                <div class="stats-val" style="color: #10b981; font-weight: 800;">₹{{ number_format($stats['profit'], 2) }}</div>
            </div>
            <div class="stats-icon" style="color: #10b981;"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>

        <div class="glass stats-card" style="border-left: 4px solid var(--color-danger);">
            <div>
                <div class="stats-lbl">Pending Support</div>
                <div class="stats-val text-gradient">{{ $stats['pending_tickets'] }}</div>
            </div>
            <div class="stats-icon" style="color: var(--color-danger);"><i class="fa-solid fa-comment-dots"></i></div>
        </div>
    </div>

    <!-- Charts & Analytics Visual Grid -->
    <div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 2rem; margin-top: 2rem;">
        
        <!-- Weekly Earnings Chart Card -->
        <div class="glass custom-card" style="margin: 0; padding: 1.5rem;">
            <h3 class="card-title" style="border: none; padding: 0; margin-bottom: 1.5rem;">
                <i class="fa-solid fa-chart-simple text-gradient"></i> Weekly Completed Revenue (INR)
            </h3>
            
            <!-- Custom CSS Flex Bar Graph Chart -->
            <div style="height: 180px; display: flex; align-items: flex-end; gap: clamp(6px, 2vw, 15px); border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: 10px;">
                @php
                    $maxAmount = collect($weeklyRevenue)->max('amount') ?: 1000;
                @endphp
                @foreach($weeklyRevenue as $dayData)
                    @php
                        $percentage = $maxAmount > 0 ? ($dayData['amount'] / $maxAmount) * 100 : 0;
                    @endphp
                    <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                        <span style="font-size: 0.7rem; font-weight: bold; margin-bottom: 6px; color: var(--text-primary);">
                            ₹{{ number_format($dayData['amount']) }}
                        </span>
                        <div style="width: 24px; height: {{ max($percentage, 4) }}%; background: var(--grad-primary); border-radius: var(--radius-sm) var(--radius-sm) 0 0; transition: height 0.3s ease; min-height: 4px;" title="₹{{ number_format($dayData['amount'], 2) }}"></div>
                        <span style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 8px; font-weight: 600;">
                            {{ $dayData['day'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Success Rate & Popular Services Card -->
        <div class="glass custom-card" style="margin: 0; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 class="card-title" style="border: none; padding: 0; margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-gauge-high text-gradient"></i> System Performance
                </h3>
                
                <!-- Success Bar -->
                <div style="margin-bottom: 1.2rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                        <span style="color: var(--text-secondary);">Order Success Rate</span>
                        <strong style="color: var(--color-success);">{{ $successRate }}%</strong>
                    </div>
                    <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                        <div style="width: {{ $successRate }}%; height: 100%; background: var(--grad-primary);"></div>
                    </div>
                </div>

                <!-- Refund counter -->
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin-bottom: 12px; flex-wrap: wrap; gap: 6px;">
                    <span style="color: var(--text-secondary);">Refunded Orders</span>
                    <strong style="color: var(--color-danger);">{{ $refundStats['count'] }} (₹{{ number_format($refundStats['amount'], 2) }})</strong>
                </div>

                <!-- Top Services Table -->
                <div style="font-size: 0.8rem;">
                    <span style="color: var(--text-secondary); font-weight: 600; display: block; margin-bottom: 6px;">Top Popular Services</span>
                    @if($topServices->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @foreach($topServices as $ts)
                                <div style="display: flex; justify-content: space-between; background: rgba(255,255,255,0.03); padding: 5px 8px; border-radius: var(--radius-sm); flex-wrap: wrap; gap: 6px; align-items: center;">
                                    <span style="color: var(--text-primary); font-weight: 500; font-size: 0.75rem;" title="{{ $ts->service ? 'ID ' . $ts->service_id . ' - ' . $ts->service->name : 'Deleted Service' }}">
                                        {{ $ts->service ? 'ID ' . $ts->service_id . ' - ' . Str::limit($ts->service->name, 28) : 'Deleted Service' }}
                                    </span>
                                    <strong style="color: var(--color-info); font-size: 0.75rem;">{{ $ts->count }} orders</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="color: var(--text-muted); font-size: 0.75rem;">No services ordered yet.</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="card-grid">
        
        <!-- Left Side: Pending Manual Payments & Support -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- Pending Deposits -->
            <div class="glass custom-card" style="margin: 0; padding: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 1rem; border: none; padding: 0;">
                    <i class="fa-solid fa-wallet text-gradient"></i> Pending Manual Deposits ({{ $stats['pending_manual_payments'] }})
                </h3>
                @if($pendingPayments->count() > 0)
                    <div class="table-responsive">
                        <table class="custom-table" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>UTR / Reference</th>
                                    <th>Amount</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPayments as $pay)
                                    <tr>
                                        <td>{{ $pay->user ? $pay->user->name : 'Deleted' }}</td>
                                        <td style="font-family: monospace;">{{ $pay->payment_id }}</td>
                                        <td style="font-weight: bold;">₹{{ number_format($pay->amount, 2) }}</td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('admin.settings') }}" class="btn-gradient" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                                Verify
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.9rem;">
                        No pending deposits to verify.
                    </div>
                @endif
            </div>

            <!-- Pending Support Tickets -->
            <div class="glass custom-card" style="margin: 0; padding: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 1rem; border: none; padding: 0;">
                    <i class="fa-solid fa-ticket text-gradient"></i> Pending Tickets
                </h3>
                @if($latestTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="custom-table" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Reply</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestTickets as $tick)
                                    <tr>
                                        <td>{{ $tick->user ? $tick->user->name : 'Deleted' }}</td>
                                        <td>{{ Str::limit($tick->subject, 20) }}</td>
                                        <td>
                                            @if($tick->status === 'open')
                                                <span class="badge badge-pending">Open</span>
                                            @else
                                                <span class="badge badge-inprogress">Reply</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('admin.tickets.show', $tick->id) }}" class="btn-outline" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.9rem;">
                        All support requests answered!
                    </div>
                @endif
            </div>
            
        </div>

        <!-- Right Side: Recent Global Orders -->
        <div class="glass custom-card" style="margin: 0; padding: 1.5rem;">
            <h3 class="card-title" style="margin-bottom: 1rem; border: none; padding: 0;">
                <i class="fa-solid fa-cart-shopping text-gradient"></i> Recent Orders Placement
            </h3>
            @if($latestOrders->count() > 0)
                <div class="table-responsive">
                    <table class="custom-table" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestOrders as $ord)
                                <tr>
                                    <td style="font-weight: bold; color: var(--text-muted);">#{{ $ord->id }}</td>
                                    <td>{{ $ord->user ? Str::limit($ord->user->name, 12) : 'Deleted' }}</td>
                                    <td>{{ $ord->service ? Str::limit($ord->service->name, 25) : 'Deleted' }}</td>
                                    <td>₹{{ number_format($ord->charge, 2) }}</td>
                                    <td>
                                        @if($ord->status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($ord->status === 'processing')
                                            <span class="badge badge-processing">Processing</span>
                                        @elseif($ord->status === 'completed')
                                            <span class="badge badge-completed">Done</span>
                                        @else
                                            <span class="badge badge-canceled">Canceled</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="{{ route('admin.orders') }}" class="btn-outline" style="padding: 8px 20px; font-size: 0.85rem; width: 100%;">
                        Inspect All Global Orders
                    </a>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 3rem; font-size: 0.9rem;">
                    No orders submitted yet.
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
