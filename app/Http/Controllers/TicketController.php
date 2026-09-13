<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('user.tickets', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Auto-generate unique random 5-digit Token ID
        do {
            $tokenId = sprintf('%05d', random_int(10000, 99999));
        } while (Ticket::where('token_id', $tokenId)->exists());

        $user = Auth::user();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'subject' => $request->subject,
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        ActivityLog::log('ticket_create', [
            'ticket_id' => $ticket->id,
            'token_id' => $tokenId,
            'subject' => $ticket->subject
        ]);

        // Send Email Notification to Admin
        try {
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com';
            $adminEmail = \App\Models\Setting::get('support_email', 'support@' . $domain);
            $siteName = \App\Models\Setting::get('site_name', 'RishiSMM');
            $adminTicketUrl = route('admin.tickets.show', $ticket->id);

            \App\Models\Setting::sendEmail(
                $adminEmail,
                "New Customer Complaint — #{$tokenId}",
                "New Customer Complaint (#{$tokenId})",
                "A new support complaint has been submitted on <strong>{$siteName}</strong>.<br><br>" .
                "<strong>Customer Name:</strong> " . htmlspecialchars($user->name) . "<br>" .
                "<strong>Customer Email:</strong> " . htmlspecialchars($user->email) . "<br>" .
                "<strong>Token ID:</strong> #" . $tokenId . "<br>" .
                "<strong>Complaint Subject:</strong> " . htmlspecialchars($ticket->subject) . "<br><br>" .
                "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($request->message)),
                [
                    'btnText' => 'View Complaint',
                    'btnUrl' => $adminTicketUrl,
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send admin ticket notification: " . $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket->id)->with('success', "Support ticket #{$tokenId} opened successfully.");
    }

    public function show($id)
    {
        $ticket = Ticket::where('user_id', Auth::id())->findOrFail($id);
        $messages = $ticket->messages;

        return view('user.ticket_show', compact('ticket', 'messages'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', Auth::id())->findOrFail($id);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This support ticket has been closed. Please open a new ticket if you still need assistance.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $ticket->status = 'client_reply';
        $ticket->save();

        ActivityLog::log('ticket_reply', [
            'ticket_id' => $ticket->id
        ]);

        return back()->with('success', 'Your reply has been submitted successfully.');
    }
}
