@extends('user.layout')
@section('title', 'API Key - KYXTRO SMM')

@section('user_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-key me-1 text-primary"></i> API Access</h5>
    <form method="POST" action="{{ route('user.api-key.regenerate') }}" onsubmit="return confirm('Regenerating your API key will invalidate the old one. Are you sure?')">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="fa-solid fa-rotate me-1"></i> Regenerate Key
        </button>
    </form>
</div>

<div class="alert alert-info mb-4">
    <i class="fa-solid fa-circle-info me-1"></i>
    Use our API to programmatically place orders, check status, and manage your account. Integrate with your own tools or build a reseller panel.
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">Your API Key</h6>
    </div>
    <div class="card-body">
        @include('partials.flash')

        <div class="mb-3">
            <label class="form-label fw-semibold">API Key</label>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" id="api-key-display"
                       value="{{ auth()->user()->profile?->api_key ?? 'Not generated yet' }}" readonly style="font-size:0.85rem;">
                <button class="btn btn-outline-primary" type="button" onclick="copyApiKey()">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            <small class="text-muted">Keep this key secret. Do not share it publicly.</small>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Base URL</label>
                <input type="text" class="form-control font-monospace" value="{{ url('/api/v1') }}" readonly style="font-size:0.85rem;">
                <small class="text-muted">All API requests go to this base URL</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Authentication</label>
                <input type="text" class="form-control font-monospace" value="Authorization: Bearer {YOUR_API_KEY}" readonly style="font-size:0.85rem;">
                <small class="text-muted">Pass your API key as Bearer token</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">API Documentation</h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-semibold text-primary mb-2">Get Services</h6>
                <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-2">
                    GET /api/v1/services<br>
                    Authorization: Bearer {KEY}
                </div>
                <p class="small text-muted">Returns all active services with your custom rates applied.</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-semibold text-primary mb-2">Place Order</h6>
                <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-2">
                    POST /api/v1/order<br>
                    Authorization: Bearer {KEY}<br>
                    { "service_id": 1, "link": "...", "quantity": 100 }
                </div>
                <p class="small text-muted">Place a new order. Returns order ID and status.</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-semibold text-primary mb-2">Check Order</h6>
                <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-2">
                    GET /api/v1/order/{order_id}<br>
                    Authorization: Bearer {KEY}
                </div>
                <p class="small text-muted">Get current status of an order.</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-semibold text-primary mb-2">Get Balance</h6>
                <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-2">
                    GET /api/v1/balance<br>
                    Authorization: Bearer {KEY}
                </div>
                <p class="small text-muted">Get your current account balance.</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const input = document.getElementById('api-key-display');
    navigator.clipboard.writeText(input.value);
    alert('API key copied!');
}
</script>
@endsection
