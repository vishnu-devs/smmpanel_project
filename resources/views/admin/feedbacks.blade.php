@extends('layouts.app')

@section('title', 'Customer Feedbacks - Admin Panel')
@section('page_header', 'Customer Feedback & Suggestions')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Filter Card -->
    <div class="glass custom-card animate-fade-in" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <h3 class="card-title" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-filter text-gradient"></i> Filter Customer Feedbacks
        </h3>

        <form action="{{ route('admin.feedbacks') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div style="flex: 1; min-width: 160px;">
                <label class="form-label" style="font-size: 0.8rem;">Category</label>
                <select name="category" class="form-control" style="font-size: 0.88rem;">
                    <option value="">All Categories</option>
                    <option value="issue" {{ request('category') == 'issue' ? 'selected' : '' }}>🐛 Issue / Bug</option>
                    <option value="feature_request" {{ request('category') == 'feature_request' ? 'selected' : '' }}>💡 Feature Request</option>
                    <option value="ui_feedback" {{ request('category') == 'ui_feedback' ? 'selected' : '' }}>🎨 UI Feedback</option>
                    <option value="payment_issue" {{ request('category') == 'payment_issue' ? 'selected' : '' }}>💳 Payment Concern</option>
                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>📌 Other</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem;">Status</label>
                <select name="status" class="form-control" style="font-size: 0.88rem;">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>

            <div style="flex: 2; min-width: 220px;">
                <label class="form-label" style="font-size: 0.8rem;">Search Customer / Details</label>
                <input type="text" name="search" class="form-control" placeholder="Search name, email, title..." value="{{ request('search') }}" style="font-size: 0.88rem;">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-gradient" style="padding: 10px 18px; font-weight: 700;">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                <a href="{{ route('admin.feedbacks') }}" class="btn-outline" style="padding: 10px 14px; text-decoration: none; color: var(--text-primary);">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Feedbacks Table Card -->
    <div class="glass custom-card animate-fade-in" style="padding: 1.25rem;">
        <h3 class="card-title" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-comments text-gradient"></i> Customer Feedbacks List
        </h3>

        @if($feedbacks->count() > 0)
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="custom-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 180px;">Customer</th>
                            <th style="width: 150px;">Category</th>
                            <th>Feedback Title & Details</th>
                            <th style="width: 120px;">Screenshot</th>
                            <th style="width: 130px;">Submitted</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 100px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feedbacks as $fb)
                            <tr>
                                <td style="font-weight: 700; color: var(--text-muted);">#{{ $fb->id }}</td>
                                <td>
                                    @if($fb->user)
                                        <div style="font-weight: 600; color: var(--text-primary);">{{ $fb->user->name }}</div>
                                        <div style="font-size: 0.78rem; color: var(--text-secondary);">{{ $fb->user->email }}</div>
                                    @else
                                        <span style="color: var(--text-muted); font-style: italic;">User Deleted (#{{ $fb->user_id }})</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-primary);">
                                        {{ $fb->category_label }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">{{ $fb->title }}</div>
                                    <div style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4; white-space: pre-wrap;">{{ Str::limit($fb->details, 200) }}</div>

                                    @if($fb->admin_notes)
                                        <div style="margin-top: 6px; font-size: 0.76rem; color: #60a5fa; font-style: italic;">
                                            <strong>Admin Note:</strong> {{ $fb->admin_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($fb->screenshot_path)
                                        <a href="{{ asset($fb->screenshot_path) }}" target="_blank" class="btn-outline" style="padding: 4px 8px; font-size: 0.75rem; text-decoration: none; color: var(--color-info); display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-image"></i> View Image
                                        </a>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">None</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap;">
                                    {{ $fb->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    @if($fb->status === 'resolved')
                                        <span class="badge" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; font-weight: 700;">Resolved</span>
                                    @elseif($fb->status === 'reviewed')
                                        <span class="badge" style="background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; font-weight: 700;">Reviewed</span>
                                    @else
                                        <span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; font-weight: 700;">Pending</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-outline" style="padding: 5px 10px; font-size: 0.78rem; border-radius: 6px;"
                                            onclick="openUpdateModal({{ $fb->id }}, '{{ $fb->status }}', '{{ addslashes($fb->admin_notes ?? '') }}')">
                                        <i class="fa-solid fa-pen"></i> Update
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $feedbacks->links('partials.pagination') }}
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 4rem 1rem;">
                <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
                <p>No customer feedbacks found matching your search criteria.</p>
            </div>
        @endif
    </div>

</div>

<!-- Status Update Modal -->
<div id="updateFeedbackModal" class="modal" onclick="if(event.target===this) closeUpdateModal()" style="display: none; position: fixed !important; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.75); z-index: 999999 !important; align-items: center; justify-content: center; padding: 1rem;">
    <div class="glass animate-fade-in" style="background: var(--bg-card, #0f172a); border-radius: var(--radius-lg); max-width: 500px; width: 100%; padding: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Update Feedback Status</h4>
            <button type="button" onclick="closeUpdateModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>

        <form id="updateFeedbackForm" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Status</label>
                <select name="status" id="modalStatus" class="form-control" required>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed (Under Consideration)</option>
                    <option value="resolved">Resolved / Implemented</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Admin Notes (Visible to Customer)</label>
                <textarea name="admin_notes" id="modalNotes" class="form-control" rows="3" placeholder="e.g. Thanks for reporting, this bug is fixed in the latest update!"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeUpdateModal()" class="btn-outline" style="padding: 8px 16px;">Cancel</button>
                <button type="submit" class="btn-gradient" style="padding: 8px 18px; font-weight: 700;">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openUpdateModal(id, currentStatus, currentNotes) {
        const modal = document.getElementById('updateFeedbackModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            const form = document.getElementById('updateFeedbackForm');
            form.action = '/admin/feedbacks/' + id + '/status';
            document.getElementById('modalStatus').value = currentStatus;
            document.getElementById('modalNotes').value = currentNotes;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUpdateModal() {
        const modal = document.getElementById('updateFeedbackModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
</script>
@endsection
