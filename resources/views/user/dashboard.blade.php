@extends('layouts.app')

@section('title', 'New Order - RishiSMM')
@section('page_header', 'New Order')

@section('styles')
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* ==========================================================================
           Custom Select2 Light Sky Blue Theme Styling (Hierarchy & Specificity Based)
           ========================================================================== */

        /* 1. Base Container */
        body .select2-container {
            display: block;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        body .select2-container .selection {
            display: block;
            width: 100%;
            overflow: hidden;
        }

        .form-group {
            overflow: hidden;
            min-width: 0;
        }

        /* 2. Selection Trigger Box */
        body .select2-container--default .select2-selection--single {
            background: #f0f7ff;
            border: 1px solid #bae6fd;
            border-radius: var(--radius-md);
            height: auto;
            min-height: 44px;
            padding: 8px 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            outline: none;
        }

        body .select2-container--default.select2-container--focus .select2-selection--single,
        body .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.18);
        }

        body .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            font-size: 0.9rem;
            padding-left: 0;
            padding-right: 24px;
            line-height: 1.4;
            font-weight: 500;
            white-space: normal;
            word-break: break-word;
            min-width: 0;
            flex-grow: 1;
        }

        body .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #64748b;
        }

        body .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 0;
            right: 12px;
            display: flex;
            align-items: center;
        }

        body .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #64748b transparent transparent transparent;
            border-width: 5px 4px 0 4px;
        }

        body .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #0284c7 transparent;
            border-width: 0 4px 5px 4px;
        }

        /* 3. Dropdown Panel */
        body .select2-dropdown {
            background-color: #f0f7ff;
            border: 1px solid #bae6fd;
            border-radius: var(--radius-md);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
            z-index: 9999;
            overflow: hidden;
        }

        body .select2-search--dropdown {
            display: none;
            padding: 10px;
            background-color: #e0f2fe;
            border-bottom: 1px solid #bae6fd;
        }

        body .select2-search--dropdown .select2-search__field {
            background-color: #ffffff;
            border: 1px solid #bae6fd;
            color: #0f172a;
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            font-size: 0.85rem;
            outline: none;
        }

        body .select2-search--dropdown .select2-search__field:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* 4. Results List & Height Expansion (Clean Senior Specificity) */
        body .select2-container--default .select2-results > .select2-results__options {
            max-height: 480px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            background-color: #f0f7ff;
        }

        body .select2-container--default .select2-results > .select2-results__options::-webkit-scrollbar {
            width: 6px;
        }

        body .select2-container--default .select2-results > .select2-results__options::-webkit-scrollbar-track {
            background: #e0f2fe;
        }

        body .select2-container--default .select2-results > .select2-results__options::-webkit-scrollbar-thumb {
            background: #0284c7;
            border-radius: 4px;
        }

        /* 5. Option Items */
        body .select2-container--default .select2-results__option {
            padding: 7px 14px;
            font-size: 13px;
            transition: var(--transition);
            user-select: none;
            font-weight: 400;
            color: #0f172a;
            background-color: transparent;
            line-height: 19px;
            border-bottom: 1px solid #bae6fd;
        }

        body .select2-container--default .select2-results__option:last-child {
            border-bottom: none;
        }

        body .select2-container--default .select2-results__group {
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #0284c7;
            background: #f0f9ff;
            border-top: 2px solid #bae6fd;
            border-bottom: 1px solid #e0f2fe;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        body .select2-container--default .select2-results__option[role=group] {
            padding: 0;
        }

        body .select2-container--default .select2-results__option--selectable {
            cursor: pointer;
        }

        body .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #0284c7;
            color: #ffffff;
            font-weight: 400;
        }

        body .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
            background: #0369a1;
            color: #ffffff;
            font-weight: 400;
        }

        body .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #bae6fd;
            color: #0369a1;
            font-weight: 400;
        }

        body .select2-container--default .select2-results__option--disabled {
            color: #94a3b8;
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* 6. Responsive Mobile Adjustments */
        @media (max-width: 768px) {
            body .select2-container--default .select2-results > .select2-results__options {
                max-height: 380px;
            }

            body .select2-container--default .select2-results__option {
                font-size: 13px;
                padding: 6px 12px;
            }

            body .select2-container--default .select2-selection--single .select2-selection__rendered {
                font-size: 13px;
                line-height: 20px;
                padding: 2px 24px 2px 0;
                white-space: normal;
                word-break: break-word;
            }

            body .select2-search--dropdown .select2-search__field {
                font-size: 13px;
                display: none;
            }
        }

        /* Styling for the service tabs */
        .service-tab-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .service-tab-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-primary);
        }

        .service-tab-btn.active {
            background: var(--grad-insta);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(204, 35, 102, 0.25);
        }

        /* Styling for search and list wrapper */
        .search-wrapper {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-wrapper .form-control {
            padding-left: 40px;
        }

        /* Service table custom styling */
        .services-list-table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .services-list-table tbody tr {
            cursor: pointer;
            transition: var(--transition);
        }

        .services-list-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03) !important;
        }

        .services-list-table tbody tr.selected-row {
            background: rgba(220, 39, 67, 0.1) !important;
            border-left: 3px solid var(--color-primary);
        }

        .services-list-table td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .btn-select-srv {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .services-list-table tr.selected-row .btn-select-srv {
            background: var(--grad-insta);
            color: white;
            border-color: transparent;
        }

        /* Platform Selection Grid */
        .platform-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .platform-btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            box-sizing: border-box;
        }

        .platform-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .platform-btn.active {
            background: linear-gradient(135deg, rgba(220, 39, 67, 0.15), rgba(225, 48, 108, 0.15));
            border-color: var(--color-primary);
            color: var(--text-primary);
            box-shadow: 0 0 15px rgba(220, 39, 67, 0.2);
        }

        .platform-btn i {
            font-size: 1.15rem;
        }

        @media (max-width: 768px) {
            .platform-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .platform-btn {
                padding: 10px 12px;
                font-size: 0.8rem;
                gap: 8px;
            }
        }

        /* Order Mode Switcher Tabs */
        .order-mode-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .order-mode-btn:hover {
            color: var(--text-primary);
        }

        .order-mode-btn.active {
            background: linear-gradient(135deg, #e11d48 0%, #dc2626 100%);
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(225, 29, 72, 0.4);
        }
    </style>
@endsection

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">

        <div class="card-grid" style="grid-template-columns: 2fr 1fr; align-items: start;">

            <!-- Left Column (Order Placement & Platform selection wrappers) -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%; min-width: 0;">

                @php
                    $discDetails = Auth::user()->getEffectiveDiscountDetails();
                    $effDiscount = $discDetails['discount_percent'];
                @endphp

                <!-- Customer Loyalty Tier & Active Discount Banner (Soft Clay Card) -->
                <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 10px 25px rgba(220, 39, 67, 0.07); padding: 18px 22px; display: flex; align-items: center; gap: 16px; width: 100%; border: 1px solid rgba(220, 39, 67, 0.05); margin-bottom: 0;">
                    <div style="width: 52px; height: 52px; background: #fff0f3; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #ff335c; font-size: 1.3rem; box-shadow: inset 0 2px 4px rgba(220, 39, 67, 0.1);">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div style="flex-grow: 1;">
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">
                            {{ $discDetails['source'] === 'individual' ? 'VIP Custom Discount: ' . $effDiscount . '%' : ($discDetails['tier_name'] ?? 'BRONZE') . ' Tier (' . $effDiscount . '% Discount)' }}
                        </div>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px; margin-bottom: 8px;">
                            @if(!empty($discDetails['next_tier']))
                                Spend ₹{{ number_format($discDetails['remaining_for_next_tier'], 2) }} more eligible orders to reach <strong>{{ $discDetails['next_tier'] }} Tier</strong>!
                            @else
                                You are at the top VIP Tier level!
                            @endif
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #edfbf4; border: 1px solid rgba(34, 197, 94, 0.25); color: #16a34a; font-weight: 700; font-size: 0.78rem; padding: 4px 12px; border-radius: 20px;">
                            <i class="fa-solid fa-tag"></i>
                            <span>{{ $effDiscount }}% OFF Applied</span>
                        </div>
                    </div>
                </div>

                <!-- Platforms Filter Grid Card -->
                <div class="glass custom-card" style="padding: 1.25rem; margin: 0; position: relative; z-index: 10;">
                    <h4
                        style="margin: 0 0 1rem; font-size: 0.95rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-shapes text-gradient"></i> Select Social Platform
                    </h4>
                    @php
                        $activePlatforms = App\Services\PlatformHelper::getAllPlatforms()->where('is_enabled', true);
                        if ($activePlatforms->count() === 0) {
                            $activePlatforms = App\Models\SocialPlatform::enabled()->get();
                        }
                        $defaultPlatformObj = $activePlatforms->where('is_default', true)->first();
                        $defaultPlatformKey = $defaultPlatformObj ? $defaultPlatformObj->key : 'all';
                    @endphp
                    <div class="platform-grid" style="margin-bottom: 1.25rem;">
                        @foreach($activePlatforms as $plat)
                            <button type="button" class="platform-btn {{ $defaultPlatformKey === $plat->key ? 'active' : '' }}"
                                data-platform="{{ $plat->key }}">
                                <i class="{{ $plat->icon ?: 'fa-solid fa-globe' }}" style="color: {{ $plat->color ?: '#0284c7' }};"></i> {{ $plat->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Service Type Tabs -->
                    <div class="form-group" id="serviceTabsContainer"
                        style="display: none; margin-top: 1.5rem; margin-bottom: 0;">
                        <label class="form-label">Service Types</label>
                        <div id="serviceTabs" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <!-- Dynamically generated tab buttons -->
                        </div>
                    </div>

                    <!-- Search & Sort Controls Stacked Rows -->
                    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1.25rem;">

                        <!-- Sort Services Dropdown -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="sortBySelect">Sort Services By</label>
                            <select id="sortBySelect" class="form-control" style="cursor: pointer;">
                                <option value="default" selected>⚡ Default Position (Admin Sort)</option>
                                <option value="price_asc">🏷️ Price: Low to High (Cheapest First)</option>
                                <option value="price_desc">💎 Price: High to Low (Premium First)</option>
                                <option value="newest">🔥 Newest Services First</option>
                            </select>
                        </div>

                        <!-- Search Services Box -->
                        <div class="form-group" id="serviceSearchContainer"
                            style="margin-bottom: 0; position: relative; overflow: visible !important;">
                            <label class="form-label" for="serviceSearch">Search Services</label>
                            <div class="search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="serviceSearch" class="form-control"
                                    placeholder="Search by ID, Name, description or price..." autocomplete="off">
                            </div>
                            <!-- Custom autocomplete dropdown list -->
                            <div id="serviceSearchDropdown" class="glass"
                                style="display: none; position: absolute; top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; z-index: 1000; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); box-shadow: var(--shadow-glow); margin-top: 5px; padding: 5px 0;">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Order Placement Box -->
                <div class="glass custom-card" style="margin: 0; overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 12px;">
                        <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-cart-plus text-gradient"></i> Place a New Order</h3>

                        <!-- Order Mode Switcher Tabs -->
                        <div class="order-mode-tabs" style="display: inline-flex; gap: 6px; background: rgba(0,0,0,0.25); padding: 4px; border-radius: 30px; border: 1px solid var(--border-color);">
                            <button type="button" class="order-mode-btn {{ session('active_tab') === 'mass' ? '' : 'active' }}" id="tabNewOrder" onclick="switchOrderMode('single')">
                                <i class="fa-solid fa-bolt"></i> New order
                            </button>
                            <button type="button" class="order-mode-btn {{ session('active_tab') === 'mass' ? 'active' : '' }}" id="tabMassOrder" onclick="switchOrderMode('mass')">
                                <i class="fa-solid fa-layer-group"></i> Mass Order
                            </button>
                        </div>
                    </div>

                    @if(session('mass_errors') && is_array(session('mass_errors')))
                        <div class="glass" style="border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                            <h5 style="color: #ef4444; font-weight: 700; margin: 0 0 10px 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Mass Order Line Error Details:
                            </h5>
                            <div style="max-height: 180px; overflow-y: auto;">
                                <table style="width: 100%; font-size: 0.82rem; border-collapse: collapse; color: var(--text-secondary);">
                                    <thead>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                                            <th style="padding: 4px 8px;">Line</th>
                                            <th style="padding: 4px 8px;">Content</th>
                                            <th style="padding: 4px 8px;">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(session('mass_errors') as $err)
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                <td style="padding: 4px 8px; font-weight: 700; color: #ef4444;">#{{ $err['line'] }}</td>
                                                <td style="padding: 4px 8px; font-family: monospace; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $err['content'] }}</td>
                                                <td style="padding: 4px 8px; color: #fca5a5;">{{ $err['reason'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Single Order Container -->
                    <div id="singleOrderContainer" style="{{ session('active_tab') === 'mass' ? 'display: none;' : '' }}">
                        <form action="{{ route('order.place') }}" method="POST" id="orderSubmitForm">
                            @csrf
                            <input type="hidden" name="idempotency_token" id="idempotency_token" value="">

                            <!-- Category Selector -->
                            <div class="form-group">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" class="form-control">
                                    <option value="all" selected>🌐 All Categories (Browse All Services)</option>
                                    @if(isset($initialCatId) && $initialCatId)
                                        @php $initCatObj = $categories->firstWhere('id', $initialCatId); @endphp
                                        @if($initCatObj)
                                            <option value="{{ $initCatObj->id }}">{{ $initCatObj->name }}</option>
                                        @endif
                                    @endif
                                </select>
                            </div>

                            <!-- Service Selector -->
                            <div class="form-group">
                                <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                                <select name="service_id" id="service_id" class="form-control" required>
                                    <option value="" disabled selected>-- Select a Category first --</option>
                                </select>
                            </div>

                            <!-- Dynamic Service Selection UI (Hidden Table format preserved) -->
                            <div id="serviceSelectionSection" style="display: none !important;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Select a Service</label>
                                    <div class="table-responsive"
                                        style="max-height: 380px; overflow: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: rgba(0,0,0,0.15);">
                                        <table class="custom-table services-list-table" style="margin-top: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem; width: 70px;">ID</th>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem;">Service</th>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem; width: 120px;">Rate/1K
                                                    </th>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem; width: 80px;">Min</th>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem; width: 80px;">Max</th>
                                                    <th style="padding: 12px 16px; font-size: 0.8rem; width: 130px;">Average
                                                        Time</th>
                                                    <th
                                                        style="padding: 12px 16px; font-size: 0.8rem; text-align: center; width: 130px;">
                                                        Description</th>
                                                </tr>
                                            </thead>
                                            <tbody id="servicesTableBody">
                                                <!-- Dynamic service rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Description Details Card (Dynamic) -->
                            <div id="serviceDetailsBox" class="glass"
                                style="display: none; border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem; background: rgba(0,0,0,0.15);">
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px; font-size: 0.9rem;">
                                    <div>
                                        <span style="color: var(--text-secondary); display: block;">Rate per 1000:</span>
                                        <strong id="detailRate"
                                            style="font-size: 1.1rem; color: var(--text-primary);">₹0.00</strong>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary); display: block;">Min / Max Limit:</span>
                                        <strong id="detailLimits" style="font-size: 1rem; color: var(--text-primary);">0 /
                                            0</strong>
                                    </div>
                                </div>
                                <div style="border-top: 1px solid var(--border-color); padding-top: 8px;">
                                    <span
                                        style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 4px;">Service
                                        Description:</span>
                                    <p id="detailDesc"
                                        style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.4;"></p>
                                </div>
                            </div>

                            <!-- Target Link URL -->
                            <div class="form-group">
                                <label for="link" class="form-label">Target URL Link</label>
                                <input type="url" name="link" id="link" class="form-control"
                                    placeholder="https://www.instagram.com/p/..." required value="{{ old('link') }}">
                            </div>

                            <!-- Custom Comments Field (Dynamic: Shown only for Custom Comments services) -->
                            <div class="form-group" id="commentsContainer" style="display: none;">
                                <label for="comments" class="form-label">Custom Comments <span class="text-danger">*</span></label>
                                <textarea name="comments" id="comments" class="form-control" rows="5"
                                    placeholder="Enter one comment per line...&#10;Great post! ❤️&#10;Awesome content 🔥&#10;Loved this! 😍"
                                    style="resize: vertical; line-height: 1.5; font-family: inherit; font-size: 0.9rem;">{{ old('comments') }}</textarea>
                                <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                                    <i class="fa-solid fa-circle-info"></i> Enter one comment per line. Total comments will set the order quantity automatically.
                                </small>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-control"
                                    placeholder="Enter amount (e.g. 1000)" required disabled min="1"
                                    value="{{ old('quantity') }}">
                            </div>

                            <!-- Live Charge Calculator Card -->
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 2rem;">
                                <div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Total Cost:</span>
                                    <div style="font-size: 1.6rem; font-weight: 800;" class="text-gradient" id="orderCost">₹0.00
                                    </div>
                                </div>
                                <button type="submit" class="btn-gradient" style="padding: 14px 35px; font-size: 1rem;">
                                    Place Order <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Mass Order Container -->
                    <div id="massOrderContainer" style="{{ session('active_tab') === 'mass' ? '' : 'display: none;' }}">
                        <form action="{{ route('order.mass-place') }}" method="POST" id="massOrderSubmitForm">
                            @csrf
                            <input type="hidden" name="idempotency_token" id="mass_idempotency_token" value="">

                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="mass_order" class="form-label" style="font-weight: 700; font-size: 0.95rem; display: block; margin-bottom: 10px;">
                                    One order per line — <span style="font-family: monospace; font-weight: 700; color: var(--text-primary); background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">service_id | link | quantity</span>
                                </label>
                                <textarea name="mass_order" id="mass_order" class="form-control" rows="8"
                                    placeholder="123 | https://instagram.com/p/abc | 1000&#10;123 https://instagram.com/p/xyz 5000" required
                                    style="resize: vertical; font-family: monospace; font-size: 0.92rem; line-height: 1.6; padding: 14px; border-radius: var(--radius-md); background: rgba(0,0,0,0.25); color: var(--text-primary);">{{ old('mass_order') }}</textarea>
                                <div style="margin-top: 8px; font-size: 0.82rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <i class="fa-solid fa-wand-magic-sparkles" style="color: #38bdf8;"></i>
                                    <span><strong>Auto Separator:</strong> Spaces, commas, or pipes (<code>|</code>) are automatically formatted and parsed! E.g. <code>123 https://link.com 1000</code></span>
                                </div>
                            </div>

                            <div style="margin-top: 1.5rem; margin-bottom: 0.5rem;">
                                <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px 28px; font-size: 1rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; border-radius: var(--radius-md); box-shadow: 0 6px 20px rgba(220, 39, 67, 0.4);">
                                    <i class="fa-solid fa-layer-group"></i> Submit Mass Order
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div> <!-- End Left Column Wrapper -->

            <!-- Right Column Wrapper -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                <!-- Mobile App Install Card (Visible only on Website, Hidden inside APK) -->
                <div class="glass custom-card app-install-only-web" style="padding: 1.35rem; border: 1px solid rgba(220, 39, 67, 0.35); background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 27, 75, 0.7)); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 20px rgba(220, 39, 67, 0.15); margin: 0;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="width: 46px; height: 46px; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 15px rgba(220, 39, 67, 0.35); border: 1px solid rgba(255, 255, 255, 0.15); flex-shrink: 0;">
                            <img src="{{ asset('images/icons/icon-192x192.png') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';" alt="App Icon" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h4 style="font-weight: 800; font-size: 1.05rem; color: #ffffff; margin: 0 0 2px 0;">
                                {{ App\Models\Setting::get('site_name', 'RishiSMM') }} App
                            </h4>
                            <span style="font-size: 0.72rem; color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 1px 6px; border-radius: 12px; display: inline-block;">
                                ⚡ Ultra Lite (1.8 MB)
                            </span>
                        </div>
                    </div>

                    <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
                        Place orders 2x faster with 1-tap instant access directly from your phone's home screen.
                    </p>

                    <button type="button" onclick="triggerPWAInstall()" class="btn-gradient" style="width: 100%; padding: 10px 14px; font-size: 0.88rem; font-weight: 800; border-radius: var(--radius-sm); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(220, 39, 67, 0.35);">
                        <i class="fa-brands fa-android" style="font-size: 1.1rem;"></i> Install App Now
                    </button>
                    <div style="text-align: center; margin-top: 8px;">
                        <span style="font-size: 0.72rem; color: #94a3b8;">
                            <i class="fa-brands fa-apple"></i> iOS Safari & <i class="fa-brands fa-android"></i> Android Supported
                        </span>
                    </div>
                </div>

                <!-- Dashboard Notes / FAQs -->
                <div class="glass custom-card" style="padding: 1.5rem; margin: 0;">
                    <h4
                        style="font-weight: 700; font-size: 1.1rem; margin-bottom: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <i class="fa-solid fa-lightbulb text-gradient" style="margin-right: 6px;"></i> Order Guidelines
                    </h4>
                    <ul
                        style="color: var(--text-secondary); font-size: 0.85rem; padding-left: 15px; display: flex; flex-direction: column; gap: 8px; line-height: 1.5;">
                        <li>Ensure the target account is set to <strong>Public</strong>. Private profile orders cannot be
                            delivered and may be canceled.</li>
                        <li>Do not place multiple orders for the same link simultaneously. Wait for the active order to
                            complete.</li>
                        <li>We fetch rates dynamically from SMM Providers. If you find a delay, please open a support ticket
                            immediately.</li>
                    </ul>
                </div>

            </div> <!-- End Right Column Wrapper -->
        </div> <!-- End Card Grid -->


        <!-- Important Ordering Rules & Flag OFF Alert -->
        <div class="glass custom-card"
            style="margin-top: 1rem; margin-bottom: 1.5rem; padding: 1rem 1.25rem; border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.05); border-radius: var(--radius-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 1.2rem;"></i>
                    <div>
                        <strong style="color: #ef4444; font-size: 0.9rem;">⚠️ Flag for Review & Ordering
                            Rules:</strong>
                        <span style="font-size: 0.85rem; color: var(--text-secondary); display: block; margin-top: 2px;">
                            Turn OFF "Flag for Review" in Instagram settings before ordering followers. No duplicate
                            active orders on same link.
                        </span>
                    </div>
                </div>
                <a href="{{ route('rules') }}" target="_blank" class="btn-gradient"
                    style="padding: 6px 14px; font-size: 0.8rem; background: linear-gradient(135deg, #ef4444, #dc2626); white-space: nowrap;">
                    <i class="fa-solid fa-gavel"></i> View Full Rules
                </a>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="glass custom-card" style="margin-top: 2rem;">
            <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> My Recent Orders</h3>
            @if($recentOrders->count() > 0)
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
                    <table class="custom-table" style="min-width: 600px; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th style="min-width: 200px;">Service</th>
                                <th style="min-width: 140px;">Link</th>
                                <th style="width: 90px;">Quantity</th>
                                <th style="width: 90px;">Charge</th>
                                <th style="width: 110px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td style="font-weight: bold; color: var(--text-muted); white-space: nowrap;">#{{ $order->id }}</td>
                                    <td style="white-space: normal; line-height: 1.4; color: var(--text-primary); font-weight: 500;">
                                        ID {{ $order->service_id }} - {{ $order->service ? $order->service->name : 'N/A' }}
                                    </td>
                                    <td>
                                        <a href="{{ $order->link }}" target="_blank"
                                            style="color: var(--color-info); text-decoration: none; word-break: break-all; font-size: 0.85rem; display: inline-block; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $order->link }}
                                        </a>
                                    </td>
                                    <td style="white-space: nowrap;">{{ number_format($order->quantity) }}</td>
                                    <td style="font-weight: 600; white-space: nowrap;">₹{{ number_format($order->charge, 2) }}</td>
                                    <td style="white-space: nowrap;">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            @if($order->status === 'pending')
                                                <span class="badge badge-pending">Pending</span>
                                            @elseif($order->status === 'processing')
                                                <span class="badge badge-processing">Processing</span>
                                            @elseif($order->status === 'in_progress')
                                                <span class="badge badge-inprogress">In Progress</span>
                                            @elseif($order->status === 'completed')
                                                <span class="badge badge-completed">Completed</span>
                                            @elseif($order->status === 'partial')
                                                <span class="badge badge-partial">Partial</span>
                                            @else
                                                <span class="badge badge-canceled">Canceled</span>
                                            @endif

                                            @if($order->canCancel())
                                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST"
                                                    style="display: inline-block; margin: 0;"
                                                    onsubmit="return confirm('Cancel Order #{{ $order->id }} and get a full refund of ₹{{ number_format($order->charge, 2) }} to your wallet?');">
                                                    @csrf
                                                    <button type="submit" class="btn-gradient"
                                                        style="padding: 3px 8px; font-size: 0.7rem; border-radius: var(--radius-sm); background: #ef4444; color: white; display: inline-flex; align-items: center; gap: 3px; border: none; font-weight: bold; cursor: pointer; box-shadow: none; line-height: 1.2;"
                                                        title="Cancel order and refund to wallet">
                                                        <i class="fa-solid fa-xmark"></i> Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    No orders placed yet. Select services above to place your first order!
                </div>
            @endif
        </div>

    </div>

    @push('modals')
        <!-- Service Details Modal Overlay -->
        <div id="serviceDetailsModal" class="custom-modal" onclick="if(event.target===this) closeDetailsModal()">
            <div class="custom-modal-content animate-fade-in">
                <div class="custom-modal-header">
                    <div class="custom-modal-header-text">
                        <h3 class="custom-modal-title">
                            <i class="fa-solid fa-circle-info text-gradient" style="margin-right: 6px;"></i> Service Information
                        </h3>
                        <p class="custom-modal-subtitle">Full pricing, limits, and description</p>
                    </div>
                    <button type="button" class="custom-modal-close" onclick="closeDetailsModal()" title="Close">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <h4 id="modalServiceName"
                        style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary); line-height: 1.4;">
                    </h4>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Service ID</div>
                            <div class="detail-value" id="modalServiceId"></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Category</div>
                            <div class="detail-value" id="modalServiceCategory"></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Rate per 1000</div>
                            <div class="detail-value text-gradient" id="modalServiceRate"></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Min / Max Limit</div>
                            <div class="detail-value" id="modalServiceLimits"></div>
                        </div>
                        <div class="detail-item" style="grid-column: span 2;">
                            <div class="detail-label">Average Speed / Time</div>
                            <div class="detail-value" id="modalServiceTime"></div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 0.5rem;">
                        <div class="detail-label" style="margin-bottom: 6px; font-weight: 600;">Detailed Description</div>
                        <p id="modalServiceDesc"
                            style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6; white-space: pre-line; word-break: break-word; background: rgba(0,0,0,0.15); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin: 0;">
                        </p>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-outline" style="height: 40px; padding: 0 18px; font-size: 0.88rem;"
                        onclick="closeDetailsModal()">Close</button>
                    <button type="button" id="modalSelectBtn" class="btn-gradient" style="height: 40px; padding: 0 22px; font-size: 0.88rem;">Select Service</button>
                </div>
            </div>
        </div>
    @endpush

    <!-- Copy Success Toast -->
    <div id="copyToast" class="copy-toast" style="z-index: 10000;">
        <i class="fa-solid fa-circle-check"></i> Copied to Clipboard!
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script>
        function cleanBrandingJS(str) {
            if (!str) return '';
            return str.replace(/\bSMM\s*BIN\b/gi, 'RishiSMM')
                .replace(/\bSMM-BIN\b/gi, 'RishiSMM')
                .replace(/\bsmmbin\b/gi, 'RishiSMM')
                .replace(/\bSMM\s*BiN\b/gi, 'RishiSMM')
                .replace(/\bSMMBiN\b/gi, 'RishiSMM');
        }

        // Inject Lightweight Categories & Initial Services dynamic configuration
        const rawCategoriesData = @json($categoriesLight ?? []);
        const rawInitialServices = @json($initialServices ?? []);
        const initialCatId = @json($initialCatId ?? null);
        const enabledPlatforms = @json(\App\Services\PlatformHelper::getEnabledPlatforms());
        const allPlatformsData = @json(\App\Services\PlatformHelper::getAllPlatforms());

        const categoriesData = rawCategoriesData.map(cat => {
            cat.name = cleanBrandingJS(cat.name);
            cat.services = [];
            return cat;
        });

        const initialServicesClean = rawInitialServices.map(srv => {
            srv.name = cleanBrandingJS(srv.name);
            srv.description = cleanBrandingJS(srv.description);
            return srv;
        });

        if (initialCatId) {
            const initCatObj = categoriesData.find(c => c.id == initialCatId);
            if (initCatObj) {
                initCatObj.services = initialServicesClean;
            }
        }

        const currencySymbol = "₹";

        document.addEventListener('DOMContentLoaded', function () {
            // Debounce helper to prevent input lag
            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Safe Select2 Destroy helper to prevent console crashes
            function safeSelect2Destroy(element) {
                try {
                    if ($(element).hasClass("select2-hidden-accessible")) {
                        $(element).select2('destroy');
                    }
                } catch (e) {
                    console.warn('Select2 destroy failed:', e);
                }
            }

            // Generate dynamic idempotency token
            const token = 'rishismm_' + Math.random().toString(36).substring(2, 15) + '_' + Math.random().toString(36).substring(2, 15);
            document.getElementById('idempotency_token').value = token;

            // Double click protection
            const form = document.getElementById('orderSubmitForm');
            let isSubmitting = false;
            form.addEventListener('submit', function (e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Placing Order... <i class="fa-solid fa-spinner fa-spin" style="margin-left: 8px;"></i>';
                }
            });

            const catSelect = document.getElementById('category_id');
            const srvSelect = document.getElementById('service_id');

            // Initialize Select2 on page load
            $(catSelect).select2({
                width: '100%',
                minimumResultsForSearch: 8
            });
            $(srvSelect).select2({
                width: '100%',
                minimumResultsForSearch: 8,
                placeholder: '-- Select a Service --'
            });
            const qtyInput = document.getElementById('quantity');
            const costDiv = document.getElementById('orderCost');
            const commentsContainer = document.getElementById('commentsContainer');
            const commentsInput = document.getElementById('comments');

            const detailsBox = document.getElementById('serviceDetailsBox');
            const dRate = document.getElementById('detailRate');
            const dLimits = document.getElementById('detailLimits');
            const dDesc = document.getElementById('detailDesc');

            const selectionSection = document.getElementById('serviceSelectionSection');
            const serviceTabsContainer = document.getElementById('serviceTabs');
            const serviceTableBody = document.getElementById('servicesTableBody');
            const serviceSearchInput = document.getElementById('serviceSearch');

            let activeService = null;
            let servicesList = [];
            let currentTab = 'All';
            let searchQuery = '';
            let isAutoSelecting = false;
            let currentPlatform = 'all';
            let currentSort = 'default';

            function isServiceCustomComments(service) {
                if (!service) return false;
                if (typeof service.is_custom_comments !== 'undefined' && service.is_custom_comments === true) {
                    return true;
                }
                const name = (service.original_name || service.name || '').toLowerCase();
                const desc = (service.description || '').toLowerCase();

                if (/\b(comment likes|comments likes|comment like|comments like|likes on comment|likes on comments)\b/i.test(name) && !name.includes('custom')) {
                    return false;
                }
                if (/\b(random comment|random comments|emoji comment|emoji comments|positive comments)\b/i.test(name) && !name.includes('custom')) {
                    return false;
                }

                if (/custom\s*comments?|custome\s*comments?|custom_comments?/i.test(name)) {
                    return true;
                }
                if (/\b(custom|custome)\b/i.test(name) && /\b(comment|comments)\b/i.test(name)) {
                    return true;
                }
                if (/[\[\|\(\/]\s*(custom|custome)\s*[\]\|\)\/]/i.test(name) && name.includes('comment')) {
                    return true;
                }
                return false;
            }

            function updateCommentsVisibility() {
                if (!activeService) {
                    if (commentsContainer) commentsContainer.style.display = 'none';
                    if (commentsInput) {
                        commentsInput.removeAttribute('required');
                        commentsInput.value = '';
                    }
                    return;
                }

                const isCustom = isServiceCustomComments(activeService);
                if (isCustom) {
                    if (commentsContainer) commentsContainer.style.display = 'block';
                    if (commentsInput) {
                        commentsInput.setAttribute('required', 'required');
                    }
                    qtyInput.readOnly = true;
                    syncCommentsToQuantity();
                } else {
                    if (commentsContainer) commentsContainer.style.display = 'none';
                    if (commentsInput) {
                        commentsInput.removeAttribute('required');
                        commentsInput.value = '';
                    }
                    qtyInput.readOnly = false;
                    qtyInput.placeholder = `Min: ${activeService.min_quantity} - Max: ${activeService.max_quantity}`;
                    calculateCost();
                }
            }

            function syncCommentsToQuantity() {
                if (!activeService || !isServiceCustomComments(activeService)) return;

                const raw = commentsInput ? commentsInput.value : '';
                const lines = raw.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
                const count = lines.length;

                qtyInput.value = count > 0 ? count : '';
                qtyInput.placeholder = count > 0 ? `${count} comment${count > 1 ? 's' : ''} entered` : `Enter comments above (Min: ${activeService.min_quantity})`;
                calculateCost();
            }

            if (commentsInput) {
                commentsInput.addEventListener('input', syncCommentsToQuantity);
            }

            let allServices = [...initialServicesClean];
            let isFullServicesLoaded = false;

            // Async background loader to fetch full services catalog & keep prices/statuses synced live
            function loadFullServicesCatalogInBackground(isSilentPoll = false) {
                fetch('/user/dashboard/services')
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            allServices = data.map(srv => {
                                srv.name = cleanBrandingJS(srv.name);
                                srv.description = cleanBrandingJS(srv.description);
                                return srv;
                            });

                            // Map services back into categoriesData
                            categoriesData.forEach(cat => {
                                cat.services = allServices.filter(s => s.category_id == cat.id);
                            });

                            isFullServicesLoaded = true;

                            // Live update currently selected active service if parameters changed by provider/admin
                            if (activeService) {
                                const freshSrv = allServices.find(s => s.id == activeService.id);
                                if (freshSrv) {
                                    if (freshSrv.status !== 'active') {
                                        if (dDesc) dDesc.innerHTML = '<span style="color: #ef4444; font-weight: 700;">⚠️ Notice: This service has just been temporarily deactivated by provider.</span>';
                                        const submitBtn = form.querySelector('button[type="submit"]');
                                        if (submitBtn) submitBtn.disabled = true;
                                    } else {
                                        let updated = false;
                                        if (parseFloat(freshSrv.price_per_k) !== parseFloat(activeService.price_per_k)) {
                                            activeService.price_per_k = freshSrv.price_per_k;
                                            updated = true;
                                        }
                                        if (parseInt(freshSrv.min_quantity) !== parseInt(activeService.min_quantity) ||
                                            parseInt(freshSrv.max_quantity) !== parseInt(activeService.max_quantity)) {
                                            activeService.min_quantity = freshSrv.min_quantity;
                                            activeService.max_quantity = freshSrv.max_quantity;
                                            updated = true;
                                        }
                                        if (freshSrv.description !== activeService.description) {
                                            activeService.description = freshSrv.description;
                                            if (dDesc) dDesc.innerHTML = freshSrv.description || 'No service description provided.';
                                        }
                                        if (updated) {
                                            if (dRate) dRate.textContent = currencySymbol + parseFloat(activeService.price_per_k).toFixed(2);
                                            if (dLimits) dLimits.textContent = `${activeService.min_quantity.toLocaleString()} Min / ${activeService.max_quantity.toLocaleString()} Max`;
                                            calculateCost();
                                        }
                                    }
                                }
                            }

                            // Re-render UI components smoothly if not silently polling while dropdown is open
                            if (!isSilentPoll || ($('.select2-container--open').length === 0)) {
                                filterCategoriesByPlatform(currentPlatform, isSilentPoll);
                            }
                        }
                    })
                    .catch(err => {
                        console.warn('Background services load warning:', err);
                    });
            }

            // Launch background fetch right after initial page render (100ms)
            setTimeout(() => loadFullServicesCatalogInBackground(false), 100);

            // Live periodic background polling every 15 seconds to update prices & disabled statuses live
            setInterval(() => {
                loadFullServicesCatalogInBackground(true);
            }, 15000);

            // Master Filter & Sort Resolver
            function getFilteredAndSortedServices() {
                let sourceServices = [];
                const catId = catSelect.value;

                if (catId && catId !== 'all') {
                    const catObj = categoriesData.find(c => c.id == catId);
                    sourceServices = catObj && catObj.services ? catObj.services.filter(s => s.status === 'active' && serviceMatchesPlatform(s, currentPlatform, catObj.name)) : [];
                } else {
                    sourceServices = allServices.filter(service => {
                        const catObj = categoriesData.find(c => c.id == service.category_id);
                        const catName = catObj ? catObj.name : null;
                        return serviceMatchesPlatform(service, currentPlatform, catName);
                    });
                }

                let result = sourceServices;

                // 2. Filter by Service Type Tab (Likes, Followers, Views, Comments, etc.)
                if (currentTab !== 'All') {
                    result = result.filter(service => {
                        const group = getServiceGroup(service.name);
                        return Boolean(group && group.key === currentTab);
                    });
                }

                // 3. Filter by Search Query
                if (searchQuery) {
                    const query = normalizeText(searchQuery);
                    result = result.filter(service => {
                        const srvNorm = normalizeText(service.name);
                        const idStr = (service.id || '').toString();
                        const pIdStr = (service.provider_service_id || '').toString();
                        const descNorm = normalizeText(service.description || '');
                        const priceStr = parseFloat(service.price_per_k || 0).toFixed(2);

                        let catNorm = '';
                        const catObj = categoriesData.find(c => c.id == service.category_id);
                        if (catObj && catObj.name) catNorm = normalizeText(catObj.name);

                        const nameMatch = srvNorm.includes(query);
                        const idMatch = idStr.includes(query) || pIdStr.includes(query);
                        const descMatch = descNorm.includes(query);
                        const priceMatch = priceStr.includes(query);
                        const catMatch = catNorm.includes(query);

                        return nameMatch || idMatch || descMatch || priceMatch || catMatch;
                    });
                }

                // 4. Sort Services
                if (currentSort === 'price_asc') {
                    result.sort((a, b) => parseFloat(a.price_per_k) - parseFloat(b.price_per_k));
                } else if (currentSort === 'price_desc') {
                    result.sort((a, b) => parseFloat(b.price_per_k) - parseFloat(a.price_per_k));
                } else if (currentSort === 'newest') {
                    result.sort((a, b) => parseInt(b.id) - parseInt(a.id));
                } else {
                    // Default admin position: sort_order ASC, id DESC
                    result.sort((a, b) => {
                        const orderA = parseInt(a.sort_order || 99999);
                        const orderB = parseInt(b.sort_order || 99999);
                        if (orderA !== orderB) return orderA - orderB;
                        return parseInt(b.id) - parseInt(a.id);
                    });
                }

                return result;
            }

            // Normalizes mathematical Unicode fonts, accents, and case for accurate matching
            function normalizeText(str) {
                if (!str) return '';
                try {
                    return str.toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                } catch (e) {
                    return str.toString().toLowerCase();
                }
            }

            // Platform resolver based on name with dynamic DB keywords and length-descending sorting
            function getPlatform(name) {
                if (!name) return null;
                const lower = normalizeText(name);

                if (typeof allPlatformsData !== 'undefined' && Array.isArray(allPlatformsData) && allPlatformsData.length > 0) {
                    let keywordList = [];
                    allPlatformsData.forEach(p => {
                        if (p.key === 'all') return;
                        let kws = [];
                        if (Array.isArray(p.keywords_array)) {
                            kws = p.keywords_array;
                        } else if (typeof p.keywords === 'string') {
                            kws = p.keywords.split(',').map(k => k.trim());
                        }
                        kws.forEach(kw => {
                            let cleanKw = normalizeText(kw);
                            if (cleanKw) {
                                keywordList.push({ kw: cleanKw, key: p.key, len: cleanKw.length });
                            }
                        });
                    });

                    // Sort keywords by length DESC so specific keywords (e.g. 'google map') match before generic keywords (e.g. 'google')
                    keywordList.sort((a, b) => b.len - a.len);

                    for (let item of keywordList) {
                        let regexKw = item.kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        let regex = new RegExp('\\b' + regexKw + '\\b', 'i');
                        if (regex.test(lower) || lower.includes(item.kw)) {
                            return item.key;
                        }
                    }
                    return null;
                }

                // Hardcoded Fallback
                if (/\b(facebook|fb|fanpage)\b/i.test(lower)) return 'facebook';
                if (/\b(instagram|insta)\b/i.test(lower)) return 'instagram';
                if (/\b(youtube|yt)\b/i.test(lower)) return 'youtube';
                if (/\b(tiktok|tik\s*tok)\b/i.test(lower)) return 'tiktok';
                if (/\b(telegram|tg)\b/i.test(lower)) return 'telegram';
                if (/\b(whatsapp|wa)\b/i.test(lower)) return 'whatsapp';
                if (/\b(twitter|tweets?|x\.com)\b/i.test(lower)) return 'twitter';
                if (/\b(spotify)\b/i.test(lower)) return 'spotify';
                if (/\b(discord)\b/i.test(lower)) return 'discord';

                if (/\b(shorts)\b/i.test(lower)) return 'youtube';
                if (/\b(threads?|reels?|igv)\b/i.test(lower) || /\big\b/i.test(lower)) return 'instagram';

                return null;
            }

            // Keyword-based group resolver
            function getServiceGroup(name) {
                if (!name) return { key: 'Others', label: '✨ Others' };
                const lower = normalizeText(name);
                if (lower.includes('comment')) {
                    return { key: 'Comments', label: '💬 Comments' };
                }
                if (lower.includes('follower') || lower.includes('subscriber') || lower.includes('member') || lower.includes('join')) {
                    return { key: 'Followers', label: '👥 Followers' };
                }
                if (lower.includes('like') || lower.includes('reaction') || lower.includes('love') || lower.includes('heart')) {
                    return { key: 'Likes', label: '❤️ Likes' };
                }
                if (lower.includes('view') || lower.includes('play') || lower.includes('impression') || lower.includes('reach') || lower.includes('watch') || lower.includes('story') || lower.includes('reel')) {
                    return { key: 'Views', label: '👁️ Views' };
                }
                if (lower.includes('share') || lower.includes('retweet') || lower.includes('repost')) {
                    return { key: 'Shares', label: '🔁 Shares' };
                }
                return { key: 'Others', label: '✨ Others' };
            }

            // Exclude divider lines, warning headers, and 'do not use' categories
            function isJunkCategory(name) {
                if (!name) return true;
                const lower = normalizeText(name);
                if (lower.includes('===') || lower.includes('---') || lower.includes('___')) return true;
                if (lower.includes("don't use") || lower.includes("dont use") || lower.includes("do not use") || lower.includes("manual order") || lower.includes('🔒')) return true;
                if (lower.includes('note:') || lower.includes('contact us') || lower.includes('⚠️')) return true;
                return false;
            }

            // Check if service or category matches the selected platform
            function serviceMatchesPlatform(service, platform, catName) {
                if (isJunkCategory(catName)) return false;

                const srvName = service ? service.name : '';
                const srvPlatform = getPlatform(srvName);
                const catPlatform = getPlatform(catName || '');

                const activeEnabledPlatforms = enabledPlatforms.filter(p => p !== 'all');

                if (!platform || platform === 'all') {
                    const detectedPlatform = srvPlatform || catPlatform;
                    if (detectedPlatform) {
                        return activeEnabledPlatforms.includes(detectedPlatform);
                    }
                    return false;
                }

                // If the category explicitly belongs to another platform, reject immediately
                if (catPlatform && catPlatform !== platform) {
                    return false;
                }

                // If the service explicitly belongs to another platform, reject immediately
                if (srvPlatform && srvPlatform !== platform) {
                    return false;
                }

                // Match if either the service or the category belongs to the requested platform
                return srvPlatform === platform || catPlatform === platform;
            }

            // Check if category matches the selected platform (must have at least 1 active matching service)
            function categoryMatchesPlatform(cat, platform) {
                if (!cat.services || cat.services.length === 0) return false;
                const activeServices = cat.services.filter(s => s.status === 'active');
                if (activeServices.length === 0) return false;

                return activeServices.some(service => serviceMatchesPlatform(service, platform, cat.name));
            }

            // Get all active services in a category matching the current platform and tab
            function getActiveMatchingServicesForCat(cat, platform, tab) {
                if (!cat.services || cat.services.length === 0) return [];
                return cat.services.filter(s => {
                    if (s.status !== 'active') return false;
                    if (!serviceMatchesPlatform(s, platform, cat.name)) return false;
                    if (tab && tab !== 'All') {
                        const group = getServiceGroup(s.name);
                        if (!group || group.key !== tab) return false;
                    }
                    return true;
                });
            }

            // Filter services by platform
            function getPlatformFilteredServices() {
                const catId = catSelect.value;
                if (catId) {
                    const categoryObj = categoriesData.find(c => c.id == catId);
                    if (categoryObj && categoryObj.services) {
                        return categoryObj.services.filter(service => {
                            return serviceMatchesPlatform(service, currentPlatform, categoryObj.name);
                        });
                    }
                    return [];
                }

                return allServices.filter(service => {
                    const catObj = categoriesData.find(c => c.id == service.category_id);
                    return serviceMatchesPlatform(service, currentPlatform, catObj ? catObj.name : null);
                });
            }

            // Render Tabs Dynamically based on all available services for selected platform across all categories
            function generateTabs() {
                serviceTabsContainer.innerHTML = '';

                const groups = new Set();
                groups.add('All');

                // Always inspect ALL services belonging to currentPlatform across ALL categories
                const platformServices = allServices.filter(service => {
                    const catObj = categoriesData.find(c => c.id == service.category_id);
                    return serviceMatchesPlatform(service, currentPlatform, catObj ? catObj.name : null);
                });

                platformServices.forEach(service => {
                    const group = getServiceGroup(service.name);
                    if (group && group.key) {
                        groups.add(group.key);
                    }
                });

                const groupDefinitions = {
                    'All': { label: '🌐 All', key: 'All' },
                    'Followers': { label: '👥 Followers', key: 'Followers' },
                    'Comments': { label: '💬 Comments', key: 'Comments' },
                    'Views': { label: '👁️ Views', key: 'Views' },
                    'Likes': { label: '❤️ Likes', key: 'Likes' },
                    'Shares': { label: '🔁 Shares', key: 'Shares' },
                    'Others': { label: '✨ Others', key: 'Others' }
                };

                const preferredOrder = ['All', 'Followers', 'Comments', 'Views', 'Likes', 'Shares', 'Others'];
                const sortedGroups = preferredOrder.filter(k => groups.has(k));
                groups.forEach(k => {
                    if (!sortedGroups.includes(k)) sortedGroups.push(k);
                });

                sortedGroups.forEach(groupKey => {
                    const def = groupDefinitions[groupKey] || { label: groupKey, key: groupKey };

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'service-tab-btn';
                    btn.dataset.key = def.key;
                    if (groupKey === currentTab) btn.classList.add('active');
                    btn.innerHTML = def.label;

                    btn.addEventListener('click', function () {
                        document.querySelectorAll('.service-tab-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        currentTab = groupKey;

                        isAutoSelecting = true;
                        catSelect.value = 'all';
                        isAutoSelecting = false;

                        filterCategoriesByPlatform(currentPlatform);
                    });

                    serviceTabsContainer.appendChild(btn);
                });

                if (groups.size > 1) {
                    document.getElementById('serviceTabsContainer').style.display = 'block';
                }
            }

            // Render Services Table
            function renderServicesTable() {
                serviceTableBody.innerHTML = '';
                const filtered = getFilteredAndSortedServices();

                if (filtered.length === 0) {
                    serviceTableBody.innerHTML = `
                                                                                                                                        <tr>
                                                                                                                                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                                                                                                                                No matching services found for selected filter.
                                                                                                                                            </td>
                                                                                                                                        </tr>
                                                                                                                                    `;
                    return;
                }

                const fragment = document.createDocumentFragment();

                filtered.forEach(service => {
                    const isSelected = activeService && activeService.id == service.id;
                    const catObj = categoriesData.find(c => c.id == service.category_id);
                    const catNameText = catObj ? `<div style="margin-top: 4px;"><span style="font-size: 0.65rem; background: rgba(255, 255, 255, 0.08); color: var(--text-secondary); padding: 2px 6px; border-radius: 4px; display: inline-block;">${catObj.name}</span></div>` : '';

                    function getFormattedDisplayId(srvObj) {
                        return srvObj.id;
                    }

                    const displayId = getFormattedDisplayId(service);
                    const tr = document.createElement('tr');
                    tr.className = 'service-row ' + (isSelected ? 'selected-row' : '');
                    tr.dataset.serviceId = service.id;

                    tr.style.cursor = 'pointer';
                    tr.onclick = function (e) {
                        if (e.target.closest('.btn-view-details') || e.target.closest('.copy-id-btn')) {
                            return;
                        }
                        selectService(service.id);
                    };

                    tr.innerHTML = `
                                                                                                                                        <td data-label="ID" style="font-weight: bold; color: var(--text-muted); padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                                                                                                                                                <span>${displayId}</span>
                                                                                                                                                <button type="button" class="copy-id-btn" onclick="copyId('${displayId}', event)" title="Copy Service ID" style="padding: 0;">
                                                                                                                                                    <i class="fa-regular fa-copy"></i>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </td>
                                                                                                                                        <td data-label="Service Name" style="padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 2px;">${service.name}</div>
                                                                                                                                            ${searchQuery ? catNameText : ''}
                                                                                                                                        </td>
                                                                                                                                        <td data-label="Rate / 1K" class="service-table-rate-col" style="font-weight: bold; color: var(--color-success); padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            ${currencySymbol}${parseFloat(service.price_per_k).toFixed(2)}
                                                                                                                                        </td>
                                                                                                                                        <td data-label="Min" style="font-size: 0.85rem; color: var(--text-secondary); padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            ${parseInt(service.min_quantity).toLocaleString()}
                                                                                                                                        </td>
                                                                                                                                        <td data-label="Max" style="font-size: 0.85rem; color: var(--text-secondary); padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            ${parseInt(service.max_quantity).toLocaleString()}
                                                                                                                                        </td>
                                                                                                                                        <td data-label="Average Time" style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            ${service.average_time ? service.average_time : 'N/A'}
                                                                                                                                        </td>
                                                                                                                                        <td style="text-align: center; padding: 12px 16px; vertical-align: middle;">
                                                                                                                                            <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                                                                                                                                                <button type="button" class="btn-outline btn-view-details" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); width: 100%; white-space: nowrap;" onclick="showDashboardServiceDetails(${service.id}, event)">
                                                                                                                                                    View Details
                                                                                                                                                </button>
                                                                                                                                                <button type="button" class="btn-select-srv btn-gradient" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); width: 100%; white-space: nowrap; ${isSelected ? '' : 'background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); color: var(--text-secondary); box-shadow: none;'}">
                                                                                                                                                    ${isSelected ? 'Selected <i class="fa-solid fa-circle-check"></i>' : 'Select'}
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </td>
                                                                                                                                    `;
                    fragment.appendChild(tr);
                });

                serviceTableBody.appendChild(fragment);
            }

            function selectService(serviceId) {
                let service = null;
                for (const cat of categoriesData) {
                    if (cat.services) {
                        const s = cat.services.find(item => item.id == serviceId);
                        if (s) {
                            service = s;
                            break;
                        }
                    }
                }

                if (service) {
                    activeService = service;
                    searchQuery = '';
                    serviceSearchInput.value = '';

                    // Explicitly update Category Dropdown to match selected service's Category
                    if (catSelect.value != service.category_id) {
                        isAutoSelecting = true;
                        catSelect.value = service.category_id;
                        safeSelect2Destroy(catSelect);
                        $(catSelect).select2({
                            width: '100%',
                            minimumResultsForSearch: 8
                        });
                        $(catSelect).val(service.category_id).trigger('change.select2');
                        isAutoSelecting = false;
                    }

                    populateServiceDropdown();

                    srvSelect.value = serviceId;
                    safeSelect2Destroy(srvSelect);
                    $(srvSelect).select2({
                        width: '100%',
                        minimumResultsForSearch: 8,
                        placeholder: '-- Select a Service --'
                    });
                    $(srvSelect).val(serviceId).trigger('change.select2');

                    // Pre-fill details box
                    dRate.innerText = currencySymbol + parseFloat(activeService.price_per_k).toFixed(2);
                    dLimits.innerText = `${activeService.min_quantity.toLocaleString()} Min / ${activeService.max_quantity.toLocaleString()} Max`;
                    dDesc.innerText = activeService.description ? activeService.description : 'No service description provided.';

                    detailsBox.style.display = 'block';
                    qtyInput.disabled = false;
                    qtyInput.setAttribute('min', activeService.min_quantity);
                    qtyInput.setAttribute('max', activeService.max_quantity);
                    qtyInput.placeholder = `Min: ${activeService.min_quantity} - Max: ${activeService.max_quantity}`;
                    calculateCost();
                    updateCommentsVisibility();

                    renderServicesTable();
                }
            }

            // Custom search dropdown container
            const searchDropdown = document.getElementById('serviceSearchDropdown');

            // Search Input Event (Debounced to prevent input lag)
            serviceSearchInput.addEventListener('input', debounce(function () {
                searchQuery = this.value.trim();
                searchDropdown.innerHTML = '';

                if (searchQuery.length === 0) {
                    searchDropdown.style.display = 'none';
                    if (!catSelect.value && currentPlatform === 'all') {
                        document.getElementById('serviceTabsContainer').style.display = 'none';
                        srvSelect.innerHTML = '<option value="" disabled selected>-- Select a Category first --</option>';
                        srvSelect.disabled = true;
                        safeSelect2Destroy(srvSelect);
                        $(srvSelect).select2({
                            width: '100%',
                            minimumResultsForSearch: 8,
                            placeholder: '-- Select a Category first --'
                        });
                    } else {
                        populateServiceDropdown();
                    }
                    return;
                }

                // Filter services for the autocomplete dropdown list
                const servicesToFilter = allServices.filter(service => {
                    const catObj = categoriesData.find(c => c.id == service.category_id);
                    return serviceMatchesPlatform(service, currentPlatform, catObj ? catObj.name : null);
                });

                const filtered = servicesToFilter.filter(service => {
                    if (currentTab !== 'All' && !searchQuery) {
                        const group = getServiceGroup(service.name);
                        if (!group || group.key !== currentTab) return false;
                    }

                    const query = normalizeText(searchQuery);
                    const srvNorm = normalizeText(service.name);
                    const idStr = (service.id || '').toString();
                    const pIdStr = (service.provider_service_id || '').toString();
                    const descNorm = normalizeText(service.description || '');
                    const priceStr = parseFloat(service.price_per_k || 0).toFixed(2);

                    let catNorm = '';
                    const catObj = categoriesData.find(c => c.id == service.category_id);
                    if (catObj && catObj.name) catNorm = normalizeText(catObj.name);

                    const nameMatch = srvNorm.includes(query);
                    const idMatch = idStr.includes(query) || pIdStr.includes(query);
                    const descMatch = descNorm.includes(query);
                    const priceMatch = priceStr.includes(query);
                    const catMatch = catNorm.includes(query);

                    return nameMatch || idMatch || descMatch || priceMatch || catMatch;
                });

                if (filtered.length > 0) {
                    searchDropdown.style.display = 'block';
                    filtered.forEach(service => {
                        const div = document.createElement('div');
                        div.className = 'search-dropdown-item';
                        div.style.padding = '10px 14px';
                        div.style.fontSize = '0.85rem';
                        div.style.color = 'var(--text-secondary)';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid var(--border-color)';
                        div.style.transition = 'var(--transition)';
                        div.style.wordBreak = 'break-word';
                        div.style.fontWeight = '300';
                        const displayId = service.id;
                        div.innerHTML = `[ID: ${displayId}] ${service.name} - <span style="color: var(--color-success); font-weight: 300;">₹${parseFloat(service.price_per_k).toFixed(2)}/1K</span>`;

                        div.addEventListener('mouseenter', () => {
                            div.style.background = 'rgba(255, 255, 255, 0.05)';
                            div.style.color = 'var(--text-primary)';
                        });
                        div.addEventListener('mouseleave', () => {
                            div.style.background = 'transparent';
                            div.style.color = 'var(--text-secondary)';
                        });

                        div.addEventListener('click', () => {
                            selectService(service.id);
                            searchDropdown.style.display = 'none';
                            serviceSearchInput.value = '';
                        });

                        searchDropdown.appendChild(div);
                    });
                } else {
                    searchDropdown.innerHTML = '<div style="padding: 12px 14px; font-size: 0.85rem; color: var(--text-muted); text-align: center;">No matching services found.</div>';
                    searchDropdown.style.display = 'block';
                }

                // Also keep the service select dropdown option list updated in parallel
                populateServiceDropdown();
            }, 200));

            // Click outside search input or dropdown to close it
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#serviceSearch') && !e.target.closest('#serviceSearchDropdown')) {
                    searchDropdown.style.display = 'none';
                }
            });

            // Show search results list when user clicks on input if there is query
            serviceSearchInput.addEventListener('focus', function () {
                if (this.value.trim().length > 0) {
                    searchDropdown.style.display = 'block';
                }
            });

            // Helper to populate and filter Service dropdown options based on current Category and active Tab Filter
            function populateServiceDropdown() {
                srvSelect.innerHTML = '<option value="" disabled selected>-- Select a Service --</option>';

                const filtered = getFilteredAndSortedServices();
                const catId = catSelect.value;

                if (filtered.length > 0) {
                    // Group services by category when "All Categories" is selected or when a specific Tab is active
                    if (!catId || catId === 'all' || currentTab !== 'All') {
                        const grouped = {};
                        const groupOrder = [];
                        filtered.forEach(service => {
                            const cId = service.category_id;
                            if (!grouped[cId]) {
                                grouped[cId] = [];
                                groupOrder.push(cId);
                            }
                            grouped[cId].push(service);
                        });

                        groupOrder.forEach(gCatId => {
                            const catObj = categoriesData.find(c => c.id == gCatId);
                            const groupLabel = catObj ? catObj.name : 'Other';
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = groupLabel;
                            grouped[gCatId].forEach(service => {
                                const opt = document.createElement('option');
                                opt.value = service.id;
                                const displayId = service.id;
                                opt.innerText = `[ID: ${displayId}] ${service.name} - ₹${parseFloat(service.price_per_k).toFixed(2)}/1K`;
                                optgroup.appendChild(opt);
                            });
                            srvSelect.appendChild(optgroup);
                        });
                    } else {
                        filtered.forEach(service => {
                            const opt = document.createElement('option');
                            opt.value = service.id;
                            const displayId = service.id;
                            opt.innerText = `[ID: ${displayId}] ${service.name} - ₹${parseFloat(service.price_per_k).toFixed(2)}/1K`;
                            srvSelect.appendChild(opt);
                        });
                    }
                    srvSelect.disabled = false;

                    let targetService = null;
                    if (activeService && filtered.some(s => s.id == activeService.id)) {
                        targetService = activeService;
                    } else if (filtered.length > 0) {
                        targetService = filtered[0];
                    }

                    if (targetService) {
                        srvSelect.value = targetService.id;
                    }

                    safeSelect2Destroy(srvSelect);
                    $(srvSelect).select2({
                        width: '100%',
                        minimumResultsForSearch: 8,
                        placeholder: '-- Select a Service --'
                    });

                    if (targetService) {
                        $(srvSelect).val(targetService.id).trigger('change');
                    }
                } else {
                    srvSelect.disabled = true;
                    safeSelect2Destroy(srvSelect);
                    $(srvSelect).select2({
                        width: '100%',
                        minimumResultsForSearch: 8,
                        placeholder: '-- No services matching --'
                    });
                }
            }

            // 1. Populate Services dropdown when Category changes
            $(catSelect).on('change', function () {
                if (isAutoSelecting) return;

                searchQuery = '';
                serviceSearchInput.value = '';

                const catId = this.value;
                const catObj = categoriesData.find(c => c.id == catId);

                if (catId && catId !== 'all' && (!catObj || !catObj.services || catObj.services.length === 0)) {
                    // Fetch category services dynamically via AJAX if not yet loaded
                    fetch('/user/dashboard/services?category_id=' + catId)
                        .then(res => res.json())
                        .then(data => {
                            if (catObj) {
                                catObj.services = data.map(srv => {
                                    srv.name = cleanBrandingJS(srv.name);
                                    srv.description = cleanBrandingJS(srv.description);
                                    return srv;
                                });
                                catObj.services.forEach(srv => {
                                    if (!allServices.some(s => s.id == srv.id)) {
                                        allServices.push(srv);
                                    }
                                });
                            }
                            populateServiceDropdown();
                            renderServicesTable();
                        })
                        .catch(err => {
                            populateServiceDropdown();
                            renderServicesTable();
                        });
                    return;
                }

                populateServiceDropdown();
                renderServicesTable();
            });

            // 2. Load Service details when Service is selected
            $(srvSelect).on('change', function () {
                const srvId = this.value;
                if (!srvId) return;

                let service = null;
                for (const cat of categoriesData) {
                    if (cat.services) {
                        const s = cat.services.find(item => item.id == srvId);
                        if (s) {
                            service = s;
                            break;
                        }
                    }
                }

                if (service) {
                    activeService = service;

                    dRate.innerText = currencySymbol + parseFloat(activeService.price_per_k).toFixed(2);
                    dLimits.innerText = `${activeService.min_quantity.toLocaleString()} Min / ${activeService.max_quantity.toLocaleString()} Max`;
                    dDesc.innerText = activeService.description ? activeService.description : 'No service description provided.';

                    detailsBox.style.display = 'block';
                    qtyInput.disabled = false;
                    qtyInput.setAttribute('min', activeService.min_quantity);
                    qtyInput.setAttribute('max', activeService.max_quantity);
                    qtyInput.placeholder = `Min: ${activeService.min_quantity} - Max: ${activeService.max_quantity}`;

                    calculateCost();
                    updateCommentsVisibility();
                    renderServicesTable();
                }
            });

            // 3. Cost calculation dynamically
            qtyInput.addEventListener('input', calculateCost);

            function calculateCost() {
                if (!activeService) return;
                const qty = parseInt(qtyInput.value);
                if (isNaN(qty) || qty <= 0) {
                    costDiv.innerText = currencySymbol + '0.00';
                    return;
                }

                const cost = (parseFloat(activeService.price_per_k) / 1000) * qty;
                costDiv.innerText = currencySymbol + cost.toFixed(4);
            }

            // Sort By Dropdown Change Event
            const sortBySelect = document.getElementById('sortBySelect');
            if (sortBySelect) {
                sortBySelect.addEventListener('change', function () {
                    currentSort = this.value;
                    populateServiceDropdown();
                    renderServicesTable();
                });
            }

            // Platform Filter Integration (Defaults to 'all' for All Platforms)
            const platformButtons = document.querySelectorAll('.platform-btn');
            currentPlatform = 'all';

            platformButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    platformButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentPlatform = this.dataset.platform;

                    searchQuery = '';
                    serviceSearchInput.value = '';

                    filterCategoriesByPlatform(currentPlatform);
                });
            });

            function filterCategoriesByPlatform(platform, isSilentPoll = false) {
                const selectedCatId = catSelect.value;
                const tabLabel = (currentTab && currentTab !== 'All') ? currentTab.toUpperCase() + ' ' : '';
                const platformLabel = platform === 'all' ? tabLabel + 'Services' : platform.toUpperCase() + ' ' + tabLabel + 'Services';

                catSelect.innerHTML = `<option value="all" ${(!selectedCatId || selectedCatId === 'all') ? 'selected' : ''}>🌐 All Categories (Browse ${platformLabel.trim()})</option>`;

                // Only include categories that actually have at least 1 active matching service
                const matchingCats = categoriesData.filter(cat => {
                    if (!isFullServicesLoaded && (!cat.services || cat.services.length === 0)) {
                        return !isJunkCategory(cat.name);
                    }
                    return getActiveMatchingServicesForCat(cat, platform, currentTab).length > 0;
                });

                let keepSelected = false;
                matchingCats.forEach(cat => {
                    const opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.innerText = cat.name;
                    if (selectedCatId && cat.id == selectedCatId) {
                        opt.selected = true;
                        keepSelected = true;
                    }
                    catSelect.appendChild(opt);
                });

                if (!keepSelected && selectedCatId && selectedCatId !== 'all') {
                    catSelect.value = 'all';
                }

                generateTabs();
                populateServiceDropdown();
                renderServicesTable();

                if (!isSilentPoll || ($('.select2-container--open').length === 0)) {
                    safeSelect2Destroy(catSelect);
                    $(catSelect).select2({
                        width: '100%',
                        minimumResultsForSearch: 8
                    });
                }
            }

            // Initial load setup on page refresh
            filterCategoriesByPlatform(currentPlatform);

            // Auto-select helper if service_id passed in query string or session
            const urlParams = new URLSearchParams(window.location.search);
            const preselectedSrv = urlParams.get('service_id') || "{{ session('service_id') }}" || "{{ old('service_id') }}";
            if (preselectedSrv) {
                selectService(preselectedSrv);
            } else if (initialCatId) {
                let initCat = categoriesData.find(cat => cat.id == initialCatId) || categoriesData[0];
                if (initCat && initCat.services && initCat.services.length > 0) {
                    isAutoSelecting = true;
                    catSelect.value = initCat.id;
                    safeSelect2Destroy(catSelect);
                    $(catSelect).select2({
                        width: '100%',
                        minimumResultsForSearch: 8
                    });
                    $(catSelect).val(initCat.id).trigger('change.select2');
                    isAutoSelecting = false;

                    const matchingSrv = initCat.services.find(s => s.status === 'active') || initCat.services[0];
                    if (matchingSrv) {
                        selectService(matchingSrv.id);
                    }
                }
            }
        });

        // 4. Modal View Details Logic
        function showDashboardServiceDetails(serviceId, event) {
            if (event) {
                event.stopPropagation();
            }

            let service = null;
            let categoryName = 'General';
            for (const cat of categoriesData) {
                const s = cat.services.find(item => item.id == serviceId);
                if (s) {
                    service = s;
                    categoryName = cat.name;
                    break;
                }
            }

            if (!service) return;

            document.getElementById('modalServiceName').innerText = service.name;
            const displayId = service.id;
            document.getElementById('modalServiceId').innerText = '#' + displayId;
            document.getElementById('modalServiceCategory').innerText = categoryName;
            document.getElementById('modalServiceRate').innerText = currencySymbol + parseFloat(service.price_per_k).toFixed(2);
            document.getElementById('modalServiceLimits').innerText = service.min_quantity.toLocaleString() + ' Min / ' + service.max_quantity.toLocaleString() + ' Max';
            document.getElementById('modalServiceTime').innerText = service.average_time ? service.average_time : 'N/A';
            document.getElementById('modalServiceDesc').innerText = service.description ? service.description : 'No description details provided.';

            // Setup the Select button inside the modal
            const selectBtn = document.getElementById('modalSelectBtn');
            selectBtn.onclick = function () {
                selectService(service.id);
                closeDetailsModal();
            };

            document.getElementById('serviceDetailsModal').classList.add('show');
        }

        function closeDetailsModal() {
            document.getElementById('serviceDetailsModal').classList.remove('show');
        }

        // 5. Copy Clipboard Helper (Safe for iOS Safari / HTTP)
        function copyId(id, event) {
            if (event) {
                event.stopPropagation();
            }
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(id).then(() => {
                    showCopyToast();
                }).catch(err => {
                    fallbackCopyId(id);
                });
            } else {
                fallbackCopyId(id);
            }
        }

        function fallbackCopyId(text) {
            try {
                const tempInput = document.createElement('textarea');
                tempInput.value = text;
                tempInput.style.position = 'fixed';
                tempInput.style.opacity = '0';
                document.body.appendChild(tempInput);
                tempInput.focus();
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showCopyToast();
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
        }

        function showCopyToast() {
            const toast = document.getElementById('copyToast');
            if (toast) {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 2000);
            }
        }

        // 6. Order Mode Switcher (Single vs Mass Order)
        function switchOrderMode(mode) {
            const singleContainer = document.getElementById('singleOrderContainer');
            const massContainer = document.getElementById('massOrderContainer');
            const tabNew = document.getElementById('tabNewOrder');
            const tabMass = document.getElementById('tabMassOrder');

            if (mode === 'mass') {
                if (singleContainer) singleContainer.style.display = 'none';
                if (massContainer) massContainer.style.display = 'block';
                if (tabNew) tabNew.classList.remove('active');
                if (tabMass) tabMass.classList.add('active');
            } else {
                if (singleContainer) singleContainer.style.display = 'block';
                if (massContainer) massContainer.style.display = 'none';
                if (tabNew) tabNew.classList.add('active');
                if (tabMass) tabMass.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const massTokenInput = document.getElementById('mass_idempotency_token');
            if (massTokenInput && !massTokenInput.value) {
                massTokenInput.value = 'mass_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            }

            const massTextarea = document.getElementById('mass_order');
            if (massTextarea) {
                massTextarea.addEventListener('blur', function () {
                    formatMassInput(this);
                });
            }

            function formatMassInput(el) {
                if (!el || !el.value) return;
                const lines = el.value.split(/\r?\n/);
                const formatted = lines.map(line => {
                    const trimmed = line.trim();
                    if (!trimmed) return '';
                    if (trimmed.includes('|')) return trimmed;
                    const match = trimmed.match(/^(\d+)\s*[\|\,\;\:\s\t\-]+\s*(https?:\/\/[^\s\|\,\;\t]+)\s*[\|\,\;\:\s\t\-]+\s*(\d+)$/i);
                    if (match) {
                        return `${match[1]} | ${match[2]} | ${match[3]}`;
                    }
                    return trimmed;
                }).join('\n');
                el.value = formatted;
            }

            const massForm = document.getElementById('massOrderSubmitForm');
            if (massForm) {
                massForm.addEventListener('submit', function () {
                    if (massTextarea) formatMassInput(massTextarea);
                    const submitBtn = massForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Mass Order...';
                    }
                });
            }
        });
    </script>
@endsection