@extends('layouts.app')

@section('title', 'Social Platforms Management - ' . App\Models\Setting::get('site_name', 'SMM Panel'))
@section('page_header', 'Social Media Platforms')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Top Action Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                <i class="fa-solid fa-share-nodes text-gradient" style="margin-right: 8px;"></i> Social Platforms Manager
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin: 0;">
                Add, toggle, or customize social media platforms visible to customers on the New Order page.
            </p>
        </div>
        <div>
            <button type="button" onclick="openAddPlatformModal()" class="btn-gradient" style="padding: 10px 20px; font-weight: 700; border-radius: var(--radius-md); font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="fa-solid fa-plus-circle"></i> Add New Social Platform
            </button>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="glass custom-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem; border: 1px solid rgba(2, 132, 199, 0.3); background: rgba(2, 132, 199, 0.05); border-radius: var(--radius-md);">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <i class="fa-solid fa-circle-info" style="color: #0284c7; font-size: 1.2rem; margin-top: 2px;"></i>
            <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                <strong style="color: var(--text-primary);">💡 Dynamic Auto-Matching Active:</strong> 
                When you add or enable a platform, the system automatically scans category and service names using its keywords (e.g. <code>linkedin</code>, <code>coinmarketcap</code>, <code>traffic</code>) to group and filter them for customers in real-time.
            </div>
        </div>
    </div>

    <!-- Platforms Grid Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 2rem;">
        @foreach($platforms as $platform)
            <div class="glass custom-card" style="margin: 0; padding: 1.25rem; border-top: 3px solid {{ $platform->color ?: '#0284c7' }}; position: relative;">
                <div style="display: flex; items-center; justify-content: space-between; gap: 10px; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: {{ $platform->color ?: '#0284c7' }}; flex-shrink: 0;">
                            <i class="{{ $platform->icon ?: 'fa-solid fa-globe' }}"></i>
                        </div>
                        <div style="min-width: 0;">
                            <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $platform->name }}
                            </h4>
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">key: {{ $platform->key }}</span>
                        </div>
                    </div>
                    <div>
                        @if($platform->is_enabled)
                            <span class="badge badge-completed" style="font-size: 0.7rem; padding: 3px 8px;">Active</span>
                        @else
                            <span class="badge badge-canceled" style="font-size: 0.7rem; padding: 3px 8px;">Disabled</span>
                        @endif
                    </div>
                </div>

                <!-- Keywords List -->
                <div style="margin-bottom: 14px; background: rgba(0,0,0,0.12); padding: 8px 10px; border-radius: var(--radius-sm); font-size: 0.78rem; color: var(--text-secondary);">
                    <span style="color: var(--text-muted); font-weight: 600;">Keywords:</span> 
                    <code>{{ $platform->keywords ?: strtolower($platform->name) }}</code>
                </div>

                <!-- Actions Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <div style="display: flex; gap: 6px;">
                        <!-- Enable/Disable Toggle Form -->
                        <form action="{{ route('admin.platforms.toggle', $platform->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-outline" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);" title="Toggle active status">
                                @if($platform->is_enabled)
                                    <i class="fa-solid fa-eye-slash text-danger"></i> Disable
                                @else
                                    <i class="fa-solid fa-eye text-success"></i> Enable
                                @endif
                            </button>
                        </form>

                        @if($platform->is_enabled && !$platform->is_default)
                            <form action="{{ route('admin.platforms.set_default', $platform->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-outline" style="padding: 4px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);" title="Set as default platform on New Order page">
                                    ⭐ Default
                                </button>
                            </form>
                        @elseif($platform->is_default)
                            <span style="font-size: 0.75rem; font-weight: 700; color: #f59e0b; padding: 4px 6px;">⭐ Pre-Selected</span>
                        @endif
                    </div>

                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn-outline" onclick='openEditPlatformModal(@json($platform))' style="padding: 4px 8px; font-size: 0.75rem;" title="Edit Platform">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        @if($platform->key !== 'all')
                            <form action="{{ route('admin.platforms.delete', $platform->id) }}" method="POST" onsubmit="return confirm('Delete social platform {{ $platform->name }}?')" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: rgba(239,68,68,0.3);" title="Delete Platform">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

