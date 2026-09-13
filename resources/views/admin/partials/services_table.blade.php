@if($services->count() > 0)
    <div class="table-responsive">
        <table class="custom-table" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Service details</th>
                    <th>Client Rate</th>
                    <th>Min / Max</th>
                    <th>Fulfilled By</th>
                    <th style="text-align: right; width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $srv)
                    <tr>
                        <td style="font-weight: bold; color: var(--text-primary);">#{{ $srv->id }}@if($srv->provider_service_id)<div style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);" title="Provider Service ID">(API #{{ $srv->provider_service_id }})</div>@endif</td>
                        <td>
                            <div style="font-weight: 400; color: var(--text-primary);">
                                {{ $srv->name }}
                                <span style="font-size: 0.7rem; background: rgba(2, 132, 199, 0.1); color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px;">Order #{{ $srv->sort_order }}</span>
                                @if($srv->status === 'inactive')
                                    <span class="badge" style="font-size: 0.65rem; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); margin-left: 4px; font-weight: 700;">⛔ Inactive</span>
                                @else
                                    <span class="badge" style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); margin-left: 4px; font-weight: 700;">✅ Active</span>
                                @endif
                                @if($srv->is_custom_name && $srv->original_name && $srv->original_name !== $srv->name)
                                    <span class="badge" style="font-size: 0.65rem; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); margin-left: 4px;">🏷️ Custom Title</span>
                                @endif
                            </div>
                            @if($srv->is_custom_name && $srv->original_name && $srv->original_name !== $srv->name)
                                <div style="margin-top: 2px; font-size: 0.72rem; color: #60a5fa; font-weight: 500;">
                                    <i class="fa-solid fa-cloud"></i> <strong>Provider API Name:</strong> <em>{{ $srv->original_name }}</em>
                                </div>
                            @endif
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">Cat: {{ $srv->category ? $srv->category->name : 'General' }} @if($srv->average_time) | Time: <span style="color: var(--color-info);">{{ $srv->average_time }}</span> @endif</div>
                        </td>
                        <td style="font-weight: 700; color: var(--color-success);">
                            ₹{{ format_currency($srv->price_per_k) }}
                        </td>
                        <td style="color: var(--text-secondary);">
                            {{ number_format($srv->min_quantity) }} / {{ number_format($srv->max_quantity) }}
                        </td>
                        <td>
                            @if($srv->provider)
                                <span style="color: var(--color-info); font-weight: 400;">API: {{ $srv->provider->name }}</span>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Cost: {{ $srv->provider_rate }}/1K</div>
                            @else
                                <span style="color: var(--text-secondary);">Manual (Self)</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                <button type="button" class="btn-outline edit-service-btn" data-service="{{ json_encode($srv) }}" style="padding: 4px 8px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                    Edit
                                </button>
                                <form action="{{ route('admin.services.delete', $srv->id) }}" method="POST" class="ajax-srv-delete-form" onsubmit="return confirm('Delete this service permanently?')">
                                    @csrf
                                    <button type="submit" class="btn-gradient" style="padding: 4px 8px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-danger);">
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
    @if(method_exists($services, 'links'))
        <div class="ajax-pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $services->links() }}
        </div>
    @endif
@else
    <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
        <i class="fa-solid fa-list-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
        <p>No services found matching your query filters.</p>
    </div>
@endif
