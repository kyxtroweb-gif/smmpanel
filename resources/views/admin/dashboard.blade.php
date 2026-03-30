@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-4">Dashboard</h1>

{{-- Stats row --}}
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0"><i class="fa fa-users fa-2x text-primary"></i></div>
          <div class="ms-3">
            <h6 class="text-muted mb-1">Total Users</h6>
            <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0"><i class="fa fa-shopping-cart fa-2x text-success"></i></div>
          <div class="ms-3">
            <h6 class="text-muted mb-1">Orders Today</h6>
            <h3 class="mb-0">{{ number_format($stats['orders_today']) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0"><i class="fa fa-dollar-sign fa-2x text-warning"></i></div>
          <div class="ms-3">
            <h6 class="text-muted mb-1">Revenue Today</h6>
            <h3 class="mb-0">${{ number_format($stats['revenue_today'], 2) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0"><i class="fa fa-ticket fa-2x text-danger"></i></div>
          <div class="ms-3">
            <h6 class="text-muted mb-1">Pending Tickets</h6>
            <h3 class="mb-0">{{ number_format($stats['pending_tickets']) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Charts row --}}
<div class="row g-4 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Orders & Revenue (Last 7 Days)</h5>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-secondary active" data-period="7">7 Days</button>
          <button type="button" class="btn btn-outline-secondary" data-period="30">30 Days</button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="statsChart" height="100"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <h5 class="mb-0">Top Services</h5>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse($topServices as $service)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="text-truncate me-3">{{ $service->name }}</span>
              <span class="badge bg-primary rounded-pill">{{ $service->orders_count }}</span>
            </li>
          @empty
            <li class="list-group-item text-center text-muted">No data yet</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>

{{-- Recent orders table --}}
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Recent Orders</h5>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Service</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($recentOrders as $order)
          <tr>
            <td><code>{{ $order->order_id }}</code></td>
            <td>
              @can('admin.users.view')
                <a href="{{ route('admin.users.edit', $order->user_id) }}">{{ $order->user->name }}</a>
              @else
                {{ $order->user->name }}
              @endcan
            </td>
            <td>{{ $order->service->name ?? 'N/A' }}</td>
            <td>${{ number_format($order->charge, 4) }}</td>
            <td>{!! $order->status_label !!}</td>
            <td>{{ $order->created_at->diffForHumans() }}</td>
            <td class="text-end">
              @can('admin.orders.view')
                <a href="{{ route('admin.orders.view', $order->id) }}" class="btn btn-sm btn-link text-decoration-none">
                  <i class="fa fa-eye"></i>
                </a>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No orders yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Recent payments & tickets --}}
<div class="row g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Payments</h5>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>TXN ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPayments as $payment)
                <tr>
                  <td><code>{{ $payment->transaction_id }}</code></td>
                  <td>{{ $payment->user->name ?? 'N/A' }}</td>
                  <td>${{ number_format($payment->amount, 2) }}</td>
                  <td>{!! $payment->status_badge !!}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No payments yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pending Tickets</h5>
        <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse($recentTickets as $ticket)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <a href="{{ route('admin.tickets.view', $ticket->id) }}" class="text-decoration-none fw-semibold">
                  #{{ $ticket->id }} - {{ Str::limit($ticket->subject, 40) }}
                </a>
                <div class="small text-muted">by {{ $ticket->user->name ?? 'Unknown' }}</div>
              </div>
              <span class="badge bg-{{ $ticket->priority_color }}">{{ ucfirst($ticket->priority) }}</span>
            </li>
          @empty
            <li class="list-group-item text-center text-muted">No pending tickets</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statsChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Orders',
                data: chartData.orders,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            }, {
                label: 'Revenue ($)',
                data: chartData.revenue,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Orders' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Revenue ($)' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
});
</script>
@endpush
