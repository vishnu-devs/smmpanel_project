@extends('layouts.app')

@section('title', 'Manage Categories - Growinsta')
@section('page_header', 'Category Settings')

@section('styles')
<style>
    .platform-tabs-scroll-container {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 8px;
        margin-bottom: 1.25rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .platform-tabs-scroll-container::-webkit-scrollbar {
        height: 4px;
    }
    .platform-tabs-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }
    .cat-platform-tab {
        flex-shrink: 0 !important;
        white-space: nowrap !important;
    }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Add / Edit Category Form -->
    <div class="glass custom-card" style="margin-bottom: 2rem;">
        <h3 class="card-title" id="formTitle"><i class="fa-solid fa-folder-plus text-gradient"></i> Add Category</h3>
        
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="cat_id">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cat_name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="cat_name" class="form-control" placeholder="e.g. Instagram Followers" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cat_sort" class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="cat_sort" class="form-control" value="0" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cat_pinned" class="form-label">Pin Priority</label>
                    <select name="is_pinned" id="cat_pinned" class="form-control">
                        <option value="0">Normal (Unpinned)</option>
                        <option value="1">📌 Pin to Top</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cat_status" class="form-label">Category Status</label>
                    <select name="status" id="cat_status" class="form-control" required>
                        <option value="active">Active (Visible on Frontend)</option>
                        <option value="inactive">Inactive (Hidden)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px; max-width: 400px;">
                <button type="button" id="cancelEditBtn" onclick="resetForm()" class="btn-outline" style="flex: 1; padding: 10px; display: none;">
                    Cancel Edit
                </button>
                <button type="submit" class="btn-gradient" style="flex: 2; padding: 12px; font-weight: bold;">
                    Save Category
                </button>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="glass custom-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 1.25rem;">
            <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-folder-tree"></i> Available Categories</h3>
            <form action="{{ route('admin.categories.clean_empty') }}" method="POST" onsubmit="return confirm('Permanently remove all empty categories (Total: 0 Services) and unused obsolete services to optimize database storage?');">
                @csrf
                <button type="submit" class="btn-gradient" style="padding: 7px 14px; font-size: 0.78rem; background: var(--grad-danger); font-weight: bold; border-radius: var(--radius-sm);" title="Delete empty categories (0 services) and obsolete services with 0 orders">
                    <i class="fa-solid fa-trash-can"></i> Clean Empty Categories & Obsolete Services
                </button>
            </form>
        </div>

        @php
            $rawEnabled = App\Models\Setting::get('enabled_platforms', null);
            $enabledPlatforms = $rawEnabled !== null ? (json_decode($rawEnabled, true) ?: ['all']) : ['all', 'instagram', 'facebook', 'youtube', 'telegram', 'whatsapp', 'tiktok', 'twitter'];

            $allPlatformsMap = [
                'all' => '🌐 All Platforms',
                'instagram' => '📷 Instagram',
                'facebook' => '👤 Facebook',
                'youtube' => '▶️ YouTube',
                'telegram' => '✈️ Telegram',
                'whatsapp' => '💬 WhatsApp',
                'tiktok' => '🎵 TikTok',
                'twitter' => '🐤 Twitter / X',
                'spotify' => '🎵 Spotify',
                'discord' => '💬 Discord',
            ];

            $platforms = [];
            foreach ($enabledPlatforms as $pKey) {
                if (isset($allPlatformsMap[$pKey])) {
                    $platforms[$pKey] = $allPlatformsMap[$pKey];
                }
            }
            if (!isset($platforms['all'])) {
                $platforms = array_merge(['all' => '🌐 All Platforms'], $platforms);
            }

            $currentPlatform = request('platform', 'all');
        @endphp

        <!-- Platform Filter Tabs (Touch Scrollable on Mobile & Desktop) -->
        <div class="platform-tabs-scroll-container">
            @foreach($platforms as $pKey => $pLabel)
                <a href="{{ route('admin.categories', array_merge(request()->query(), ['platform' => $pKey])) }}" 
                   class="btn-outline cat-platform-tab" 
                   data-platform="{{ $pKey }}"
                   style="padding: 7px 16px; font-size: 0.82rem; border-radius: 30px; text-decoration: none; flex-shrink: 0; white-space: nowrap; {{ $currentPlatform === $pKey ? 'background: var(--grad-insta); color: white !important; border-color: transparent; font-weight: 700;' : 'color: var(--text-secondary);' }}">
                    {{ $pLabel }}
                </a>
            @endforeach
        </div>
        
        <!-- Filter Controls Bar: Provider Selector + Sort Filter + Search input -->
        <form id="catFilterForm" action="{{ route('admin.categories') }}" method="GET" style="margin-bottom: 1.5rem; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;" onsubmit="return false;">
            <input type="hidden" name="platform" id="catPlatformInput" value="{{ $currentPlatform }}">

            <!-- Mobile Platform Dropdown Selector -->
            <div style="min-width: 200px; flex: 1;">
                <label style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; display: block; font-weight: 600;">
                    <i class="fa-solid fa-layer-group" style="color: var(--color-primary);"></i> Filter Platform:
                </label>
                <select id="mobilePlatformSelect" class="form-control" style="padding: 10px 14px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border-color: var(--border-color);">
                    @foreach($platforms as $pKey => $pLabel)
                        <option value="{{ $pKey }}" {{ $currentPlatform === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Provider Select Dropdown Filter -->
            <div style="min-width: 220px; flex: 1;">
                <label style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; display: block; font-weight: 600;">
                    <i class="fa-solid fa-plug" style="color: var(--color-primary);"></i> Select Provider API:
                </label>
                <select name="provider_id" id="catProviderSelect" class="form-control" style="padding: 10px 14px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border-color: var(--border-color);">
                    <option value="all" {{ ($providerId ?? 'all') == 'all' ? 'selected' : '' }}>🌐 All Providers (API & Manual)</option>
                    <option value="manual" {{ ($providerId ?? 'all') == 'manual' ? 'selected' : '' }}>🛠️ Manual / Internal Services Only</option>
                    @if(isset($providers) && $providers->count() > 0)
                        @foreach($providers as $prov)
                            <option value="{{ $prov->id }}" {{ ($providerId ?? 'all') == $prov->id ? 'selected' : '' }}>
                                ⚡ {{ $prov->name }} (Provider API #{{ $prov->id }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Sort Order Ascending / Descending Dropdown Filter -->
            <div style="min-width: 220px; flex: 1;">
                <label style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; display: block; font-weight: 600;">
                    <i class="fa-solid fa-arrow-down-up-wide" style="color: var(--color-primary);"></i> Sort Category List:
                </label>
                <select name="sort" id="catSortSelect" class="form-control" style="padding: 10px 14px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border-color: var(--border-color);">
                    <option value="order_asc" {{ ($sort ?? 'order_asc') == 'order_asc' ? 'selected' : '' }}>🔢 Sort Order: Ascending (#1 ➔ #9999)</option>
                    <option value="order_desc" {{ ($sort ?? 'order_asc') == 'order_desc' ? 'selected' : '' }}>⬇️ Sort Order: Descending (#9999 ➔ #1)</option>
                    <option value="id_desc" {{ ($sort ?? 'order_asc') == 'id_desc' ? 'selected' : '' }}>✨ Newest Added First (ID High ➔ Low)</option>
                    <option value="id_asc" {{ ($sort ?? 'order_asc') == 'id_asc' ? 'selected' : '' }}>📜 Oldest First (ID Low ➔ High)</option>
                    <option value="name_asc" {{ ($sort ?? 'order_asc') == 'name_asc' ? 'selected' : '' }}>🔤 Name: A to Z</option>
                </select>
            </div>

            <!-- Search by Name or ID Input -->
            <div style="flex: 2; min-width: 260px; display: flex; flex-direction: column;">
                <label style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; display: block; font-weight: 600;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search Category Name/ID:
                </label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="search" id="catSearchInput" class="form-control" placeholder="Search by category name or ID..." value="{{ $search ?? '' }}" style="padding: 10px 16px;">
                    <button type="button" id="catSearchBtn" class="btn-gradient" style="padding: 10px 20px;">Search</button>
                    <a href="{{ route('admin.categories') }}" id="catClearBtn" class="btn-outline" style="padding: 10px 15px; text-decoration: none; {{ (request('search') || (request('provider_id') && request('provider_id') !== 'all') || (request('sort') && request('sort') !== 'order_asc')) ? '' : 'display: none;' }}">Clear</a>
                </div>
            </div>
        </form>

        <!-- Table Container for AJAX Live Refresh -->
        <div id="categoriesTableContainer" style="position: relative; min-height: 200px;">
            @include('admin.partials.categories_table')
        </div>
    </div>

    @push('modals')
    <!-- Edit Category Modal Overlay -->
    <div id="editCategoryModal" class="custom-modal" onclick="if(event.target===this) closeEditCategoryModal()">
        <div class="custom-modal-content glass animate-fade-in" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 90%; max-width: 550px; max-height: 91vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 0;">
            <div class="custom-modal-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 class="custom-modal-title" style="margin: 0; font-size: 1.2rem; font-weight: bold; color: var(--text-primary);"><i class="fa-solid fa-pen-to-square text-gradient"></i> Edit Category</h3>
                <button class="custom-modal-close" onclick="closeEditCategoryModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit_cat_id">
                <div class="custom-modal-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_cat_name" class="form-label">Category Name</label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                    </div>

                    <!-- Provider API Original Name Info Box -->
                    <div id="edit_cat_orig_box" style="display: none; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); padding: 10px 12px; border-radius: var(--radius-sm);">
                        <div style="font-size: 0.78rem; color: #60a5fa; margin-bottom: 4px;">
                            <i class="fa-solid fa-cloud"></i> <strong>Provider API Name:</strong> <span id="edit_cat_orig_name" style="color: var(--text-primary); font-style: italic;"></span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--text-secondary); cursor: pointer; margin: 0;">
                            <input type="checkbox" name="reset_original" value="1" style="accent-color: var(--color-primary); cursor: pointer;">
                            <span>Revert title back to Provider API Name</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="edit_cat_sort" class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_cat_sort" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                    </div>

                    <div class="form-group">
                        <label for="edit_cat_status" class="form-label">Category Status</label>
                        <select name="status" id="edit_cat_status" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="active">Active (Visible on Frontend)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_cat_pinned" class="form-label">Pin Priority</label>
                        <select name="is_pinned" id="edit_cat_pinned" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="0">Normal (Unpinned)</option>
                            <option value="1">📌 Pin to Top</option>
                        </select>
                    </div>
                </div>
                <div class="custom-modal-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-outline" style="padding: 8px 18px;" onclick="closeEditCategoryModal()">Cancel</button>
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
    function openEditCategoryModal(cat) {
        document.getElementById('edit_cat_id').value = cat.id;
        document.getElementById('edit_cat_name').value = cat.name;
        document.getElementById('edit_cat_sort').value = cat.sort_order;
        document.getElementById('edit_cat_status').value = cat.status;
        document.getElementById('edit_cat_pinned').value = cat.is_pinned ? '1' : '0';
        
        const origBox = document.getElementById('edit_cat_orig_box');
        const origNameEl = document.getElementById('edit_cat_orig_name');
        if (origBox && origNameEl) {
            if (cat.original_name) {
                origNameEl.textContent = cat.original_name;
                origBox.style.display = 'block';
            } else {
                origBox.style.display = 'none';
            }
        }

        const modal = document.getElementById('editCategoryModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeEditCategoryModal() {
        document.getElementById('editCategoryModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    function resetForm() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_sort').value = '0';
        document.getElementById('cat_status').value = 'active';
        
        document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-folder-plus text-gradient"></i> Add Category';
        document.getElementById('cancelEditBtn').style.display = 'none';
    }

    // Event Delegation for Edit Buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-category-btn');
        if (editBtn) {
            try {
                const cat = JSON.parse(editBtn.getAttribute('data-category'));
                openEditCategoryModal(cat);
            } catch (err) {
                console.error('Error parsing category data:', err);
            }
        }
    });

    // AJAX Live Search & Filter Execution
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('categoriesTableContainer');
        const platformInput = document.getElementById('catPlatformInput');
        const providerSelect = document.getElementById('catProviderSelect');
        const sortSelect = document.getElementById('catSortSelect');
        const searchInput = document.getElementById('catSearchInput');
        const clearBtn = document.getElementById('catClearBtn');
        let searchTimeout = null;

        function fetchCategoriesData() {
            container.style.opacity = '0.5';
            
            const params = new URLSearchParams();
            if (platformInput && platformInput.value) params.set('platform', platformInput.value);
            if (providerSelect && providerSelect.value) params.set('provider_id', providerSelect.value);
            if (sortSelect && sortSelect.value) params.set('sort', sortSelect.value);
            if (searchInput && searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());

            const url = `{{ route('admin.categories') }}?${params.toString()}`;

            // Toggle clear button
            if (clearBtn) {
                const isFiltered = (searchInput && searchInput.value.trim() !== '') ||
                                   (providerSelect && providerSelect.value !== 'all') ||
                                   (sortSelect && sortSelect.value !== 'order_asc');
                clearBtn.style.display = isFiltered ? 'inline-block' : 'none';
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.html !== undefined) {
                    container.innerHTML = data.html;
                }
            })
            .catch(err => console.error('Categories AJAX fetch error:', err))
            .finally(() => {
                container.style.opacity = '1';
            });
        }

        // Platform Tab Clicks
        document.querySelectorAll('.cat-platform-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const pKey = this.getAttribute('data-platform');
                if (platformInput) platformInput.value = pKey;
                const mobileSelect = document.getElementById('mobilePlatformSelect');
                if (mobileSelect) mobileSelect.value = pKey;

                document.querySelectorAll('.cat-platform-tab').forEach(t => {
                    t.style.background = '';
                    t.style.color = 'var(--text-secondary)';
                    t.style.borderColor = '';
                    t.style.fontWeight = 'normal';
                });
                this.style.background = 'var(--grad-insta)';
                this.style.color = 'white';
                this.style.borderColor = 'transparent';
                this.style.fontWeight = '700';

                fetchCategoriesData();
            });
        });

        // Mobile Platform Dropdown Selection
        const mobilePlatformSelect = document.getElementById('mobilePlatformSelect');
        if (mobilePlatformSelect) {
            mobilePlatformSelect.addEventListener('change', function() {
                const pKey = this.value;
                if (platformInput) platformInput.value = pKey;
                document.querySelectorAll('.cat-platform-tab').forEach(t => {
                    if (t.getAttribute('data-platform') === pKey) {
                        t.style.background = 'var(--grad-insta)';
                        t.style.color = 'white';
                        t.style.borderColor = 'transparent';
                        t.style.fontWeight = '700';
                        t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } else {
                        t.style.background = '';
                        t.style.color = 'var(--text-secondary)';
                        t.style.borderColor = '';
                        t.style.fontWeight = 'normal';
                    }
                });
                fetchCategoriesData();
            });
        }

        // Dropdown Filters
        if (providerSelect) providerSelect.addEventListener('change', fetchCategoriesData);
        if (sortSelect) sortSelect.addEventListener('change', fetchCategoriesData);

        // Search Input
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchCategoriesData, 300);
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    fetchCategoriesData();
                }
            });
        }

        // AJAX Pagination Click Interceptor
        container.addEventListener('click', function(e) {
            const link = e.target.closest('.ajax-pagination-wrapper a, .pagination a');
            if (link && link.href) {
                e.preventDefault();
                container.style.opacity = '0.5';
                fetch(link.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.html !== undefined) {
                        container.innerHTML = data.html;
                        window.scrollTo(0, container.offsetTop - 80);
                    }
                })
                .catch(err => console.error('Categories pagination AJAX error:', err))
                .finally(() => {
                    container.style.opacity = '1';
                });
            }
        });

        // Handle AJAX submission for Pin, Delete, and Edit Forms without page reloads
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('ajax-pin-form') || form.classList.contains('ajax-delete-form') || form.closest('#editCategoryModal')) {
                e.preventDefault();
                const savedScrollY = window.scrollY;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        if (form.closest('#editCategoryModal')) {
                            closeEditCategoryModal();
                        }
                        // Refresh categories table and keep scroll position
                        container.style.opacity = '0.5';
                        const params = new URLSearchParams();
                        if (platformInput && platformInput.value) params.set('platform', platformInput.value);
                        if (providerSelect && providerSelect.value) params.set('provider_id', providerSelect.value);
                        if (sortSelect && sortSelect.value) params.set('sort', sortSelect.value);
                        if (searchInput && searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());

                        const refreshUrl = `{{ route('admin.categories') }}?${params.toString()}`;
                        return fetch(refreshUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        }).then(r => r.json()).then(tableData => {
                            if (tableData && tableData.html !== undefined) {
                                container.innerHTML = tableData.html;
                            }
                            window.scrollTo(0, savedScrollY);
                        });
                    } else if (data && data.message) {
                        alert(data.message);
                    }
                })
                .catch(err => console.error('Form AJAX submit error:', err))
                .finally(() => {
                    container.style.opacity = '1';
                });
            }
        });
    });
</script>
@endsection
