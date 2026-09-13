@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Services Catalog - Growinsta')
@section('page_header', 'Services List')

@section('content')
<div style="padding: {{ Auth::check() ? '0' : '3rem 5%' }}; max-width: 1200px; margin: 0 auto;">

    @if(!Auth::check())
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">Our Marketing <span class="text-gradient">Services</span></h2>
            <p style="color: var(--text-secondary);">Browse our catalog of automated promotion and growth services.</p>
        </div>
    @endif

    <!-- Filtering & Search Bar -->
    <div class="glass services-filter-bar" style="border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 10px; flex-grow: 1; min-width: 280px;">
            <input type="text" id="serviceSearch" class="form-control" placeholder="Search services (e.g. Instagram Followers)..." aria-label="Search services" style="padding: 10px 16px;">
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <select id="categoryFilter" class="form-control" aria-label="Filter by category" style="width: 220px; padding: 10px 16px;">
                <option value="all">All Categories</option>
                @foreach($categories as $category)
                    @if($category->services && $category->services->where('status', 'active')->count() > 0)
                        <option value="cat-{{ $category->id }}">{{ $category->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <!-- Services Grouped By Categories -->
    <div id="servicesContainer">
        @foreach($categories as $category)
            @if($category->services && $category->services->where('status', 'active')->count() > 0)
                <div class="category-block" id="cat-{{ $category->id }}" style="margin-bottom: 3rem;">
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 6px; height: 24px; background: var(--grad-primary); border-radius: 4px; display: inline-block;"></span>
                        {{ $category->name }}
                    </h3>
                    
                    <div class="glass" style="border-radius: var(--radius-lg); overflow: hidden;">
                        <div class="table-responsive">
                            <table class="custom-table" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th>Service</th>
                                        <th style="width: 140px;">Rate per 1000</th>
                                        <th style="width: 100px;">Min</th>
                                        <th style="width: 100px;">Max</th>
                                        <th style="width: 160px;">Average Time <i class="fa-solid fa-circle-info" style="font-size: 0.78rem; opacity: 0.65; cursor: help;" title="Average completion time based on recent orders"></i></th>
                                        <th style="width: 130px; text-align: center;">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->services->where('status', 'active') as $service)
                                        <tr class="service-row" data-name="{{ strtolower($service->name) }}">
                                            <td data-label="ID" style="font-weight: bold; color: var(--text-muted); vertical-align: middle;">
                                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                                                    <span>{{ $service->id }}</span>
                                                    <button class="copy-id-btn" onclick="copyId('{{ $service->id }}', event)" title="Copy Service ID" aria-label="Copy Service ID #{{ $service->id }}" style="padding: 0;">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td data-label="Service" style="vertical-align: middle;">
                                                <div class="service-name-title" style="font-weight: 400; color: var(--text-primary);">{{ $service->name }}</div>
                                            </td>
                                            <td data-label="Rate/1K" style="vertical-align: middle; font-weight: 400; color: var(--color-success);">
                                                <span class="price-val" data-inr="{{ $service->price_per_k }}">
                                                    ₹{{ format_currency($service->price_per_k) }}
                                                </span>
                                            </td>
                                            <td data-label="Min" style="vertical-align: middle; font-size: 0.85rem; color: var(--text-secondary);">
                                                {{ number_format($service->min_quantity) }}
                                            </td>
                                            <td data-label="Max" style="vertical-align: middle; font-size: 0.85rem; color: var(--text-secondary);">
                                                {{ number_format($service->max_quantity) }}
                                            </td>
                                            <td data-label="Average Time" style="vertical-align: middle; font-size: 0.85rem; font-weight: 400; color: var(--text-primary);">
                                                {{ $service->average_time ?: 'N/A' }}
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <button class="btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-sm); white-space: nowrap;" onclick="showServiceDetails({{ $service->id }}, event)">
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Load More Button Container -->
    <div id="loadMoreContainer" style="text-align: center; margin: 3rem 0; display: none;">
        <button id="loadMoreBtn" class="btn-gradient" style="padding: 14px 32px; font-size: 0.95rem; font-weight: 700; border-radius: 30px; border: none; cursor: pointer; box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3); display: inline-flex; align-items: center; gap: 10px; transition: transform 0.2s ease;">
            <i class="fa-solid fa-circle-chevron-down" style="font-size: 1.1rem;"></i>
            Load More Services (<span id="remainingCatCount">0</span> categories remaining)
        </button>
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
            <h4 id="modalServiceName" style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary); line-height: 1.4;"></h4>
            
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
                <p id="modalServiceDesc" style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6; white-space: pre-line; word-break: break-word; background: rgba(0,0,0,0.15); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin: 0;"></p>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-outline" style="height: 40px; padding: 0 18px; font-size: 0.88rem;" onclick="closeDetailsModal()">Close</button>
            <a id="modalActionBtn" href="#" class="btn-gradient" style="height: 40px; padding: 0 22px; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center;">Order Now</a>
        </div>
    </div>
