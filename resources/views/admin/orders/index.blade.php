@extends('layouts.admin')
@section('title', 'Orders - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Orders Management</h1>
    <div class="btn-group">
        <a href="{{ route('admin.orders.export') }}?{{ http_build_query(request()->except('page')) }}"
           class="btn btn-success btn-sm">
            <i class="fa fa-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Manual Order
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Total Orders</div>
                <div class="fs-5 fw-bold">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Pending</div>
                <div class="fs-5 fw-bold text-warning">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Processing</div>
                <div class="fs-5 fw-bold text-info">{{ number_format($stats['processing']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Completed</div>
                <div class="fs-5 fw-bold text-success">{{ number_format($stats['completed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Cancelled</div>
                <div class="fs-5 fw-bold text-danger">{{ number_format($stats['cancelled']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Total Revenue</div>
                <div class="fs-5 fw-bold text-primary">${{ number_format($stats['revenue'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Order ID, Link, User..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="service_id" class="form-label small text-muted">Service</label>
                <select name="service_id" id="service_id" class="form-select">
                    <option value="">All Services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="provider_id" class="form-label small text-muted">Provider</label>
                <select name="provider_id" id="provider_id" class="form-select">
                    <option value="">All Providers</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" {{ request('provider_id') == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted">From</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label for="date_to" class="form-label small text-muted">To</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>
        </form>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" form="filterForm">
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">{{ $orders->total() }} results</small>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Actions --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="selectAll"
               onchange="toggleSelectAll()">
        <label class="form-check-label" for="selectAll">Select All</label>
    </div>
    <div class="btn-group" id="bulkActions" style="display: none;">
        <button type="button" class="btn btn-outline-danger btn-sm"
                onclick="bulkAction('cancel')">
            <i class="fa fa-ban me-1"></i> Cancel Selected
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm"
                onclick="bulkAction('refund')">
            <i class="fa fa-undo me-1"></i> Refund Selected
        </button>
    </div>
</div>

{{-- Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Link</th>
                        <th>Qty</th>
                        <th>Charge</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr data-id="{{ $order->id }}">
                        <td>
                            <input class="form-check-input order-checkbox" type="checkbox"
                                   value="{{ $order->id }}">
                        </td>
                        <td><code>{{ $order->order_id }}</code></td>
                        <td>
                            @can('admin.users.view')
                                <a href="{{ route('admin.users.edit', $order->user_id) }}">
                                    {{ $order->user->name ?? 'N/A' }}
                                </a>
                            @else
                                {{ $order->user->name ?? 'N/A' }}
                            @endcan
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                {{ $order->service->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ $order->link }}" target="_blank" class="text-decoration-none"
                               title="{{ $order->link }}">
                                {{ Str::limit($order->link, 30) }}
                                <i class="fa fa-external-link-alt ms-1"></i>
                            </a>
                        </td>
                        <td>{{ number_format($order->quantity) }}</td>
                        <td class="fw-semibold">${{ number_format($order->charge, 4) }}</td>
                        <td>{!! $order->status_label !!}</td>
                        <td class="text-muted small">{{ $order->created_at->format('M d, H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.view', $order->id) }}"
                                   class="btn btn-outline-secondary" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if($order->status === 'pending')
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="cancelOrder({{ $order->id }})"
                                            title="Cancel">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                @endif
                                @if(in_array($order->status, ['completed', 'partial']))
                                    <button type="button" class="btn btn-outline-warning"
                                            onclick="refundOrder({{ $order->id }})"
                                            title="Refund">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No orders found.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="refundForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-undo text-warning me-1"></i> Refund Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="refund_type" class="form-label">Refund Type</label>
                        <select name="type" id="refund_type" class="form-select">
                            <option value="full">Full Refund</option>
                            <option value="partial">Partial Refund</option>
                        </select>
                    </div>
                    <div class="mb-3" id="partialAmountGroup">
                        <label for="refund_amount" class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="amount" id="refund_amount"
                                   class="form-control" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="Reason for refund..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-undo me-1"></i> Process Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="cancelForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-ban text-danger me-1"></i> Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Are you sure you want to cancel this order?
                    </div>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason (optional)</label>
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="Reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-ban me-1"></i> Yes, Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.order-checkbox:checked').length;
    document.getElementById('bulkActions').style.display = checked > 0 ? '' : 'none';
}

document.querySelectorAll('.order-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.order-checkbox:checked'))
        .map(cb => cb.value);

    if (!confirm(`Perform ${action} on ${selected.length} orders?`)) return;

    fetch('/admin/orders/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action, ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(`${selected.length} orders ${action}ed`);
            location.reload();
        } else {
            toastr.error(data.message || 'Action failed');
        }
    });
}

function refundOrder(id) {
    document.getElementById('refundForm').action = '/admin/orders/' + id + '/refund';
    document.getElementById('refund_amount').value = '0';
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}

function cancelOrder(id) {
    document.getElementById('cancelForm').action = '/admin/orders/' + id + '/cancel';
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

document.getElementById('refund_type').addEventListener('change', function() {
    document.getElementById('partialAmountGroup').style.display =
        this.value === 'partial' ? '' : 'none';
});
</script>
@endpush
