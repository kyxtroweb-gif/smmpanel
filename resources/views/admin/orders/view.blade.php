@extends('layouts.admin')
@section('title', 'Order Details - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back to Orders
        </a>
        <h1 class="h3 mb-0">Order Details</h1>
    </div>
    <div class="btn-group">
        {!! $order->status_badge ?? '<span class="badge bg-secondary">Unknown</span>' !!}
        @if($order->status === 'pending')
            <button type="button" class="btn btn-danger" onclick="showCancelModal()">
                <i class="fa fa-ban me-1"></i> Cancel
            </button>
        @endif
        @if(in_array($order->status, ['completed', 'partial']))
            <button type="button" class="btn btn-warning" onclick="showRefundModal()">
                <i class="fa fa-undo me-1"></i> Refund
            </button>
        @endif
        @if(in_array($order->status, ['pending', 'processing', 'in_progress']))
            <button type="button" class="btn btn-info" onclick="showStatusModal()">
                <i class="fa fa-sync me-1"></i> Change Status
            </button>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Order Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-shopping-bag me-2 text-primary"></i>Order Information</h5>
                <span class="text-muted small">#{{ $order->id }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 180px;">Order ID</td>
                                <td><code class="fs-6">{{ $order->order_id }}</code></td>
                            </tr>
                            @if($order->provider_order_id)
                                <tr>
                                    <td class="text-muted fw-semibold">Provider Order ID</td>
                                    <td><code>{{ $order->provider_order_id }}</code></td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted fw-semibold">User</td>
                                <td>
                                    @can('admin.users.view')
                                        <a href="{{ route('admin.users.edit', $order->user_id) }}" class="fw-semibold">
                                            {{ $order->user->name ?? 'N/A' }}
                                        </a>
                                    @else
                                        <span class="fw-semibold">{{ $order->user->name ?? 'N/A' }}</span>
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Service</td>
                                <td>
                                    @if($order->service)
                                        <a href="{{ route('admin.services.edit', $order->service_id) }}">
                                            {{ $order->service->name }}
                                        </a>
                                        @if($order->service->category)
                                            <span class="text-muted"> ({{ $order->service->category->name }})</span>
                                        @endif
                                    @else
                                        <span class="text-danger">Service Deleted</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Link</td>
                                <td>
                                    <a href="{{ $order->link }}" target="_blank" class="text-break">
                                        {{ $order->link }}
                                        <i class="fa fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Quantity</td>
                                <td>{{ number_format($order->quantity) }}</td>
                            </tr>
                            @if($order->runs)
                                <tr>
                                    <td class="text-muted fw-semibold">Runs (Dripfeed)</td>
                                    <td>{{ $order->runs }} x {{ number_format($order->interval) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted fw-semibold">Charge</td>
                                <td class="fs-5 fw-bold">${{ number_format($order->charge, 4) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Cost</td>
                                <td>${{ number_format($order->cost, 4) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Profit</td>
                                <td class="{{ $order->profit >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    ${{ number_format($order->profit, 4) }}
                                    @if($order->profit < 0)
                                        <i class="fa fa-exclamation-triangle ms-1"></i>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Order Progress --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-chart-line me-2 text-primary"></i>Order Progress</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="fs-3 fw-bold">{{ number_format($order->start_count ?? 0) }}</span>
                            </div>
                            <div class="text-muted small">Start Count</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="fs-3 fw-bold text-success">{{ number_format($order->remains ?? $order->quantity) }}</span>
                            </div>
                            <div class="text-muted small">Remains</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="fs-3 fw-bold">{{ number_format($order->quantity) }}</span>
                            </div>
                            <div class="text-muted small">Ordered Qty</div>
                        </div>
                    </div>
                </div>
                @if($order->start_count !== null)
                    <div class="mt-3">
                        <div class="progress" style="height: 20px;">
                            @php
                                $delivered = $order->start_count ?? 0;
                                $total = $order->quantity + $delivered;
                                $percentage = $total > 0 ? min(100, ($delivered / $total) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success progress-bar-striped"
                                 role="progressbar" style="width: {{ $percentage }}%">
                                {{ round($percentage) }}%
                            </div>
                        </div>
                    </div>
                @endif

                @if($order->error)
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fa fa-exclamation-circle me-1"></i>
                        <strong>Error:</strong> {{ $order->error }}
                    </div>
                @endif

                {{-- Status History --}}
                @if($order->status_history && count($order->status_history) > 0)
                    <hr>
                    <h6 class="mb-3">Status History</h6>
                    <ul class="timeline timeline-simple mb-0">
                        @foreach($order->status_history as $history)
                            <li class="timeline-item">
                                <span class="timeline-point timeline-point-secondary">
                                    <i class="fa fa-circle"></i>
                                </span>
                                <div class="timeline-event">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">{{ ucfirst($history['status']) }}</span>
                                        <span class="small text-muted">{{ $history['date'] }}</span>
                                    </div>
                                    @if(isset($history['note']))
                                        <p class="text-muted small mb-0">{{ $history['note'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Provider Response --}}
        @if($order->provider_response)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-code me-2 text-primary"></i>Provider Response</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0 bg-light p-3 rounded font-monospace small"
                         style="max-height: 200px; overflow: auto;">{{ json_encode($order->provider_response, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

        {{-- Timestamps --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-clock me-2 text-primary"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 180px;">Created</td>
                                <td>{{ $order->created_at->format('M d, Y H:i:s') }}
                                    <span class="text-muted">({{ $order->created_at->diffForHumans() }})</span>
                                </td>
                            </tr>
                            @if($order->updated_at != $order->created_at)
                                <tr>
                                    <td class="text-muted fw-semibold">Last Updated</td>
                                    <td>{{ $order->updated_at->format('M d, Y H:i:s') }}
                                        <span class="text-muted">({{ $order->updated_at->diffForHumans() }})</span>
                                    </td>
                                </tr>
                            @endif
                            @if($order->completed_at)
                                <tr>
                                    <td class="text-muted fw-semibold">Completed</td>
                                    <td>{{ $order->completed_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Quick Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-bolt me-2 text-primary"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($order->status === 'pending')
                        <button type="button" class="btn btn-danger" onclick="showCancelModal()">
                            <i class="fa fa-ban me-1"></i> Cancel Order
                        </button>
                    @endif
                    @if(in_array($order->status, ['completed', 'partial']))
                        <button type="button" class="btn btn-warning" onclick="showRefundModal()">
                            <i class="fa fa-undo me-1"></i> Refund Order
                        </button>
                    @endif
                    @if(in_array($order->status, ['pending', 'processing', 'in_progress']))
                        <button type="button" class="btn btn-info" onclick="showStatusModal()">
                            <i class="fa fa-sync me-1"></i> Change Status
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Service Info --}}
        @if($order->service)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($order->service->provider)
                            @if($order->service->provider->logo)
                                <img src="{{ asset('storage/' . $order->service->provider->logo) }}"
                                     alt="" class="rounded me-2" style="height: 24px;">
                            @else
                                <i class="fa fa-server text-muted me-2"></i>
                            @endif
                            <span class="small text-muted">{{ $order->service->provider->name }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Price / 1K</span>
                        <span class="small">${{ number_format($order->service->price, 4) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Cost / 1K</span>
                        <span class="small">${{ number_format($order->service->cost, 4) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Min Order</span>
                        <span class="small">{{ number_format($order->service->min_quantity) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Max Order</span>
                        <span class="small">{{ number_format($order->service->max_quantity) }}</span>
                    </div>
                    @can('admin.services.edit')
                        <a href="{{ route('admin.services.edit', $order->service_id) }}"
                           class="btn btn-outline-primary btn-sm w-100 mt-3">
                            <i class="fa fa-edit me-1"></i> Edit Service
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        {{-- User Info --}}
        @if($order->user)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Info</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px; font-size: 20px;">
                            {{ strtoupper(substr($order->user->name, 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $order->user->name }}</h5>
                        <p class="text-muted small mb-1">{{ $order->user->email }}</p>
                        <span class="badge bg-{{ $order->user->role === 'admin' ? 'danger' : 'secondary' }}">
                            {{ ucfirst($order->user->role) }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Balance</span>
                        <span class="small fw-semibold">${{ number_format($order->user->balance, 4) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total Orders</span>
                        <span class="small">{{ $order->user->orders->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Registered</span>
                        <span class="small">{{ $order->user->created_at->format('M d, Y') }}</span>
                    </div>
                    @can('admin.users.view')
                        <a href="{{ route('admin.users.edit', $order->user_id) }}"
                           class="btn btn-outline-secondary btn-sm w-100 mt-3">
                            <i class="fa fa-user me-1"></i> View Profile
                        </a>
                    @endcan
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Are you sure you want to cancel this order?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="Optional reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-ban me-1"></i> Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.orders.refund', $order->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-warning">Refund Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Original Charge:</strong> ${{ number_format($order->charge, 4) }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Refund Type</label>
                        <select name="type" class="form-select" id="refundTypeSelect"
                                onchange="togglePartialAmount()">
                            <option value="full">Full Refund</option>
                            <option value="partial">Partial Refund</option>
                        </select>
                    </div>
                    <div class="mb-3" id="partialAmountField" style="display: none;">
                        <label class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="amount" class="form-control"
                                   min="0" max="{{ $order->charge }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
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

{{-- Status Change Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="partial" {{ $order->status === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Internal note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function showRefundModal() {
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}

function showStatusModal() {
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function togglePartialAmount() {
    const type = document.getElementById('refundTypeSelect').value;
    document.getElementById('partialAmountField').style.display =
        type === 'partial' ? '' : 'none';
}
</script>
@endpush
