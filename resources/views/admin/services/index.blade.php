@extends('layouts.admin')
@section('title', 'Services - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Services Management</h1>
    <div class="btn-group">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Add Service
        </a>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('admin.services.import') }}">
                    <i class="fa fa-upload me-2"></i> Import from CSV
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('admin.services.export') }}">
                    <i class="fa fa-download me-2"></i> Export to CSV
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Services</div>
                        <div class="fs-5 fw-bold">{{ number_format($stats['total']) }}</div>
                    </div>
                    <i class="fa fa-box fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Active Services</div>
                        <div class="fs-5 fw-bold text-success">{{ number_format($stats['active']) }}</div>
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
                        <div class="small text-muted">Categories</div>
                        <div class="fs-5 fw-bold">{{ number_format($stats['categories']) }}</div>
                    </div>
                    <i class="fa fa-folder fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Providers</div>
                        <div class="fs-5 fw-bold">{{ number_format($stats['providers']) }}</div>
                    </div>
                    <i class="fa fa-server fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.services.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Name, ID..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label small text-muted">Category</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }} ({{ $category->services->count() }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="provider_id" class="form-label small text-muted">Provider</label>
                <select name="provider_id" id="provider_id" class="form-select">
                    <option value="">All Providers</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" {{ request('provider_id') == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Actions --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">{{ $services->total() }} services found</div>
    <div class="btn-group">
        <select class="form-select form-select-sm" id="bulkActionSelect"
                style="width: auto;">
            <option value="">Bulk Actions</option>
            <option value="activate">Activate Selected</option>
            <option value="deactivate">Deactivate Selected</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="applyBulkAction()">
            Apply
        </button>
    </div>
</div>

{{-- Services Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input class="form-check-input" type="checkbox" id="selectAll"
                                   onchange="toggleSelectAll()">
                        </th>
                        <th>
                            <a href="{{ route('admin.services.index', array_merge(request()->except('page'), ['sort' => 'id', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}">
                                ID
                            </a>
                        </th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Provider</th>
                        <th>Price</th>
                        <th>Min / Max</th>
                        <th>Avg Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($services as $service)
                    <tr data-id="{{ $service->id }}">
                        <td>
                            <input class="form-check-input service-checkbox" type="checkbox"
                                   value="{{ $service->id }}">
                        </td>
                        <td><code>{{ $service->id }}</code></td>
                        <td>
                            <div class="fw-semibold">{{ $service->name }}</div>
                            @if($service->description)
                                <div class="small text-muted text-truncate" style="max-width: 200px;">
                                    {{ Str::limit(strip_tags($service->description), 50) }}
                                </div>
                            @endif
                            @if($service->is_featured)
                                <span class="badge bg-warning text-dark small">Featured</span>
                            @endif
                        </td>
                        <td>
                            @if($service->category)
                                <span class="badge bg-light text-dark border">
                                    {{ $service->category->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($service->provider)
                                <span class="small">{{ $service->provider->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-success fw-semibold">${{ number_format($service->price, 4) }}</div>
                            <div class="text-muted small">${{ number_format($service->cost, 4) }} cost</div>
                        </td>
                        <td>
                            <div class="small">
                                {{ number_format($service->min_quantity) }} - {{ number_format($service->max_quantity) }}
                            </div>
                        </td>
                        <td>
                            <span class="small text-muted">{{ $service->average_time ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block me-1">
                                <input class="form-check-input" type="checkbox"
                                       {{ $service->is_active ? 'checked' : '' }}
                                       onchange="toggleService({{ $service->id }})"
                                       {{ !auth()->user()->can('admin.services.edit') ? 'disabled' : '' }}>
                            </div>
                            <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @can('admin.services.edit')
                                    <a href="{{ route('admin.services.edit', $service->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('admin.services.delete')
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteService({{ $service->id }}, '{{ addslashes($service->name) }}')"
                                            title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fa fa-box-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No services found.</p>
                            @can('admin.services.create')
                                <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="fa fa-plus me-1"></i> Add First Service
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($services->hasPages())
        <div class="card-footer bg-white">
            {{ $services->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    Are you sure you want to delete <strong id="deleteServiceName"></strong>?
                </div>
                <p class="text-muted mb-0">Orders using this service will show "Service Deleted" but remain in history.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash me-1"></i> Delete Service
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = checked);
}

function toggleService(id) {
    fetch('/admin/services/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        toastr.success(data.success ? 'Service status updated' : 'Failed to update');
    })
    .catch(() => toastr.error('An error occurred'));
}

function applyBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        toastr.warning('Please select an action');
        return;
    }

    const selected = Array.from(document.querySelectorAll('.service-checkbox:checked'))
        .map(cb => cb.value);

    if (selected.length === 0) {
        toastr.warning('Please select at least one service');
        return;
    }

    const messages = {
        activate: 'activate',
        deactivate: 'deactivate',
        delete: 'delete'
    };

    if (!confirm(`Are you sure you want to ${messages[action]} ${selected.length} service(s)?`)) {
        return;
    }

    fetch('/admin/services/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action, ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message || 'Bulk action completed');
            location.reload();
        } else {
            toastr.error(data.message || 'Action failed');
        }
    })
    .catch(() => toastr.error('An error occurred'));
}

function deleteService(id, name) {
    document.getElementById('deleteServiceName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/services/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
