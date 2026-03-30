@extends('layouts.admin')
@section('title', 'Payment Details - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back to Payments
        </a>
        <h1 class="h3 mb-0">Payment Details</h1>
    </div>
    <div class="btn-group">
        @if($payment->status === 'pending')
            @can('admin.payments.approve')
                <button type="button" class="btn btn-success" onclick="showApproveModal()">
                    <i class="fa fa-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                    <i class="fa fa-times me-1"></i> Reject
                </button>
            @endcan
        @elseif($payment->status === 'approved')
            @can('admin.payments.refund')
                <button type="button" class="btn btn-warning" onclick="showRefundModal()">
                    <i class="fa fa-undo me-1"></i> Refund
                </button>
            @endcan
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Main Details Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Information</h5>
                {!! $payment->status_badge !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 180px;">Payment ID</td>
                                <td>#{{ $payment->id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Transaction ID</td>
                                <td><code class="fs-6">{{ $payment->transaction_id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">User TXN ID</td>
                                <td>
                                    @if($payment->user_transaction_id)
                                        {{ $payment->user_transaction_id }}
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">User</td>
                                <td>
                                    @can('admin.users.view')
                                        <a href="{{ route('admin.users.edit', $payment->user_id) }}" class="fw-semibold">
                                            {{ $payment->user->name ?? 'N/A' }}
                                        </a>
                                        <span class="text-muted ms-2">({{ $payment->user->email ?? 'N/A' }})</span>
                                    @else
                                        <span class="fw-semibold">{{ $payment->user->name ?? 'N/A' }}</span>
                                        <span class="text-muted ms-2">({{ $payment->user->email ?? 'N/A' }})</span>
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Payment Method</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $payment->paymentMethod->name ?? 'N/A' }}
                                    </span>
                                    @if($payment->paymentMethod && $payment->paymentMethod->type)
                                        <span class="badge bg-{{ $payment->paymentMethod->type === 'auto' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($payment->paymentMethod->type) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Amount Claimed</td>
                                <td class="fs-5 fw-bold">${{ number_format($payment->amount, 4) }}</td>
                            </tr>
                            @if($payment->amount !== $payment->adjusted_amount)
                                <tr>
                                    <td class="text-muted fw-semibold">Admin-Adjusted Amount</td>
                                    <td>
                                        <span class="text-success fw-semibold">${{ number_format($payment->adjusted_amount, 4) }}</span>
                                        @if($payment->amount !== $payment->adjusted_amount)
                                            <span class="text-muted small ms-2">
                                                (Changed by admin)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted fw-semibold">Bonus</td>
                                <td class="text-success">
                                    @if($payment->bonus > 0)
                                        +${{ number_format($payment->bonus, 4) }}
                                    @else
                                        <span class="text-muted">No bonus</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="table-success">
                                <td class="fw-bold">Net Amount (Credited)</td>
                                <td class="fs-5 fw-bold">${{ number_format($payment->net_amount, 4) }}</td>
                            </tr>
                            @if($payment->note)
                                <tr>
                                    <td class="text-muted fw-semibold">Admin Note</td>
                                    <td>{{ $payment->note }}</td>
                                </tr>
                            @endif
                            @if($payment->reject_reason)
                                <tr class="table-danger">
                                    <td class="text-danger fw-semibold">Rejection Reason</td>
                                    <td class="text-danger">{{ $payment->reject_reason }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- User Payment Details (dynamic fields) --}}
        @if($payment->payment_details && is_array($payment->payment_details))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Details Submitted by User</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                @foreach($payment->payment_details as $key => $value)
                                    <tr>
                                        <td class="text-muted fw-semibold" style="width: 180px;">
                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                        </td>
                                        <td>
                                            @if(is_array($value))
                                                <pre class="mb-0 bg-light p-2 rounded" style="max-width: 400px;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline/Audit --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Activity Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline timeline-simple mb-0">
                    <li class="timeline-item">
                        <span class="timeline-point timeline-point-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'secondary') }}">
                            <i class="fa fa-{{ $payment->status === 'approved' ? 'check' : ($payment->status === 'rejected' ? 'times' : 'clock') }}"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">
                                    Payment {{ ucfirst($payment->status) }}
                                </span>
                                <span class="small text-muted">{{ $payment->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                            @if($payment->processedBy)
                                <p class="mb-0 text-muted small">Processed by {{ $payment->processedBy->name }}</p>
                            @endif
                        </div>
                    </li>
                    <li class="timeline-item">
                        <span class="timeline-point timeline-point-secondary">
                            <i class="fa fa-upload"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Payment Submitted</span>
                                <span class="small text-muted">{{ $payment->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Quick Actions --}}
        @if($payment->status === 'pending')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-success w-100 mb-2" onclick="showApproveModal()">
                        <i class="fa fa-check me-1"></i> Approve Payment
                    </button>
                    <button type="button" class="btn btn-outline-danger w-100" onclick="showRejectModal()">
                        <i class="fa fa-times me-1"></i> Reject Payment
                    </button>
                </div>
            </div>
        @elseif($payment->status === 'approved')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Refund Action</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        This payment has been approved. You can refund it to deduct the balance from the user's account.
                    </p>
                    <button type="button" class="btn btn-warning w-100" onclick="showRefundModal()">
                        <i class="fa fa-undo me-1"></i> Process Refund
                    </button>
                </div>
            </div>
        @endif

        {{-- Related User Info --}}
        @if($payment->user)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                             style="width: 60px; height: 60px; font-size: 24px;">
                            {{ strtoupper(substr($payment->user->name, 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $payment->user->name }}</h5>
                        <p class="text-muted small mb-0">{{ $payment->user->email }}</p>
                        <span class="badge bg-{{ $payment->user->role === 'admin' ? 'danger' : 'secondary' }}">
                            {{ ucfirst($payment->user->role) }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Current Balance</span>
                        <span class="fw-semibold">${{ number_format($payment->user->balance, 4) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Orders</span>
                        <span class="fw-semibold">{{ $payment->user->orders->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Deposits</span>
                        <span class="fw-semibold">${{ number_format($payment->user->deposits->where('status', 'approved')->sum('net_amount'), 2) }}</span>
                    </div>
                    @can('admin.users.view')
                        <a href="{{ route('admin.users.edit', $payment->user_id) }}"
                           class="btn btn-outline-primary btn-sm w-100 mt-3">
                            <i class="fa fa-user-edit me-1"></i> View Full Profile
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        {{-- Payment Method Info --}}
        @if($payment->paymentMethod)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        @if($payment->paymentMethod->logo)
                            <img src="{{ asset('storage/' . $payment->paymentMethod->logo) }}"
                                 alt="{{ $payment->paymentMethod->name }}" class="img-fluid mb-2" style="max-height: 40px;">
                        @else
                            <i class="fa fa-credit-card fa-2x text-muted mb-2"></i>
                        @endif
                        <h5 class="mb-0">{{ $payment->paymentMethod->name }}</h5>
                    </div>
                    @if($payment->paymentMethod->description)
                        <p class="text-muted small mb-2">{{ $payment->paymentMethod->description }}</p>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Min Amount</span>
                        <span class="small">${{ number_format($payment->paymentMethod->min_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Max Amount</span>
                        <span class="small">${{ number_format($payment->paymentMethod->max_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Bonus %</span>
                        <span class="small">{{ $payment->paymentMethod->bonus_percentage }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Fixed Charge</span>
                        <span class="small">{{ $payment->paymentMethod->fixed_charge }}%</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.action', $payment->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-check text-success me-1"></i> Approve Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-1"></i>
                        Original amount: <strong>${{ number_format($payment->amount, 4) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Final Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="amount" id="amount"
                                   class="form-control" value="{{ $payment->amount }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bonus" class="form-label">Bonus Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="bonus" id="bonus"
                                   class="form-control" value="0" min="0">
                        </div>
                        @if($payment->paymentMethod && $payment->paymentMethod->bonus_percentage > 0)
                            <div class="form-text">
                                Method default bonus: {{ $payment->paymentMethod->bonus_percentage }}%
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Internal Note</label>
                        <textarea name="note" id="note" class="form-control" rows="2"
                                  placeholder="Optional note for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-1"></i> Confirm Approval
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
            <form method="POST" action="{{ route('admin.payments.action', $payment->id) }}">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-times text-danger me-1"></i> Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        This will reject the payment and notify the user.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" class="form-control" rows="3"
                                  placeholder="Explain why this payment is being rejected..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times me-1"></i> Confirm Rejection
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
            <form method="POST" action="{{ route('admin.payments.action', $payment->id) }}">
                @csrf
                <input type="hidden" name="action" value="refund">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-undo text-warning me-1"></i> Refund Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        This will deduct <strong>${{ number_format($payment->net_amount, 4) }}</strong> from the user's balance.
                    </div>
                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Refund Reason</label>
                        <textarea name="reason" id="refund_reason" class="form-control" rows="2"
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
@endsection

@push('scripts')
<script>
function showApproveModal() {
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showRefundModal() {
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}
</script>
@endpush
