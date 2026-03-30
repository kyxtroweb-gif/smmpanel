@extends('layouts.admin')
@section('title', 'Create Service - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Add New Service</h1>
    </div>
</div>

<form method="POST" action="{{ route('admin.services.store') }}">
    @csrf

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
                                   value="{{ old('name') }}" placeholder="e.g., Instagram Followers"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                      placeholder="Service description...">{{ old('description') }}</textarea>
                            <div class="form-text">HTML is supported</div>
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
                            <label for="provider_id" class="form-label">Provider <span class="text-danger">*</span></label>
                            <select name="provider_id" id="provider_id" class="form-select">
                                <option value="">Select Provider</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="provider_service_id" class="form-label">Provider Service ID</label>
                            <input type="text" name="provider_service_id" id="provider_service_id"
                                   class="form-control" value="{{ old('provider_service_id') }}"
                                   placeholder="Service ID from provider">
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
                                       value="{{ old('price', 0) }}" min="0" required>
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
                                       value="{{ old('cost', 0) }}" min="0" required>
                                <span class="input-group-text">/ 1K</span>
                            </div>
                            <div class="form-text">
                                Profit: $<span id="profit_display">0.00</span> / 1K
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
                                   value="{{ old('min_quantity', 100) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="max_quantity" class="form-label">Maximum Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="max_quantity" id="max_quantity"
                                   class="form-control @error('max_quantity') is-invalid @enderror"
                                   value="{{ old('max_quantity', 10000) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="average_time" class="form-label">Average Time</label>
                            <input type="text" name="average_time" id="average_time"
                                   class="form-control" value="{{ old('average_time') }}"
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
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured"
                                       id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    <strong>Featured</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="dripfeed"
                                       id="dripfeed" value="1" {{ old('dripfeed') ? 'checked' : '' }}>
                                <label class="form-check-label" for="dripfeed">
                                    <strong>Dripfeed</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="refill"
                                       id="refill" value="1" {{ old('refill') ? 'checked' : '' }}>
                                <label class="form-check-label" for="refill">
                                    <strong>Refill</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cancel"
                                       id="cancel" value="1" {{ old('cancel') ? 'checked' : '' }}>
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
            {{-- Submit Card --}}
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Create Service</h5>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fa fa-save me-1"></i> Create Service
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary w-100">
                        Cancel
                    </a>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fa fa-info-circle me-1"></i>
                        Services can be imported from providers in bulk.
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>
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
</script>
@endpush
