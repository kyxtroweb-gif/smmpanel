@extends('user.layout')
@section('title', 'Dashboard - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">My Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name ?? 'User' }}!</p>
    </div>
    <div>
        <a href="{{ route('user.orders.new') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>New Order
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-white bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="opacity-75 text-white">Current Balance</h6>
                        <h2 class="mb-0 fw-bold">${{ number_format(auth()->user()->balance ?? 0, 2) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-wallet fa-lg"></i>
                    </div>
                </div>
                <a href="{{ route('user.deposit') }}" class="btn btn-light btn-sm mt-3 w-100">
                    <i class="fas fa-plus me-1"></i>Add Funds
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Total Orders</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ number_format($stats['total_orders'] ?? 0) }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-shopping-bag text-primary fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('user.orders') }}" class="text-decoration-none small">View all orders <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Pending Orders</h6>
                        <h2 class="mb-0 fw-bold text-warning">{{ number_format($stats['pending_orders'] ?? 0) }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning">In Progress</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase small">Total Spent</h6>
                        <h2 class="mb-0 fw-bold text-dark">${{ number_format($stats['total_spent'] ?? 0, 2) }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-dollar-sign text-success fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('user.orders', ['status' => 'completed']) }}" class="text-decoration-none small">Completed orders <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="fas fa-bolt text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Quick Order</h5>
                        <p class="text-muted mb-0 small">Place an order instantly</p>
                    </div>
                </div>
                <a href="{{ route('user.orders.new') }}" class="btn btn-outline-primary w-100 mt-3">
                    <i class="fas fa-arrow-right me-2"></i>Start Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                        <i class="fas fa-plus-circle text-success fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Add Funds</h5>
                        <p class="text-muted mb-0 small">Recharge your balance</p>
                    </div>
                </div>
                <a href="{{ route('user.deposit') }}" class="btn btn-outline-success w-100 mt-3">
                    <i class="fas fa-arrow-right me-2"></i>Deposit
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                        <i class="fas fa-code text-info fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">API Access</h5>
                        <p class="text-muted mb-0 small">Integrate with our API</p>
                    </div>
                </div>
                <a href="{{ route('user.api') }}" class="btn btn-outline-info w-100 mt-3">
                    <i class="fas fa-arrow-right me-2"></i>View API
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="fas fa-list me-2 text-primary"></i>Recent Orders
        </h5>
        <a href="{{ route('user.orders') }}" class="btn btn-sm btn-outline-primary">
            View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 rounded-start">Order ID</th>
                        <th class="border-0">Service</th>
                        <th class="border-0">Link</th>
                        <th class="border-0">Qty</th>
                        <th class="border-0">Charge</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentOrders ?? [] as $order)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('user.orders.view', $order->order_id) }}'">
                        <td>
                            <a href="{{ route('user.orders.view', $order->order_id) }}" class="text-decoration-none">
                                <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $order->order_id }}</code>
                            </a>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $order->service->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:180px" title="{{ $order->link }}">
                                {{ Str::limit($order->link, 30) }}
                            </span>
                        </td>
                        <td>{{ number_format($order->quantity) }}</td>
                        <td class="fw-medium">${{ number_format($order->charge, 4) }}</td>
                        <td>{!! $order->status_label ?? '<span class="badge bg-secondary">Unknown</span>' !!}</td>
                        <td class="text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No orders yet</h5>
                                <p class="text-muted mb-3">Place your first order and get started!</p>
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
</div>

{{-- Services Preview --}}
@if(isset($popularServices) && $popularServices->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2 text-warning"></i>Popular Services
                </h5>
                <a href="{{ route('user.orders.new') }}" class="btn btn-sm btn-warning">
                    View All Services <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($popularServices as $service)
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">{{ $service->category->name ?? 'General' }}</span>
                                <small class="text-muted">{{ $service->name }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <small class="text-muted d-block">Starting at</small>
                                    <span class="fw-bold text-success">${{ number_format($service->price, 4) }}/1K</span>
                                </div>
                                <a href="{{ route('user.orders.new', ['service' => $service->id]) }}" class="btn btn-sm btn-outline-primary">
                                    Order
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush
