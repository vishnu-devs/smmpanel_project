@if($categories->count() > 0)
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Category Name & Provider</th>
                    <th style="width: 170px;">Services Breakdown</th>
                    <th style="width: 100px;">Sort Order</th>
                    <th style="width: 120px;">Category Status</th>
                    <th style="text-align: right; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                    @php
                        $totalSrvCount = $cat->services->count();
                        $activeSrvCount = $cat->services->where('status', 'active')->count();
                        $inactiveSrvCount = $totalSrvCount - $activeSrvCount;

                        $catProviders = $cat->services->map(function($s) {
                            return $s->provider ? $s->provider->name : 'Manual';
                        })->unique()->values();
                    @endphp
                    <tr>
                        <td style="font-weight: bold; color: var(--text-muted);">#{{ $cat->id }}</td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span>{{ $cat->name }}</span>
                                <span style="font-size: 0.7rem; background: rgba(255,255,255,0.08); border: 1px solid var(--border-color); color: var(--text-secondary); padding: 2px 6px; border-radius: 4px; text-transform: capitalize;">
                                    {{ $cat->platform }}
                                </span>
                                @if($cat->is_pinned)
                                    <span class="badge" style="font-size: 0.68rem; background: #facc15; color: #000; font-weight: bold;">📌 PINNED TOP</span>
                                @endif
                                @if($cat->is_custom_name && $cat->original_name && $cat->original_name !== $cat->name)
                                    <span class="badge" style="font-size: 0.68rem; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">🏷️ Custom Name</span>
                                @endif
                            </div>
                            @if($cat->is_custom_name && $cat->original_name && $cat->original_name !== $cat->name)
                                <div style="margin-top: 3px; font-size: 0.73rem; color: #60a5fa; font-weight: 500;">
                                    <i class="fa-solid fa-cloud"></i> <strong>Provider API Name:</strong> <em>{{ $cat->original_name }}</em>
                                </div>
                            @endif
                            <div style="margin-top: 4px; font-size: 0.78rem; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <i class="fa-solid fa-plug" style="font-size: 0.75rem; color: var(--color-primary);"></i>
                                <strong>Provider:</strong>
                                @if($catProviders->count() > 0)
                                    @foreach($catProviders as $pName)
                                        <span style="background: rgba(124, 58, 237, 0.1); color: #8b5cf6; border: 1px solid rgba(124, 58, 237, 0.2); padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">
                                            {{ $pName }}
                                        </span>
                                    @endforeach
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">No services</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.8rem;">
                                <span style="color: var(--color-success); font-weight: 600;">✓ {{ $activeSrvCount }} Active</span>
                                @if($inactiveSrvCount > 0)
                                    <span style="color: var(--color-danger); font-weight: 600; margin-left: 4px;">✕ {{ $inactiveSrvCount }} Inactive</span>
                                @endif
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Total: {{ $totalSrvCount }} Services</div>
                            </div>
                        </td>
                        <td><span class="badge badge-processing" style="font-size: 0.8rem;">#{{ $cat->sort_order }}</span></td>
                        <td>
                            @if($cat->status === 'active')
                                <span class="badge badge-completed">Active</span>
                            @else
                                <span class="badge badge-canceled">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                <form action="{{ route('admin.categories.toggle_pin', $cat->id) }}" method="POST" class="ajax-pin-form" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-outline" style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); {{ $cat->is_pinned ? 'background: rgba(250, 204, 21, 0.2); color: #facc15 !important; border-color: #facc15;' : '' }}">
                                        {{ $cat->is_pinned ? '📌 Pinned' : '📍 Pin' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-outline edit-category-btn" data-category="{{ json_encode($cat) }}" style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                    Edit
                                </button>
                                <form action="{{ route('admin.categories.delete', $cat->id) }}" method="POST" class="ajax-delete-form" style="display: inline;" onsubmit="return confirm('Deleting this category will delete all services inside it. Continue?')">
                                    @csrf
                                    <button type="submit" class="btn-gradient" style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-danger);">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ajax-pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $categories->links() }}
    </div>
@else
    <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
        <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
        <p>No categories found matching your filter criteria.</p>
    </div>
@endif
