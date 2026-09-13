@extends('layouts.app')

@section('title', 'Customer Spending Tiers - Admin Panel')
@section('page_header', 'Customer Spending Tiers')

@section('styles')
<style>
    .tier-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }
    .tier-btn-group {
        display: flex;
        gap: 10px;
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        .tier-form-grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        .tier-btn-group {
            flex-direction: column;
        }
        .tier-btn-group button {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Add / Edit Tier Card -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-layer-group text-gradient"></i> Add / Manage Customer Loyalty Tier</h3>

        <form action="{{ route('admin.tiers.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <input type="hidden" name="id" id="tier_id_input">
            
            <div class="tier-form-grid">
                <div class="form-group">
                    <label class="form-label">Tier Name</label>
                    <input type="text" name="name" id="tier_name_input" class="form-control" placeholder="e.g. GOLD" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Min Spending (₹)</label>
                    <input type="number" step="0.01" min="0" name="min_spending" id="tier_min_spending_input" class="form-control" placeholder="e.g. 25000" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Spending (₹) <span style="font-size: 0.75rem; color: var(--text-muted);">(Blank = Unlimited)</span></label>
                    <input type="number" step="0.01" min="0" name="max_spending" id="tier_max_spending_input" class="form-control" placeholder="e.g. 99999">
                </div>

                <div class="form-group">
                    <label class="form-label">Tier Discount (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="discount_percentage" id="tier_discount_input" class="form-control" placeholder="e.g. 1.0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="tier_status_input" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" min="0" name="sort_order" id="tier_sort_order_input" class="form-control" value="1" required>
                </div>
            </div>

            <div class="tier-btn-group">
                <button type="submit" class="btn-gradient" style="padding: 10px 24px;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Save Customer Tier
                </button>
                <button type="button" class="btn-outline" onclick="resetTierForm()" style="padding: 10px 16px;">
                    Reset Form
                </button>
            </div>
        </form>
    </div>

    <!-- Active Tiers Table -->
    <div class="glass custom-card animate-fade-in">
        <h3 class="card-title"><i class="fa-solid fa-trophy text-gradient"></i> Active Loyalty Spending Tiers</h3>

        <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
            <table class="data-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Tier Name</th>
                        <th>Min Spending</th>
                        <th>Max Spending</th>
                        <th>Discount %</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiers as $tier)
                        <tr>
                            <td style="font-weight: 700;">#{{ $tier->sort_order }}</td>
                            <td style="font-weight: 800; font-size: 1.05rem; white-space: nowrap;" class="text-gradient">{{ $tier->name }}</td>
                            <td style="font-weight: 700; white-space: nowrap;">₹{{ number_format($tier->min_spending, 2) }}</td>
                            <td style="white-space: nowrap;">{{ $tier->max_spending !== null ? '₹' . number_format($tier->max_spending, 2) : 'Unlimited' }}</td>
                            <td style="font-weight: 800; color: #22c55e; white-space: nowrap;">{{ $tier->discount_percentage }}%</td>
                            <td>
                                <span style="background: {{ $tier->status === 'active' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ $tier->status === 'active' ? '#22c55e' : '#ef4444' }}; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">
                                    {{ ucfirst($tier->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: nowrap;">
                                    <button type="button" class="btn-outline" onclick="editTier({{ json_encode($tier) }})" style="padding: 4px 10px; font-size: 0.78rem; white-space: nowrap;">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.tiers.delete', $tier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tier?');">
                                        @csrf
                                        <button type="submit" class="btn-outline" style="padding: 4px 10px; font-size: 0.78rem; color: #ef4444; border-color: #ef4444; white-space: nowrap;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No customer tiers configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function editTier(tier) {
        document.getElementById('tier_id_input').value = tier.id;
        document.getElementById('tier_name_input').value = tier.name;
        document.getElementById('tier_min_spending_input').value = tier.min_spending;
        document.getElementById('tier_max_spending_input').value = tier.max_spending ?? '';
        document.getElementById('tier_discount_input').value = tier.discount_percentage;
        document.getElementById('tier_status_input').value = tier.status;
        document.getElementById('tier_sort_order_input').value = tier.sort_order;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetTierForm() {
        document.getElementById('tier_id_input').value = '';
        document.getElementById('tier_name_input').value = '';
        document.getElementById('tier_min_spending_input').value = '';
        document.getElementById('tier_max_spending_input').value = '';
        document.getElementById('tier_discount_input').value = '0';
        document.getElementById('tier_status_input').value = 'active';
        document.getElementById('tier_sort_order_input').value = '1';
    }
</script>
@endsection
