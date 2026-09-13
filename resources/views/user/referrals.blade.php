@extends('layouts.app')

@section('title', 'Refer & Earn - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Refer & Earn')

@section('styles')
<style>
    .referral-banner-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .referral-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .referral-copy-flex {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .referral-copy-flex .input-wrap {
        flex: 1;
        min-width: 250px;
    }
    .referral-copy-flex button {
        padding: 10px 24px;
        font-weight: 700;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .referral-banner-flex {
            flex-direction: column;
            align-items: flex-start;
        }
        .referral-banner-title {
            font-size: 1.3rem !important;
        }
        .referral-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.75rem !important;
        }
        .referral-stat-card {
            padding: 1rem 0.75rem !important;
        }
        .referral-stat-val {
            font-size: 1.4rem !important;
        }
        .referral-copy-flex {
            flex-direction: column;
        }
        .referral-copy-flex .input-wrap {
            min-width: 100% !important;
            width: 100%;
        }
        .referral-copy-flex button {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .referral-stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Top Banner Card -->
    <div class="glass custom-card animate-fade-in" style="background: linear-gradient(135deg, rgba(220, 39, 67, 0.12) 0%, rgba(204, 35, 102, 0.05) 100%); border: 1px solid rgba(220, 39, 67, 0.25); margin-bottom: 2rem;">
        <div class="referral-banner-flex">
            <div>
                <h2 class="text-gradient referral-banner-title" style="font-size: 1.6rem; font-weight: 800; margin-bottom: 6px;">
                    <i class="fa-solid fa-gift" style="margin-right: 8px;"></i> Refer Friends & Earn {{ $commissionPercent }}% Commission!
                </h2>
                <p style="color: var(--text-secondary); font-size: 0.92rem; margin: 0; max-width: 650px;">
                    Share your unique referral link with your friends and audience. Every time they deposit ₹{{ number_format($minDeposit, 0) }} or more, you automatically earn <strong>{{ $commissionPercent }}% referral commission</strong> added directly to your wallet!
                </p>
            </div>
            <div>
                <span style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #22c55e; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; display: inline-block;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Rate: {{ $commissionPercent }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Referral Link Box -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-link text-gradient"></i> Your Unique Referral Link</h3>
        
        <div class="referral-copy-flex">
            <div class="input-wrap">
                <input type="text" id="referralUrlInput" class="form-control" value="{{ $referralUrl }}" readonly style="font-weight: 600; background: rgba(255,255,255,0.03);">
            </div>
            <button type="button" class="btn-gradient" onclick="copyReferralLink()">
                <i class="fa-solid fa-copy" style="margin-right: 6px;"></i> Copy Link
            </button>
        </div>
        <p style="color: var(--text-muted); font-size: 0.82rem; margin-top: 8px;">
            Your Referral Code: <strong style="color: var(--color-primary);">{{ $user->referral_code }}</strong>
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="referral-stats-grid">
        <div class="glass custom-card referral-stat-card" style="padding: 1.25rem; text-align: center;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Total Referred</div>
            <div class="referral-stat-val" style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary);">{{ number_format($totalReferrals) }}</div>
        </div>
        <div class="glass custom-card referral-stat-card" style="padding: 1.25rem; text-align: center;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Active Referrals</div>
            <div class="referral-stat-val" style="font-size: 1.8rem; font-weight: 800; color: #10b981;">{{ number_format($activeReferrals) }}</div>
        </div>
        <div class="glass custom-card referral-stat-card" style="padding: 1.25rem; text-align: center;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Total Earned</div>
            <div class="referral-stat-val" style="font-size: 1.8rem; font-weight: 800; color: #22c55e;">₹{{ number_format($totalEarnings, 2) }}</div>
        </div>
        <div class="glass custom-card referral-stat-card" style="padding: 1.25rem; text-align: center;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Pending Review</div>
            <div class="referral-stat-val" style="font-size: 1.8rem; font-weight: 800; color: #f59e0b;">₹{{ number_format($pendingEarnings, 2) }}</div>
        </div>
    </div>

    <!-- Commission History Table -->
    <div class="glass custom-card animate-fade-in">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 1rem;">
            <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-clock-rotate-left text-gradient"></i> Referral Commission Earnings</h3>
            <div style="position: relative; min-width: 240px; max-width: 350px; flex: 1;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; pointer-events: none;"></i>
                <input type="text" id="referralSearchInput" class="form-control" placeholder="Search referral user or date..." style="padding-left: 34px; font-size: 0.85rem; height: 38px;">
            </div>
        </div>

        <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
            <table class="data-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Referred User</th>
                        <th>Deposit Amount</th>
                        <th>Commission Rate</th>
                        <th>Earned Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                        <tr>
                            <td style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap;">
                                {{ $comm->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td style="font-weight: 600;">
                                {{ $comm->referred ? $comm->referred->name : 'User #' . $comm->referred_id }}
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $comm->referred ? $comm->referred->email : '' }}</div>
                            </td>
                            <td style="font-weight: 700; white-space: nowrap;">₹{{ number_format($comm->deposit_amount, 2) }}</td>
                            <td>{{ $comm->commission_rate }}%</td>
                            <td style="font-weight: 800; color: #22c55e; white-space: nowrap;">+₹{{ number_format($comm->commission_amount, 2) }}</td>
                            <td>
                                @if($comm->status === 'approved')
                                    <span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">Approved</span>
                                @elseif($comm->status === 'pending_review')
                                    <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">Pending Review</span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">{{ ucfirst($comm->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                <i class="fa-solid fa-gift" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                                No referral earnings recorded yet. Share your referral link to start earning!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $commissions->links('partials.pagination') }}
        </div>
    </div>

</div>

<script>
    function copyReferralLink() {
        const input = document.getElementById('referralUrlInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);
        alert('Referral link copied to clipboard!');
    }
</script>
@endsection
