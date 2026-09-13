@extends('layouts.app')

@section('title', 'API Providers - RishiSMM')
@section('page_header', 'Reseller API Providers')

@section('styles')
<style>
    .desktop-only-table {
        display: block;
    }
    .mobile-only-providers {
        display: none;
    }
    .modal-desktop-view {
        display: block;
    }
    .modal-mobile-view {
        display: none;
    }

    @media (max-width: 768px) {
        .desktop-only-table {
            display: none !important;
        }
        .mobile-only-providers {
            display: flex !important;
            flex-direction: column;
            gap: 1rem;
        }
        .modal-desktop-view {
            display: none !important;
        }
        .modal-mobile-view {
            display: flex !important;
            flex-direction: column;
        }
        #servicesModal {
            padding: 8px 4px !important;
        }
        #servicesModal > div {
            max-height: 96vh !important;
        }
        .modal-header-flex {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
            padding: 1rem !important;
        }
        #modalServiceSearch {
            width: 100% !important;
        }
        #modalImportControls {
            padding: 1rem !important;
        }
        .modal-controls-grid {
            flex-direction: column !important;
            gap: 10px !important;
        }
        #modalServicesBody {
            padding: 8px !important;
            max-height: 55vh !important;
        }
    }
</style>
@endsection

@section('content')
    <div style="max-width: 1100px; margin: 0 auto;">

        <!-- Add / Edit Provider Form -->
        <div class="glass custom-card" style="margin-bottom: 2rem;">
            <h3 class="card-title" id="formTitle"><i class="fa-solid fa-plug text-gradient"></i> Add Provider</h3>

            <form action="{{ route('admin.providers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="prov_id">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_name" class="form-label">Provider Name</label>
                        <input type="text" name="name" id="prov_name" class="form-control" placeholder="e.g. SMMBulk"
                            required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_url" class="form-label">API Endpoint URL</label>
                        <input type="url" name="api_url" id="prov_url" class="form-control"
                            placeholder="https://providerpanel.com/api/v2" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_key" class="form-label">API Access Key</label>
                        <input type="password" name="api_key" id="prov_key" class="form-control"
                            placeholder="Insert provider API key" autocomplete="new-password" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_status" class="form-label">Provider Status</label>
                        <select name="status" id="prov_status" class="form-control" required>
                            <option value="active">Active (Sourcing Allowed)</option>
                            <option value="inactive">Inactive (Disabled)</option>
                        </select>
                    </div>
                </div>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 15px; margin-bottom: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_priority" class="form-label">Priority Order (1 = Highest)</label>
                        <input type="number" name="priority" id="prov_priority" class="form-control" value="1" min="1"
                            required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_timeout" class="form-label">API Timeout (Seconds)</label>
                        <input type="number" name="timeout" id="prov_timeout" class="form-control" value="10" min="5"
                            max="30" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_retry" class="form-label">Retry Count</label>
                        <input type="number" name="retry_count" id="prov_retry" class="form-control" value="3" min="1"
                            max="5" required>
                    </div>
                    <input type="hidden" name="currency" id="prov_currency" value="INR">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="prov_sync" class="form-label">Auto Sync Status</label>
                        <select name="auto_sync" id="prov_sync" class="form-control" required>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; max-width: 400px;">
                    <button type="button" id="cancelEditBtn" onclick="resetForm()" class="btn-outline"
                        style="flex: 1; padding: 10px; display: none;">
                        Cancel Edit
                    </button>
                    <button type="submit" class="btn-gradient" style="flex: 2; padding: 12px; font-weight: bold;">
                        Save Provider
                    </button>
                </div>
            </form>
        </div>

        <!-- API Providers List -->
        <div class="glass custom-card">
            <h3 class="card-title"><i class="fa-solid fa-network-wired"></i> Registered Providers</h3>

            @if($providers->count() > 0)
                <!-- Desktop Table View (visible on > 768px) -->
                <div class="table-responsive desktop-only-table">
                    <table class="custom-table" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Provider Name & Health Logs</th>
                                <th>Cached Balance</th>
                                <th>Status</th>
                                <th style="text-align: right; width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $prov)
                                <tr>
                                    <td style="font-weight: bold; color: var(--text-muted);">#{{ $prov->id }}</td>
                                    <td>
                                        <div style="font-weight: 400; color: var(--text-primary);">{{ $prov->name }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary); word-break: break-all;">URL:
                                            {{ $prov->api_url }}</div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">
                                            Priority: {{ $prov->priority }} | Timeout: {{ $prov->timeout }}s | Retries:
                                            {{ $prov->retry_count }} | Sync: {{ $prov->auto_sync ? 'Auto' : 'Manual' }}
                                        </div>
                                        @if($prov->last_sync_success)
                                            <div style="font-size: 0.7rem; color: var(--color-success); margin-top: 2px;"><i
                                                    class="fa-solid fa-circle-check"></i> Last Successful Check:
                                                {{ $prov->last_sync_success }} (Latency: {{ $prov->response_time_ms }}ms)</div>
                                        @endif
                                        @if($prov->failed_requests_count > 0 || ($prov->last_sync_failed && (!$prov->last_sync_success || $prov->last_sync_failed > $prov->last_sync_success)))
                                            <div style="font-size: 0.7rem; color: var(--color-danger); margin-top: 2px;" @if($prov->last_sync_error) title="{{ $prov->last_sync_error }}" @endif>
                                                <i class="fa-solid fa-triangle-exclamation"></i> Last Failed Check:
                                                {{ $prov->last_sync_failed }} (Sync Failures: {{ $prov->failed_requests_count }})
                                                @if($prov->last_sync_error)
                                                    <div style="font-style: italic; margin-top: 2px; font-weight: 500; font-family: monospace; word-break: break-all;">
                                                        Error: {{ Str::limit($prov->last_sync_error, 100) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td style="font-weight: bold; color: var(--color-info);">
                                        ₹{{ number_format($prov->balance, 2) }}
                                    </td>
                                    <td>
                                        @if($prov->status === 'active')
                                            <span class="badge badge-completed">Active</span>
                                        @else
                                            <span class="badge badge-canceled">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div
                                            style="display: flex; gap: 6px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                            <!-- View Services Button -->
                                            <button
                                                onclick="fetchProviderServices({{ $prov->id }}, '{{ addslashes($prov->name) }}')"
                                                class="btn-gradient"
                                                style="padding: 6px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-purple); white-space: nowrap;"
                                                title="View all available services from this provider's API">
                                                <i class="fa-solid fa-list"></i> View
                                            </button>

                                            <!-- Sync All Services Button -->
                                            <button onclick="toggleSyncForm({{ $prov->id }})" class="btn-gradient"
                                                style="padding: 6px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-emerald); white-space: nowrap;"
                                                title="Import all services from this provider's API">
                                                <i class="fa-solid fa-cloud-arrow-down"></i> Sync
                                            </button>

                                            <!-- Refresh Balance Action -->
                                            <form action="{{ route('admin.providers.balance', $prov->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn-outline"
                                                    style="padding: 6px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);"
                                                    title="Sync Balance">
                                                    <i class="fa-solid fa-arrows-rotate"></i>
                                                </button>
                                            </form>

                                            <button type="button" class="btn-outline edit-provider-btn" data-provider="{{ json_encode($prov->only(['id', 'name', 'api_url', 'status', 'priority', 'timeout', 'retry_count', 'auto_sync', 'currency'])) }}"
                                                style="padding: 6px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                                Edit
                                            </button>

                                            <form action="{{ route('admin.providers.delete', $prov->id) }}" method="POST"
                                                onsubmit="return confirm('Remove this SMM API provider? Existing services linked to it will revert to manual fulfillment.')">
                                                @csrf
                                                <button type="submit" class="btn-gradient"
                                                    style="padding: 6px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-danger);">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                        <!-- Sync Services Inline Form (hidden by default) -->
                                        <div id="syncForm_{{ $prov->id }}"
                                            style="display: none; margin-top: 10px; padding: 12px; background: rgba(0,0,0,0.2); border: 1px dashed var(--border-color); border-radius: var(--radius-sm);">
                                            <form action="{{ route('admin.providers.sync_services', $prov->id) }}" method="POST"
                                                onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Syncing...';">
                                                @csrf
                                                <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                                                    <div style="flex: 1; min-width: 120px;">
                                                        <label
                                                            style="font-size: 0.72rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">Profit
                                                            Margin (%)</label>
                                                        <input type="number" name="profit_margin"
                                                            value="{{ \App\Models\Setting::get('profit_margin', 50) }}" min="-100"
                                                            max="10000" class="form-control"
                                                            style="padding: 6px 10px; font-size: 0.8rem; height: auto; margin: 0;"
                                                            required>
                                                    </div>
                                                    <button type="submit" class="btn-gradient"
                                                        style="padding: 6px 14px; font-size: 0.78rem; border-radius: var(--radius-sm); background: var(--grad-emerald); white-space: nowrap;">
                                                        <i class="fa-solid fa-cloud-arrow-down"></i> Import All Services
                                                    </button>
                                                    <button type="button" onclick="toggleSyncForm({{ $prov->id }})"
                                                        class="btn-outline"
                                                        style="padding: 6px 10px; font-size: 0.78rem; border-radius: var(--radius-sm);">
                                                        Cancel
                                                    </button>
                                                </div>
                                                <p style="font-size: 0.7rem; color: var(--text-muted); margin: 6px 0 0;">
                                                    Auto-creates categories from provider. Already imported services will be
                                                    skipped. 50% margin = ₹10 cost → ₹15 selling.</p>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (visible on <= 768px) -->
                <div class="mobile-only-providers">
                    @foreach($providers as $prov)
                        <div class="provider-mobile-card glass" style="padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                            <!-- Provider Header: Name, ID & Status -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div>
                                    <span style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted); display: inline-block; margin-right: 6px;">#{{ $prov->id }}</span>
                                    <strong style="font-size: 1.05rem; color: var(--text-primary);">{{ $prov->name }}</strong>
                                </div>
                                <div>
                                    @if($prov->status === 'active')
                                        <span class="badge badge-completed" style="font-size: 0.75rem;">Active</span>
                                    @else
                                        <span class="badge badge-canceled" style="font-size: 0.75rem;">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Balance & URL -->
                            <div style="background: rgba(0,0,0,0.2); padding: 10px 12px; border-radius: var(--radius-sm); margin-bottom: 0.75rem; border: 1px solid rgba(255,255,255,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <span style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 600;">Cached Balance:</span>
                                    <span style="font-size: 1.05rem; font-weight: 800; color: var(--color-info);">₹{{ number_format($prov->balance, 2) }}</span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary); word-break: break-all; font-family: monospace;">
                                    <i class="fa-solid fa-link" style="font-size: 0.7rem; margin-right: 4px;"></i> {{ $prov->api_url }}
                                </div>
                            </div>

                            <!-- Parameters Grid -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; background: rgba(255,255,255,0.01); padding: 8px 10px; border-radius: var(--radius-sm);">
                                <div>Priority: <strong style="color: var(--text-primary);">{{ $prov->priority }}</strong></div>
                                <div>Timeout: <strong style="color: var(--text-primary);">{{ $prov->timeout }}s</strong></div>
                                <div>Retries: <strong style="color: var(--text-primary);">{{ $prov->retry_count }}</strong></div>
                                <div>Sync Mode: <strong style="color: var(--text-primary);">{{ $prov->auto_sync ? 'Auto' : 'Manual' }}</strong></div>
                            </div>

                            <!-- Health Status Alerts -->
                            @if($prov->last_sync_success)
                                <div style="font-size: 0.72rem; color: var(--color-success); margin-bottom: 6px; background: rgba(16, 185, 129, 0.08); padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 185, 129, 0.2);">
                                    <i class="fa-solid fa-circle-check"></i> Last Successful Check: {{ $prov->last_sync_success }} (Latency: {{ $prov->response_time_ms }}ms)
                                </div>
                            @endif

                            @if($prov->failed_requests_count > 0 || ($prov->last_sync_failed && (!$prov->last_sync_success || $prov->last_sync_failed > $prov->last_sync_success)))
                                <div style="font-size: 0.72rem; color: var(--color-danger); margin-bottom: 6px; background: rgba(239, 68, 68, 0.08); padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(239, 68, 68, 0.2);" @if($prov->last_sync_error) title="{{ $prov->last_sync_error }}" @endif>
                                    <i class="fa-solid fa-triangle-exclamation"></i> Last Failed Check: {{ $prov->last_sync_failed }} (Sync Failures: {{ $prov->failed_requests_count }})
                                    @if($prov->last_sync_error)
                                        <div style="font-style: italic; margin-top: 2px; font-weight: 500; font-family: monospace; word-break: break-all;">
                                            Error: {{ Str::limit($prov->last_sync_error, 100) }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Buttons Grid -->
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 10px;">
                                <button onclick="fetchProviderServices({{ $prov->id }}, '{{ addslashes($prov->name) }}')" class="btn-gradient" style="padding: 8px 10px; font-size: 0.78rem; border-radius: var(--radius-sm); background: var(--grad-purple); text-align: center;">
                                    <i class="fa-solid fa-list"></i> View
                                </button>

                                <button onclick="toggleSyncForm({{ $prov->id }})" class="btn-gradient" style="padding: 8px 10px; font-size: 0.78rem; border-radius: var(--radius-sm); background: var(--grad-emerald); text-align: center;">
                                    <i class="fa-solid fa-cloud-arrow-down"></i> Sync
                                </button>

                                <form action="{{ route('admin.providers.balance', $prov->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-outline" style="padding: 8px 10px; font-size: 0.78rem; width: 100%; text-align: center; border-radius: var(--radius-sm);" title="Sync Balance">
                                        <i class="fa-solid fa-arrows-rotate"></i> Balance
                                    </button>
                                </form>

                                <button type="button" class="btn-outline edit-provider-btn" data-provider="{{ json_encode($prov->only(['id', 'name', 'api_url', 'status', 'priority', 'timeout', 'retry_count', 'auto_sync', 'currency'])) }}" style="padding: 8px 10px; font-size: 0.78rem; text-align: center; border-radius: var(--radius-sm);">
                                    Edit
                                </button>

                                <form action="{{ route('admin.providers.delete', $prov->id) }}" method="POST" onsubmit="return confirm('Remove this SMM API provider? Existing services linked to it will revert to manual fulfillment.')" style="margin: 0; grid-column: span 2;">
                                    @csrf
                                    <button type="submit" class="btn-gradient" style="padding: 8px 10px; font-size: 0.78rem; width: 100%; text-align: center; border-radius: var(--radius-sm); background: var(--grad-danger);">
                                        Delete Provider
                                    </button>
                                </form>
                            </div>

                            <!-- Sync Services Inline Form (Mobile) -->
                            <div id="syncFormMobile_{{ $prov->id }}" style="display: none; margin-top: 10px; padding: 12px; background: rgba(0,0,0,0.3); border: 1px dashed var(--border-color); border-radius: var(--radius-sm);">
                                <form action="{{ route('admin.providers.sync_services', $prov->id) }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Syncing...';">
                                    @csrf
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <div>
                                            <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">Profit Margin (%)</label>
                                            <input type="number" name="profit_margin" value="{{ \App\Models\Setting::get('profit_margin', 50) }}" min="-100" max="10000" class="form-control" style="padding: 8px 12px; font-size: 0.85rem;" required>
                                        </div>
                                        <div style="display: flex; gap: 8px;">
                                            <button type="submit" class="btn-gradient" style="flex: 2; padding: 8px 12px; font-size: 0.8rem; background: var(--grad-emerald); border-radius: var(--radius-sm);">
                                                <i class="fa-solid fa-cloud-arrow-down"></i> Import All Services
                                            </button>
                                            <button type="button" onclick="toggleSyncForm({{ $prov->id }})" class="btn-outline" style="flex: 1; padding: 8px 12px; font-size: 0.8rem; border-radius: var(--radius-sm);">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                    No external SMM API providers registered. Create one above!
                </div>
            @endif
        </div>

        @push('modals')
        <div id="servicesModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); z-index: 9999; overflow-y: auto; padding: 2rem;"
            onclick="if(event.target===this) closeServicesModal()">
            <div
                style="max-width: 1100px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
                <!-- Modal Header -->
                <div class="modal-header-flex"
                    style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2);">
                    <div>
                        <h3 id="modalProviderName" style="font-size: 1.1rem; font-weight: 700; margin: 0;"><i
                                class="fa-solid fa-cloud-arrow-down text-gradient"></i> Provider Services</h3>
                        <p id="modalServiceCount" style="font-size: 0.8rem; color: var(--text-secondary); margin: 4px 0 0;">
                            Loading...</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="modalServiceSearch" placeholder="Search services..." class="form-control"
                            style="width: 220px; padding: 8px 12px; font-size: 0.85rem; margin: 0;"
                            oninput="filterModalServices()">
                        <button onclick="closeServicesModal()"
                            style="background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; padding: 5px 10px; line-height: 1;"
                            title="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Selective Import Control Panel -->
                <div id="modalImportControls" style="display: none; background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border-color); padding: 1.25rem 1.75rem;">
                    <!-- Master Select All Checkbox Bar (Always visible on Mobile & Desktop) -->
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.25); padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 12px; border: 1px solid var(--border-color);">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: bold; font-size: 0.9rem; color: var(--text-primary); margin: 0;">
                            <input type="checkbox" id="masterSelectAllHeader" onchange="toggleSelectAllModalSrv(this)" style="width: 18px; height: 18px; accent-color: var(--color-primary); cursor: pointer;">
                            <span>Select / Deselect All Services</span>
                        </label>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600;">Selected: <strong id="selectedCountHeader" class="text-gradient">0</strong></span>
                    </div>

                    <div class="modal-controls-grid" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                        <!-- Destination Category Dropdown -->
                        <div style="flex: 2; min-width: 200px;">
                            <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Target Category</label>
                            <select id="modal_target_category" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; height: auto; margin: 0; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <option value="auto">Auto-Create (Match Provider's Category)</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Profit Margin Input -->
                        <div style="flex: 1; min-width: 110px;">
                            <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Profit Margin (%)</label>
                            <input type="number" id="modal_profit_margin" value="{{ \App\Models\Setting::get('profit_margin', 50) }}" min="-100" max="10000" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; height: auto; margin: 0; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                        <!-- Submit Button -->
                        <div style="flex: 1.5; min-width: 170px;">
                            <button id="modalImportBtn" onclick="importSelectedServices()" class="btn-gradient" style="padding: 10px 15px; font-size: 0.85rem; width: 100%; font-weight: bold; background: var(--grad-emerald);" disabled>
                                <i class="fa-solid fa-cloud-arrow-down"></i> Import Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div id="modalServicesBody" style="padding: 1.5rem 2rem; max-height: 70vh; overflow-y: auto;">
                    <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>Fetching services from provider API...</p>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div
                    style="padding: 1rem 2rem; border-top: 1px solid var(--border-color); background: rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center;">
                    <span id="modalImportInfo" style="font-size: 0.8rem; color: var(--text-secondary);">To import these
                        services, use the <strong>Import from SMM API</strong> tab on the Services page.</span>
                    <a id="modalImportLink" href="{{ route('admin.services') }}" class="btn-gradient"
                        style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none;">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Go to Import Page
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Provider Modal Overlay -->
        <div id="editProviderModal" class="custom-modal" onclick="if(event.target===this) closeEditProviderModal()">
            <div class="custom-modal-content glass animate-fade-in" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 90%; max-width: 600px; max-height: 91vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 0;">
                <div class="custom-modal-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="custom-modal-title" style="margin: 0; font-size: 1.2rem; font-weight: bold; color: var(--text-primary);"><i class="fa-solid fa-pen-to-square text-gradient"></i> Edit API Provider</h3>
                    <button class="custom-modal-close" onclick="closeEditProviderModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
                </div>
                <form action="{{ route('admin.providers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="edit_prov_id">
                    <div class="custom-modal-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="edit_prov_name" class="form-label">Provider Name</label>
                                <input type="text" name="name" id="edit_prov_name" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                            <div class="form-group">
                                <label for="edit_prov_status" class="form-label">Provider Status</label>
                                <select name="status" id="edit_prov_status" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                                    <option value="active">Active (Sourcing Allowed)</option>
                                    <option value="inactive">Inactive (Disabled)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_prov_url" class="form-label">API Endpoint URL</label>
                            <input type="url" name="api_url" id="edit_prov_url" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <div class="form-group">
                            <label for="edit_prov_key" class="form-label">API Access Key</label>
                            <input type="password" name="api_key" id="edit_prov_key" class="form-control" autocomplete="new-password" placeholder="•••••••••••••••• (Leave blank to keep existing)" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: block;">Leave blank if you don't want to change the existing API key.</span>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div class="form-group">
                                <label for="edit_prov_priority" class="form-label">Priority Order</label>
                                <input type="number" name="priority" id="edit_prov_priority" class="form-control" min="1" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                            <div class="form-group">
                                <label for="edit_prov_timeout" class="form-label">API Timeout (Seconds)</label>
                                <input type="number" name="timeout" id="edit_prov_timeout" class="form-control" min="5" max="30" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_prov_retry" class="form-label">Retry Count</label>
                                <input type="number" name="retry_count" id="edit_prov_retry" class="form-control" min="1" max="5" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_prov_sync" class="form-label">Auto Sync Status</label>
                                <select name="auto_sync" id="edit_prov_sync" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="custom-modal-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn-outline" style="padding: 8px 18px;" onclick="closeEditProviderModal()">Cancel</button>
                        <button type="submit" class="btn-gradient" style="padding: 8px 25px; font-weight: bold; background: var(--grad-purple);">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endpush

    </div>
@endsection

@section('scripts')
    <script>
        function toggleSyncForm(providerId) {
            const form = document.getElementById('syncForm_' + providerId);
            if (form) form.style.display = form.style.display === 'none' ? 'block' : 'none';
            const mobileForm = document.getElementById('syncFormMobile_' + providerId);
            if (mobileForm) mobileForm.style.display = mobileForm.style.display === 'none' ? 'block' : 'none';
        }

        function editProvider(prov) {
            document.getElementById('edit_prov_id').value = prov.id;
            document.getElementById('edit_prov_name').value = prov.name;
            document.getElementById('edit_prov_url').value = prov.api_url;
            document.getElementById('edit_prov_key').value = '';
            document.getElementById('edit_prov_status').value = prov.status;

            document.getElementById('edit_prov_priority').value = prov.priority || 1;
            document.getElementById('edit_prov_timeout').value = prov.timeout || 10;
            document.getElementById('edit_prov_retry').value = prov.retry_count || 3;
            document.getElementById('edit_prov_sync').value = prov.auto_sync !== undefined ? (prov.auto_sync ? '1' : '0') : '1';

            const modal = document.getElementById('editProviderModal');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeEditProviderModal() {
            document.getElementById('editProviderModal').classList.remove('show');
            document.body.style.overflow = '';
        }

        function resetForm() {
            document.getElementById('prov_id').value = '';
            document.getElementById('prov_name').value = '';
            document.getElementById('prov_url').value = '';
            document.getElementById('prov_key').value = '';
            document.getElementById('prov_status').value = 'active';

            document.getElementById('prov_priority').value = 1;
            document.getElementById('prov_timeout').value = 10;
            document.getElementById('prov_retry').value = 3;
            // Currency is hardcoded to INR
            document.getElementById('prov_sync').value = '1';

            document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-plug text-gradient"></i> Add Provider';
            document.getElementById('cancelEditBtn').style.display = 'none';
        }
        // --- Provider Services Modal ---
        let allModalServices = [];
        let activeProviderId = null;

        function fetchProviderServices(providerId, providerName) {
            const modal = document.getElementById('servicesModal');
            const body = document.getElementById('modalServicesBody');
            const countEl = document.getElementById('modalServiceCount');
            const nameEl = document.getElementById('modalProviderName');
            const searchEl = document.getElementById('modalServiceSearch');
            const controls = document.getElementById('modalImportControls');

            // Reset
            searchEl.value = '';
            allModalServices = [];
            activeProviderId = providerId;
            if (controls) {
                controls.style.display = 'none';
                document.getElementById('selectedCount').textContent = '0';
                document.getElementById('modalImportBtn').setAttribute('disabled', 'true');
            }

            nameEl.innerHTML = '<i class="fa-solid fa-cloud-arrow-down text-gradient"></i> ' + providerName + ' — API Services';
            countEl.textContent = 'Loading...';
            body.innerHTML = '<div style="text-align: center; padding: 4rem; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Fetching services from provider API...</p></div>';
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';

            fetch('/admin/providers/' + providerId + '/services')
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        body.innerHTML = '<div style="text-align: center; padding: 4rem; color: var(--color-danger);"><i class="fa-solid fa-triangle-exclamation" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>' + data.error + '</p></div>';
                        countEl.textContent = 'Error fetching services';
                        return;
                    }

                    allModalServices = data.services || [];
                    const importedCount = allModalServices.filter(s => s.already_imported).length;
                    countEl.textContent = 'Total: ' + data.total + ' services | Currently Active: ' + importedCount + ' | Currency: INR';

                    const catSelect = document.getElementById('modal_target_category');
                    if (catSelect && data.categories) {
                        let catHtml = '<option value="auto">Auto-Create (Match Provider\'s Category)</option>';
                        data.categories.forEach(c => {
                            catHtml += '<option value="' + c.id + '">' + c.name + '</option>';
                        });
                        catSelect.innerHTML = catHtml;
                    }

                    if (controls) {
                        controls.style.display = 'block';
                    }

                    renderModalServices(allModalServices, '₹');
                    updateSelectedCount();
                })
                .catch(err => {
                    body.innerHTML = '<div style="text-align: center; padding: 4rem; color: var(--color-danger);"><i class="fa-solid fa-triangle-exclamation" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Network error: ' + err.message + '</p></div>';
                    countEl.textContent = 'Connection failed';
                });
        }

        function renderModalServices(services, currSymbol) {
            const body = document.getElementById('modalServicesBody');

            if (services.length === 0) {
                body.innerHTML = '<div style="text-align: center; padding: 4rem; color: var(--text-muted);"><i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>No services found matching your search.</p></div>';
                return;
            }

            // Desktop Table View (visible > 768px)
            let html = '<div class="table-responsive modal-desktop-view"><table class="custom-table" style="font-size: 0.82rem; margin: 0;"><thead><tr>';
            html += '<th style="width:40px;text-align:center;"><input type="checkbox" id="selectAllModalSrv" onchange="toggleSelectAllModalSrv(this)" style="accent-color: var(--color-primary); cursor: pointer;"></th>';
            html += '<th style="width:70px;">ID</th>';
            html += '<th>Service Name</th>';
            html += '<th>Category</th>';
            html += '<th>Rate/1K</th>';
            html += '<th>Min</th>';
            html += '<th>Max</th>';
            html += '<th style="width:90px;text-align:center;">Status</th>';
            html += '</tr></thead><tbody>';

            // Mobile Compact View (visible <= 768px)
            let mobileHtml = '<div class="modal-mobile-view" style="gap: 6px;">';

            services.forEach((srv, index) => {
                const imported = srv.already_imported;
                const isChecked = imported ? 'checked' : '';

                // Desktop Table Row
                html += '<tr class="modal-srv-row" data-name="' + (srv.name || '').toLowerCase() + '">';
                html += '<td style="text-align: center;">';
                html += '<input type="checkbox" class="modal-srv-checkbox" data-index="' + index + '" ' + isChecked + ' onchange="updateSelectedCount()" style="accent-color: var(--color-primary); cursor: pointer;">';
                html += '</td>';
                html += '<td style="font-weight:bold;color:var(--text-muted);">' + (srv.service || '-') + '</td>';
                html += '<td><div style="font-weight:400;color:var(--text-primary);">' + (srv.name || 'Unnamed') + '</div>';
                if (srv.description) {
                    html += '<div style="font-size:0.72rem;color:var(--text-secondary);margin-top:2px;max-width:350px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + srv.description + '</div>';
                }
                html += '</td>';
                html += '<td style="color:var(--text-secondary);font-size:0.78rem;">' + (srv.category || 'General') + '</td>';
                html += '<td style="font-weight:700;color:var(--color-success);">' + currSymbol + parseFloat(srv.rate || 0).toFixed(4) + '</td>';
                html += '<td>' + (srv.min || 0) + '</td>';
                html += '<td>' + (srv.max || 0) + '</td>';
                html += '<td style="text-align:center;">';
                if (imported) {
                    html += '<span class="badge badge-completed" style="font-size:0.7rem;">Active</span>';
                } else {
                    html += '<span class="badge badge-pending" style="font-size:0.7rem;">Available</span>';
                }
                html += '</td></tr>';

                // Mobile Card Row (Super Compact 55px height)
                mobileHtml += '<div class="modal-srv-row" data-name="' + (srv.name || '').toLowerCase() + '" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">';
                mobileHtml += '<div style="flex-shrink: 0;">';
                mobileHtml += '<input type="checkbox" class="modal-srv-checkbox" data-index="' + index + '" ' + isChecked + ' onchange="updateSelectedCount()" style="width: 18px; height: 18px; accent-color: var(--color-primary); cursor: pointer;">';
                mobileHtml += '</div>';
                mobileHtml += '<div style="flex: 1; min-width: 0;">';
                mobileHtml += '<div style="display: flex; justify-content: space-between; align-items: center; gap: 6px;">';
                mobileHtml += '<div style="display: flex; align-items: center; gap: 4px; overflow: hidden;">';
                mobileHtml += '<span style="font-size: 0.7rem; font-weight: bold; color: var(--text-muted); flex-shrink: 0;">#' + (srv.service || '-') + '</span>';
                mobileHtml += '<strong style="font-size: 0.8rem; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">' + (srv.name || 'Unnamed') + '</strong>';
                mobileHtml += '</div>';
                mobileHtml += '<span style="font-size: 0.85rem; font-weight: 800; color: var(--color-success); flex-shrink: 0;">' + currSymbol + parseFloat(srv.rate || 0).toFixed(4) + '</span>';
                mobileHtml += '</div>';
                mobileHtml += '<div style="display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-top: 2px;">';
                mobileHtml += '<span style="font-size: 0.68rem; color: var(--text-secondary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">' + (srv.category || 'General') + '</span>';
                mobileHtml += '<div style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; color: var(--text-muted); flex-shrink: 0;">';
                mobileHtml += '<span>Min: ' + (srv.min || 0) + ' | Max: ' + (srv.max || 0) + '</span>';
                if (imported) {
                    mobileHtml += '<span class="badge badge-completed" style="font-size:0.6rem; padding:1px 5px;">Active</span>';
                } else {
                    mobileHtml += '<span class="badge badge-pending" style="font-size:0.6rem; padding:1px 5px;">Available</span>';
                }
                mobileHtml += '</div></div></div></div>';
            });

            html += '</tbody></table></div>';
            mobileHtml += '</div>';

            body.innerHTML = html + mobileHtml;
        }

        function toggleSelectAllModalSrv(master) {
            const checkboxes = document.querySelectorAll('.modal-srv-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
            });

            const headerCb = document.getElementById('masterSelectAllHeader');
            if (headerCb && headerCb !== master) headerCb.checked = master.checked;
            const tableCb = document.getElementById('selectAllModalSrv');
            if (tableCb && tableCb !== master) tableCb.checked = master.checked;

            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.modal-srv-checkbox:checked');
            const totalCheckboxes = document.querySelectorAll('.modal-srv-checkbox');
            const count = checkboxes.length;

            const countEl = document.getElementById('selectedCount');
            if (countEl) countEl.textContent = count;
            const countHeaderEl = document.getElementById('selectedCountHeader');
            if (countHeaderEl) countHeaderEl.textContent = count;

            const btn = document.getElementById('modalImportBtn');
            if (btn) {
                if (count > 0) {
                    btn.removeAttribute('disabled');
                } else {
                    btn.setAttribute('disabled', 'true');
                }
            }

            const headerCb = document.getElementById('masterSelectAllHeader');
            if (headerCb && totalCheckboxes.length > 0) {
                headerCb.checked = (count === totalCheckboxes.length);
            }
        }

        function importSelectedServices() {
            const checkboxes = document.querySelectorAll('.modal-srv-checkbox:checked');
            if (checkboxes.length === 0) return;

            const selectedServices = [];
            checkboxes.forEach(cb => {
                const idx = parseInt(cb.getAttribute('data-index'));
                if (!isNaN(idx) && allModalServices[idx]) {
                    selectedServices.push(allModalServices[idx]);
                }
            });

            const profitMargin = document.getElementById('modal_profit_margin').value;
            const categoryMapping = document.getElementById('modal_target_category').value;
            const importBtn = document.getElementById('modalImportBtn');

            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

            fetch('/admin/providers/' + activeProviderId + '/import-selected-services', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    profit_margin: profitMargin,
                    category_mapping: categoryMapping,
                    services: selectedServices
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeServicesModal();
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to import services.');
                    importBtn.disabled = false;
                    importBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Import Selected (<span id="selectedCount">' + checkboxes.length + '</span>)';
                }
            })
            .catch(err => {
                alert('Connection error: ' + err.message);
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Import Selected (<span id="selectedCount">' + checkboxes.length + '</span>)';
            });
        }

        // Debounce helper to prevent input lag
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        const filterModalServices = debounce(function() {
            const query = document.getElementById('modalServiceSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.modal-srv-row');
            let visible = 0;
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                if (name.includes(query)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });
        }, 200);

        function closeServicesModal() {
            document.getElementById('servicesModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeServicesModal();
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-provider-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const prov = JSON.parse(this.getAttribute('data-provider'));
                    editProvider(prov);
                });
            });
        });
    </script>
@endsection