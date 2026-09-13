@extends('layouts.app')

@section('title', 'Ticket Details #' . $ticket->id . ' - Growinsta')
@section('page_header', 'Ticket Manager #' . $ticket->id)

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

    <!-- Ticket Meta Summary Header Card -->
    <div class="glass custom-card" style="padding: 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 4px;">Subject: {{ $ticket->subject }}</h3>
            <span style="color: var(--text-secondary); font-size: 0.85rem;">
                Opened By: <strong>{{ $ticket->user ? $ticket->user->name : 'Deleted Client' }}</strong> ({{ $ticket->user ? $ticket->user->email : '' }})
            </span>
        </div>
        <div>
            @if($ticket->status === 'open')
                <span class="badge badge-pending" style="font-size: 0.85rem; padding: 6px 16px;">Open</span>
            @elseif($ticket->status === 'client_reply')
                <span class="badge badge-inprogress" style="font-size: 0.85rem; padding: 6px 16px;">Client Reply</span>
            @elseif($ticket->status === 'answered')
                <span class="badge badge-completed" style="font-size: 0.85rem; padding: 6px 16px;">Answered</span>
            @else
                <span class="badge badge-canceled" style="font-size: 0.85rem; padding: 6px 16px;">Closed</span>
            @endif
        </div>
    </div>

    <!-- Message Thread Cards -->
    <div class="glass custom-card" style="margin-bottom: 1.5rem;">
        <h4 style="font-weight: 700; font-size: 1rem; margin-bottom: 1.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-comments text-gradient"></i> Conversation Logs
        </h4>
        
        <div class="chat-container" id="chatBox">
            @foreach($ticket->messages as $msg)
                @php $isAdminMsg = ($msg->user && $msg->user->isAdmin()); @endphp
                <div class="chat-msg {{ $isAdminMsg ? 'sent' : 'received' }}">
                    <div style="font-weight: bold; font-size: 0.8rem; margin-bottom: 4px; color: {{ $isAdminMsg ? 'white' : 'var(--color-primary)' }}">
                        {{ $isAdminMsg ? 'Support Team (' . ($msg->user ? $msg->user->name : 'Staff') . ')' : 'Client (' . ($ticket->user ? $ticket->user->name : 'User') . ')' }}
                    </div>
                    <div style="white-space: pre-wrap; line-height: 1.4;">{{ $msg->message }}</div>
                    <div class="chat-msg-meta">{{ $msg->created_at->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Admin Response Editor Form -->
    <div class="glass custom-card">
        <h4 class="card-title" style="font-size: 1rem; border: none; margin: 0 0 10px 0; padding: 0;"><i class="fa-solid fa-reply text-gradient"></i> Write Staff Response</h4>
        <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
            @csrf
            
            <div class="form-group">
                <textarea name="message" id="replyMessage" class="form-control" rows="5" placeholder="Type support staff message details here..." required></textarea>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); cursor: pointer; font-size: 0.95rem;">
                    <input type="checkbox" name="close" value="1" style="accent-color: var(--color-danger); cursor: pointer;">
                    <strong>Close ticket thread</strong> after sending this response
                </label>
                
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.tickets') }}" class="btn-outline" style="padding: 10px 20px;">
                        Back
                    </a>
                    <button type="submit" class="btn-gradient" style="padding: 12px 35px;">
                        Submit Response <i class="fa-solid fa-paper-plane" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto scroll
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endsection
