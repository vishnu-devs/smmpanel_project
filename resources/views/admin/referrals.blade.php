@extends('layouts.app')

@section('title', 'Referral Management - Admin Panel')
@section('page_header', 'Referral System Management')

@section('styles')
<style>
    .admin-ref-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .admin-ref-btn {
        margin-top: 1rem;
        padding: 10px 24px;
    }

    @media (max-width: 768px) {
        .admin-ref-grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        .admin-ref-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Referral Settings Form Card -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-sliders text-gradient"></i> Referral Program Configuration</h3>

        <form action="{{ route('admin.referrals.settings') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            
            <div class="admin-ref-grid">
                <div class="form-group">
                    <label class="form-label">Referral Program Status</label>
                    <select name="status" class="form-control" required>
                        <option value="enabled" {{ $status === 'enabled' ? 'selected' : '' }}>Enabled (ON)</option>
                        <option value="disabled" {{ $status === 'disabled' ? 'selected' : '' }}>Disabled (OFF)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Commission Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="commission_percent" class="form-control" value="{{ old('commission_percent', $commissionPercent) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Minimum Qualifying Deposit (₹)</label>
                    <input type="number" step="1" min="0" name="min_deposit" class="form-control" value="{{ old('min_deposit', $minDeposit) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Commission Per Deposit (₹)</label>
                    <input type="number" step="1" min="0" name="max_commission_per_deposit" class="form-control" value="{{ old('max_commission_per_deposit', $maxPerDeposit) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Commission Cap (₹) <span style="font-size: 0.75rem; color: var(--text-muted);">(0 = Unlimited)</span></label>
                    <input type="number" step="1" min="0" name="monthly_cap" class="form-control" value="{{ old('monthly_cap', $monthlyCap) }}">
                </div>
            </div>

            <button type="submit" class="btn-gradient admin-ref-btn">
                <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Save Referral Settings
            </button>
        </form>
    </div>

    <!-- Pending / Suspicious Review Alert Box -->
    @if(count($pendingCommissions) > 0 || count($suspiciousReferrals) > 0)
        <div class="glass custom-card animate-fade-in" style="border: 1px solid rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.05); margin-bottom: 2rem;">
            <h3 class="card-title" style="color: #f59e0b;"><i class="fa-solid fa-shield-cat"></i> Flagged / Pending Review Referrals ({{ count($pendingCommissions) + count($suspiciousReferrals) }})</h3>
            
            <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
                <table class="data-table" style="min-width: 650px;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Referrer</th>
                            <th>Referred User</th>
                            <th>Deposit Reference</th>
                            <th>Commission Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingCommissions as $pComm)
                            <tr>
                                <td style="white-space: nowrap;"><span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 2px 8px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;">Commission Review</span></td>
                                <td style="font-weight: 600;">{{ $pComm->referrer ? $pComm->referrer->name : 'ID #' . $pComm->referrer_id }} ({{ $pComm->referrer ? $pComm->referrer->email : '' }})</td>
                                <td>{{ $pComm->referred ? $pComm->referred->name : 'ID #' . $pComm->referred_id }}</td>
                                <td style="white-space: nowrap;">{{ $pComm->deposit_reference }}</td>
                                <td style="font-weight: 800; color: #22c55e; white-space: nowrap;">₹{{ number_format($pComm->commission_amount, 2) }}</td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: nowrap;">
                                        <form action="{{ route('admin.referrals.commission_update_status', $pComm->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn-gradient" style="padding: 4px 10px; font-size: 0.75rem; white-space: nowrap;">Approve & Credit</button>
                                        </form>
                                        <form action="{{ route('admin.referrals.commission_update_status', $pComm->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn-outline" style="padding: 4px 10px; font-size: 0.75rem; color: #ef4444; border-color: #ef4444; white-space: nowrap;">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Referral Commissions History Table -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-coins text-gradient"></i> All Referral Commissions</h3>

        <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
            <table class="data-table" style="min-width: 650px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Referrer</th>
                        <th>Referred User</th>
                        <th>Deposit Amount</th>
                        <th>Commission Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                        <tr>
                            <td style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap;">{{ $comm->created_at->format('d M Y, h:i A') }}</td>
                            <td style="font-weight: 600;">{{ $comm->referrer ? $comm->referrer->name : 'ID #' . $comm->referrer_id }}</td>
                            <td>{{ $comm->referred ? $comm->referred->name : 'ID #' . $comm->referred_id }}</td>
                            <td style="white-space: nowrap;">₹{{ number_format($comm->deposit_amount, 2) }}</td>
                            <td style="font-weight: 800; color: #22c55e; white-space: nowrap;">₹{{ number_format($comm->commission_amount, 2) }} ({{ $comm->commission_rate }}%)</td>
                            <td>
                                @if($comm->status === 'approved')
                                    <span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">Approved</span>
                                @elseif($comm->status === 'pending_review')
                                    <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">Pending</span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">{{ ucfirst($comm->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($comm->status === 'approved')
                                    <form action="{{ route('admin.referrals.commission_update_status', $comm->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reverse this commission? Amount will be deducted from referrer wallet.');">
                                        @csrf
                                        <input type="hidden" name="status" value="reversed">
                                        <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #ef4444; white-space: nowrap;">Reverse</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No commissions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">{{ $commissions->links('partials.pagination') }}</div>
    </div>

</div>
@endsection
