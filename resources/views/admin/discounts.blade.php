@extends('layouts.app')

@section('title', 'Individual Customer Discounts - Admin Panel')
@section('page_header', 'Individual Customer Discounts')

@section('styles')
<style>
    .discount-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .discount-search-form {
        margin: 1rem 0;
        display: flex;
        gap: 10px;
    }
    .discount-search-form input {
        max-width: 350px;
    }

    @media (max-width: 768px) {
        .discount-form-grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        .discount-form-grid .full-width-field {
            grid-column: 1 / -1;
        }
        .discount-submit-btn {
            width: 100%;
        }
        .discount-search-form {
            flex-direction: column;
        }
        .discount-search-form input {
            max-width: 100% !important;
            width: 100%;
        }
        .discount-search-form button,
        .discount-search-form a {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Add / Manage Custom Discount Card -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-user-tag text-gradient"></i> Assign Custom Customer Discount</h3>
        <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 4px;">
            Individual customer discounts <strong>override loyalty tier discounts</strong>. Ideal for API resellers or VIP accounts.
        </p>

        <form action="{{ route('admin.discounts.store') }}" method="POST" style="margin-top: 1.25rem;">
            @csrf
            
            <div class="discount-form-grid">
                <div class="form-group">
                    <label class="form-label">Select Customer</label>
                    <select name="user_id" class="form-control" required style="font-size: 0.9rem;">
                        <option value="">-- Choose Customer --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Discount Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="discount_percentage" class="form-control" placeholder="e.g. 5.0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Starts At <span style="font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></label>
                    <input type="date" name="starts_at" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Ends At <span style="font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></label>
                    <input type="date" name="ends_at" class="form-control">
                </div>

                <div class="form-group full-width-field" style="grid-column: 1 / -1;">
                    <label class="form-label">Reason / Note <span style="font-size: 0.75rem; color: var(--text-muted);">(Internal Note)</span></label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g. Bulk API Reseller Discount for Panel Integration">
                </div>
            </div>

            <button type="submit" class="btn-gradient discount-submit-btn" style="margin-top: 1rem; padding: 10px 24px;">
                <i class="fa-solid fa-check" style="margin-right: 6px;"></i> Assign Customer Discount
            </button>
        </form>
    </div>

    <!-- Active Custom Discounts Table -->
    <div class="glass custom-card animate-fade-in">
        <h3 class="card-title"><i class="fa-solid fa-tags text-gradient"></i> Active Individual Customer Discounts</h3>

        <form action="{{ route('admin.discounts') }}" method="GET" class="discount-search-form">
            <input type="text" name="search" class="form-control" placeholder="Search customer name or email..." value="{{ request('search') }}">
            <button type="submit" class="btn-gradient" style="padding: 8px 16px;">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.discounts') }}" class="btn-outline" style="padding: 8px 12px; text-decoration: none; color: var(--text-primary);">Clear</a>
            @endif
        </form>

        <div class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" style="min-width: 650px;">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Discount %</th>
                        <th>Status</th>
                        <th>Valid Period</th>
                        <th>Note / Reason</th>
                        <th>Assigned By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $disc)
                        <tr>
                            <td style="font-weight: 600;">
                                {{ $disc->user ? $disc->user->name : 'User #' . $disc->user_id }}
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $disc->user ? $disc->user->email : '' }}</div>
                            </td>
                            <td style="font-weight: 800; font-size: 1.1rem; color: #22c55e; white-space: nowrap;">{{ $disc->discount_percentage }}%</td>
                            <td>
                                <span style="background: {{ $disc->status === 'active' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ $disc->status === 'active' ? '#22c55e' : '#ef4444' }}; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">
                                    {{ ucfirst($disc->status) }}
                                </span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-secondary); white-space: nowrap;">
                                {{ $disc->starts_at ? $disc->starts_at->format('d M Y') : 'Always' }} - 
                                {{ $disc->ends_at ? $disc->ends_at->format('d M Y') : 'Never' }}
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $disc->reason ?: '-' }}</td>
                            <td style="font-size: 0.82rem; white-space: nowrap;">{{ $disc->creator ? $disc->creator->name : 'Admin' }}</td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: nowrap;">
                                    <form action="{{ route('admin.discounts.toggle', $disc->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 0.78rem; white-space: nowrap;">
                                            {{ $disc->status === 'active' ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.discounts.delete', $disc->id) }}" method="POST" onsubmit="return confirm('Remove custom discount?');">
                                        @csrf
                                        <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 0.78rem; color: #ef4444; border-color: #ef4444; white-space: nowrap;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No individual customer discounts configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">{{ $discounts->links('partials.pagination') }}</div>
    </div>

</div>
@endsection
