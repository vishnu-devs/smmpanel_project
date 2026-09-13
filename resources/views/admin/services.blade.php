@extends('layouts.app')

@section('title', 'Manage Services - Growinsta')
@section('page_header', 'Service Catalog Manager')

@section('styles')
<style>
    .tab-nav {
        display: flex;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .tab-btn {
        flex: 1;
        padding: 10px;
        text-align: center;
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }
    .tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div style="max-width: 1280px; margin: 0 auto;">

    <!-- Forms Configuration Block -->
    <div id="serviceEditorBlock" class="glass custom-card" style="margin-bottom: 2rem;">
            <!-- Tabs Header Navigation -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('manualForm')">Manual Add / Edit</button>
                <button class="tab-btn" onclick="switchTab('importForm')">Import from SMM API</button>
            </div>

            <!-- Tab 1: Manual Service Editor -->
            <div id="manualForm" class="tab-pane active">
                <h3 class="card-title" id="editorTitle" style="border: none; margin-bottom: 1rem; padding: 0;">
                    <i class="fa-solid fa-square-plus text-gradient"></i> Add New Service
                </h3>
                
                <form id="addServiceFormAjax" action="{{ route('admin.services.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="srv_id">

                    <div class="form-group">
                        <label for="srv_category" class="form-label">Category</label>
                        <select name="category_id" id="srv_category" class="form-control" required>
                            <option value="" disabled selected>-- Choose Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="srv_name" class="form-label">Service Name</label>
                        <input type="text" name="name" id="srv_name" class="form-control" placeholder="e.g. Instagram Followers [Real]" required>
                    </div>

                    <div class="form-group">
                        <label for="srv_rate" class="form-label">Client Selling Price per 1,000 (INR)</label>
                        <input type="number" name="price_per_k" id="srv_rate" class="form-control" placeholder="0.00" min="0" step="any" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="srv_min" class="form-label">Min Limit</label>
                            <input type="number" name="min_quantity" id="srv_min" class="form-control" value="100" required>
                        </div>
                        <div class="form-group">
                            <label for="srv_max" class="form-label">Max Limit</label>
                            <input type="number" name="max_quantity" id="srv_max" class="form-control" value="10000" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="srv_status" class="form-label">Service Status</label>
                            <select name="status" id="srv_status" class="form-control" required>
                                <option value="active">Active (Visible)</option>
                                <option value="inactive">Inactive (Hidden)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="srv_sort_order" class="form-label">Sort Order (Position #)</label>
                            <input type="number" name="sort_order" id="srv_sort_order" class="form-control" placeholder="e.g. 1" min="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="srv_avg_time" class="form-label">Average Time (e.g. 15 minutes, Instant)</label>
                        <input type="text" name="average_time" id="srv_avg_time" class="form-control" placeholder="e.g. 15 minutes">
                    </div>

                    <!-- API Sourcing Panel -->
                    <div style="border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 15px; margin-bottom: 1.5rem; background: rgba(0,0,0,0.15);">
                        <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; color: var(--color-info);">
                            <i class="fa-solid fa-network-wired"></i> Automated Reselling (API Sourcing)
                        </h4>
                        
                        <div class="form-group">
                            <label for="srv_provider" class="form-label" style="font-size: 0.8rem;">API Provider</label>
                            <select name="provider_id" id="srv_provider" class="form-control" style="padding: 8px 12px; font-size: 0.85rem;">
                                <option value="">Self-Fulfilled (Manual Order Handling)</option>
                                @foreach($providers as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="srv_prov_id" class="form-label" style="font-size: 0.8rem;">Provider Service ID</label>
                                <input type="text" name="provider_service_id" id="srv_prov_id" class="form-control" placeholder="e.g. 154" style="padding: 8px 12px; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="srv_prov_rate" class="form-label" style="font-size: 0.8rem;">Provider Buying Cost</label>
                                <input type="number" name="provider_rate" id="srv_prov_rate" class="form-control" placeholder="0.00" step="any" style="padding: 8px 12px; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="srv_desc" class="form-label">Service Description</label>
                        <textarea name="description" id="srv_desc" class="form-control" placeholder="Explain startup time, warranty details, guidelines..." rows="2"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="button" id="cancelBtn" onclick="resetServiceForm()" class="btn-outline" style="flex: 1; padding: 12px; display: none;">
                            Cancel
                        </button>
                        <button type="submit" class="btn-gradient" style="flex: 2; padding: 12px; font-weight: bold;">
                            Save Service
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 2: SMM Provider Bulk Fetch / Import -->
            <div id="importForm" class="tab-pane">
                <h3 class="card-title" style="border: none; margin-bottom: 1rem; padding: 0;">
                    <i class="fa-solid fa-cloud-arrow-down text-gradient"></i> Fetch API Services
                </h3>
                
                <form action="{{ route('admin.services.import') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="imp_provider" class="form-label">Choose API Provider</label>
                        <select name="provider_id" id="imp_provider" class="form-control" required>
                            <option value="" disabled selected>-- Select Provider API --</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imp_category" class="form-label">Destination Category</label>
                        <select name="category_id" id="imp_category" class="form-control" required>
                            <option value="" disabled selected>-- Choose Target Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imp_markup" class="form-label">Profit Markup Margin Percentage (%)</label>
                        <input type="number" name="profit_margin" id="imp_markup" class="form-control" value="{{ \App\Models\Setting::get('profit_margin', 50) }}" placeholder="e.g. 50 to add 50% profit margin" required>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">
                            If provider rate is ₹10.00, 50% markup sets your selling price to ₹15.00.
                        </span>
                    </div>

                    <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; background: var(--grad-purple); box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);">
                        Fetch & Import Services <i class="fa-solid fa-circle-down" style="margin-left: 8px;"></i>
                    </button>
                </form>
            </div>
        </div>

    <!-- Services Data List Block -->
    <div class="glass custom-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 10px;">
                <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-list-check"></i> Available Catalog ({{ method_exists($services, 'total') ? $services->total() : $services->count() }})</h3>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <form action="{{ route('admin.categories.clean_empty') }}" method="POST" onsubmit="return confirm('Permanently remove all empty categories (Total: 0 Services) and unused obsolete services to optimize database storage?');">
                        @csrf
                        <button type="submit" class="btn-gradient" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; background: var(--grad-danger); font-weight: bold; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Delete empty categories (0 services) and obsolete services with 0 orders">
                            <i class="fa-solid fa-trash-can"></i> Clean Empty Categories
                        </button>
                    </form>
                    <form action="{{ route('admin.services.sync_platform_statuses') }}" method="POST" onsubmit="return confirm('Synchronize active/inactive status of all services based on Enabled Platforms settings?')">
                        @csrf
                        <button type="submit" class="btn-outline" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; color: var(--color-primary); border-color: var(--color-primary); display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <i class="fa-solid fa-rotate"></i> Sync Enabled Platforms
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Search & Filter bar -->
            <form id="servicesFilterForm" action="{{ route('admin.services') }}" method="GET" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 10px;">
                <input type="text" name="search" id="srvSearchInput" class="form-control" placeholder="Search by service name, ID, or provider..." value="{{ $search ?? '' }}" style="flex: 1; min-width: 200px; padding: 10px 16px;">
                <select name="category_id" id="srvCategorySelect" class="form-control" style="flex: 0 0 180px; padding: 10px 16px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (isset($categoryId) && $categoryId == $cat->id) ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <select name="provider_id" id="srvProviderSelect" class="form-control" style="flex: 0 0 180px; padding: 10px 16px;">
                    <option value="">All Providers</option>
                    @foreach($providers as $p)
                        <option value="{{ $p->id }}" {{ (isset($providerId) && $providerId == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" id="srvStatusSelect" class="form-control" style="flex: 0 0 150px; padding: 10px 16px;">
                    <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ ($status ?? 'all') == 'active' ? 'selected' : '' }}>✅ Active Only</option>
                    <option value="inactive" {{ ($status ?? 'all') == 'inactive' ? 'selected' : '' }}>⛔ Inactive Only</option>
                </select>
                <button type="submit" class="btn-gradient" style="padding: 10px 20px;">Search</button>
                <a href="{{ route('admin.services') }}" id="srvClearBtn" class="btn-outline" style="padding: 10px 15px; {{ (request('search') || request('category_id') || request('provider_id') || (request('status') && request('status') !== 'all')) ? '' : 'display:none;' }}">Clear</a>
            </form>

            <div id="services-table-container">
                @include('admin.partials.services_table')
            </div>
    </div>

    @push('modals')
    <!-- Edit Service Modal Overlay -->
    <div id="editServiceModal" class="custom-modal" onclick="if(event.target===this) closeEditServiceModal()">
        <div class="custom-modal-content glass animate-fade-in" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 90%; max-width: 600px; max-height: 91vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 0;">
            <div class="custom-modal-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 class="custom-modal-title" style="margin: 0; font-size: 1.2rem; font-weight: bold; color: var(--text-primary);"><i class="fa-solid fa-pen-to-square text-gradient"></i> Edit Service Details</h3>
                <button class="custom-modal-close" onclick="closeEditServiceModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <form id="editServiceFormAjax" action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit_srv_id">
                <div class="custom-modal-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    <div class="form-group">
                        <label for="edit_srv_category" class="form-label">Category</label>
                        <select name="category_id" id="edit_srv_category" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="" disabled>-- Choose Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_srv_name" class="form-label">Service Name</label>
                        <input type="text" name="name" id="edit_srv_name" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                    </div>

                    <!-- Provider API Original Name Info Box -->
                    <div id="edit_srv_orig_box" style="display: none; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); padding: 10px 12px; border-radius: var(--radius-sm);">
                        <div style="font-size: 0.78rem; color: #60a5fa; margin-bottom: 4px;">
                            <i class="fa-solid fa-cloud"></i> <strong>Provider API Name:</strong> <span id="edit_srv_orig_name" style="color: var(--text-primary); font-style: italic;"></span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--text-secondary); cursor: pointer; margin: 0;">
                            <input type="checkbox" name="reset_original" value="1" style="accent-color: var(--color-primary); cursor: pointer;">
                            <span>Revert title back to Provider API Name</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="edit_srv_rate" class="form-label">Client Selling Price per 1,000 (INR)</label>
                        <input type="number" name="price_per_k" id="edit_srv_rate" class="form-control" min="0" step="any" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="edit_srv_min" class="form-label">Min Limit</label>
                            <input type="number" name="min_quantity" id="edit_srv_min" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                        <div class="form-group">
                            <label for="edit_srv_max" class="form-label">Max Limit</label>
                            <input type="number" name="max_quantity" id="edit_srv_max" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="edit_srv_status" class="form-label">Service Status</label>
                            <select name="status" id="edit_srv_status" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <option value="active">Active (Visible)</option>
                                <option value="inactive">Inactive (Hidden)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_srv_sort_order" class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_srv_sort_order" class="form-control" min="1" placeholder="e.g. 1" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                        <div class="form-group">
                            <label for="edit_srv_avg_time" class="form-label">Average Time</label>
                            <input type="text" name="average_time" id="edit_srv_avg_time" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                    </div>

                    <!-- API Sourcing Panel -->
                    <div style="border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 15px; background: rgba(0,0,0,0.15);">
                        <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; color: var(--color-info);">
                            <i class="fa-solid fa-network-wired"></i> Automated Reselling (API Sourcing)
                        </h4>
                        
                        <div class="form-group">
                            <label for="edit_srv_provider" class="form-label" style="font-size: 0.8rem;">API Provider</label>
                            <select name="provider_id" id="edit_srv_provider" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <option value="">Self-Fulfilled (Manual Order Handling)</option>
                                @foreach($providers as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_srv_prov_id" class="form-label" style="font-size: 0.8rem;">Provider Service ID</label>
                                <input type="text" name="provider_service_id" id="edit_srv_prov_id" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_srv_prov_rate" class="form-label" style="font-size: 0.8rem;">Provider Buying Cost</label>
                                <input type="number" name="provider_rate" id="edit_srv_prov_rate" class="form-control" step="any" style="padding: 8px 12px; font-size: 0.85rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_srv_desc" class="form-label">Service Description</label>
                        <textarea name="description" id="edit_srv_desc" class="form-control" rows="3" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);"></textarea>
                    </div>
                </div>
                <div class="custom-modal-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-outline" style="padding: 8px 18px;" onclick="closeEditServiceModal()">Cancel</button>
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
    function switchTab(tabId) {
        // Toggle active button
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('onclick').includes(tabId)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Toggle active pane
        const panes = document.querySelectorAll('.tab-pane');
        panes.forEach(pane => {
            if (pane.id === tabId) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });
    }

    function selectServiceForEdit(srv) {
        document.getElementById('edit_srv_id').value = srv.id;
        document.getElementById('edit_srv_category').value = srv.category_id;
        document.getElementById('edit_srv_name').value = srv.name;
        document.getElementById('edit_srv_rate').value = srv.price_per_k;
        document.getElementById('edit_srv_min').value = srv.min_quantity;
        document.getElementById('edit_srv_max').value = srv.max_quantity;
        document.getElementById('edit_srv_status').value = srv.status;
        document.getElementById('edit_srv_sort_order').value = srv.sort_order ? srv.sort_order : '';
        document.getElementById('edit_srv_avg_time').value = srv.average_time ? srv.average_time : '';
        document.getElementById('edit_srv_provider').value = srv.provider_id ? srv.provider_id : '';
        document.getElementById('edit_srv_prov_id').value = srv.provider_service_id ? srv.provider_service_id : '';
        document.getElementById('edit_srv_prov_rate').value = srv.provider_rate ? srv.provider_rate : '';
        document.getElementById('edit_srv_desc').value = srv.description ? srv.description : '';

        const origBox = document.getElementById('edit_srv_orig_box');
        const origNameEl = document.getElementById('edit_srv_orig_name');
        if (origBox && origNameEl) {
            if (srv.original_name) {
                origNameEl.textContent = srv.original_name;
                origBox.style.display = 'block';
            } else {
                origBox.style.display = 'none';
            }
        }

        const modal = document.getElementById('editServiceModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeEditServiceModal() {
        document.getElementById('editServiceModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    function resetServiceForm() {
        document.getElementById('srv_id').value = '';
        document.getElementById('srv_category').value = '';
        document.getElementById('srv_name').value = '';
        document.getElementById('srv_rate').value = '';
        document.getElementById('srv_min').value = '100';
        document.getElementById('srv_max').value = '10000';
        document.getElementById('srv_status').value = 'active';
        document.getElementById('srv_avg_time').value = '';
        document.getElementById('srv_provider').value = '';
        document.getElementById('srv_prov_id').value = '';
        document.getElementById('srv_prov_rate').value = '';
        document.getElementById('srv_desc').value = '';

        document.getElementById('editorTitle').innerHTML = '<i class="fa-solid fa-square-plus text-gradient"></i> Add New Service';
        document.getElementById('cancelBtn').style.display = 'none';
    }

    // Restore scroll position if saved
    const savedScrollY = sessionStorage.getItem('services_scroll_y');
    if (savedScrollY !== null) {
        window.scrollTo(0, parseInt(savedScrollY, 10));
        sessionStorage.removeItem('services_scroll_y');
    }

    let searchTimeout = null;
    let currentServicePageUrl = null;
    let silentPollIntervalId = null;

    function fetchServices(url = null, isSilent = false) {
        const form = document.getElementById('servicesFilterForm');
        if (!form) return;

        if (url) {
            currentServicePageUrl = url;
        }

        const targetUrl = currentServicePageUrl || (form.action + '?' + new URLSearchParams(new FormData(form)).toString());
        const container = document.getElementById('services-table-container');
        
        if (!isSilent && container) {
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
        }

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (container && data.html) {
                // Update table HTML seamlessly if changed or non-silent
                if (!isSilent || container.innerHTML !== data.html) {
                    container.innerHTML = data.html;
                }
            }
        })
        .catch(err => console.error('Error fetching services:', err))
        .finally(() => {
            if (!isSilent && container) {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        });
    }

    function startSilentServicePolling() {
        if (silentPollIntervalId) clearInterval(silentPollIntervalId);

        silentPollIntervalId = setInterval(() => {
            const modal = document.getElementById('editServiceModal');
            const isModalOpen = modal && modal.classList.contains('show');
            const searchInput = document.getElementById('srvSearchInput');
            const isTyping = searchInput && document.activeElement === searchInput;

            if (!document.hidden && navigator.onLine && !isModalOpen && !isTyping) {
                fetchServices(null, true);
            }
        }, 15000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        startSilentServicePolling();
        const form = document.getElementById('servicesFilterForm');
        const searchInput = document.getElementById('srvSearchInput');
        const catSelect = document.getElementById('srvCategorySelect');
        const provSelect = document.getElementById('srvProviderSelect');
        const statusSelect = document.getElementById('srvStatusSelect');
        const clearBtn = document.getElementById('srvClearBtn');

        function updateClearBtnVisibility() {
            if (clearBtn) {
                const isFiltered = (searchInput && searchInput.value.trim() !== '') ||
                                   (catSelect && catSelect.value !== '') ||
                                   (provSelect && provSelect.value !== '') ||
                                   (statusSelect && statusSelect.value !== 'all');
                clearBtn.style.display = isFiltered ? 'inline-block' : 'none';
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                updateClearBtnVisibility();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchServices();
                }, 350);
            });
        }

        [catSelect, provSelect, statusSelect].forEach(el => {
            if (el) {
                el.addEventListener('change', function() {
                    updateClearBtnVisibility();
                    fetchServices();
                });
            }
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchServices();
            });
        }

        // Handle AJAX submission for Edit Service Modal
        const editForm = document.getElementById('editServiceFormAjax');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const currentScroll = window.scrollY;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeEditServiceModal();
                        fetchServices();
                        setTimeout(() => window.scrollTo(0, currentScroll), 100);
                    } else {
                        alert(data.message || 'Error updating service');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error updating service. Please try again.');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }

        // Handle AJAX submission for Add Service Form
        const addForm = document.getElementById('addServiceFormAjax');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        resetServiceForm();
                        fetchServices();
                    } else {
                        alert(data.message || 'Error saving service');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error saving service.');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }

        // Delegate pagination link clicks and edit button clicks
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#services-table-container .pagination a');
            if (paginationLink) {
                e.preventDefault();
                fetchServices(paginationLink.href);
                return;
            }

            const editBtn = e.target.closest('.edit-service-btn');
            if (editBtn) {
                const srvData = editBtn.getAttribute('data-service');
                if (srvData) {
                    try {
                        const srv = JSON.parse(srvData);
                        selectServiceForEdit(srv);
                    } catch(err) {
                        console.error('Failed to parse service JSON', err);
                    }
                }
            }
        });

        // Delegate AJAX delete forms submit
        document.addEventListener('submit', function(e) {
            const srvDeleteForm = e.target.closest('.ajax-srv-delete-form');
            if (srvDeleteForm) {
                e.preventDefault();
                const currentScroll = window.scrollY;

                fetch(srvDeleteForm.action, {
                    method: 'POST',
                    body: new FormData(srvDeleteForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchServices();
                        setTimeout(() => window.scrollTo(0, currentScroll), 100);
                    } else {
                        alert(data.message || 'Error deleting service');
                    }
                })
                .catch(err => console.error(err));
            }
        });
    });
</script>
@endsection
