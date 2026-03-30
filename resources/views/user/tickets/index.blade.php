@extends('user.layout')
@section('title', 'Support Tickets - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-ticket-alt me-2 text-primary"></i>Support Tickets
        </h2>
        <p class="text-muted mb-0">Manage your support requests</p>
    </div>
    <div>
        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>New Ticket
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Total Tickets</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ $stats['total'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-ticket-alt text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Open Tickets</h6>
                        <h2 class="mb-0 fw-bold text-warning">{{ $stats['open'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-exclamation-circle text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Resolved</h6>
                        <h2 class="mb-0 fw-bold text-success">{{ $stats['resolved'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tickets Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">All Tickets</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width: auto;" onchange="filterStatus(this.value)">
                <option value="">All Status</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 rounded-start px-4">Ticket ID</th>
                        <th class="border-0">Subject</th>
                        <th class="border-0">Priority</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Last Reply</th>
                        <th class="border-0">Created</th>
                        <th class="border-0 rounded-end text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tickets ?? [] as $ticket)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('user.tickets.view', $ticket->ticket_id) }}'">
                        <td class="px-4">
                            <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $ticket->ticket_id }}</code>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $ticket->subject }}</span>
                            @if($ticket->messages_count > 0)
                                <br><small class="text-muted">{{ $ticket->messages_count }} messages</small>
                            @endif
                        </td>
                        <td>
                            @if($ticket->priority == 'high')
                                <span class="badge bg-danger">High</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-secondary">Low</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->status == 'open')
                                <span class="badge bg-success">Open</span>
                            @elseif($ticket->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($ticket->status == 'closed')
                                <span class="badge bg-secondary">Closed</span>
                            @else
                                <span class="badge bg-primary">{{ ucfirst($ticket->status) }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            @if($ticket->last_reply_at)
                                {{ $ticket->last_reply_at->diffForHumans() }}
                                <br>
                                <small>by {{ $ticket->last_reply_by ?? 'N/A' }}</small>
                            @else
                                No replies yet
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $ticket->created_at->format('M d, Y') }}
                            <br>
                            {{ $ticket->created_at->format('h:i A') }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                <a href="{{ route('user.tickets.view', $ticket->ticket_id) }}"
                                   class="btn btn-outline-primary"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($ticket->status != 'closed')
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="closeTicket('{{ $ticket->ticket_id }}')"
                                            title="Close Ticket">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No tickets found</h5>
                                <p class="text-muted mb-3">Need help? Create a support ticket!</p>
                                <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>New Ticket
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($tickets) && $tickets->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $tickets->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Close Ticket Modal --}}
<div class="modal fade" id="closeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Close Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="closeForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to close this ticket?</p>
                    <p class="text-muted small mb-0">Ticket ID: <code id="closeTicketId"></code></p>
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
    function closeTicket(ticketId) {
        document.getElementById('closeTicketId').textContent = ticketId;
        document.getElementById('closeForm').action = '/user/tickets/' + ticketId + '/close';
        new bootstrap.Modal(document.getElementById('closeModal')).show();
    }

    function filterStatus(status) {
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    }
</script>
@endpush

@push('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush
