@extends('layouts.admin')
@section('title', 'Edit Payment Method - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Edit Payment Method</h1>
    </div>
    <div class="badge bg-primary fs-6 py-2 px-3">
        {{ $method->name }}
    </div>
</div>

<form method="POST" action="{{ route('admin.payment-methods.update', $method->id) }}" enctype="multipart/form-data">
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
                        <div class="col-md-6">
                            <label for="name" class="form-label">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $method->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="type_manual"
                                       value="manual" {{ old('type', $method->type) === 'manual' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()" required>
                                <label class="btn btn-outline-secondary" for="type_manual">
                                    <i class="fa fa-hand-pointer me-1"></i> Manual
                                </label>
                                <input type="radio" class="btn-check" name="type" id="type_auto"
                                       value="auto" {{ old('type', $method->type) === 'auto' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()" required>
                                <label class="btn btn-outline-secondary" for="type_auto">
                                    <i class="fa fa-robot me-1"></i> Auto
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2"
                                      placeholder="Brief description of this payment method...">{{ old('description', $method->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branding --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-image me-2 text-primary"></i>Branding</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="logo" class="form-label">Logo</label>
                            @if($method->logo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $method->logo) }}"
                                         alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                    <div class="form-text">Currently uploaded</div>
                                </div>
                            @endif
                            <input type="file" name="logo" id="logo" class="form-control"
                                   accept="image/*" onchange="previewImage(this, 'logo_preview')">
                            <div class="form-text">Leave empty to keep current. Recommended: 200x60px</div>
                            <div class="mt-2">
                                <img id="logo_preview" src="#" alt="New Logo Preview"
                                     class="img-thumbnail d-none" style="max-height: 60px;">
                            </div>
                        </div>
                        <div class="col-md-6" id="qr_section">
                            <label for="qr_image" class="form-label">QR Code Image</label>
                            @if($method->qr_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $method->qr_image) }}"
                                         alt="Current QR" class="img-thumbnail" style="max-height: 100px;">
                                    <div class="form-text">Currently uploaded</div>
                                </div>
                            @endif
                            <input type="file" name="qr_image" id="qr_image" class="form-control"
                                   accept="image/*" onchange="previewImage(this, 'qr_preview')">
                            <div class="form-text">Leave empty to keep current</div>
                            <div class="mt-2">
                                <img id="qr_preview" src="#" alt="New QR Preview"
                                     class="img-thumbnail d-none" style="max-height: 100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Amount Limits --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-coins me-2 text-primary"></i>Amount Limits & Charges</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="min_amount" class="form-label">Minimum Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="min_amount" id="min_amount"
                                       class="form-control @error('min_amount') is-invalid @enderror"
                                       value="{{ old('min_amount', $method->min_amount) }}" required>
                            </div>
                            @error('min_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="max_amount" class="form-label">Maximum Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="max_amount" id="max_amount"
                                       class="form-control @error('max_amount') is-invalid @enderror"
                                       value="{{ old('max_amount', $method->max_amount) }}" required>
                            </div>
                            @error('max_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="fixed_charge" class="form-label">Fixed Charge</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="fixed_charge" id="fixed_charge"
                                       class="form-control @error('fixed_charge') is-invalid @enderror"
                                       value="{{ old('fixed_charge', $method->fixed_charge) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="percent_charge" class="form-label">Percent Charge</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="percent_charge" id="percent_charge"
                                       class="form-control @error('percent_charge') is-invalid @enderror"
                                       value="{{ old('percent_charge', $method->percent_charge) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bonus Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-gift me-2 text-primary"></i>Bonus Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="bonus_percentage" class="form-label">Bonus Percentage</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="bonus_percentage" id="bonus_percentage"
                                       class="form-control @error('bonus_percentage') is-invalid @enderror"
                                       value="{{ old('bonus_percentage', $method->bonus_percentage) }}" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="bonus_threshold" class="form-label">Bonus Threshold</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="bonus_threshold" id="bonus_threshold"
                                       class="form-control @error('bonus_threshold') is-invalid @enderror"
                                       value="{{ old('bonus_threshold', $method->bonus_threshold) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-list-alt me-2 text-primary"></i>Payment Instructions</h5>
                </div>
                <div class="card-body">
                    <label for="instructions" class="form-label">Instructions for Users</label>
                    <textarea name="instructions" id="instructions" class="form-control" rows="5"
                              placeholder="Step-by-step instructions for making payment...">{{ old('instructions', $method->instructions) }}</textarea>
                    <div class="form-text">HTML is supported for formatting</div>
                </div>
            </div>

            {{-- Dynamic Form Fields --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-wpforms me-2 text-primary"></i>User Input Fields</h5>
                </div>
                <div class="card-body">
                    <label for="fields_json" class="form-label">Fields JSON</label>
                    <textarea name="fields_json" id="fields_json" class="form-control font-monospace"
                              rows="8">{{ old('fields_json', $method->fields_json ?? '[{"name": "transaction_id", "label": "Transaction ID", "type": "text", "required": true}]') }}</textarea>
                    @error('fields_json')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- API Credentials --}}
            <div class="card border-0 shadow-sm mb-4" id="credentials_card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-key me-2 text-primary"></i>API Credentials</h5>
                </div>
                <div class="card-body">
                    <label for="credentials_json" class="form-label">Credentials JSON</label>
                    <textarea name="credentials_json" id="credentials_json" class="form-control font-monospace"
                              rows="6">{{ old('credentials_json', $method->credentials_json) }}</textarea>
                    <div class="form-text">
                        Stored encrypted. Enter new value to update, leave blank to keep current.
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-cog me-2 text-primary"></i>Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" {{ old('is_active', $method->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                    <div class="text-muted small">Show this method to users</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_approval"
                                       id="requires_approval" value="1" {{ old('requires_approval', $method->requires_approval) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_approval">
                                    <strong>Admin Approval Required</strong>
                                    <div class="text-muted small">Manually approve each deposit</div>
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
                        <span class="text-muted">Total Payments</span>
                        <span class="fw-semibold">{{ number_format($method->payments->count()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Approved</span>
                        <span class="fw-semibold text-success">{{ number_format($method->payments->where('status', 'approved')->count()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending</span>
                        <span class="fw-semibold text-warning">{{ number_format($method->payments->where('status', 'pending')->count()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Volume</span>
                        <span class="fw-semibold">${{ number_format($method->payments->where('status', 'approved')->sum('net_amount'), 2) }}</span>
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
                        <i class="fa fa-save me-1"></i> Update Method
                    </button>
                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary w-100">
                        Cancel
                    </a>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fa fa-clock me-1"></i>
                        Created: {{ $method->created_at->format('M d, Y H:i') }}
                    </small>
                </div>
            </div>

            {{-- Danger Zone --}}
            @can('admin.payment-methods.delete')
                <div class="card border-0 shadow-sm border-danger mt-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Deleting this payment method will remove it from the system. Existing payment records will be preserved.
                        </p>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="deleteMethod()">
                            <i class="fa fa-trash me-1"></i> Delete Payment Method
                        </button>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</form>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    Are you sure you want to delete <strong>{{ $method->name }}</strong>?
                </div>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.payment-methods.destroy', $method->id) }}">
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
function toggleTypeFields() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const qrSection = document.getElementById('qr_section');
    const credentialsCard = document.getElementById('credentials_card');

    if (type === 'manual') {
        qrSection.style.display = '';
        credentialsCard.style.display = 'none';
    } else {
        qrSection.style.display = 'none';
        credentialsCard.style.display = '';
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteMethod() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleTypeFields();
});
</script>
@endpush
