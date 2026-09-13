@extends('layouts.app')

@section('title', 'Manage Tickets - Growinsta')
@section('page_header', 'Support Ticket Center')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Ticket Management Data Grid -->
    <div class="glass custom-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 1.25rem;">
            <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-ticket"></i> Customer Inquiries</h3>
            <div style="position: relative; min-width: 220px; max-width: 320px; flex: 1;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; pointer-events: none;"></i>
                <input type="text" id="adminTicketSearchInput" class="form-control" placeholder="Search tickets by ID, user, or subject..." style="padding-left: 34px; font-size: 0.85rem; height: 38px;">
            </div>
        </div>
        
        @if($tickets->count() > 0)
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Opening User</th>
                            <th>Subject Title</th>
                            <th style="width: 140px;">Status</th>
                            <th style="width: 160px;">Last Response</th>
                            <th style="text-align: right; width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td style="font-weight: bold; color: var(--color-primary); font-family: monospace;">#{{ $ticket->token_id ?: sprintf('%05d', $ticket->id) }}</td>
                                <td>
                                    <div style="font-weight: 600;">{{ $ticket->user ? $ticket->user->name : 'Deleted User' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $ticket->user ? $ticket->user->email : '' }}</div>
                                </td>
                                <td style="font-weight: 500;">{{ $ticket->subject }}</td>
                                <td>
                                    @if($ticket->status === 'open')
                                        <span class="badge badge-pending">New / Open</span>
                                    @elseif($ticket->status === 'client_reply')
                                        <span class="badge badge-inprogress">Client Reply</span>
                                    @elseif($ticket->status === 'answered')
                                        <span class="badge badge-completed">Answered</span>
                                    @else
                                        <span class="badge badge-canceled">Closed</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-sm);">
                                        Open Chat
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                {{ $tickets->links() }}
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--color-success); opacity: 0.7;"></i>
                <p>No customer support tickets registered. Clean queue!</p>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('adminTicketSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const query = this.value.toLowerCase().trim().replace(/^#/, '');
                const rows = document.querySelectorAll('.custom-table tbody tr');
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (query === '' || text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
