@extends('user.layout')
@section('title', 'Submit Payment - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.deposit') }}">Deposits</a></li>
                <li class="breadcrumb-item active" aria-current="page">Submit Payment</li>
            </ol>
        </nav>
        <h2 class="mb-0">
            <i class="fas fa-receipt me-2 text-primary"></i>Submit Payment
        </h2>
    </div>
    <a href="{{ route('user.deposit') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

{{-- Alert --}}
<div class="alert alert-warning border-0 d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
    <div>
        <strong>Important:</strong> Do not close this page until you have submitted your transaction details.
        Your funds will be credited after manual verification (usually within 24 hours).
    </div>
</div>

<div class="row g-4" x-data="depositForm()">
    {{-- Left Panel - Payment Instructions --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Payment Details
                </h5>
            </div>
            <div class="card-body">
                {{-- Payment Method Info --}}
                <div class="text-center mb-4">
                    @if(isset($method->logo_url) && $method->logo_url)
                        <img src="{{ $method->logo_url }}" alt="{{ $method->name }}" class="mb-3" style="max-height: 60px;">
                    @else
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-wallet text-primary fa-2x"></i>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $method->name ?? 'Payment Method' }}</h4>
                    @if(isset($method->bonus) && $method->bonus > 0)
                        <span class="badge bg-success">+{{ $method->bonus }}% Bonus</span>
                    @endif
                </div>

                {{-- Deposit Summary --}}
                <div class="bg-light rounded p-3 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount</span>
                        <span class="fw-semibold">${{ number_format($amount ?? 0, 2) }}</span>
                    </div>
                    @if(isset($method->bonus) && $method->bonus > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Bonus ({{ $method->bonus }}%)</span>
                            <span class="text-success fw-semibold">+${{ number_format(($amount ?? 0) * $method->bonus / 100, 2) }}</span>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">You Will Receive</span>
                        <span class="fw-bold text-success h5 mb-0">
                            ${{ number_format(($amount ?? 0) * (1 + ($method->bonus ?? 0) / 100), 2) }}
                        </span>
                    </div>
                </div>

                {{-- QR Code --}}
                @if(isset($method->qr_image) && $method->qr_image)
                    <div class="text-center mb-4">
                        <p class="text-muted small mb-2">Scan QR Code to Pay</p>
                        <div class="border rounded p-3 d-inline-block">
                            <img src="{{ asset('storage/' . $method->qr_image) }}"
                                 alt="Payment QR Code"
                                 style="max-width: 200px;">
                        </div>
                    </div>
                @endif

                {{-- UPI ID --}}
                @if(isset($method->upi_id) && $method->upi_id)
                    <div class="alert alert-primary border-0 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="d-block text-muted">Pay to UPI ID</small>
                                <span class="fw-bold fs-5">{{ $method->upi_id }}</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyUPI()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Payment Account --}}
                @if(isset($method->account_number))
                    <div class="mb-4">
                        <label class="form-label text-muted small">Account/Payment To</label>
                        <div class="d-flex justify-content-between align-items-center bg-light rounded p-3">
                            <span class="fw-semibold">{{ $method->account_number }}</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyAccount()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Instructions --}}
                @if(isset($method->instructions) && $method->instructions)
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-list me-1"></i>Payment Instructions
                        </label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($method->instructions)) !!}
                        </div>
                    </div>
                @endif

                {{-- Min/Max Info --}}
                @if(isset($method->min_amount) || isset($method->max_amount))
                    <div class="row g-2 mb-4">
                        @if($method->min_amount)
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Min Amount</small>
                                    <span class="fw-semibold">${{ number_format($method->min_amount, 2) }}</span>
                                </div>
                            </div>
                        @endif
                        @if($method->max_amount)
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Max Amount</small>
                                    <span class="fw-semibold">${{ number_format($method->max_amount, 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Panel - Transaction Form --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-paper-plane me-2 text-primary"></i>Submit Transaction Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.deposit.submit', $method->id ?? '')">
                    @csrf

                    {{-- Amount Display --}}
                    <div class="alert alert-primary border-0 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-dollar-sign me-2"></i>
                                Deposit Amount
                            </span>
                            <span class="fw-bold h4 mb-0">${{ number_format($amount ?? 0, 2) }}</span>
                        </div>
                    </div>

                    {{-- Transaction ID --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Transaction ID / UTR Number <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-hashtag text-muted"></i>
                            </span>
                            <input type="text"
                                   name="transaction_id"
                                   class="form-control border-start-0 @error('transaction_id') is-invalid @enderror"
                                   :class="{ 'is-invalid': errors.transaction_id }"
                                   x-model="transaction_id"
                                   @input="errors.transaction_id = ''"
                                   placeholder="Enter transaction ID / UTR number"
                                   required>
                            @error('transaction_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback" x-show="errors.transaction_id" x-text="errors.transaction_id"></div>
                        </div>
                        <small class="text-muted">Enter the unique transaction ID from your payment receipt</small>
                    </div>

                    {{-- Amount Paid --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Amount Paid (in your currency) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-dollar-sign text-muted"></i>
                            </span>
                            <input type="number"
                                   name="amount_paid"
                                   class="form-control border-start-0 @error('amount_paid') is-invalid @enderror"
                                   :class="{ 'is-invalid': errors.amount_paid }"
                                   x-model.number="amount_paid"
                                   @input="errors.amount_paid = ''"
                                   placeholder="Enter the amount you paid"
                                   step="0.01"
                                   min="1"
                                   required>
                            @error('amount_paid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback" x-show="errors.amount_paid" x-text="errors.amount_paid"></div>
                        </div>
                        <small class="text-muted">Enter the exact amount you transferred</small>
                    </div>

                    {{-- Payment Screenshot (Optional) --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Payment Screenshot <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="file"
                               name="screenshot"
                               class="form-control @error('screenshot') is-invalid @enderror"
                               accept="image/*">
                        @error('screenshot')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload a screenshot of your payment proof</small>
                    </div>

                    {{-- Additional Notes --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Additional Notes <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea name="notes"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Any additional information..."></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="btn btn-success btn-lg w-100"
                            :disabled="submitting">
                        <template x-if="submitting">
                            <span><i class="fas fa-spinner fa-spin me-2"></i>Submitting...</span>
                        </template>
                        <template x-if="!submitting">
                            <span><i class="fas fa-check-circle me-2"></i>I Have Paid - Submit</span>
                        </template>
                    </button>

                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your payment will be verified within 24 hours
                    </p>
                </form>
            </div>
        </div>

        {{-- Help Card --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="fas fa-question-circle me-2 text-primary"></i>Need Help?
                </h6>
                <p class="text-muted small mb-3">
                    If you face any issues with your payment, please create a support ticket with your transaction details.
                </p>
                <a href="{{ route('user.tickets.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-ticket-alt me-2"></i>Create Support Ticket
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function depositForm() {
        return {
            transaction_id: '',
            amount_paid: {{ $amount ?? 0 }},
            errors: {},
            submitting: false,

            copyUPI() {
                navigator.clipboard.writeText('{{ $method->upi_id ?? '' }}');
                toastr.success('UPI ID copied to clipboard!');
            },

            copyAccount() {
                navigator.clipboard.writeText('{{ $method->account_number ?? '' }}');
                toastr.success('Account number copied to clipboard!');
            }
        }
    }

    function copyUPI() {
        navigator.clipboard.writeText('{{ $method->upi_id ?? '' }}');
        toastr.success('UPI ID copied to clipboard!');
    }

    function copyAccount() {
        navigator.clipboard.writeText('{{ $method->account_number ?? '' }}');
        toastr.success('Account number copied to clipboard!');
    }
</script>
@endpush
