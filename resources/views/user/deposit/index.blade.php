@extends('user.layout')
@section('title', 'Deposits - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-credit-card me-2 text-primary"></i>Deposits
        </h2>
        <p class="text-muted mb-0">Add funds to your account balance</p>
    </div>
</div>

{{-- Balance Card --}}
<div class="card border-0 shadow-sm mb-4 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body text-white p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="opacity-75 text-white">Current Balance</h6>
                <h1 class="mb-2">${{ number_format(auth()->user()->balance ?? 0, 2) }}</h1>
                <p class="mb-0 opacity-75">Available for orders</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="bg-white bg-opacity-25 rounded p-3 d-inline-block">
                    <i class="fas fa-wallet fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Deposit Methods --}}
<div class="row g-4 mb-4">
    {{-- Auto Payment Methods --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2 text-warning"></i>Automatic Payment Methods
                </h5>
                <p class="text-muted small mb-0 mt-1">Instant processing via payment gateway</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($autoMethods ?? [] as $method)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100 text-center hover-shadow transition cursor-pointer"
                                 onclick="selectMethod('{{ $method->id }}', 'auto')">
                                <img src="{{ $method->logo_url ?? asset('images/payment/default.png') }}"
                                     alt="{{ $method->name }}"
                                     class="mb-3"
                                     style="max-height: 50px; max-width: 100px;">
                                <h6 class="mb-1">{{ $method->name }}</h6>
                                <small class="text-muted d-block">
                                    @if($method->min_amount)
                                        Min: ${{ number_format($method->min_amount, 2) }}
                                    @endif
                                </small>
                                @if($method->bonus > 0)
                                    <span class="badge bg-success mt-2">+{{ $method->bonus }}% Bonus</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">No automatic payment methods available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Manual Payment Methods --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-hand-holding-usd me-2 text-info"></i>Manual Payment Methods
                </h5>
                <p class="text-muted small mb-0 mt-1">Send payment manually and submit transaction details</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($manualMethods ?? [] as $method)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100 text-center hover-shadow transition cursor-pointer"
                                 onclick="selectMethod('{{ $method->id }}', 'manual')">
                                @if($method->qr_image)
                                    <img src="{{ asset('storage/' . $method->qr_image) }}"
                                         alt="{{ $method->name }} QR"
                                         class="mb-3"
                                         style="max-height: 80px; max-width: 80px;">
                                @else
                                    <div class="bg-light rounded mb-3" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-qrcode fa-2x text-muted"></i>
                                    </div>
                                @endif
                                <h6 class="mb-1">{{ $method->name }}</h6>
                                <small class="text-muted d-block">
                                    @if($method->min_amount)
                                        Min: ${{ number_format($method->min_amount, 2) }}
                                    @endif
                                    @if($method->max_amount)
                                        - Max: ${{ number_format($method->max_amount, 2) }}
                                    @endif
                                </small>
                                @if($method->bonus > 0)
                                    <span class="badge bg-success mt-2">+{{ $method->bonus }}% Bonus</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">No manual payment methods available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Suggested Amounts --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            <i class="fas fa-lightbulb me-2 text-warning"></i>Suggested Deposit Amounts
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([5, 10, 25, 50, 100, 200] as $amount)
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <button type="button"
                            class="btn btn-outline-primary w-100 py-3"
                            onclick="setAmount({{ $amount }})">
                        <h4 class="mb-0">${{ $amount }}</h4>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Custom Amount --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            <i class="fas fa-calculator me-2 text-primary"></i>Custom Amount
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('user.deposit.submit') }}" id="customAmountForm">
            <input type="hidden" name="method_id" id="selectedMethodId">
            <input type="hidden" name="method_type" id="selectedMethodType">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Enter Amount (USD)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               name="amount"
                               id="depositAmount"
                               class="form-control form-control-lg"
                               min="1"
                               step="0.01"
                               placeholder="Enter amount"
                               required>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="proceedBtn" disabled>
                        <i class="fas fa-arrow-right me-2"></i>Proceed to Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Recent Deposits --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-history me-2 text-primary"></i>Recent Deposits
        </h5>
        <a href="{{ route('user.deposits.history') }}" class="btn btn-sm btn-outline-primary">
            View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 rounded-start px-4">Transaction ID</th>
                        <th class="border-0">Method</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Bonus</th>
                        <th class="border-0">Total</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentDeposits ?? [] as $deposit)
                    <tr>
                        <td class="px-4">
                            <code class="bg-dark bg-opacity-10 px-2 py-1 rounded">{{ $deposit->transaction_id ?? 'N/A' }}</code>
                        </td>
                        <td>{{ $deposit->method->name ?? 'N/A' }}</td>
                        <td class="fw-medium">${{ number_format($deposit->amount, 2) }}</td>
                        <td class="text-success">+${{ number_format($deposit->bonus ?? 0, 2) }}</td>
                        <td class="fw-semibold">${{ number_format($deposit->total, 2) }}</td>
                        <td>
                            @if($deposit->status == 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($deposit->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($deposit->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($deposit->status) }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $deposit->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="py-3">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No deposits yet</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function selectMethod(methodId, methodType) {
        document.getElementById('selectedMethodId').value = methodId;
        document.getElementById('selectedMethodType').value = methodType;
        document.getElementById('proceedBtn').disabled = false;

        // Scroll to custom amount form
        document.getElementById('customAmountForm').scrollIntoView({ behavior: 'smooth' });

        toastr.info('Method selected. Enter an amount and proceed.');
    }

    function setAmount(amount) {
        document.getElementById('depositAmount').value = amount;
        document.getElementById('proceedBtn').disabled = false;
    }

    // Enable proceed button when amount is entered
    document.getElementById('depositAmount').addEventListener('input', function() {
        document.getElementById('proceedBtn').disabled = this.value <= 0;
    });
</script>
@endpush

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    .transition {
        transition: all 0.3s ease;
    }
</style>
@endpush
