@extends('user.layout')
@section('title', 'Order Details - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.orders') }}">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Order #{{ $order->order_id }}</li>
            </ol>
        </nav>
        <h2 class="mb-0">
            <i class="fas fa-file-invoice me-2 text-primary"></i>Order Details
        </h2>
    </div>
    <div class="d-flex gap-2">
        @if($order->can_cancel)
            <button type="button" class="btn btn-outline-danger" onclick="cancelOrder()">
                <i class="fas fa-times-circle me-2"></i>Cancel Order
            </button>
        @endif
        @if($order->can_refill)
            <button type="button" class="btn btn-outline-success" onclick="refillOrder()">
                <i class="fas fa-redo me-2"></i>Request Refill
            </button>
        @endif
        <a href="{{ route('user.orders') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

{{-- Order Status Banner --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-shopping-bag text-primary fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Order ID</p>
                        <h4 class="mb-0"><code>{{ $order->order_id }}</code></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <p class="text-muted mb-1">Status</p>
                {!! $order->status_label !!}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Main Details --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Order Information
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 200px;">Order ID</td>
                                <td><code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $order->order_id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Service</td>
                                <td>
                                    <span class="fw-semibold">{{ $order->service->name ?? 'N/A' }}</span>
                                    @if(isset($order->service->category))
                                        <br><small class="text-muted">{{ $order->service->category->name }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Link</td>
                                <td>
                                    <a href="{{ $order->link }}" target="_blank" class="text-break">
                                        {{ $order->link }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Quantity</td>
                                <td>{{ number_format($order->quantity) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Charge</td>
                                <td class="fw-semibold text-success">${{ number_format($order->charge, 4) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created</td>
                                <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            @if($order->updated_at && $order->updated_at != $order->created_at)
                                <tr>
                                    <td class="text-muted">Last Updated</td>
                                    <td>{{ $order->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Additional Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Order Progress
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Start Count</span>
                                <span class="fw-semibold">{{ number_format($order->start_count ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Remains</span>
                                <span class="fw-semibold">{{ number_format($order->remains ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($order->service && $order->quantity > 0)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Progress</span>
                            <span class="fw-semibold">
                                {{ round((($order->start_count ?? 0) / $order->quantity) * 100, 1) }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar"
                                 style="width: {{ min(100, (($order->start_count ?? 0) / $order->quantity) * 100) }}%">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Provider Info --}}
        @if(isset($order->provider_order_id) || isset($order->average_time))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2 text-primary"></i>Provider Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                @if(isset($order->provider_order_id))
                                    <tr>
                                        <td class="text-muted" style="width: 200px;">Provider Order ID</td>
                                        <td>
                                            <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $order->provider_order_id }}</code>
                                        </td>
                                    </tr>
                                @endif
                                @if(isset($order->average_time))
                                    <tr>
                                        <td class="text-muted">Average Time</td>
                                        <td>{{ $order->average_time }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Status Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Status</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    {!! $order->status_icon !!}
                </div>
                <h4 class="mb-0">{!! $order->status_name !!}</h4>
                <p class="text-muted small mt-2 mb-0">{{ $order->status_description ?? '' }}</p>
            </div>
        </div>

        {{-- Service Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Service Details</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-bolt fa-3x text-primary"></i>
                </div>
                <h5 class="text-center">{{ $order->service->name ?? 'N/A' }}</h5>
                @if(isset($order->service->category))
                    <p class="text-center text-muted small mb-3">{{ $order->service->category->name }}</p>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Price per 1K</span>
                    <span class="fw-semibold">${{ number_format(($order->service->price ?? 0), 4) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Min/Max</span>
                    <span class="fw-semibold">{{ number_format($order->service->min ?? 0) }} - {{ number_format($order->service->max ?? 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('user.orders.new', ['service' => $order->service_id]) }}"
                   class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-redo me-2"></i>Reorder Same Service
                </a>
                <a href="{{ route('user.orders.new') }}" class="btn btn-outline-primary w-100">
                    <i class="fas fa-plus me-2"></i>New Order
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('user.orders.cancel', $order->order_id) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order?</p>
                    <p class="text-muted small mb-0">Order ID: <code>{{ $order->order_id }}</code></p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. Any remaining balance will be refunded.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Refill Modal --}}
<div class="modal fade" id="refillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Refill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('user.orders.refill', $order->order_id) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to request a refill for this order?</p>
                    <p class="text-muted small mb-0">Order ID: <code>{{ $order->order_id }}</code></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Request Refill</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function cancelOrder() {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function refillOrder() {
        new bootstrap.Modal(document.getElementById('refillModal')).show();
    }
</script>
@endpush
