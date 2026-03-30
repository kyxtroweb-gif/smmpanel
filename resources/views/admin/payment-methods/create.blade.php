@extends('layouts.admin')
@section('title', 'Create Payment Method - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Create Payment Method</h1>
    </div>
</div>

<form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
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
                        <div class="col-md-6">
                            <label for="name" class="form-label">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g., PayPal, Bank Transfer"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="type_manual"
                                       value="manual" {{ old('type', 'manual') === 'manual' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()" required>
                                <label class="btn btn-outline-secondary" for="type_manual">
                                    <i class="fa fa-hand-pointer me-1"></i> Manual
                                </label>
                                <input type="radio" class="btn-check" name="type" id="type_auto"
                                       value="auto" {{ old('type') === 'auto' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()" required>
                                <label class="btn btn-outline-secondary" for="type_auto">
                                    <i class="fa fa-robot me-1"></i> Auto
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2"
                                      placeholder="Brief description of this payment method...">{{ old('description') }}</textarea>
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
                            <input type="file" name="logo" id="logo" class="form-control"
                                   accept="image/*" onchange="previewImage(this, 'logo_preview')">
                            <div class="form-text">Recommended: 200x60px, PNG or JPG</div>
                            <div class="mt-2">
                                <img id="logo_preview" src="#" alt="Logo Preview"
                                     class="img-thumbnail d-none" style="max-height: 60px;">
                            </div>
                        </div>
                        <div class="col-md-6" id="qr_section">
                            <label for="qr_image" class="form-label">QR Code Image</label>
                            <input type="file" name="qr_image" id="qr_image" class="form-control"
                                   accept="image/*" onchange="previewImage(this, 'qr_preview')">
                            <div class="form-text">For manual payment methods (optional)</div>
                            <div class="mt-2">
                                <img id="qr_preview" src="#" alt="QR Preview"
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
                                       value="{{ old('min_amount', 1.00) }}" required>
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
                                       value="{{ old('max_amount', 1000.00) }}" required>
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
                                       value="{{ old('fixed_charge', 0) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Per transaction fee</div>
                        </div>
                        <div class="col-md-3">
                            <label for="percent_charge" class="form-label">Percent Charge</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="percent_charge" id="percent_charge"
                                       class="form-control @error('percent_charge') is-invalid @enderror"
                                       value="{{ old('percent_charge', 0) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Percentage of amount</div>
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
                                       value="{{ old('bonus_percentage', 0) }}" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Extra balance on deposit</div>
                        </div>
                        <div class="col-md-4">
                            <label for="bonus_threshold" class="form-label">Bonus Threshold</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="bonus_threshold" id="bonus_threshold"
                                       class="form-control @error('bonus_threshold') is-invalid @enderror"
                                       value="{{ old('bonus_threshold', 0) }}">
                            </div>
                            <div class="form-text">Min amount for bonus</div>
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
                              placeholder="Step-by-step instructions for making payment...">{{ old('instructions') }}</textarea>
                    <div class="form-text">HTML is supported for formatting</div>
                </div>
            </div>

            {{-- Dynamic Form Fields --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-wpforms me-2 text-primary"></i>User Input Fields</h5>
                    <span class="text-muted small">Define fields users must fill when submitting payment</span>
                </div>
                <div class="card-body">
                    <label for="fields_json" class="form-label">Fields JSON</label>
                    <textarea name="fields_json" id="fields_json" class="form-control font-monospace"
                              rows="8" placeholder='[{"name": "transaction_id", "label": "Transaction ID", "type": "text", "required": true}]'>{{ old('fields_json', '[{"name": "transaction_id", "label": "Transaction ID", "type": "text", "required": true}]') }}</textarea>
                    <div class="form-text">
                        JSON array of field objects. Supported types: text, number, file, textarea
                    </div>
                    @error('fields_json')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- API Credentials (for auto methods) --}}
            <div class="card border-0 shadow-sm mb-4" id="credentials_card" style="display: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-key me-2 text-primary"></i>API Credentials</h5>
                    <span class="text-muted small">Required for automatic payment processing</span>
                </div>
                <div class="card-body">
                    <label for="credentials_json" class="form-label">Credentials JSON</label>
                    <textarea name="credentials_json" id="credentials_json" class="form-control font-monospace"
                              rows="6" placeholder='{"api_key": "xxx", "environment": "live"}'>{{ old('credentials_json') }}</textarea>
                    <div class="form-text">Stored encrypted. Format depends on the payment provider.</div>
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
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                    <div class="text-muted small">Show this method to users</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_approval"
                                       id="requires_approval" value="1" {{ old('requires_approval') ? 'checked' : '' }}>
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
            {{-- Submit Card --}}
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Create Payment Method</h5>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fa fa-save me-1"></i> Create Method
                    </button>
                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary w-100">
                        Cancel
                    </a>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fa fa-info-circle me-1"></i>
                        After creating, you can configure additional settings.
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeFields();
});
</script>
@endpush
