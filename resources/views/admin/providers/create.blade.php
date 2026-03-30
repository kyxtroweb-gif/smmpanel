@extends('layouts.admin')
@section('title', 'Create Provider - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Add Service Provider</h1>
    </div>
</div>

<form method="POST" action="{{ route('admin.providers.store') }}" id="providerForm">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-server me-2 text-primary"></i>Provider Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g., SMMKing, Followiz"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description"
                                   class="form-control" value="{{ old('description') }}"
                                   placeholder="Optional description">
                        </div>
                    </div>
                </div>
            </div>

            {{-- API Configuration --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-key me-2 text-primary"></i>API Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="api_url" class="form-label">API URL <span class="text-danger">*</span></label>
                        <input type="url" name="api_url" id="api_url"
                               class="form-control @error('api_url') is-invalid @enderror"
                               value="{{ old('api_url', 'https://www.smmking.com/api/v2') }}"
                               placeholder="https://api.provider.com/api/v2" required>
                        @error('api_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Provider's API endpoint URL</div>
                    </div>
                    <div class="mb-3">
                        <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="api_key" id="api_key"
                                   class="form-control @error('api_key') is-invalid @enderror"
                                   value="{{ old('api_key') }}"
                                   placeholder="Enter API key"
                                   autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="toggleApiKey()">
                                <i class="fa fa-eye" id="apiKeyIcon"></i>
                            </button>
                        </div>
                        @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Your API key from the provider</div>
                    </div>

                    {{-- Test Connection --}}
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fa fa-plug me-1"></i> Test Connection</strong>
                                <p class="mb-0 small text-muted">Verify your API credentials before saving</p>
                            </div>
                            <button type="button" class="btn btn-outline-info" onclick="testConnection()">
                                <i class="fa fa-bolt me-1"></i> Test
                            </button>
                        </div>
                    </div>

                    {{-- Test Results --}}
                    <div id="testResults" class="d-none">
                        <div class="alert" id="testAlert">
                            <div class="d-flex align-items-center">
                                <span id="testIcon"></span>
                                <span id="testMessage" class="ms-2"></span>
                            </div>
                        </div>
                        <div id="testDetails" class="d-none">
                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="small text-muted">Provider Status</div>
                                        <div class="fw-semibold" id="providerStatus">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="small text-muted">Your Balance</div>
                                        <div class="fw-semibold text-success" id="providerBalance">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="small text-muted">Service Count</div>
                                        <div class="fw-semibold" id="serviceCount">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-cog me-2 text-primary"></i>Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active"
                               id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <strong>Active</strong>
                            <div class="text-muted small">Enable this provider for service imports</div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Submit Card --}}
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Create Provider</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fa fa-save me-1"></i> Create Provider
                        </button>
                        <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fa fa-info-circle me-1"></i>
                        After creating, you can import services from this provider.
                    </small>
                </div>
            </div>

            {{-- Common Providers Guide --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-book me-2"></i>Popular Providers</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item">
                            <strong>SMMKing</strong>
                            <div class="text-muted">smmking.com/api/v2</div>
                        </li>
                        <li class="list-group-item">
                            <strong>Followiz</strong>
                            <div class="text-muted">followiz.com/api/v2</div>
                        </li>
                        <li class="list-group-item">
                            <strong>SMMService</strong>
                            <div class="text-muted">smmteam.com/api/v2</div>
                        </li>
                        <li class="list-group-item">
                            <strong>Peakerr</strong>
                            <div class="text-muted">peakerr.com/api/v2</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleApiKey() {
    const input = document.getElementById('api_key');
    const icon = document.getElementById('apiKeyIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testConnection() {
    const apiUrl = document.getElementById('api_url').value;
    const apiKey = document.getElementById('api_key').value;

    if (!apiUrl || !apiKey) {
        toastr.error('Please enter both API URL and API Key');
        return;
    }

    const resultsDiv = document.getElementById('testResults');
    const alert = document.getElementById('testAlert');
    const icon = document.getElementById('testIcon');
    const message = document.getElementById('testMessage');
    const details = document.getElementById('testDetails');

    resultsDiv.classList.remove('d-none');
    alert.className = 'alert alert-info';
    icon.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';
    message.textContent = 'Testing connection...';
    details.classList.add('d-none');

    fetch('/admin/providers/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ api_url: apiUrl, api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert.className = 'alert alert-success';
            icon.innerHTML = '<i class="fa fa-check-circle text-success fa-lg"></i>';
            message.textContent = 'Connection successful!';

            document.getElementById('providerStatus').textContent =
                data.data.status || 'Active';
            document.getElementById('providerBalance').textContent =
                '$' + (data.data.balance || 0).toFixed(4);
            document.getElementById('serviceCount').textContent =
                (data.data.services_count || 0);
            details.classList.remove('d-none');
        } else {
            alert.className = 'alert alert-danger';
            icon.innerHTML = '<i class="fa fa-times-circle text-danger fa-lg"></i>';
            message.textContent = 'Connection failed: ' + (data.message || 'Unknown error');
        }
    })
    .catch(error => {
        alert.className = 'alert alert-danger';
        icon.innerHTML = '<i class="fa fa-times-circle text-danger fa-lg"></i>';
        message.textContent = 'Connection failed: Network error';
    });
}
</script>
@endpush
