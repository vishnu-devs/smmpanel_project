@extends('layouts.app')

@section('title', 'Ticket #' . ($ticket->token_id ?: $ticket->id) . ' - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Ticket Complaint #' . ($ticket->token_id ?: $ticket->id))

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

    <!-- Ticket Summary Card -->
    <div class="glass custom-card" style="padding: 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 5px;">Subject: {{ $ticket->subject }}</h3>
            <span style="color: var(--text-secondary); font-size: 0.85rem;">Opened on: {{ $ticket->created_at->format('d M Y, h:i A') }}</span>
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

    <!-- Messages Chat History Box -->
    <div class="glass custom-card" style="margin-bottom: 1.5rem;">
        <h4 style="font-weight: 700; font-size: 1rem; margin-bottom: 1.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-comments text-gradient"></i> Conversation Thread
        </h4>
        
        <div class="chat-container" id="chatBox">
            @foreach($messages as $msg)
                @php $isMe = ($msg->user_id === Auth::id()); @endphp
                <div class="chat-msg {{ $isMe ? 'sent' : 'received' }}">
                    <div style="font-weight: bold; font-size: 0.8rem; margin-bottom: 4px; color: {{ $isMe ? 'white' : 'var(--color-primary)' }}">
                        {{ $isMe ? 'You' : 'Support Staff' }}
                    </div>
                    <div style="white-space: pre-wrap; line-height: 1.4;">{{ $msg->message }}</div>
                    <div class="chat-msg-meta">{{ $msg->created_at->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Reply Form Box -->
    <div class="glass custom-card">
        @if($ticket->status !== 'closed')
            <h4 class="card-title" style="font-size: 1rem; border: none; margin: 0 0 10px 0; padding: 0;"><i class="fa-solid fa-reply text-gradient"></i> Send Reply</h4>
            <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <textarea name="message" id="replyMessage" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('tickets.index') }}" class="btn-outline" style="padding: 10px 20px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Support
                    </a>
                    <button type="submit" class="btn-gradient" style="padding: 12px 30px;">
                        Send Reply <i class="fa-solid fa-paper-plane" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </form>
        @else
            <div style="text-align: center; padding: 1rem; color: var(--text-muted);">
                <i class="fa-solid fa-lock" style="font-size: 2rem; margin-bottom: 10px; color: var(--color-danger); opacity: 0.7;"></i>
                <p style="font-weight: 600;">This support ticket is closed and archived. You cannot write further replies.</p>
                <a href="{{ route('tickets.index') }}" class="btn-outline" style="padding: 10px 20px; margin-top: 15px; display: inline-block;">
                    Back to Support
                </a>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto scroll to bottom of chatbox
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endsection
