@extends('user.layout')
@section('title', 'Ticket Details - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active" aria-current="page">#{{ $ticket->ticket_id }}</li>
            </ol>
        </nav>
        <h2 class="mb-0">
            <i class="fas fa-ticket-alt me-2 text-primary"></i>{{ $ticket->subject }}
        </h2>
        <div class="d-flex align-items-center gap-2 mt-2">
            <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $ticket->ticket_id }}</code>
            @if($ticket->priority == 'high')
                <span class="badge bg-danger">High Priority</span>
            @elseif($ticket->priority == 'medium')
                <span class="badge bg-warning text-dark">Medium Priority</span>
            @else
                <span class="badge bg-secondary">Low Priority</span>
            @endif
            @if($ticket->status == 'open')
                <span class="badge bg-success">Open</span>
            @elseif($ticket->status == 'pending')
                <span class="badge bg-warning text-dark">Pending</span>
            @else
                <span class="badge bg-secondary">Closed</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($ticket->status != 'closed')
            <button type="button" class="btn btn-outline-danger" onclick="closeTicket()">
                <i class="fas fa-times-circle me-2"></i>Close Ticket
            </button>
        @endif
        <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row g-4" x-data="ticketReply()">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Ticket Info --}}
        @if($ticket->order_id)
            <div class="alert alert-info border-0 d-flex align-items-center mb-4">
                <i class="fas fa-link me-3 fa-lg"></i>
                <div>
                    <strong>Related Order:</strong>
                    <a href="{{ route('user.orders.view', $ticket->order->order_id ?? '#') }}" class="alert-link">
                        #{{ $ticket->order->order_id ?? 'N/A' }}
                    </a>
                </div>
            </div>
        @endif

        {{-- Messages Thread --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2 text-primary"></i>Conversation
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;" id="messagesContainer">
                @forelse($ticket->messages ?? [] as $message)
                    <div class="mb-4 {{ $message->is_admin ? 'ms-5' : 'me-5' }}">
                        <div class="d-flex {{ $message->is_admin ? '' : 'flex-row-reverse' }} align-items-start">
                            <div class="flex-shrink-0">
                                @if($message->is_admin)
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-headset text-white"></i>
                                    </div>
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 {{ $message->is_admin ? 'ms-3' : 'me-3' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">
                                        {{ $message->is_admin ? 'Support Team' : (auth()->user()->name ?? 'You') }}
                                    </span>
                                    <small class="text-muted">
                                        {{ $message->created_at->format('M d, Y h:i A') }}
                                    </small>
                                </div>
                                <div class="p-3 rounded {{ $message->is_admin ? 'bg-light' : 'bg-primary text-white' }}">
                                    {!! nl2br(e($message->message)) !!}
                                </div>
                                @if($message->attachments && $message->attachments->count() > 0)
                                    <div class="mt-2">
                                        @foreach($message->attachments as $attachment)
                                            <a href="{{ asset('storage/' . $attachment->path) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-paperclip me-1"></i>{{ $attachment->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No messages yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Reply Form --}}
        @if($ticket->status != 'closed')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-reply me-2 text-primary"></i>Reply
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.tickets.reply', $ticket->ticket_id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message"
                                      class="form-control @error('message') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Type your reply here..."
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Attachments (Optional)</label>
                            <input type="file"
                                   name="attachments[]"
                                   class="form-control"
                                   multiple
                                   accept="image/*,.pdf,.doc,.docx">
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Please wait for our response</small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-secondary border-0">
                <i class="fas fa-lock me-2"></i>
                This ticket has been closed. If you need further assistance, please create a new ticket.
                <a href="{{ route('user.tickets.create') }}" class="alert-link">Create New Ticket</a>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Ticket Status --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Ticket Status</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($ticket->status == 'open')
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-check text-success fa-2x"></i>
                        </div>
                        <h4 class="mt-3 mb-1 text-success">Open</h4>
                    @elseif($ticket->status == 'pending')
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                        <h4 class="mt-3 mb-1 text-warning">Pending</h4>
                    @else
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-times text-secondary fa-2x"></i>
                        </div>
                        <h4 class="mt-3 mb-1 text-secondary">Closed</h4>
                    @endif
                </div>
                <p class="text-muted small mb-0">
                    @if($ticket->status == 'open')
                        Our team will respond to your ticket soon.
                    @elseif($ticket->status == 'pending')
                        Awaiting your response. Please reply to continue.
                    @else
                        This ticket has been marked as resolved.
                    @endif
                </p>
            </div>
        </div>

        {{-- Ticket Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Ticket Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0 small">
                    <tbody>
                        <tr>
                            <td class="text-muted">Ticket ID</td>
                            <td><code>{{ $ticket->ticket_id }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority</td>
                            <td>
                                @if($ticket->priority == 'high')
                                    <span class="badge bg-danger">High</span>
                                @elseif($ticket->priority == 'medium')
                                    <span class="badge bg-warning text-dark">Medium</span>
                                @else
                                    <span class="badge bg-secondary">Low</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td>{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Update</td>
                            <td>{{ $ticket->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Messages</td>
                            <td>{{ $ticket->messages->count() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if($ticket->status != 'closed')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-danger w-100 mb-2" onclick="closeTicket()">
                        <i class="fas fa-times-circle me-2"></i>Close Ticket
                    </button>
                    <a href="{{ route('user.tickets.create') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-plus-circle me-2"></i>Create New Ticket
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Close Modal --}}
<div class="modal fade" id="closeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Close Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('user.tickets.close', $ticket->ticket_id) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to close this ticket?</p>
                    <p class="text-muted small mb-0">Ticket ID: <code>{{ $ticket->ticket_id }}</code></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Close Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function closeTicket() {
        new bootstrap.Modal(document.getElementById('closeModal')).show();
    }

    function ticketReply() {
        return {
            init() {
                // Scroll to bottom of messages
                const container = document.getElementById('messagesContainer');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        }
    }
</script>
@endpush
