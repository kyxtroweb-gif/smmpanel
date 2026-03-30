@extends('layouts.admin')
@section('title', 'Edit Service - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Edit Service</h1>
    </div>
    <div class="badge bg-primary fs-6 py-2 px-3">
        {{ $service->name }}
    </div>
</div>

<form method="POST" action="{{ route('admin.services.update', $service->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-info-circle me-2 text-primary"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $service->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Provider Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-server me-2 text-primary"></i>Provider Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="provider_id" class="form-label">Provider</label>
                            <select name="provider_id" id="provider_id" class="form-select">
                                <option value="">No Provider (Manual)</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old('provider_id', $service->provider_id) == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="provider_service_id" class="form-label">Provider Service ID</label>
                            <input type="text" name="provider_service_id" id="provider_service_id"
                                   class="form-control" value="{{ old('provider_service_id', $service->provider_service_id) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-dollar-sign me-2 text-primary"></i>Pricing</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price per 1,000 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.0001" name="price" id="price"
                                       class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price', $service->price) }}" min="0" required>
                                <span class="input-group-text">/ 1K</span>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="cost" class="form-label">Cost per 1,000 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.0001" name="cost" id="cost"
                                       class="form-control @error('cost') is-invalid @enderror"
                                       value="{{ old('cost', $service->cost) }}" min="0" required>
                                <span class="input-group-text">/ 1K</span>
                            </div>
                            <div class="form-text">
                                Profit: $<span id="profit_display">{{ number_format($service->price - $service->cost, 4) }}</span> / 1K
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Limits --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-sliders-h me-2 text-primary"></i>Order Limits</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="min_quantity" class="form-label">Minimum Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="min_quantity" id="min_quantity"
                                   class="form-control @error('min_quantity') is-invalid @enderror"
                                   value="{{ old('min_quantity', $service->min_quantity) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="max_quantity" class="form-label">Maximum Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="max_quantity" id="max_quantity"
                                   class="form-control @error('max_quantity') is-invalid @enderror"
                                   value="{{ old('max_quantity', $service->max_quantity) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="average_time" class="form-label">Average Time</label>
                            <input type="text" name="average_time" id="average_time"
                                   class="form-control" value="{{ old('average_time', $service->average_time) }}"
                                   placeholder="e.g., 24 hours">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-star me-2 text-primary"></i>Features</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured"
                                       id="is_featured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    <strong>Featured</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="dripfeed"
                                       id="dripfeed" value="1" {{ old('dripfeed', $service->dripfeed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dripfeed">
                                    <strong>Dripfeed</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="refill"
                                       id="refill" value="1" {{ old('refill', $service->refill) ? 'checked' : '' }}>
                                <label class="form-check-label" for="refill">
                                    <strong>Refill</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cancel"
                                       id="cancel" value="1" {{ old('cancel', $service->cancel) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cancel">
                                    <strong>Cancellation</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Stats Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-chart-bar me-2 text-primary"></i>Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Orders</span>
                        <span class="fw-semibold">{{ number_format($service->orders->count()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Revenue</span>
                        <span class="fw-semibold text-success">${{ number_format($service->orders->sum('charge'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Cost</span>
                        <span class="fw-semibold text-danger">${{ number_format($service->orders->sum('cost'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Profit</span>
                        <span class="fw-semibold">${{ number_format($service->orders->sum('profit'), 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Submit Card --}}
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Save Changes</h5>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fa fa-save me-1"></i> Update Service
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary w-100">
                        Cancel
                    </a>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fa fa-clock me-1"></i>
                        Created: {{ $service->created_at->format('M d, Y') }}
                    </small>
                </div>
            </div>

            {{-- Danger Zone --}}
            @can('admin.services.delete')
                <div class="card border-0 shadow-sm border-danger mt-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Deleting this service will not affect existing orders.
                        </p>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="deleteService()">
                            <i class="fa fa-trash me-1"></i> Delete Service
                        </button>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</form>

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
                    Are you sure you want to delete <strong>{{ $service->name }}</strong>?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.services.destroy', $service->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('price').addEventListener('input', updateProfit);
document.getElementById('cost').addEventListener('input', updateProfit);

function updateProfit() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const cost = parseFloat(document.getElementById('cost').value) || 0;
    const profit = price - cost;
    const display = document.getElementById('profit_display');
    display.textContent = profit.toFixed(4);
    display.parentElement.parentElement.className = profit >= 0 ? 'form-text text-success' : 'form-text text-danger';
}

function deleteService() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