</div>
@endpush

<!-- Copy Success Toast -->
<div id="copyToast" class="copy-toast">
    <i class="fa-solid fa-circle-check"></i> Copied to Clipboard!
</div>
@endsection

@section('scripts')
<script>
    const categoriesData = @json($categories);

    document.addEventListener('DOMContentLoaded', function() {
        // Debounce helper to prevent input lag
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        const searchInput = document.getElementById('serviceSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const categoryBlocks = Array.from(document.querySelectorAll('.category-block'));
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        const remainingCatCount = document.getElementById('remainingCatCount');

        let visibleBatchLimit = 10;
        const batchIncrement = 15;
        
        // 1. Live Text Search Filter (Debounced)
        searchInput.addEventListener('input', debounce(filterServices, 200));
        
        // 2. Category Select Filter
        categoryFilter.addEventListener('change', filterServices);

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                visibleBatchLimit += batchIncrement;
                filterServices();
            });
        }
        
        function normalizeText(str) {
            if (!str) return '';
            try {
                return str.toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            } catch (e) {
                return str.toString().toLowerCase();
            }
        }

        function filterServices() {
            const query = normalizeText(searchInput.value);
            const selectedCat = categoryFilter.value;
            const isFiltering = query.length > 0 || selectedCat !== 'all';
            
            let matchingBlocks = [];
            
            categoryBlocks.forEach(block => {
                const blockId = block.id;
                const rows = block.querySelectorAll('.service-row');
                let visibleRows = 0;
                
                rows.forEach(row => {
                    const name = normalizeText(row.getAttribute('data-name') || '');
                    const srvId = (row.querySelector('[data-label="ID"] span') ? row.querySelector('[data-label="ID"] span').innerText : '').toLowerCase();
                    const matchesSearch = name.includes(query) || srvId.includes(query);
                    
                    if (matchesSearch) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show/hide entire category block based on category filter and search match counts
                const matchesCat = (selectedCat === 'all' || blockId === selectedCat);
                
                if (matchesCat && visibleRows > 0) {
                    matchingBlocks.push(block);
                } else {
                    block.style.display = 'none';
                }
            });

            if (isFiltering) {
                // When searching/filtering, display all matching categories directly
                matchingBlocks.forEach(block => block.style.display = '');
                if (loadMoreContainer) loadMoreContainer.style.display = 'none';
            } else {
                // Progressive batch loading
                let hiddenCount = 0;
                matchingBlocks.forEach((block, index) => {
                    if (index < visibleBatchLimit) {
                        block.style.display = '';
                    } else {
                        block.style.display = 'none';
                        hiddenCount++;
                    }
                });

                if (hiddenCount > 0) {
                    if (loadMoreContainer) loadMoreContainer.style.display = 'block';
                    if (remainingCatCount) remainingCatCount.innerText = hiddenCount;
                } else {
                    if (loadMoreContainer) loadMoreContainer.style.display = 'none';
                }
            }
        }

        // Initialize progressive batch view on load
        filterServices();
    });

    // 4. Modal View Details Logic
    function showServiceDetails(serviceId, event) {
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
        document.getElementById('modalServiceId').innerText = '#' + service.id;
        document.getElementById('modalServiceCategory').innerText = categoryName;
        
        // Rate is always in INR
        const inrPrice = parseFloat(service.price_per_k);
        document.getElementById('modalServiceRate').innerText = '₹' + inrPrice.toFixed(2);
        document.getElementById('modalServiceLimits').innerText = service.min_quantity.toLocaleString() + ' Min / ' + service.max_quantity.toLocaleString() + ' Max';
        document.getElementById('modalServiceTime').innerText = service.average_time ? service.average_time : 'N/A';
        document.getElementById('modalServiceDesc').innerText = service.description ? service.description : 'No description details provided.';
        
        // Set action button link
        const isAuth = @json(Auth::check());
        const actionBtn = document.getElementById('modalActionBtn');
        if (isAuth) {
            actionBtn.href = "{{ route('dashboard') }}?service_id=" + service.id;
            actionBtn.innerText = 'Order Now';
        } else {
            actionBtn.href = "{{ route('register') }}";
            actionBtn.innerText = 'Register & Buy';
        }
        
        document.getElementById('serviceDetailsModal').classList.add('show');
    }

    function closeDetailsModal() {
        document.getElementById('serviceDetailsModal').classList.remove('show');
    }

    // 5. Copy Clipboard Logic (Safe for iOS Safari / HTTP)
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
</script>
