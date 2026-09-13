@extends('layouts.app')

@section('title', 'My Orders - Growinsta')
@section('page_header', 'Order Logs')

@section('content')
    <div style="max-width: 1100px; margin: 0 auto;">

        <!-- Filter Navigation Tabs & Search Input Bar -->
        <div class="glass"
            style="border-radius: var(--radius-md); padding: 12px; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
            
            <!-- Search Input Box -->
            <form action="{{ route('orders.history') }}" method="GET" style="display: flex; gap: 8px; flex: 1; min-width: 250px; margin: 0;">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div style="position: relative; width: 100%; display: flex; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; color: var(--text-muted); font-size: 0.9rem; pointer-events: none;"></i>
                    <input type="text" id="orderSearchInput" name="search" class="form-control"
                           placeholder="Search order ID (#100976), link, or service..."
                           value="{{ request('search') }}"
                           style="padding-left: 38px; padding-right: 32px; font-size: 0.88rem; height: 42px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-primary); width: 100%;">
                    @if(request('search'))
                        <a href="{{ route('orders.history', array_filter(['status' => $status])) }}" style="position: absolute; right: 12px; color: var(--text-muted); text-decoration: none; font-size: 0.85rem;" title="Clear Search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
                <button type="submit" class="btn-gradient" style="padding: 0 16px; height: 42px; font-size: 0.88rem; border-radius: var(--radius-sm); white-space: nowrap; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; box-shadow: none;">
                    Search
                </button>
            </form>

            <!-- Status Tabs -->
            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                <a href="{{ route('orders.history', array_filter(['search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ !$status ? 'background: var(--grad-insta); color: white;' : '' }}">
                    All Orders
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'pending', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'pending' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Pending
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'processing', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'processing' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Processing
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'in_progress', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'in_progress' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    In Progress
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'completed', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'completed' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Completed
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'partial', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'partial' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Partial
                </a>
                <a href="{{ route('orders.history', array_filter(['status' => 'canceled', 'search' => request('search')])) }}" class="btn-outline"
                    style="border: none; padding: 8px 14px; font-size: 0.85rem; border-radius: var(--radius-sm); {{ $status === 'canceled' ? 'background: var(--grad-insta); color: white;' : '' }}">
                    Canceled
                </a>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="glass custom-card">
            <h3 class="card-title"><i class="fa-solid fa-list-check"></i> Orders History</h3>

            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">ID</th>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Link</th>
                                <th>Quantity Details</th>
                                <th>Charge</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}" data-can-refill="{{ $order->canRefill() ? '1' : '0' }}">
                                    <td style="font-weight: bold; color: var(--text-muted); vertical-align: top; padding-top: 12px;"
                                        class="order-id-cell">
                                        <span class="local-id-display">#{{ $order->id }}</span>
                                        <div class="api-ref-display"
                                            style="font-size: 0.72rem; font-weight: normal; color: var(--text-muted); margin-top: 4px; white-space: nowrap; {{ $order->provider_order_id ? '' : 'display: none;' }}">
                                            Order id: #<span class="api-id-val">{{ $order->provider_order_id }}</span>
                                        </div>
                                        <div class="refill-btn-container"
                                            style="{{ $order->canRefill() ? '' : 'display: none;' }}">
                                            <form action="{{ route('orders.refill', $order->id) }}" method="POST"
                                                style="margin-top: 6px;"
                                                onsubmit="return confirm('Send refill request for this order?')">
                                                @csrf
                                                <button type="submit" class="btn-gradient"
                                                    style="padding: 4px 8px; font-size: 0.72rem; border-radius: var(--radius-sm); background: var(--grad-emerald); color: white; display: inline-flex; align-items: center; gap: 4px; border: none; font-weight: bold; width: 100%; justify-content: center; box-shadow: none; cursor: pointer;">
                                                    REFILL
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <div class="created-time">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                        <div class="updated-time"
                                            style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;"
                                            data-order-updated-at>
                                            Updated: {{ $order->updated_at->diffForHumans() }}
                                        </div>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-primary);">
                                            ID {{ $order->service_id }} - {{ $order->service ? $order->service->name : 'Deleted Service' }}
                                        </div>
                                        @if(!empty($order->comments))
                                            <div style="margin-top: 4px;">
                                                <span class="badge" style="background: rgba(147, 51, 234, 0.15); color: #c084fc; border: 1px solid rgba(147, 51, 234, 0.3); font-size: 0.72rem; padding: 2px 6px; display: inline-flex; align-items: center; gap: 4px;" title="{{ e($order->comments) }}">
                                                    💬 {{ count(array_filter(preg_split('/\r\n|\r|\n/', $order->comments))) }} Comments
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $order->link }}" target="_blank"
                                            style="color: var(--color-info); text-decoration: none; word-break: break-all; font-size: 0.85rem;">
                                            {{ Str::limit($order->link, 45) }}
                                        </a>
                                    </td>
                                    <td data-order-qty-details data-quantity="{{ $order->quantity }}">
                                        <div style="font-size: 0.82rem; line-height: 1.4;">
                                            <div><strong>Qty:</strong> <span
                                                    class="qty-display">{{ number_format($order->quantity) }}</span></div>
                                            <div style="color: var(--text-secondary);"><strong>Remains:</strong> <span
                                                    class="remains-display">{{ number_format($order->remains) }}</span></div>

                                            <!-- Real-time Progress Bar -->
                                            @php
                                                $progress = 0;
                                                if ($order->quantity > 0) {
                                                    $delivered = $order->quantity - $order->remains;
                                                    $progress = max(0, min(100, ($delivered / $order->quantity) * 100));
                                                }
                                                $showProgress = ($order->start_count > 0 || in_array($order->status, ['processing', 'in_progress', 'completed', 'partial']));
                                            @endphp
                                            <div class="progress-bar-container"
                                                style="background: rgba(255,255,255,0.05); height: 4px; border-radius: 2px; margin-top: 6px; overflow: hidden; {{ $showProgress ? '' : 'display: none;' }}">
                                                <div class="progress-bar-fill"
                                                    style="background: var(--grad-primary); height: 100%; width: {{ $progress }}%; transition: width 0.5s ease;">
                                                </div>
                                            </div>

                                            <div class="start-end-container"
                                                style="color: var(--text-muted); font-size: 0.76rem; margin-top: 4px; border-top: 1px dashed rgba(255,255,255,0.08); padding-top: 2px; {{ $showProgress ? '' : 'display: none;' }}">
                                                Start: <span
                                                    class="start-display">{{ number_format($order->start_count) }}</span><br>
                                                End: <span
                                                    class="end-display">{{ number_format($order->start_count + $order->quantity) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 600;">₹{{ format_currency($order->charge) }}</td>
                                    <td class="status-cell">
                                        @php
                                            $badgeClass = 'badge-pending';
                                            $pulseClass = '';
                                            if ($order->status === 'processing') {
                                                $badgeClass = 'badge-processing';
                                                $pulseClass = 'status-pulse';
                                            } elseif ($order->status === 'in_progress') {
                                                $badgeClass = 'badge-inprogress';
                                                $pulseClass = 'status-pulse';
                                            } elseif ($order->status === 'completed') {
                                                $badgeClass = 'badge-completed';
                                            } elseif ($order->status === 'partial') {
                                                $badgeClass = 'badge-partial';
                                            } elseif ($order->status === 'canceled') {
                                                $badgeClass = 'badge-canceled';
                                            } elseif ($order->status === 'failed') {
                                                $badgeClass = 'badge-canceled';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} {{ $pulseClass }}" data-order-status-badge>
                                            {{ $order->status === 'in_progress' ? 'In Progress' : ucfirst($order->status) }}
                                        </span>

                                        @if($order->canCancel())
                                            <div class="cancel-btn-container" style="margin-top: 6px;">
                                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST"
                                                    style="margin: 0;"
                                                    onsubmit="return confirm('Cancel Order #{{ $order->id }} and get a full refund of ₹{{ number_format($order->charge, 2) }} to your wallet?');">
                                                    @csrf
                                                    <button type="submit" class="btn-gradient"
                                                        style="padding: 4px 8px; font-size: 0.72rem; border-radius: var(--radius-sm); background: #ef4444; color: white; display: inline-flex; align-items: center; gap: 4px; border: none; font-weight: bold; width: 100%; justify-content: center; box-shadow: none; cursor: pointer;"
                                                        title="Cancel order and refund to wallet">
                                                        <i class="fa-solid fa-xmark"></i> CANCEL
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Custom Pagination Links -->
                <div style="margin-top: 2rem; display: flex; justify-content: center;">
                    {{ $orders->links() }}
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <p>No orders found under this status.</p>
                </div>
            @endif
        </div>

    </div>

    <div id="connLostBanner"
        style="display: none; background: #dc2626; color: white; padding: 12px 24px; border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.2); font-weight: 600; font-size: 0.9rem; align-items: center; gap: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); position: fixed; bottom: 20px; right: 20px; z-index: 1100;">
        <i class="fa-solid fa-triangle-exclamation animate-pulse"></i> Connection Lost. Retrying when online...
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let pollTimeoutId = null;
            let pollingStartTime = null;
            let currentAbortController = null;
            let isTabActive = true;
            let isOnline = navigator.onLine;

            function getActiveOrders() {
                return document.querySelectorAll('tr[data-order-id][data-order-status="pending"], tr[data-order-id][data-order-status="processing"], tr[data-order-id][data-order-status="in_progress"]');
            }

            function timeAgo(dateString) {
                const date = new Date(dateString);
                const seconds = Math.floor((new Date() - date) / 1000);
                if (seconds < 10) return 'Just now';
                if (seconds < 60) return seconds + 's ago';
                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return minutes + 'm ago';
                const hours = Math.floor(minutes / 60);
                if (hours < 24) return hours + 'h ago';
                return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            }

            function updateUI(orders) {
                orders.forEach(ord => {
                    const tr = document.querySelector('tr[data-order-id="' + ord.id + '"]');
                    if (!tr) return;

                    const oldStatus = tr.getAttribute('data-order-status');
                    const newStatus = ord.status;

                    // Update dynamic values (Remains & Qty details)
                    const remainsDisplay = tr.querySelector('.remains-display');
                    if (remainsDisplay) {
                        remainsDisplay.textContent = Number(ord.remains).toLocaleString();
                    }

                    // Update Order id if it gets updated
                    if (ord.provider_order_id) {
                        const apiRefEl = tr.querySelector('.api-ref-display');
                        const apiIdVal = tr.querySelector('.api-id-val');
                        if (apiRefEl && apiIdVal) {
                            apiIdVal.textContent = ord.provider_order_id;
                            apiRefEl.style.display = '';
                        }
                    }

                    // If remains or start_count changes, compute and update progress bar + start/end containers
                    const quantity = parseInt(tr.querySelector('[data-order-qty-details]').getAttribute('data-quantity') || 0);
                    const showProgress = (ord.start_count > 0 || ['processing', 'in_progress', 'completed', 'partial'].includes(newStatus));

                    const startDisplay = tr.querySelector('.start-display');
                    const endDisplay = tr.querySelector('.end-display');
                    if (startDisplay) startDisplay.textContent = Number(ord.start_count).toLocaleString();
                    if (endDisplay) endDisplay.textContent = Number(ord.start_count + quantity).toLocaleString();

                    const startEndContainer = tr.querySelector('.start-end-container');
                    if (startEndContainer) {
                        startEndContainer.style.display = showProgress ? '' : 'none';
                    }

                    const progressBarContainer = tr.querySelector('.progress-bar-container');
                    const progressBarFill = tr.querySelector('.progress-bar-fill');
                    if (progressBarContainer && progressBarFill) {
                        progressBarContainer.style.display = showProgress ? '' : 'none';
                        if (quantity > 0) {
                            const delivered = quantity - ord.remains;
                            const progress = Math.max(0, Math.min(100, (delivered / quantity) * 100));
                            progressBarFill.style.width = progress + '%';
                        }
                    }

                    // Update updated_at time
                    const updatedTimeEl = tr.querySelector('[data-order-updated-at]');
                    if (updatedTimeEl) {
                        updatedTimeEl.textContent = 'Updated: ' + timeAgo(ord.updated_at);
                    }

                    // Check status change
                    if (oldStatus !== newStatus) {
                        tr.setAttribute('data-order-status', newStatus);

                        const badge = tr.querySelector('[data-order-status-badge]');
                        if (badge) {
                            // Clear old classes
                            badge.className = 'badge';

                            let badgeClass = 'badge-pending';
                            let pulseClass = '';

                            if (newStatus === 'processing') {
                                badgeClass = 'badge-processing';
                                pulseClass = 'status-pulse';
                            } else if (newStatus === 'in_progress') {
                                badgeClass = 'badge-inprogress';
                                pulseClass = 'status-pulse';
                            } else if (newStatus === 'completed') {
                                badgeClass = 'badge-completed';
                                pulseClass = 'status-completed-flash';
                                tr.classList.add('row-completed-flash');
                            } else if (newStatus === 'partial') {
                                badgeClass = 'badge-partial';
                            } else if (newStatus === 'canceled' || newStatus === 'failed') {
                                badgeClass = 'badge-canceled';
                            }

                            badge.classList.add(badgeClass);
                            if (pulseClass) badge.classList.add(pulseClass);

                            badge.textContent = newStatus === 'in_progress' ? 'In Progress' : newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        }

                        // Show refill button if service supports refill
                        const canRefill = tr.dataset.canRefill === '1';
                        const refillContainer = tr.querySelector('.refill-btn-container');
                        if (refillContainer) {
                            if (canRefill && ['completed', 'partial'].includes(newStatus)) {
                                refillContainer.style.display = '';
                            } else {
                                refillContainer.style.display = 'none';
                            }
                        }
                    }
                });

                // Stop polling if no active orders left
                if (getActiveOrders().length === 0) {
                    stopPolling();
                }
            }

            function performPoll() {
                if (currentAbortController) {
                    currentAbortController.abort();
                }

                currentAbortController = new AbortController();
                const signal = currentAbortController.signal;

                fetch('{{ route("api.orders.active-status") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: signal
                })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data.orders) {
                            updateUI(data.orders);
                        }
                        scheduleNextPoll();
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            console.warn('Adaptive polling warning:', err.message);
                            scheduleNextPoll();
                        }
                    });
            }

            function scheduleNextPoll() {
                if (pollTimeoutId) clearTimeout(pollTimeoutId);
                if (!isTabActive || !isOnline) return;

                const activeOrders = getActiveOrders();
                if (activeOrders.length === 0) {
                    stopPolling();
                    return;
                }

                let elapsedSeconds = (Date.now() - pollingStartTime) / 1000;
                let interval = 5000; // 5s standard

                if (elapsedSeconds > 600) {
                    interval = 30000; // 30s after 10m
                } else if (elapsedSeconds > 120) {
                    interval = 10000; // 10s after 2m
                }

                pollTimeoutId = setTimeout(performPoll, interval);
            }

            function startPolling() {
                if (pollTimeoutId) return;

                const activeOrders = getActiveOrders();
                if (activeOrders.length === 0) return;

                pollingStartTime = Date.now();
                scheduleNextPoll();
            }

            function stopPolling() {
                if (pollTimeoutId) {
                    clearTimeout(pollTimeoutId);
                    pollTimeoutId = null;
                }
                if (currentAbortController) {
                    currentAbortController.abort();
                    currentAbortController = null;
                }
            }

            // Listen for visibility change
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    isTabActive = false;
                    stopPolling();
                } else {
                    isTabActive = true;
                    startPolling();
                }
            });

            // Listen for network changes
            window.addEventListener('online', () => {
                isOnline = true;
                document.getElementById('connLostBanner').style.display = 'none';
                startPolling();
            });

            window.addEventListener('offline', () => {
                isOnline = false;
                document.getElementById('connLostBanner').style.display = 'flex';
                stopPolling();
            });

            // Real-time Search Input Filter for Order Rows
            const orderSearchInput = document.getElementById('orderSearchInput');
            if (orderSearchInput) {
                orderSearchInput.addEventListener('keyup', function () {
                    const query = this.value.toLowerCase().trim().replace(/^#/, '');
                    const rows = document.querySelectorAll('.custom-table tbody tr');
                    rows.forEach(row => {
                        const text = row.innerText.toLowerCase();
                        if (query === '' || text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Restore scroll position if saved
            const savedUserOrdScrollY = sessionStorage.getItem('user_orders_scroll_y');
            if (savedUserOrdScrollY !== null) {
                window.scrollTo(0, parseInt(savedUserOrdScrollY, 10));
                sessionStorage.removeItem('user_orders_scroll_y');
            }

            document.addEventListener('submit', function(e) {
                sessionStorage.setItem('user_orders_scroll_y', window.scrollY);
            });

            // Initialize adaptive polling
            startPolling();
        });
    </script>
@endsection