@extends('user.layout')
@section('title', 'Deposit - ' . $method->name)
@section('user_content')

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('user.deposit') }}" class="btn btn-sm btn-outline-secondary me-3">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-wallet me-1 text-primary"></i> Deposit via {{ $method->name }}</h5>
</div>

@if($pendingPayment)
    <div class="alert alert-warning">
        <i class="fa-solid fa-clock me-1"></i>
        You have a pending deposit. TXN ID: <code>{{ $pendingPayment->transaction_id }}</code>
        <a href="{{ route('user.deposit.submit-txn', $pendingPayment->transaction_id) }}" class="alert-link">Continue here →</a>
    </div>
@endif

<div class="row g-4">
    <!-- Payment Info -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-3 mb-3" style="width:60px;height:60px;">
                    @if($method->logo)
                        <img src="{{ asset('storage/' . $method->logo) }}" style="height:40px;">
                    @else
                        <i class="fa-solid fa-credit-card fa-2x text-primary"></i>
                    @endif
                </div>
                <h5 class="fw-bold mb-1">{{ $method->name }}</h5>
                @if($method->description)
                    <p class="text-muted small mb-0">{{ $method->description }}</p>
                @endif
            </div>
        </div>

        @if($method->qr_image)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center">
                    <img src="{{ asset('storage/' . $method->qr_image) }}" alt="QR Code" class="img-fluid rounded" style="max-height:280px;">
                    <p class="small text-muted mt-2 mb-0">Scan with any UPI app</p>
                </div>
            </div>
        @endif

        @if($method->instructions)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2"><i class="fa-solid fa-list-check me-1 text-primary"></i> How to Pay</h6>
                    <div class="small text-muted" style="white-space: pre-line;">{{ $method->instructions }}</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Amount Selection -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-indian-rupee-sign me-1 text-primary"></i> Select Amount</h6>
            </div>
            <div class="card-body">
                @include('partials.flash')

                <form method="POST" action="{{ route('user.deposit.select') }}">
                    @csrf
                    <input type="hidden" name="method_id" value="{{ $method->id }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (USD)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" required min="{{ $method->min_amount }}"
                                   max="{{ $method->max_amount ?: 100000 }}" step="0.01" placeholder="Enter amount" id="amount-input">
                        </div>
                        @if($method->min_amount || $method->max_amount)
                            <small class="text-muted">
                                Min: ${{ number_format($method->min_amount, 2) }}
                                @if($method->max_amount) | Max: ${{ number_format($method->max_amount, 2) }} @endif
                            </small>
                        @endif
                    </div>

                    <!-- Bonus calculation -->
                    @if($method->bonus_percent > 0)
                        <div class="alert alert-success mb-3" id="bonus-display" style="display:none;">
                            <i class="fa-solid fa-gift me-1"></i>
                            You'll receive a <strong>{{ $method->bonus_percent }}% bonus</strong>!
                            Total credit: <strong id="bonus-total">$0.00</strong>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Suggested Amounts</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach([10, 25, 50, 100, 200, 500] as $suggested)
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="document.getElementById('amount-input').value='{{ $suggested }}'; calcBonus();">
                                    ${{ $suggested }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-arrow-right me-1"></i>
                            Continue to Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calcBonus() {
    const amt = parseFloat(document.getElementById('amount-input').value) || 0;
    const bonusPct = {{ $method->bonus_percent ?? 0 }};
    const bonusThreshold = {{ $method->bonus_threshold ?? 0 }};
    if (bonusPct > 0 && amt >= bonusThreshold) {
        const bonus = amt * bonusPct / 100;
        document.getElementById('bonus-total').textContent = '$' + (amt + bonus).toFixed(2);
        document.getElementById('bonus-display').style.display = '';
    } else {
        document.getElementById('bonus-display').style.display = 'none';
    }
}
document.getElementById('amount-input')?.addEventListener('input', calcBonus);
</script>
@endsection
