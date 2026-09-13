@extends('layouts.app')

@section('title', 'Support Tickets - Growinsta')
@section('page_header', 'Support Center')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <div class="card-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">
        
        <!-- Open Ticket Form -->
        <div class="glass custom-card">
            <h3 class="card-title"><i class="fa-solid fa-circle-question text-gradient"></i> Open Ticket</h3>
            
            <form action="{{ route('tickets.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <select name="subject" id="subject" class="form-control" required>
                        <option value="" disabled selected>-- Select Subject --</option>
                        <option value="Order Issue">Order Sizing / Status Delay</option>
                        <option value="Payment Deposit Issue">Payment Verification / Add Funds</option>
                        <option value="API Reseller Integration">API Integration Query</option>
                        <option value="Custom Quotation / Bulk">Bulk Service Inquiry</option>
                        <option value="Other">Other / General Feedback</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Message Details</label>
                    <textarea name="message" id="message" class="form-control" rows="5" placeholder="Explain your problem in detail. If relating to an order, please include the Order ID..." required></textarea>
                </div>

                <button type="submit" class="btn-gradient" style="width: 100%; padding: 12px;">
                    Submit Inquiry <i class="fa-solid fa-paper-plane" style="margin-left: 8px; font-size: 0.95rem;"></i>
                </button>
            </form>
        </div>

        <!-- Tickets List -->
        <div class="glass custom-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 1rem;">
                <h3 class="card-title" style="margin: 0;"><i class="fa-solid fa-envelope-open-text"></i> Ticket History</h3>
                <div style="position: relative; min-width: 200px; max-width: 300px; flex: 1;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; pointer-events: none;"></i>
                    <input type="text" id="ticketSearchInput" class="form-control" placeholder="Search tickets by ID or Subject..." style="padding-left: 34px; font-size: 0.85rem; height: 38px;">
                </div>
            </div>
            
            @if($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Subject</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 140px;">Last Update</th>
                                <th style="width: 100px; text-align: center;">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td style="font-weight: bold; color: var(--color-primary); font-family: monospace;">#{{ $ticket->token_id ?: sprintf('%05d', $ticket->id) }}</td>
                                    <td>{{ $ticket->subject }}</td>
                                    <td>
                                        @if($ticket->status === 'open')
                                            <span class="badge badge-pending">Open</span>
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
                                    <td style="text-align: center;">
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-sm);">
                                            Inspect
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
                    <i class="fa-solid fa-comments" style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <p>No support tickets opened yet.</p>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ticketSearchInput = document.getElementById('ticketSearchInput');
        if (ticketSearchInput) {
            ticketSearchInput.addEventListener('keyup', function () {
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
