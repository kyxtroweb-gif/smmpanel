<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of tickets.
     */
    public function getIndex(Request $request)
    {
        $query = Ticket::with(['user:id,name,email', 'assignee:id,name']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('ticket_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee
        if ($request->has('assignee_id') && $request->assignee_id) {
            $query->where('assigned_to', $request->assignee_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $statuses = ['open', 'pending', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $admins = User::where('role', 'admin')->orderBy('name')->get(['id', 'name']);

        // Summary counts
        $summary = [
            'open' => Ticket::where('status', 'open')->count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereIn('status', ['open', 'pending'])->count(),
        ];

        return view('admin.tickets.index', compact(
            'tickets',
            'statuses',
            'priorities',
            'admins',
            'summary'
        ));
    }

    /**
     * Display ticket details.
     */
    public function getView(int $id)
    {
        $ticket = Ticket::with(['user.profile', 'assignee:id,name,email'])
            ->findOrFail($id);

        $messages = TicketMessage::where('ticket_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $replies = TicketReply::where('ticket_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Merge and sort all conversation items
        $conversation = [];
        foreach ($messages as $message) {
            $conversation[] = [
                'type' => 'message',
                'data' => $message,
                'created_at' => $message->created_at,
            ];
        }
        foreach ($replies as $reply) {
            $conversation[] = [
                'type' => 'reply',
                'data' => $reply,
                'created_at' => $reply->created_at,
            ];
        }
        usort($conversation, function ($a, $b) {
            return $a['created_at']->timestamp - $b['created_at']->timestamp;
        });

        return view('admin.tickets.view', compact('ticket', 'conversation'));
    }

    /**
     * Admin reply to a ticket.
     */
    public function postReply(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'message' => ['required', 'string', 'min:5'],
        ]);

        DB::beginTransaction();

        try {
            // Create the reply
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
            ]);

            // Update ticket status
            if ($ticket->status === 'closed') {
                $ticket->status = 'open';
            }
            $ticket->last_reply_at = now();
            $ticket->last_reply_by = auth()->id();
            $ticket->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Replied to ticket #{$ticket->ticket_id}");

            // Send notification email to user (in production)
            // Mail::to($ticket->user)->send(new TicketReplyMail($ticket, $reply));

            DB::commit();

            return back()->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }

    /**
     * Close a ticket.
     */
    public function postClose(int $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Ticket is already closed.');
        }

        DB::beginTransaction();

        try {
            $ticket->status = 'closed';
            $ticket->closed_at = now();
            $ticket->closed_by = auth()->id();
            $ticket->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Closed ticket #{$ticket->ticket_id}");

            DB::commit();

            return back()->with('success', 'Ticket closed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to close ticket: ' . $e->getMessage());
        }
    }

    /**
     * Reopen a closed ticket.
     */
    public function postReopen(int $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Ticket is not closed.');
        }

        DB::beginTransaction();

        try {
            $ticket->status = 'open';
            $ticket->closed_at = null;
            $ticket->closed_by = null;
            $ticket->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Reopened ticket #{$ticket->ticket_id}");

            DB::commit();

            return back()->with('success', 'Ticket reopened successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reopen ticket: ' . $e->getMessage());
        }
    }

    /**
     * Change ticket priority.
     */
    public function postChangePriority(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $oldPriority = $ticket->priority;
        $ticket->priority = $request->priority;
        $ticket->save();

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("Changed ticket #{$ticket->ticket_id} priority from {$oldPriority} to {$request->priority}");

        return back()->with('success', "Priority changed to {$request->priority}.");
    }

    /**
     * Assign ticket to an admin.
     */
    public function postAssign(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->assigned_to = $request->assigned_to;
        $ticket->save();

        $assigneeName = $request->assigned_to ? User::find($request->assigned_to)->name : 'Unassigned';

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("Assigned ticket #{$ticket->ticket_id} to {$assigneeName}");

        return back()->with('success', "Ticket assigned to {$assigneeName}.");
    }

    /**
     * Delete a ticket.
     */
    public function postDelete(int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticketId = $ticket->ticket_id;

        DB::beginTransaction();

        try {
            // Delete related messages and replies
            TicketMessage::where('ticket_id', $ticket->id)->delete();
            TicketReply::where('ticket_id', $ticket->id)->delete();

            // Delete the ticket
            $ticket->delete();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Deleted ticket #{$ticketId}");

            DB::commit();

            return redirect()->route('admin.tickets.index')
                ->with('success', 'Ticket deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete ticket: ' . $e->getMessage());
        }
    }

    /**
     * Mark ticket as pending (waiting for user response).
     */
    public function postPending(int $id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->status = 'pending';
        $ticket->save();

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("Marked ticket #{$ticket->ticket_id} as pending");

        return back()->with('success', 'Ticket marked as pending.');
    }

    /**
     * Bulk close tickets.
     */
    public function postBulkClose(Request $request)
    {
        $request->validate([
            'ticket_ids' => ['required', 'array', 'min:1'],
        ]);

        $ticketIds = $request->ticket_ids;

        DB::beginTransaction();

        try {
            $tickets = Ticket::whereIn('id', $ticketIds)
                ->whereIn('status', ['open', 'pending'])
                ->get();

            foreach ($tickets as $ticket) {
                $ticket->status = 'closed';
                $ticket->closed_at = now();
                $ticket->closed_by = auth()->id();
                $ticket->save();
            }

            activity()
                ->causedBy(auth()->user())
                ->log("Bulk closed {$tickets->count()} tickets");

            DB::commit();

            return back()->with('success', "{$tickets->count()} tickets closed.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk close: ' . $e->getMessage());
        }
    }

    /**
     * Bulk change priority.
     */
    public function postBulkPriority(Request $request)
    {
        $request->validate([
            'ticket_ids' => ['required', 'array', 'min:1'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $ticketIds = $request->ticket_ids;

        DB::beginTransaction();

        try {
            Ticket::whereIn('id', $ticketIds)->update([
                'priority' => $request->priority,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->log("Bulk changed priority to {$request->priority} for " . count($ticketIds) . " tickets");

            DB::commit();

            return back()->with('success', "Priority changed to {$request->priority} for " . count($ticketIds) . " tickets.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk change priority: ' . $e->getMessage());
        }
    }
}
