@extends('layouts.admin')
@section('title', 'Sync Services - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back to Providers
        </a>
        <h1 class="h3 mb-0">
            <i class="fa fa-sync me-2 text-primary"></i>
            Import Services from {{ $provider->name }}
        </h1>
    </div>
</div>

{{-- Provider Info --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fa fa-server fa-3x text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Provider</h6>
                        <h4 class="mb-0">{{ $provider->name }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fa fa-wallet fa-3x text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Current Balance</h6>
                        <h4 class="mb-0">${{ number_format($provider->balance, 4) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fa fa-box fa-3x text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Current Services</h6>
                        <h4 class="mb-0">{{ number_format($provider->services->count()) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sync Form --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Import Options</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.providers.sync', $provider->id) }}" id="syncForm">
            @csrf
            <div class="row g-4">
                <div class="col-md-4">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">-- Auto-create from provider --</option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @empty
                            <option value="">No categories available</option>
                        @endforelse
                    </select>
                    <div class="form-text">Leave empty to auto-create categories from provider</div>
                </div>
                <div class="col-md-4">
                    <label for="markup" class="form-label">Price Markup</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="markup" id="markup"
                               class="form-control" value="0" min="-50" max="500">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Add percentage to provider prices (0 = no change)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="update_existing"
                               id="update_existing" value="1" checked>
                        <label class="form-check-label" for="update_existing">
                            Update existing services
                        </label>
                    </div>
                    <div class="form-text">Match by Provider Service ID</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="fetchServiceCount()">
                        <i class="fa fa-search me-1"></i> Preview Services
                    </button>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" id="syncBtn">
                    <i class="fa fa-download me-2"></i> Import Services
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Preview Results --}}
<div class="card border-0 shadow-sm" id="previewCard" style="display: none;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-list me-2 text-primary"></i>Services Preview</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="previewTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Min - Max</th>
                        <th>Price / 1K</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody id="previewBody">
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted" id="previewSummary"></span>
                <span class="text-muted small">Showing first 50 services</span>
            </div>
        </div>
    </div>
</div>

{{-- Sync Progress Modal --}}
<div class="modal fade" id="syncModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-sync fa-spin me-2"></i>Importing Services</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span id="syncStatus">Connecting to provider...</span>
                        <span id="syncProgress">0%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="small text-muted" id="syncDetails"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let previewModal;

function fetchServiceCount() {
    const categoryId = document.getElementById('category_id').value;
    const markup = document.getElementById('markup').value;

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Fetching...';
    btn.disabled = true;

    fetch('{{ route('admin.providers.preview', $provider->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ category_id: categoryId, markup: markup })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPreview(data.services);
            toastr.success('Found ' + data.total + ' services');
        } else {
            toastr.error(data.message || 'Failed to fetch services');
        }
    })
    .catch(() => toastr.error('Failed to connect to provider'))
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function displayPreview(services) {
    const card = document.getElementById('previewCard');
    const tbody = document.getElementById('previewBody');
    const summary = document.getElementById('previewSummary');

    tbody.innerHTML = '';
    services.slice(0, 50).forEach(service => {
        tbody.innerHTML += `
            <tr>
                <td><code>${service.service}</code></td>
                <td>${service.name}</td>
                <td>${service.category || 'N/A'}</td>
                <td>${service.min} - ${service.max}</td>
                <td class="text-success">$${parseFloat(service.rate).toFixed(4)}</td>
                <td><span class="badge bg-light text-dark">${service.type || 'Default'}</span></td>
            </tr>
        `;
    });

    summary.textContent = `${services.length} services available for import`;
    card.style.display = '';
}

document.getElementById('syncForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const modal = new bootstrap.Modal(document.getElementById('syncModal'));
    modal.show();

    const form = this;
    const formData = new FormData(form);

    const progressBar = document.getElementById('progressBar');
    const status = document.getElementById('syncStatus');
    const details = document.getElementById('syncDetails');

    status.textContent = 'Starting import...';
    progressBar.style.width = '0%';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            progressBar.style.width = '100%';
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-success');
            status.textContent = 'Import completed!';
            details.innerHTML = `
                <div class="alert alert-success mb-0">
                    <strong>Success!</strong><br>
                    Created: ${data.created || 0} services<br>
                    Updated: ${data.updated || 0} services
                </div>
            `;
            setTimeout(() => {
                modal.hide();
                window.location.href = '{{ route('admin.providers.index') }}';
            }, 2000);
        } else {
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-danger');
            status.textContent = 'Import failed';
            details.innerHTML = `<div class="alert alert-danger mb-0">${data.message}</div>`;
        }
    })
    .catch(error => {
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-danger');
        status.textContent = 'Network error';
        details.innerHTML = '<div class="alert alert-danger mb-0">Failed to complete import</div>';
    });
});
</script>
@endpush
