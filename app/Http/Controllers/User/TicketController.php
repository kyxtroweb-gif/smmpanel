<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display paginated list of user's tickets.
     */
    public function getIndex(Request $request): View
    {
        $user = auth()->user();

        $query = Ticket::where('user_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search by subject
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('subject', 'like', '%' . $search . '%');
        }

        $tickets = $query->with('category')
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get status counts
        $statusCounts = Ticket::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('user.tickets.index', compact('tickets', 'statusCounts'));
    }

    /**
     * Display create ticket form.
     */
    public function getCreate(): View
    {
        $categories = TicketCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];

        return view('user.tickets.create', compact('categories', 'priorities'));
    }

    /**
     * Store a new support ticket.
     */
    public function postStore(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:ticket_categories,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|min:10|max:5000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,txt,zip',
        ]);

        try {
            DB::beginTransaction();

            // Create ticket
            $ticket = Ticket::create([
                'user_id' => $user->id,
                'category_id' => $validated['category_id'] ?? null,
                'subject' => $validated['subject'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'last_reply_at' => now(),
                'last_reply_by' => $user->id,
            ]);

            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ];
                }
            }

            // Create first message
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_admin' => false,
                'attachments' => $attachments,
            ]);

            // Update ticket with first message reference
            $ticket->first_message_id = $message->id;
            $ticket->save();

            DB::commit();

            return redirect()->route('user.tickets.view', $ticket->id)
                ->with('success', 'Ticket created successfully! We will respond shortly.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create ticket: ' . $e->getMessage()]);
        }
    }

    /**
     * Display ticket details with messages.
     */
    public function getView(int $id): View
    {
        $user = auth()->user();

        $ticket = Ticket::with(['category', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Mark ticket as read
        if ($ticket->user_unread_count > 0) {
            $ticket->user_unread_count = 0;
            $ticket->save();
        }

        return view('user.tickets.view', compact('ticket'));
    }

    /**
     * Reply to a ticket.
     */
    public function postReply(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();

        $ticket = Ticket::where('user_id', $user->id)
            ->findOrFail($id);

        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            // Reopen ticket for new reply
            $ticket->status = 'open';
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,txt,zip',
        ]);

        try {
            DB::beginTransaction();

            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ];
                }
            }

            // Create message
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_admin' => false,
                'attachments' => $attachments,
            ]);

            // Update ticket
            $ticket->last_reply_at = now();
            $ticket->last_reply_by = $user->id;
            $ticket->admin_unread_count = ($ticket->admin_unread_count ?? 0) + 1;
            $ticket->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Reply sent successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to send reply: ' . $e->getMessage()]);
        }
    }

    /**
     * Close a ticket.
     */
    public function postClose(int $id): RedirectResponse
    {
        $user = auth()->user();

        $ticket = Ticket::where('user_id', $user->id)
            ->findOrFail($id);

        if ($ticket->status === 'closed') {
            return redirect()->back()
                ->withErrors(['error' => 'Ticket is already closed.']);
        }

        try {
            DB::beginTransaction();

            $ticket->status = 'closed';
            $ticket->closed_at = now();
            $ticket->save();

            // Add system message
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'message' => 'Ticket has been closed by the user.',
                'is_admin' => true,
                'is_system' => true,
            ]);

            DB::commit();

            return redirect()->route('user.tickets.index')
                ->with('success', 'Ticket closed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to close ticket: ' . $e->getMessage()]);
        }
    }

    /**
     * Reopen a closed ticket.
     */
    public function postReopen(int $id): RedirectResponse
    {
        $user = auth()->user();

        $ticket = Ticket::where('user_id', $user->id)
            ->findOrFail($id);

        if ($ticket->status !== 'closed') {
            return redirect()->back()
                ->withErrors(['error' => 'Ticket is not closed.']);
        }

        try {
            DB::beginTransaction();

            $ticket->status = 'open';
            $ticket->closed_at = null;
            $ticket->save();

            // Add system message
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'message' => 'Ticket has been reopened by the user.',
                'is_admin' => true,
                'is_system' => true,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Ticket reopened successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to reopen ticket: ' . $e->getMessage()]);
        }
    }

    /**
     * Download ticket attachment.
     */
    public function getAttachment(int $id, int $attachmentIndex): Response
    {
        $user = auth()->user();

        $ticket = Ticket::where('user_id', $user->id)
            ->findOrFail($id);

        $message = $ticket->messages->first(function ($msg) use ($attachmentIndex) {
            return isset($msg->attachments[$attachmentIndex]);
        });

        if (!$message) {
            abort(404);
        }

        $attachment = $message->attachments[$attachmentIndex];

        if (!Storage::disk('public')->exists($attachment['path'])) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment['path'],
            $attachment['name']
        );
    }

    /**
     * Get ticket statistics.
     */
    public function getStatistics(): View
    {
        $user = auth()->user();

        $stats = [
            'total_tickets' => Ticket::where('user_id', $user->id)->count(),
            'open_tickets' => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            'closed_tickets' => Ticket::where('user_id', $user->id)->where('status', 'closed')->count(),
            'avg_response_time' => $this->calculateAverageResponseTime($user->id),
        ];

        // Tickets by category
        $ticketsByCategory = Ticket::where('tickets.user_id', $user->id)
            ->join('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->selectRaw('ticket_categories.name as category_name, COUNT(*) as count')
            ->groupBy('ticket_categories.id', 'ticket_categories.name')
            ->get();

        // Tickets by priority
        $ticketsByPriority = Ticket::where('user_id', $user->id)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        return view('user.tickets.statistics', compact(
            'stats',
            'ticketsByCategory',
            'ticketsByPriority'
        ));
    }

    /**
     * Calculate average response time for user's tickets.
     */
    protected function calculateAverageResponseTime(int $userId): ?string
    {
        $tickets = Ticket::where('user_id', $userId)
            ->whereNotNull('first_response_at')
            ->get();

        if ($tickets->isEmpty()) {
            return null;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($tickets as $ticket) {
            if ($ticket->first_response_at) {
                $diff = $ticket->created_at->diffInMinutes($ticket->first_response_at);
                $totalMinutes += $diff;
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $avgMinutes = $totalMinutes / $count;

        if ($avgMinutes < 60) {
            return round($avgMinutes) . ' minutes';
        } elseif ($avgMinutes < 1440) {
            return round($avgMinutes / 60, 1) . ' hours';
        } else {
            return round($avgMinutes / 1440, 1) . ' days';
        }
    }
}
