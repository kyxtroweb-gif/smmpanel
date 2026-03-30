@extends('layouts.admin')
@section('title', 'Payments - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payments / Deposits</h1>
    <div class="btn-group">
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-cog me-1"></i> Manage Payment Methods
        </a>
        <a href="{{ route('admin.payments.export') }}?{{ http_build_query(request()->except('page')) }}"
           class="btn btn-success btn-sm">
            <i class="fa fa-download me-1"></i> Export CSV
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Transaction ID, User TXN ID, User name..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="method" class="form-label small text-muted">Payment Method</label>
                <select name="method" id="method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ request('method') == $method->id ? 'selected' : '' }}>
                            {{ $method->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-muted">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-white-50">Pending</div>
                        <div class="fs-5 fw-bold">{{ number_format($summary['pending']) }}</div>
                    </div>
                    <i class="fa fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-white-50">Approved</div>
                        <div class="fs-5 fw-bold">${{ number_format($summary['approved'], 2) }}</div>
                    </div>
                    <i class="fa fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-white-50">Rejected</div>
                        <div class="fs-5 fw-bold">{{ number_format($summary['rejected']) }}</div>
                    </div>
                    <i class="fa fa-times-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small">Total Bonus Given</div>
                        <div class="fs-5 fw-bold">${{ number_format($summary['total_bonus'], 2) }}</div>
                    </div>
                    <i class="fa fa-gift fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payments Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Payments ({{ $payments->total() }} total)</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('admin.payments.index', array_merge(request()->except('page'), ['sort' => 'id', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-decoration-none text-dark">ID</a>
                        </th>
                        <th>Transaction ID</th>
                        <th>User TXN ID</th>
                        <th>
                            <a href="{{ route('admin.payments.index', array_merge(request()->except('page'), ['sort' => 'user_id', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-decoration-none text-dark">User</a>
                        </th>
                        <th>Method</th>
                        <th>
                            <a href="{{ route('admin.payments.index', array_merge(request()->except('page'), ['sort' => 'amount', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-decoration-none text-dark">Amount</a>
                        </th>
                        <th>Bonus</th>
                        <th>Net Amount</th>
                        <th>
                            <a href="{{ route('admin.payments.index', array_merge(request()->except('page'), ['sort' => 'status', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-decoration-none text-dark">Status</a>
                        </th>
                        <th>
                            <a href="{{ route('admin.payments.index', array_merge(request()->except('page'), ['sort' => 'created_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-decoration-none text-dark">Date</a>
                        </th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td class="text-muted">#{{ $payment->id }}</td>
                        <td><code>{{ $payment->transaction_id }}</code></td>
                        <td>
                            @if($payment->user_transaction_id)
                                <span class="text-break">{{ $payment->user_transaction_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @can('admin.users.view')
                                <a href="{{ route('admin.users.edit', $payment->user_id) }}">
                                    {{ $payment->user->name ?? 'N/A' }}
                                </a>
                            @else
                                {{ $payment->user->name ?? 'N/A' }}
                            @endcan
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $payment->paymentMethod->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="fw-semibold">${{ number_format($payment->amount, 4) }}</td>
                        <td class="text-success">
                            @if($payment->bonus > 0)
                                +${{ number_format($payment->bonus, 4) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-semibold text-success">${{ number_format($payment->net_amount, 4) }}</td>
                        <td>{!! $payment->status_badge !!}</td>
                        <td class="text-muted small">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.payments.view', $payment->id) }}"
                                   class="btn btn-outline-primary" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if($payment->status === 'pending')
                                    @can('admin.payments.approve')
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="approvePayment({{ $payment->id }}, {{ $payment->amount }})"
                                                title="Approve">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="rejectPayment({{ $payment->id }})"
                                                title="Reject">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">
                            <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No payments found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
        <div class="card-footer bg-white">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="approveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <div class="mb-3">
                        <label for="approve_amount" class="form-label">Amount (override if needed)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="amount" id="approve_amount"
                                   class="form-control" placeholder="Original amount">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="approve_bonus" class="form-label">Bonus Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="bonus" id="approve_bonus"
                                   class="form-control" value="0" placeholder="0">
                        </div>
                        <div class="form-text">Leave at 0 for no bonus</div>
                    </div>
                    <div class="mb-3">
                        <label for="approve_note" class="form-label">Note (optional)</label>
                        <textarea name="note" id="approve_note" class="form-control" rows="2"
                                  placeholder="Internal note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-1"></i> Approve Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="rejectForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reject_reason" class="form-control" rows="3"
                                  placeholder="Explain why this payment is being rejected..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times me-1"></i> Reject Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approvePayment(id, originalAmount) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.getElementById('approveForm').action = '/admin/payments/' + id + '/action';
    document.getElementById('approve_amount').value = originalAmount;
    modal.show();
}

function rejectPayment(id) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('rejectForm').action = '/admin/payments/' + id + '/action';
    document.getElementById('reject_reason').value = '';
    modal.show();
}
</script>
@endpush
