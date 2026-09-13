@extends('layouts.app')

@section('title', 'Manage Orders - RishiSMM')
@section('page_header', 'Order Log Manager')

@section('styles')
    <style>
        .desktop-only-table {
            display: block;
        }

        .mobile-only-orders {
            display: none;
        }

        .orders-filter-bar {
            border-radius: var(--radius-md);
            padding: 8px 12px;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .orders-tabs-scroll {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .orders-search-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .orders-search-input {
            padding: 8px 12px;
            font-size: 0.85rem;
            width: 220px;
        }

        .pagination-wrapper {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 8px;
        }

        @media (max-width: 768px) {
            .desktop-only-table {
                display: none !important;
            }

            .mobile-only-orders {
                display: flex !important;
                flex-direction: column;
                gap: 10px;
            }

            .orders-filter-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                padding: 10px;
            }

            .orders-tabs-scroll {
                overflow-x: auto;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                padding-bottom: 6px;
                scrollbar-width: none;
            }

            .orders-tabs-scroll::-webkit-scrollbar {
                display: none;
            }

            .orders-tabs-scroll .btn-outline {
                white-space: nowrap;
                flex-shrink: 0;
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .orders-search-form {
                width: 100%;
                display: flex;
                gap: 6px;
            }

            .orders-search-input {
                width: 100% !important;
                flex: 1;
                min-width: 0;
            }

            .pagination-wrapper .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px;
            }
        }
    </style>
@endsection

@section('content')
    <div style="max-width: 1300px; margin: 0 auto;">

        <!-- Filter Navigation Tabs -->
        <div class="glass orders-filter-bar">
            <div class="orders-tabs-scroll">
                <a href="{{ route('admin.orders', array_filter(['search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ !$status ? 'background: var(--grad-insta); color: white;' : '' }}">
                    All Orders
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'pending', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'pending' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Pending
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'processing', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'processing' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Processing
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'in_progress', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'in_progress' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    In Progress
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'completed', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'completed' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Completed
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'partial', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'partial' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Partial
                </a>
                <a href="{{ route('admin.orders', array_filter(['status' => 'canceled', 'search' => $search, 'per_page' => $perPage])) }}"
                    class="btn-outline"
                    style="border: none; padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'canceled' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Canceled
                </a>
            </div>

            <form action="{{ route('admin.orders') }}" method="GET" class="orders-search-form">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <select name="per_page" class="form-control" onchange="this.form.submit()"
                    style="padding: 8px 10px; font-size: 0.85rem; width: auto; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer; flex-shrink: 0;">
                    <option value="10" {{ ($perPage ?? 20) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100 / page</option>
                    <option value="500" {{ ($perPage ?? 20) == 500 ? 'selected' : '' }}>500 / page</option>
                </select>
                <input type="text" name="search" class="form-control orders-search-input"
                    placeholder="ID, link, or client email..." value="{{ $search }}">
                <button type="submit" class="btn-gradient"
                    style="padding: 8px 12px; font-size: 0.85rem; flex-shrink: 0;">Search</button>
                @if($search)
                    <a href="{{ route('admin.orders', array_filter(['status' => $status, 'per_page' => $perPage])) }}"
                        class="btn-outline" style="padding: 8px 12px; font-size: 0.85rem; flex-shrink: 0;">Clear</a>
                @endif
            </form>
        </div>

        <!-- Orders Grid Block -->
        <div class="glass custom-card" style="margin-bottom: 2rem;">
            <h3 class="card-title"><i class="fa-solid fa-cart-shopping"></i> Global Platform Orders</h3>

            @if($orders->count() > 0)
                <!-- Desktop Table View (visible on > 768px) -->
                <div class="table-responsive desktop-only-table">
                    <table class="custom-table" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Client / Service</th>
                                <th>Date</th>
                                <th>Target Link</th>
                                <th>Quant / Cost</th>
                                <th>Start / Remains</th>
                                <th>Status</th>
                                <th style="text-align: right; width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $ord)
                                <tr>
                                    <td style="font-weight: bold; color: var(--text-muted);">
                                        #{{ $ord->id }}
                                        @if($ord->provider_order_id)
                                            <div style="font-size: 0.75rem; color: var(--color-info); font-weight: 600; margin-top: 2px;"
                                                title="Provider API Order ID">
                                                API #{{ $ord->provider_order_id }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $ord->user ? $ord->user->name : 'Deleted Client' }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            ID {{ $ord->service_id }} -
                                            {{ $ord->service ? $ord->service->name : 'Deleted Service' }}
                                            @if($ord->service && $ord->service->provider_service_id)
                                                <span style="color: var(--color-info); font-size: 0.7rem;"
                                                    title="Provider Service ID">(API Srv
                                                    #{{ $ord->service->provider_service_id }}{{ $ord->service->provider ? ' [' . $ord->service->provider->name . ']' : '' }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="font-weight: 500; color: var(--text-primary); font-size: 0.82rem;">
                                            <i class="fa-regular fa-calendar" style="color: var(--color-info); font-size: 0.75rem;"></i>
                                            {{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : 'N/A' }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;"
                                            title="Last Status / Details Update: {{ $ord->updated_at ? $ord->updated_at->format('d M Y, h:i A') : '' }}">
                                            <i class="fa-regular fa-clock" style="font-size: 0.68rem;"></i> Updated: {{ $ord->updated_at ? $ord->updated_at->diffForHumans() : 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ $ord->link }}" target="_blank"
                                            style="color: var(--color-info); text-decoration: none; word-break: break-all;">
                                            {{ Str::limit($ord->link, 35) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>Qty: {{ number_format($ord->quantity) }}</div>
                                        <div style="font-weight: bold;">₹{{ format_currency($ord->charge) }}</div>
                                    </td>
                                    <td style="color: var(--text-secondary);">
                                        {{ number_format($ord->start_count) }} / {{ number_format($ord->remains) }}
                                    </td>
                                    <td>
                                        @if($ord->status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($ord->status === 'processing')
                                            <span class="badge badge-processing">Processing</span>
                                        @elseif($ord->status === 'in_progress')
                                            <span class="badge badge-inprogress">In Progress</span>
                                        @elseif($ord->status === 'completed')
                                            <span class="badge badge-completed">Completed</span>
                                        @elseif($ord->status === 'partial')
                                            <span class="badge badge-partial">Partial</span>
                                        @elseif($ord->status === 'failed')
                                            <span class="badge badge-canceled">Failed</span>
                                        @else
                                            <span class="badge badge-canceled">Canceled</span>
                                        @endif

                                        @if($ord->provider_order_id)
                                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px;">Order id:
                                                #{{ $ord->provider_order_id }}</div>
                                        @elseif($ord->latestLog && $ord->latestLog->error_message)
                                            <div style="font-size: 0.7rem; color: var(--color-danger); margin-top: 3px; font-weight: 500;"
                                                title="{{ $ord->latestLog->error_message }}">
                                                <i class="fa-solid fa-triangle-exclamation"></i> API Error:
                                                {{ Str::limit($ord->latestLog->error_message, 25) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                            <button onclick="showOrderDetails(this)" class="btn-outline" data-id="{{ $ord->id }}"
                                                data-client="{{ $ord->user ? $ord->user->name . ' (' . $ord->user->email . ')' : 'Deleted Client' }}"
                                                data-service="ID {{ $ord->service_id }} - {{ $ord->service ? $ord->service->name : 'Deleted Service' }}"
                                                data-link="{{ $ord->link }}" data-qty="{{ number_format($ord->quantity) }}"
                                                data-charge="₹{{ format_currency($ord->charge) }}"
                                                data-start="{{ number_format($ord->start_count) }}"
                                                data-end="{{ number_format($ord->start_count + $ord->quantity) }}"
                                                data-remains="{{ number_format($ord->remains) }}"
                                                data-provider-id="{{ $ord->provider_order_id ? '#' . $ord->provider_order_id : 'N/A' }}"
                                                data-status="{{ $ord->status }}"
                                                data-created-at="{{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : 'N/A' }}"
                                                data-updated-at="{{ $ord->updated_at ? $ord->updated_at->format('d M Y, h:i A') . ' (' . $ord->updated_at->diffForHumans() . ')' : 'N/A' }}"
                                                data-error-msg="{{ ($ord->latestLog && $ord->latestLog->error_message) ? $ord->latestLog->error_message : '' }}"
                                                style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); border-color: var(--color-info); color: var(--color-info);">
                                                View
                                            </button>
                                            <button onclick="selectOrderForEdit(this)" class="btn-outline" data-id="{{ $ord->id }}"
                                                data-status="{{ $ord->status }}" data-start="{{ $ord->start_count }}"
                                                data-remains="{{ $ord->remains }}"
                                                data-provider-id="{{ $ord->provider_order_id ?: '' }}"
                                                data-charge="{{ $ord->charge }}" data-qty="{{ $ord->quantity }}"
                                                data-refunded="{{ $ord->refunded ? 'true' : 'false' }}"
                                                data-user-name="{{ $ord->user ? $ord->user->name : 'Deleted Client' }}"
                                                style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Compact Order Cards (visible on <= 768px) -->
                <div class="mobile-only-orders">
                    @foreach($orders as $ord)
                        <div class="order-mobile-card glass"
                            style="padding: 12px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="font-size: 0.88rem; color: var(--text-primary);">#{{ $ord->id }}</strong>
                                    @if($ord->provider_order_id)
                                        <span
                                            style="font-size: 0.7rem; color: var(--color-info); background: rgba(59, 130, 246, 0.1); padding: 1px 6px; border-radius: 4px;">API
                                            #{{ $ord->provider_order_id }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($ord->status === 'pending')
                                        <span class="badge badge-pending" style="font-size: 0.68rem; padding: 2px 8px;">Pending</span>
                                    @elseif($ord->status === 'processing')
                                        <span class="badge badge-processing"
                                            style="font-size: 0.68rem; padding: 2px 8px;">Processing</span>
                                    @elseif($ord->status === 'in_progress')
                                        <span class="badge badge-inprogress" style="font-size: 0.68rem; padding: 2px 8px;">In
                                            Progress</span>
                                    @elseif($ord->status === 'completed')
                                        <span class="badge badge-completed"
                                            style="font-size: 0.68rem; padding: 2px 8px;">Completed</span>
                                    @elseif($ord->status === 'partial')
                                        <span class="badge badge-partial" style="font-size: 0.68rem; padding: 2px 8px;">Partial</span>
                                    @elseif($ord->status === 'failed')
                                        <span class="badge badge-canceled" style="font-size: 0.68rem; padding: 2px 8px;">Failed</span>
                                    @else
                                        <span class="badge badge-canceled" style="font-size: 0.68rem; padding: 2px 8px;">Canceled</span>
                                    @endif
                                </div>
                            </div>

                            <div style="font-size: 0.8rem; color: var(--text-primary); font-weight: 600; margin-bottom: 2px;">
                                {{ $ord->user ? $ord->user->name : 'Deleted Client' }}
                            </div>
                            <div
                                style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 4px; word-break: break-word;">
                                ID {{ $ord->service_id }} - {{ $ord->service ? $ord->service->name : 'Deleted Service' }}
                            </div>

                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span><i class="fa-regular fa-calendar-check" style="color: var(--color-info);"></i> {{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : 'N/A' }}</span>
                                <span>•</span>
                                <span title="{{ $ord->updated_at ? $ord->updated_at->format('d M Y, h:i A') : '' }}"><i class="fa-regular fa-clock"></i> Updated: {{ $ord->updated_at ? $ord->updated_at->diffForHumans() : 'N/A' }}</span>
                            </div>

                            <div
                                style="font-size: 0.72rem; color: var(--color-info); word-break: break-all; margin-bottom: 8px; font-family: monospace;">
                                <i class="fa-solid fa-link" style="font-size: 0.68rem;"></i> {{ Str::limit($ord->link, 45) }}
                            </div>

                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; font-size: 0.7rem; color: var(--text-muted); background: rgba(0,0,0,0.2); padding: 6px 8px; border-radius: var(--radius-sm); margin-bottom: 8px; text-align: center;">
                                <div><span style="color: var(--text-secondary); display: block;">Qty</span>
                                    <strong>{{ number_format($ord->quantity) }}</strong></div>
                                <div><span style="color: var(--text-secondary); display: block;">Charge</span> <strong
                                        style="color: var(--color-info);">₹{{ format_currency($ord->charge) }}</strong></div>
                                <div><span style="color: var(--text-secondary); display: block;">Start</span>
                                    <strong>{{ number_format($ord->start_count) }}</strong></div>
                                <div><span style="color: var(--text-secondary); display: block;">Remains</span>
                                    <strong>{{ number_format($ord->remains) }}</strong></div>
                            </div>

                            @if($ord->latestLog && $ord->latestLog->error_message)
                                <div style="font-size: 0.7rem; color: var(--color-danger); margin-bottom: 8px; font-style: italic;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ Str::limit($ord->latestLog->error_message, 80) }}
                                </div>
                            @endif

                            <div style="display: flex; gap: 8px;">
                                <button onclick="showOrderDetails(this)" class="btn-outline" data-id="{{ $ord->id }}"
                                    data-client="{{ $ord->user ? $ord->user->name . ' (' . $ord->user->email . ')' : 'Deleted Client' }}"
                                    data-service="ID {{ $ord->service_id }} - {{ $ord->service ? $ord->service->name : 'Deleted Service' }}"
                                    data-link="{{ $ord->link }}" data-qty="{{ number_format($ord->quantity) }}"
                                    data-charge="₹{{ format_currency($ord->charge) }}"
                                    data-start="{{ number_format($ord->start_count) }}"
                                    data-remains="{{ number_format($ord->remains) }}"
                                    data-provider-id="{{ $ord->provider_order_id ? '#' . $ord->provider_order_id : 'N/A' }}"
                                    data-status="{{ $ord->status }}"
                                    data-created-at="{{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : 'N/A' }}"
                                    data-updated-at="{{ $ord->updated_at ? $ord->updated_at->format('d M Y, h:i A') . ' (' . $ord->updated_at->diffForHumans() . ')' : 'N/A' }}"
                                    data-error-msg="{{ ($ord->latestLog && $ord->latestLog->error_message) ? $ord->latestLog->error_message : '' }}"
                                    style="flex: 1; padding: 6px; font-size: 0.75rem; text-align: center; border-color: var(--color-info); color: var(--color-info);">
                                    View Details
                                </button>
                                <button onclick="selectOrderForEdit(this)" class="btn-outline" data-id="{{ $ord->id }}"
                                    data-status="{{ $ord->status }}" data-start="{{ $ord->start_count }}"
                                    data-remains="{{ $ord->remains }}" data-provider-id="{{ $ord->provider_order_id ?: '' }}"
                                    data-charge="{{ $ord->charge }}" data-qty="{{ $ord->quantity }}"
                                    data-refunded="{{ $ord->refunded ? 'true' : 'false' }}"
                                    data-user-name="{{ $ord->user ? $ord->user->name : 'Deleted Client' }}"
                                    style="flex: 1; padding: 6px; font-size: 0.75rem; text-align: center;">
                                    Edit Order
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pagination-wrapper">
                    {{ $orders->links() }}
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                    No orders registered in the system yet.
                </div>
            @endif
        </div>

        <!-- Edit Order Panel Block -->
        <div class="glass custom-card" id="editOrderPanel">
            <h3 class="card-title" id="editOrderTitle"><i class="fa-solid fa-file-pen"></i> Update Order</h3>

            <div id="noOrderSelected" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <i class="fa-solid fa-cart-flatbed-suitcases"
                    style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Select an order from the list on the left to modify status, start counts, or trigger client balance
                    refunds.</p>
            </div>

            <!-- Edit Form -->
            <form id="editOrderForm" action="" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="order_id" id="edit_ord_id">

                <div class="form-group">
                    <label class="form-label">Order Reference ID</label>
                    <input type="text" id="edit_ord_ref" class="form-control" readonly
                        style="background: rgba(255,255,255,0.01); color: var(--text-muted);">
                </div>

                <div class="form-group">
                    <label for="edit_ord_status" class="form-label">Order Status</label>
                    <select name="status" id="edit_ord_status" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="partial">Partial (Refund Remaining Items)</option>
                        <option value="canceled">Canceled (Full Refund)</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="edit_ord_start" class="form-label">Start Count</label>
                        <input type="number" name="start_count" id="edit_ord_start" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_ord_rem" class="form-label">Remains Count</label>
                        <input type="number" name="remains" id="edit_ord_rem" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_ord_prov_id" class="form-label">Provider API Order ID (Optional)</label>
                    <input type="text" name="provider_order_id" id="edit_ord_prov_id" class="form-control"
                        placeholder="e.g. SMM provider reference order id">
                </div>

                <div id="refundNoticeCard"
                    style="display: none; background: rgba(239,68,68,0.04); border: 1px dashed rgba(239,68,68,0.2); border-radius: var(--radius-md); padding: 12px; margin-bottom: 2rem; font-size: 0.85rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-exclamation" style="color: var(--color-danger); margin-right: 5px;"></i>
                    <span id="refundNoticeText">Warning: Saving as Canceled will return ₹0.00 to the client's balance
                        wallet.</span>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="cancelOrderEdit()" class="btn-outline" style="flex: 1; padding: 10px;">
                        Cancel
                    </button>
                    <button type="submit" class="btn-gradient" style="flex: 2; padding: 10px;">
                        Save Order Details
                    </button>
                </div>
            </form>

            <!-- Manual Retry Block -->
            <div id="retryOrderSection"
                style="display: none; background: rgba(255,255,255,0.02); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 15px; margin-top: 1.5rem;">
                <h5 style="font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);"><i
                        class="fa-solid fa-rotate-right text-gradient"></i> Resend to Provider API</h5>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 12px;">If order placement failed
                    or timed out, click below to try routing it again.</p>
                <form id="retryOrderForm" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn-gradient"
                        style="padding: 10px 20px; font-size: 0.85rem; width: 100%; font-weight: bold;">
                        Manual Retry Placement
                    </button>
                </form>
            </div>

            <!-- Order Processing Logs -->
            <div id="orderLogsSection"
                style="display: none; margin-top: 1.5rem; border-top: 1px dashed var(--border-color); padding-top: 1.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 10px; color: var(--text-primary);"><i
                        class="fa-solid fa-receipt text-gradient"></i> Provider Dispatch Logs</h4>
                <div id="logsContainer"
                    style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto; padding: 4px;">
                    <!-- Appended dynamically -->
                </div>
            </div>
        </div>

        @push('modals')
            <!-- Order Details View Modal -->
            <div id="orderViewDetailsModal" class="custom-modal" onclick="if(event.target===this) closeOrderDetailsModal()">
                <div class="custom-modal-content glass animate-fade-in"
                    style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 90%; max-width: 600px; max-height: 91vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 0;">
                    <div class="custom-modal-header"
                        style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="custom-modal-title"
                            style="margin: 0; font-size: 1.2rem; font-weight: bold; color: var(--text-primary);"><i
                                class="fa-solid fa-receipt text-gradient"></i> Order #<span id="view_ord_id"></span> Details
                        </h3>
                        <button class="custom-modal-close" onclick="closeOrderDetailsModal()"
                            style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
                    </div>
                    <div class="custom-modal-body"
                        style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Client</div>
                                <div style="font-weight: 600; color: var(--text-primary);" id="view_ord_client"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Status</div>
                                <div id="view_ord_status_badge"></div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: rgba(0,0,0,0.1); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                            <div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;"><i class="fa-regular fa-calendar" style="color: var(--color-info);"></i> Created Date</div>
                                <div style="font-weight: 500; color: var(--text-primary); font-size: 0.85rem;" id="view_ord_created_at"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;"><i class="fa-regular fa-clock"></i> Last Updated</div>
                                <div style="font-weight: 500; color: var(--text-primary); font-size: 0.85rem;" id="view_ord_updated_at"></div>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Service</div>
                            <div style="font-weight: 500; color: var(--text-primary); font-size: 0.9rem;" id="view_ord_service">
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Target Link
                            </div>
                            <a href="" id="view_ord_link" target="_blank"
                                style="color: var(--color-info); text-decoration: none; word-break: break-all; font-size: 0.85rem;"></a>
                        </div>

                        <div
                            style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: rgba(0,0,0,0.15); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Quantity
                                </div>
                                <div style="font-weight: bold; color: var(--text-primary);" id="view_ord_qty"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Charge
                                </div>
                                <div style="font-weight: bold; color: var(--color-success);" id="view_ord_charge"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Start Count
                                </div>
                                <div style="color: var(--text-primary);" id="view_ord_start"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">End Count
                                </div>
                                <div style="color: var(--color-success); font-weight: bold;" id="view_ord_end"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Remains
                                </div>
                                <div style="color: var(--text-primary);" id="view_ord_remains"></div>
                            </div>
                        </div>

                        <div>
                            <div
                                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">
                                Provider Details</div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                Provider Order ID: <span id="view_ord_provider_id"
                                    style="font-weight: 600; color: var(--text-primary);"></span>
                            </div>
                        </div>

                        <div id="view_ord_error_container"
                            style="display: none; background: rgba(239,68,68,0.08); border: 1px dashed rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 12px; color: #ef4444; font-size: 0.85rem;">
                            <h5 style="margin: 0 0 5px 0; font-weight: bold; font-size: 0.85rem;"><i
                                    class="fa-solid fa-triangle-exclamation"></i> Latest API Error Details:</h5>
                            <p id="view_ord_error_message"
                                style="margin: 0; font-family: monospace; white-space: pre-line; word-break: break-word; line-height: 1.4;">
                            </p>
                        </div>

                        <div style="border-top: 1px dashed var(--border-color); padding-top: 1rem;">
                            <h5 style="margin: 0 0 8px 0; font-size: 0.9rem; font-weight: 700; color: var(--text-primary);"><i
                                    class="fa-solid fa-receipt text-gradient"></i> API Log History</h5>
                            <div id="view_ord_logs_container"
                                style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                                <!-- Appended dynamically -->
                            </div>
                        </div>
                    </div>
                    <div class="custom-modal-footer"
                        style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn-outline" style="padding: 8px 18px;"
                            onclick="closeOrderDetailsModal()">Close</button>
                    </div>
                </div>
            </div>
        @endpush

    </div>
@endsection

@section('scripts')
    <script>
        let activeOrderData = null;

        function showOrderDetails(btn) {
            const id = btn.getAttribute('data-id');
            const client = btn.getAttribute('data-client');
            const service = btn.getAttribute('data-service');
            const link = btn.getAttribute('data-link');
            const qty = btn.getAttribute('data-qty');
            const charge = btn.getAttribute('data-charge');
            const start = btn.getAttribute('data-start');
            const end = btn.getAttribute('data-end');
            const remains = btn.getAttribute('data-remains');
            const providerId = btn.getAttribute('data-provider-id');
            const status = btn.getAttribute('data-status');
            const createdAt = btn.getAttribute('data-created-at');
            const updatedAt = btn.getAttribute('data-updated-at');
            const errorMsg = btn.getAttribute('data-error-msg');

            document.getElementById('view_ord_id').innerText = id;
            document.getElementById('view_ord_client').innerText = client;
            document.getElementById('view_ord_service').innerText = service;
            document.getElementById('view_ord_created_at').innerText = createdAt || 'N/A';
            document.getElementById('view_ord_updated_at').innerText = updatedAt || 'N/A';

            const linkTag = document.getElementById('view_ord_link');
            linkTag.href = link;
            linkTag.innerText = link;

            document.getElementById('view_ord_qty').innerText = qty;
            document.getElementById('view_ord_charge').innerText = charge;
            document.getElementById('view_ord_start').innerText = start;
            document.getElementById('view_ord_end').innerText = end || (parseInt((start || '').replace(/,/g, '')) + parseInt((qty || '').replace(/,/g, ''))).toLocaleString();
            document.getElementById('view_ord_remains').innerText = remains;
            document.getElementById('view_ord_provider_id').innerText = providerId;

            // Status Badge
            const statusBadgeContainer = document.getElementById('view_ord_status_badge');
            let badgeHTML = '';
            if (status === 'pending') badgeHTML = '<span class="badge badge-pending">Pending</span>';
            else if (status === 'processing') badgeHTML = '<span class="badge badge-processing">Processing</span>';
            else if (status === 'in_progress') badgeHTML = '<span class="badge badge-inprogress">In Progress</span>';
            else if (status === 'completed') badgeHTML = '<span class="badge badge-completed">Completed</span>';
            else if (status === 'partial') badgeHTML = '<span class="badge badge-partial">Partial</span>';
            else if (status === 'failed') badgeHTML = '<span class="badge badge-canceled">Failed</span>';
            else badgeHTML = '<span class="badge badge-canceled">Canceled</span>';
            statusBadgeContainer.innerHTML = badgeHTML;

            // Error Message
            const errorContainer = document.getElementById('view_ord_error_container');
            const errorMessage = document.getElementById('view_ord_error_message');
            if (errorMsg) {
                errorMessage.innerText = errorMsg;
                errorContainer.style.display = 'block';
            } else {
                errorContainer.style.display = 'none';
            }

            // Fetch logs for the modal
            const logsContainer = document.getElementById('view_ord_logs_container');
            logsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 10px;">Loading API logs...</div>';

            // Show modal
            document.getElementById('orderViewDetailsModal').classList.add('show');

            fetch(`/admin/orders/${id}/logs`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        logsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 10px;">No logs recorded.</div>';
                        return;
                    }

                    logsContainer.innerHTML = '';

                    const latestLog = data[0];
                    if (latestLog && latestLog.error_message) {
                        errorMessage.innerText = latestLog.error_message;
                        errorContainer.style.display = 'block';
                    }

                    data.forEach(log => {
                        const block = document.createElement('div');
                        block.style.background = 'rgba(255,255,255,0.02)';
                        block.style.border = '1px solid var(--border-color)';
                        block.style.borderRadius = 'var(--radius-sm)';
                        block.style.padding = '8px';
                        block.style.fontSize = '0.75rem';

                        let providerName = log.provider ? log.provider.name : 'Unknown Reseller';
                        let actionBadge = `<span class="badge" style="background: rgba(59,130,246,0.1); color: #3b82f6; text-transform: uppercase; font-size: 0.62rem; padding: 1px 4px;">${log.action}</span>`;
                        if (log.action === 'error' || log.action === 'failover_switch') {
                            actionBadge = `<span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444; text-transform: uppercase; font-size: 0.62rem; padding: 1px 4px;">${log.action}</span>`;
                        }

                        let payloadDetails = '';
                        if (log.error_message) {
                            payloadDetails = `<div style="color: var(--color-danger); margin-top: 4px; font-family: monospace; font-size: 0.7rem; word-break: break-all;">Error: ${log.error_message}</div>`;
                        } else if (log.response_payload) {
                            payloadDetails = `<div style="color: var(--color-success); margin-top: 4px; font-family: monospace; font-size: 0.7rem; word-break: break-all;">Response: ${JSON.stringify(log.response_payload)}</div>`;
                        }

                        block.innerHTML = `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <strong>${providerName}</strong>
                                    <div>${actionBadge}</div>
                                </div>
                                <div style="color: var(--text-secondary); font-size: 0.7rem;">Latency: ${log.response_time_ms}ms | Retries: ${log.retry_count}</div>
                                ${payloadDetails}
                            `;
                        logsContainer.appendChild(block);
                    });
                })
                .catch(err => {
                    logsContainer.innerHTML = '<div style="color: var(--color-danger); font-size: 0.8rem; text-align: center; padding: 10px;">Failed to fetch logs.</div>';
                });
        }

        function closeOrderDetailsModal() {
            document.getElementById('orderViewDetailsModal').classList.remove('show');
        }

        function selectOrderForEdit(btn) {
            const id = btn.getAttribute('data-id');
            const status = btn.getAttribute('data-status');
            const start = btn.getAttribute('data-start');
            const remains = btn.getAttribute('data-remains');
            const providerId = btn.getAttribute('data-provider-id');

            activeOrderData = {
                id: id,
                status: status,
                start_count: start,
                remains: remains,
                provider_order_id: providerId,
                charge: parseFloat(btn.getAttribute('data-charge')),
                quantity: parseInt(btn.getAttribute('data-qty')),
                refunded: btn.getAttribute('data-refunded') === 'true',
                user: {
                    name: btn.getAttribute('data-user-name')
                }
            };

            document.getElementById('noOrderSelected').style.display = 'none';

            const form = document.getElementById('editOrderForm');
            form.style.display = 'block';

            // Populate inputs
            document.getElementById('edit_ord_id').value = id;
            document.getElementById('edit_ord_ref').value = 'Order #' + id;
            document.getElementById('edit_ord_status').value = status;
            document.getElementById('edit_ord_start').value = start;
            document.getElementById('edit_ord_rem').value = remains;
            document.getElementById('edit_ord_prov_id').value = providerId;

            // Update action route
            form.action = `/admin/orders/${id}/update`;

            document.getElementById('editOrderTitle').innerText = 'Edit Order: #' + id;

            // Configure Retry Button
            const retrySection = document.getElementById('retryOrderSection');
            const retryForm = document.getElementById('retryOrderForm');
            if (status === 'pending' || status === 'processing') {
                retrySection.style.display = 'block';
                retryForm.action = `/admin/orders/${id}/retry`;
            } else {
                retrySection.style.display = 'none';
            }

            // Fetch logs via AJAX
            fetchOrderLogs(id);

            // Register listener for status changes to show refund indicators
            document.getElementById('edit_ord_status').removeEventListener('change', checkRefundNotice);
            document.getElementById('edit_ord_status').addEventListener('change', checkRefundNotice);
            checkRefundNotice();

            // Scroll
            document.getElementById('editOrderPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function fetchOrderLogs(orderId) {
            const logsSection = document.getElementById('orderLogsSection');
            const logsContainer = document.getElementById('logsContainer');
            logsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 10px;">Loading API histories...</div>';
            logsSection.style.display = 'block';

            fetch(`/admin/orders/${orderId}/logs`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        logsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 10px;">No provider transactions recorded.</div>';
                        return;
                    }

                    logsContainer.innerHTML = '';
                    data.forEach(log => {
                        const block = document.createElement('div');
                        block.style.background = 'rgba(255,255,255,0.02)';
                        block.style.border = '1px solid var(--border-color)';
                        block.style.borderRadius = 'var(--radius-sm)';
                        block.style.padding = '10px';
                        block.style.fontSize = '0.75rem';

                        let providerName = log.provider ? log.provider.name : 'Unknown Reseller';
                        let actionBadge = `<span class="badge" style="background: rgba(59,130,246,0.1); color: #3b82f6; text-transform: uppercase; font-size: 0.65rem;">${log.action}</span>`;
                        if (log.action === 'error' || log.action === 'failover_switch') {
                            actionBadge = `<span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444; text-transform: uppercase; font-size: 0.65rem;">${log.action}</span>`;
                        }

                        let payloadDetails = '';
                        if (log.error_message) {
                            payloadDetails = `<div style="color: var(--color-danger); margin-top: 5px; font-family: monospace;">Error: ${log.error_message}</div>`;
                        } else if (log.response_payload) {
                            payloadDetails = `<div style="color: var(--color-success); margin-top: 5px; font-family: monospace;">Response: ${JSON.stringify(log.response_payload)}</div>`;
                        }

                        block.innerHTML = `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <strong>${providerName}</strong>
                                    <div>${actionBadge}</div>
                                </div>
                                <div style="color: var(--text-secondary);">Latency: ${log.response_time_ms}ms | Retries: ${log.retry_count}</div>
                                ${payloadDetails}
                            `;
                        logsContainer.appendChild(block);
                    });
                })
                .catch(err => {
                    logsContainer.innerHTML = '<div style="color: var(--color-danger); font-size: 0.8rem; text-align: center; padding: 10px;">Failed to fetch logs.</div>';
                });
        }

        function checkRefundNotice() {
            if (!activeOrderData) return;
            const statusValue = document.getElementById('edit_ord_status').value;
            const noticeCard = document.getElementById('refundNoticeCard');
            const noticeText = document.getElementById('refundNoticeText');
            const charge = parseFloat(activeOrderData.charge);

            if (activeOrderData.refunded) {
                noticeCard.style.display = 'none';
                return;
            }

            if (statusValue === 'canceled') {
                noticeText.innerText = `Warning: Saving as Canceled will return the full charge of ₹${charge.toFixed(2)} to ${activeOrderData.user ? activeOrderData.user.name : 'client'} balance.`;
                noticeCard.style.display = 'block';
            } else if (statusValue === 'partial') {
                const quantity = parseInt(activeOrderData.quantity);
                const remainsInput = parseInt(document.getElementById('edit_ord_rem').value) || 0;
                const partialRefund = (charge / quantity) * remainsInput;
                noticeText.innerText = `Warning: Saving as Partial will refund remaining ${remainsInput} items. User wallet will be credited with ₹${partialRefund.toFixed(4)}.`;
                noticeCard.style.display = 'block';
            } else {
                noticeCard.style.display = 'none';
            }
        }

        // Check refund values if remains changes too
        document.getElementById('edit_ord_rem').removeEventListener('input', checkRefundNotice);
        document.getElementById('edit_ord_rem').addEventListener('input', checkRefundNotice);

        // Restore scroll position if saved
        const savedAdminOrdScrollY = sessionStorage.getItem('admin_orders_scroll_y');
        if (savedAdminOrdScrollY !== null) {
            window.scrollTo(0, parseInt(savedAdminOrdScrollY, 10));
            sessionStorage.removeItem('admin_orders_scroll_y');
        }

        document.addEventListener('submit', function(e) {
            sessionStorage.setItem('admin_orders_scroll_y', window.scrollY);
        });

        function cancelOrderEdit() {
            document.getElementById('editOrderForm').style.display = 'none';
            document.getElementById('noOrderSelected').style.display = 'block';
            document.getElementById('editOrderTitle').innerText = 'Update Order';
            document.getElementById('refundNoticeCard').style.display = 'none';
            document.getElementById('retryOrderSection').style.display = 'none';
            document.getElementById('orderLogsSection').style.display = 'none';
            activeOrderData = null;
        }
    </script>
@endsection