@push('modals')
<!-- Modal: Add / Edit Platform (Rendered at Body Root level via @stack('modals')) -->
<div id="platformModal" class="custom-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.8); z-index: 999999; overflow-y: auto; padding: 20px 15px; box-sizing: border-box; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="glass" style="width: 100%; max-width: 520px; margin: 20px auto; border-radius: var(--radius-lg); background: var(--bg-card, #1e293b); border: 1px solid var(--border-color); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); overflow: hidden; position: relative; max-height: calc(100vh - 40px); display: flex; flex-direction: column;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-card, #1e293b); flex-shrink: 0;">
            <h3 id="modalTitle" style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                Add New Social Platform
            </h3>
            <button type="button" onclick="closePlatformModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; line-height: 1; padding: 0 4px;" title="Close">&times;</button>
        </div>

        <form action="{{ route('admin.platforms.store') }}" method="POST" id="platformForm" style="display: flex; flex-direction: column; overflow: hidden; flex: 1; margin: 0;">
            @csrf
            <input type="hidden" name="id" id="platform_id" value="">

            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 14px; overflow-y: auto; flex: 1;">
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="platform_name" class="form-label">Platform Display Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="platform_name" class="form-control" placeholder="e.g. LinkedIn, Website Traffic, Pinterest" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="platform_key" class="form-label">System Key / Slug <small style="color: var(--text-muted);">(Auto-generated if empty)</small></label>
                    <input type="text" name="key" id="platform_key" class="form-control" placeholder="e.g. linkedin, website_traffic">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="platform_icon" class="form-label">FontAwesome Icon Class</label>
                        <input type="text" name="icon" id="platform_icon" class="form-control" placeholder="fa-brands fa-linkedin" value="fa-solid fa-globe">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="platform_color" class="form-label">Brand Color Hex</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="color" id="color_picker" style="height: 42px; width: 45px; padding: 2px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;" onchange="document.getElementById('platform_color').value = this.value">
                            <input type="text" name="color" id="platform_color" class="form-control" placeholder="#0a66c2" value="#0284c7" onchange="document.getElementById('color_picker').value = this.value">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="platform_keywords" class="form-label">Matching Keywords <small style="color: var(--text-muted);">(Comma separated)</small></label>
                    <textarea name="keywords" id="platform_keywords" class="form-control" rows="2" placeholder="e.g. linkedin, linked in, company page"></textarea>
                    <small style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 4px;">
                        Services or Categories matching any of these keywords will be grouped under this platform button.
                    </small>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.15); padding: 10px 14px; border-radius: var(--radius-sm); margin-top: 4px;">
                    <div>
                        <strong style="font-size: 0.88rem; color: var(--text-primary);">Enable Platform</strong>
                        <span style="font-size: 0.78rem; color: var(--text-secondary); display: block;">Show button to customers on New Order page</span>
                    </div>
                    <input type="checkbox" name="is_enabled" id="platform_is_enabled" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="platform_sort_order" class="form-label">Sort Order Position</label>
                    <input type="number" name="sort_order" id="platform_sort_order" class="form-control" value="0">
                </div>

            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background: rgba(0,0,0,0.15); display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" class="btn-outline" onclick="closePlatformModal()">Cancel</button>
                <button type="submit" class="btn-gradient" style="padding: 8px 22px; font-weight: 700;">Save Platform</button>
            </div>
        </form>
    </div>
</div>
@endpush

@endsection

@section('scripts')
<script>
function openAddPlatformModal() {
    document.getElementById('modalTitle').innerText = 'Add New Social Platform';
    document.getElementById('platform_id').value = '';
    document.getElementById('platformForm').reset();
    document.getElementById('platform_is_enabled').checked = true;
    document.getElementById('platform_color').value = '#0284c7';
    document.getElementById('color_picker').value = '#0284c7';
    document.getElementById('platformModal').style.display = 'flex';
}

function openEditPlatformModal(platform) {
    document.getElementById('modalTitle').innerText = 'Edit Social Platform: ' + platform.name;
    document.getElementById('platform_id').value = platform.id;
    document.getElementById('platform_name').value = platform.name;
    document.getElementById('platform_key').value = platform.key;
    document.getElementById('platform_icon').value = platform.icon || 'fa-solid fa-globe';
    document.getElementById('platform_color').value = platform.color || '#0284c7';
    document.getElementById('color_picker').value = platform.color || '#0284c7';
    document.getElementById('platform_keywords').value = platform.keywords || '';
    document.getElementById('platform_is_enabled').checked = platform.is_enabled ? true : false;
    document.getElementById('platform_sort_order').value = platform.sort_order || 0;
    document.getElementById('platformModal').style.display = 'flex';
}

function closePlatformModal() {
    document.getElementById('platformModal').style.display = 'none';
}
</script>
@endsection
