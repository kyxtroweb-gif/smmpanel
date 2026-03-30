@extends('user.layout')
@section('title', 'Orders - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-shopping-bag me-2 text-primary"></i>My Orders
        </h2>
        <p class="text-muted mb-0">View and manage all your orders</p>
    </div>
    <div>
        <a href="{{ route('user.orders.new') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>New Order
        </a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('user.orders') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Order ID or Link" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('user.orders') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                <button type="button" class="btn btn-success" onclick="exportOrders()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            All Orders
            @if(isset($orders) && $orders->total() > 0)
                <span class="badge bg-primary ms-2">{{ $orders->total() }}</span>
            @endif
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 rounded-start px-4">Order ID</th>
                        <th class="border-0">Service</th>
                        <th class="border-0">Link</th>
                        <th class="border-0">Quantity</th>
                        <th class="border-0">Charge</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 rounded-end text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders ?? [] as $order)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('user.orders.view', $order->order_id) }}'">
                        <td class="px-4">
                            <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $order->order_id }}</code>
                        </td>
                        <td>
                            <div>
                                <span class="fw-medium">{{ $order->service->name ?? 'N/A' }}</span>
                                @if(isset($order->service->category))
                                    <br><small class="text-muted">{{ $order->service->category->name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:200px" title="{{ $order->link }}">
                                {{ Str::limit($order->link, 35) }}
                            </span>
                        </td>
                        <td>{{ number_format($order->quantity) }}</td>
                        <td class="fw-medium">${{ number_format($order->charge, 4) }}</td>
                        <td>{!! $order->status_label ?? '<span class="badge bg-secondary">Unknown</span>' !!}</td>
                        <td class="small text-muted">
                            <div>{{ $order->created_at->format('M d, Y') }}</div>
                            <div>{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                <a href="{{ route('user.orders.view', $order->order_id) }}"
                                   class="btn btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($order->can_cancel)
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="cancelOrder('{{ $order->order_id }}')"
                                            title="Cancel Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                @if($order->can_refill)
                                    <button type="button" class="btn btn-outline-success"
                                            onclick="refillOrder('{{ $order->order_id }}')"
                                            title="Refill Order">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No orders found</h5>
                                <p class="text-muted mb-3">
                                    @if(request()->hasAny(['status', 'search', 'date_from', 'date_to']))
                                        Try adjusting your filters
                                    @else
                                        You haven't placed any orders yet
                                    @endif
                                </p>
                                <a href="{{ route('user.orders.new') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>New Order
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($orders) && $orders->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="cancelForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order?</p>
                    <p class="text-muted small">Order ID: <code id="cancelOrderId"></code></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function cancelOrder(orderId) {
        document.getElementById('cancelOrderId').textContent = orderId;
        document.getElementById('cancelForm').action = '/user/orders/' + orderId + '/cancel';
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function refillOrder(orderId) {
        if (confirm('Are you sure you want to refill this order?')) {
            fetch('/user/orders/' + orderId + '/refill', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Refill request submitted successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.message || 'Failed to submit refill request');
                }
            })
            .catch(error => {
                toastr.error('An error occurred. Please try again.');
            });
        }
    }

    function exportOrders() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', '1');
        window.location.href = '{{ route('user.orders.export') }}?' + params.toString();
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
