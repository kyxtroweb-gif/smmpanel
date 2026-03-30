@extends('layouts.admin')
@section('title', 'Providers - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Service Providers</h1>
    @can('admin.providers.create')
        <a href="{{ route('admin.providers.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Add Provider
        </a>
    @endcan
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Providers</div>
                        <div class="fs-5 fw-bold">{{ $providers->count() }}</div>
                    </div>
                    <i class="fa fa-server fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Active Providers</div>
                        <div class="fs-5 fw-bold">{{ $providers->where('is_active', true)->count() }}</div>
                    </div>
                    <i class="fa fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Services</div>
                        <div class="fs-5 fw-bold">{{ number_format($totalServices) }}</div>
                    </div>
                    <i class="fa fa-box fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Balance</div>
                        <div class="fs-5 fw-bold">${{ number_format($totalBalance, 2) }}</div>
                    </div>
                    <i class="fa fa-wallet fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sync Panel --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-sync me-2 text-primary"></i>Import Services</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.providers.sync') }}" class="row g-3">
            <div class="col-md-3">
                <label for="sync_provider" class="form-label">Provider</label>
                <select name="provider_id" id="sync_provider" class="form-select">
                    <option value="">Select Provider</option>
                    @foreach($providers->where('is_active', true) as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="sync_category" class="form-label">Category</label>
                <select name="category_id" id="sync_category" class="form-select">
                    <option value="">Auto-create</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="sync_markup" class="form-label">Markup %</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="markup" id="sync_markup"
                           class="form-control" value="0" min="-50" max="500">
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="update_existing"
                           id="sync_update" value="1" checked>
                    <label class="form-check-label" for="sync_update">
                        Update existing services
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-download me-1"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Providers Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Providers</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>API URL</th>
                        <th>Balance</th>
                        <th>Services</th>
                        <th>Last Sync</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($providers as $provider)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $provider->name }}</div>
                            @if($provider->description)
                                <div class="small text-muted">{{ Str::limit($provider->description, 40) }}</div>
                            @endif
                        </td>
                        <td>
                            <code class="small">{{ Str::limit($provider->api_url, 40) }}</code>
                        </td>
                        <td>
                            <span class="fw-semibold">${{ number_format($provider->balance, 4) }}</span>
                            @if($provider->balance < 10)
                                <span class="badge bg-warning text-dark ms-1">Low</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ number_format($provider->services->count()) }}
                            </span>
                        </td>
                        <td>
                            @if($provider->last_sync_at)
                                <span class="small text-muted">
                                    {{ $provider->last_sync_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted small">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block me-2">
                                <input class="form-check-input" type="checkbox"
                                       {{ $provider->is_active ? 'checked' : '' }}
                                       onchange="toggleProvider({{ $provider->id }})"
                                       {{ !auth()->user()->can('admin.providers.edit') ? 'disabled' : '' }}>
                            </div>
                            <span class="badge bg-{{ $provider->is_active ? 'success' : 'secondary' }}">
                                {{ $provider->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="checkBalance({{ $provider->id }})"
                                        title="Check Balance" data-bs-toggle="tooltip">
                                    <i class="fa fa-wallet"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info"
                                        onclick="syncServices({{ $provider->id }})"
                                        title="Sync Services" data-bs-toggle="tooltip">
                                    <i class="fa fa-sync"></i>
                                </button>
                                @can('admin.providers.edit')
                                    <a href="{{ route('admin.providers.edit', $provider->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('admin.providers.delete')
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteProvider({{ $provider->id }}, '{{ $provider->name }}')"
                                            title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa fa-server fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No providers configured yet.</p>
                                @can('admin.providers.create')
                                    <a href="{{ route('admin.providers.create') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="fa fa-plus me-1"></i> Add First Provider
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Balance Modal --}}
<div class="modal fade" id="balanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Provider Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Checking balance...</p>
                </div>
                <div id="balanceResult" style="display: none;">
                    <h4 class="text-center mb-3">Balance: <span id="balanceValue" class="text-success"></span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Provider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    Are you sure you want to delete <strong id="deleteProviderName"></strong>?
                </div>
                <p class="text-muted mb-0">This will also delete all associated services. This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash me-1"></i> Delete Provider
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleProvider(id) {
    fetch('/admin/providers/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        toastr.success(data.success ? 'Provider status updated' : 'Failed to update status');
    })
    .catch(() => toastr.error('An error occurred'));
}

function checkBalance(id) {
    const modal = new bootstrap.Modal(document.getElementById('balanceModal'));
    document.getElementById('balanceResult').style.display = 'none';
    modal.show();

    fetch('/admin/providers/' + id + '/balance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('balanceValue').textContent = '$' + data.balance.toFixed(4);
        document.getElementById('balanceResult').style.display = 'block';
    })
    .catch(error => {
        document.querySelector('#balanceModal .modal-body').innerHTML =
            '<div class="alert alert-danger">Failed to check balance. Please check the API URL and key.</div>';
    });
}

function syncServices(id) {
    window.location.href = '/admin/providers/' + id + '/sync';
}

function deleteProvider(id, name) {
    document.getElementById('deleteProviderName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/providers/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
