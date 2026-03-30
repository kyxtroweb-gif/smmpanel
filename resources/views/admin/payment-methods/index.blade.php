@extends('layouts.admin')
@section('title', 'Payment Methods - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payment Methods</h1>
    @can('admin.payment-methods.create')
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Add New Method
        </a>
    @endcan
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Methods</div>
                        <div class="fs-5 fw-bold">{{ $methods->count() }}</div>
                    </div>
                    <i class="fa fa-credit-card fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Auto Methods</div>
                        <div class="fs-5 fw-bold">{{ $methods->where('type', 'auto')->count() }}</div>
                    </div>
                    <i class="fa fa-robot fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Manual Methods</div>
                        <div class="fs-5 fw-bold">{{ $methods->where('type', 'manual')->count() }}</div>
                    </div>
                    <i class="fa fa-hand-pointer fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Active Methods</div>
                        <div class="fs-5 fw-bold">{{ $methods->where('is_active', true)->count() }}</div>
                    </div>
                    <i class="fa fa-toggle-on fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Payment Methods</h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary active" onclick="filterMethods('all')">
                    All ({{ $methods->count() }})
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="filterMethods('auto')">
                    Auto ({{ $methods->where('type', 'auto')->count() }})
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="filterMethods('manual')">
                    Manual ({{ $methods->where('type', 'manual')->count() }})
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Min / Max</th>
                        <th>Charges</th>
                        <th>Bonus %</th>
                        <th>Status</th>
                        <th>Approvals</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($methods as $method)
                    <tr data-type="{{ $method->type }}">
                        <td>
                            <div class="d-flex align-items-center">
                                @if($method->logo)
                                    <img src="{{ asset('storage/' . $method->logo) }}"
                                         alt="{{ $method->name }}" class="rounded me-2" style="height: 30px; width: 30px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center me-2"
                                         style="width: 30px; height: 30px;">
                                        <i class="fa fa-credit-card text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-semibold">{{ $method->name }}</span>
                                    @if($method->description)
                                        <div class="small text-muted">{{ Str::limit($method->description, 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $method->type === 'auto' ? 'info' : 'secondary' }}">
                                <i class="fa fa-{{ $method->type === 'auto' ? 'robot' : 'hand-pointer' }} me-1"></i>
                                {{ ucfirst($method->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-muted">Min:</span> ${{ number_format($method->min_amount, 2) }}<br>
                                <span class="text-muted">Max:</span> ${{ number_format($method->max_amount, 2) }}
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                @if($method->fixed_charge > 0)
                                    Fixed: {{ $method->fixed_charge }}%<br>
                                @endif
                                @if($method->percent_charge > 0)
                                    %: {{ $method->percent_charge }}%
                                @endif
                                @if($method->fixed_charge == 0 && $method->percent_charge == 0)
                                    <span class="text-muted">No charges</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($method->bonus_percentage > 0)
                                <span class="badge bg-success">
                                    <i class="fa fa-gift me-1"></i> {{ $method->bonus_percentage }}%
                                </span>
                            @else
                                <span class="text-muted small">No bonus</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="toggle_{{ $method->id }}"
                                       {{ $method->is_active ? 'checked' : '' }}
                                       onchange="toggleMethod({{ $method->id }})"
                                       {{ !auth()->user()->can('admin.payment-methods.edit') ? 'disabled' : '' }}>
                                <label class="form-check-label" for="toggle_{{ $method->id }}">
                                    <span class="badge bg-{{ $method->is_active ? 'success' : 'secondary' }}">
                                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </div>
                        </td>
                        <td>
                            @if($method->requires_approval)
                                <span class="badge bg-warning text-dark">
                                    <i class="fa fa-user-check me-1"></i> Required
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">
                                    <i class="fa fa-bolt me-1"></i> Instant
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @can('admin.payment-methods.view')
                                    <a href="{{ route('admin.payment-methods.view', $method->id) }}"
                                       class="btn btn-outline-secondary" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endcan
                                @can('admin.payment-methods.edit')
                                    <a href="{{ route('admin.payment-methods.edit', $method->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('admin.payment-methods.delete')
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteMethod({{ $method->id }}, '{{ $method->name }}')"
                                            title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fa fa-credit-card fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No payment methods configured yet.</p>
                            @can('admin.payment-methods.create')
                                <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="fa fa-plus me-1"></i> Add First Method
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

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    Are you sure you want to delete <strong id="deleteMethodName"></strong>?
                </div>
                <p class="text-muted mb-0">This action cannot be undone. All associated payment records will be preserved but will show "Unknown" as the method.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash me-1"></i> Delete Method
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterMethods(type) {
    const rows = document.querySelectorAll('table tbody tr[data-type]');
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));

    if (type === 'all') {
        rows.forEach(row => row.style.display = '');
        event.target.classList.add('active');
    } else {
        rows.forEach(row => {
            row.style.display = row.dataset.type === type ? '' : 'none';
        });
        event.target.classList.add('active');
    }
}

function toggleMethod(id) {
    fetch('/admin/payment-methods/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Payment method status updated');
        } else {
            toastr.error('Failed to update status');
            document.getElementById('toggle_' + id).checked = !document.getElementById('toggle_' + id).checked;
        }
    })
    .catch(error => {
        toastr.error('An error occurred');
        document.getElementById('toggle_' + id).checked = !document.getElementById('toggle_' + id).checked;
    });
}

function deleteMethod(id, name) {
    document.getElementById('deleteMethodName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/payment-methods/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
