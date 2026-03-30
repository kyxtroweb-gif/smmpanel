@extends('layouts.admin')
@section('title', 'Edit User - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0">Edit User</h1>
    </div>
    <div class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} fs-6 py-2 px-3">
        {{ $user->name }}
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Basic Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-user me-2 text-primary"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Leave blank to keep current" autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control"
                               placeholder="Confirm new password"
                               autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Settings --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-cog me-2 text-primary"></i>Account Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select">
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                                User
                            </option>
                            <option value="reseller" {{ old('role', $user->role) === 'reseller' ? 'selected' : '' }}>
                                Reseller
                            </option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                Admin
                            </option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="balance" class="form-label">Balance <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.0001" name="balance" id="balance"
                                   class="form-control @error('balance') is-invalid @enderror"
                                   value="{{ old('balance', $user->balance) }}" required>
                        </div>
                        <div class="form-text">
                            <a href="#" onclick="showBalanceHistory(event)">View balance history</a>
                        </div>
                        @error('balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="balance_note" class="form-label">Balance Adjustment Note</label>
                        <input type="text" name="balance_note" id="balance_note"
                               class="form-control"
                               placeholder="Reason for balance change...">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active</strong>
                                <div class="text-muted small">Suspended users cannot log in</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Rates --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-percentage me-2 text-primary"></i>Custom Service Rates</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRateRow()">
                    <i class="fa fa-plus me-1"></i> Add Rate
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fa fa-info-circle me-1"></i>
                    Set custom prices for specific services. Leave price empty to remove custom rate.
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="ratesTable">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Default Price</th>
                                <th>Custom Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="ratesBody">
                            @forelse($customRates as $rate)
                                <tr>
                                    <td>
                                        <select name="rates[{{ $loop->index }}][service_id]"
                                                class="form-select form-select-sm service-select">
                                            <option value="{{ $rate->service_id }}" selected>
                                                {{ $rate->service->name ?? 'Deleted Service' }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="text-muted">${{ number_format($rate->service->price ?? 0, 4) }}</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.0001" name="rates[{{ $loop->index }}][price]"
                                                   class="form-control" value="{{ $rate->custom_price }}">
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeRateRow(this)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="noRatesRow">
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No custom rates set. Click "Add Rate" to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" id="mainForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
            <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
            <input type="hidden" name="password" value="">
            <input type="hidden" name="password_confirmation" value="">
            <input type="hidden" name="role" value="{{ old('role', $user->role) }}">
            <input type="hidden" name="balance" value="{{ old('balance', $user->balance) }}">
            <input type="hidden" name="balance_note" value="">
            <input type="hidden" name="is_active" value="{{ $user->is_active ? '1' : '0' }}">
        </form>
    </div>

    <div class="col-lg-4">
        {{-- User Stats --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-chart-bar me-2 text-primary"></i>User Statistics</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Orders</span>
                    <span class="fw-semibold">{{ number_format($user->orders->count()) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Spent</span>
                    <span class="fw-semibold text-danger">${{ number_format($user->orders->sum('charge'), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Deposits</span>
                    <span class="fw-semibold text-success">${{ number_format($user->deposits->where('status', 'approved')->sum('net_amount'), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Tickets</span>
                    <span class="fw-semibold">{{ number_format($user->tickets->count()) }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-bolt me-2 text-primary"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.orders.index', ['user_id' => $user->id]) }}"
                       class="btn btn-outline-primary">
                        <i class="fa fa-shopping-cart me-1"></i> View Orders
                    </a>
                    <a href="{{ route('admin.payments.index', ['user_id' => $user->id]) }}"
                       class="btn btn-outline-success">
                        <i class="fa fa-credit-card me-1"></i> View Payments
                    </a>
                    @if($user->id !== auth()->id())
                        <a href="{{ route('admin.users.login-as', $user->id) }}"
                           class="btn btn-outline-info">
                            <i class="fa fa-sign-in-alt me-1"></i> Login as User
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Submit Card --}}
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h5 class="mb-0">Save Changes</h5>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-primary w-100 mb-3" onclick="submitForm()">
                    <i class="fa fa-save me-1"></i> Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                    Cancel
                </a>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="fa fa-clock me-1"></i>
                    Registered: {{ $user->created_at->format('M d, Y H:i') }}<br>
                    Last login: {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                </small>
            </div>
        </div>

        {{-- Danger Zone --}}
        @if($user->id !== auth()->id())
            @can('admin.users.delete')
                <div class="card border-0 shadow-sm border-danger mt-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Deleting this user will permanently remove all their data.
                        </p>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="deleteUser()">
                            <i class="fa fa-trash me-1"></i> Delete User
                        </button>
                    </div>
                </div>
            @endcan
        @endif
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    Are you sure you want to delete <strong>{{ $user->name }}</strong>?
                </div>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
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

{{-- Balance History Modal --}}
<div class="modal fade" id="balanceHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Balance History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($balanceHistory as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('M d, Y H:i') }}</td>
                                    <td class="{{ $history->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $history->amount >= 0 ? '+' : '' }}${{ number_format($history->amount, 4) }}
                                    </td>
                                    <td>{{ ucfirst($history->type) }}</td>
                                    <td>{{ $history->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No history available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rateIndex = {{ $customRates->count() }};

function submitForm() {
    // Copy values from input fields to hidden form
    document.querySelector('input[name="name"]').value = document.getElementById('name').value;
    document.querySelector('input[name="email"]').value = document.getElementById('email').value;
    document.querySelector('input[name="password"]').value = document.getElementById('password').value;
    document.querySelector('input[name="password_confirmation"]').value = document.getElementById('password_confirmation').value;
    document.querySelector('select[name="role"]').value = document.getElementById('role').value;
    document.querySelector('input[name="balance"]').value = document.getElementById('balance').value;
    document.querySelector('input[name="balance_note"]').value = document.getElementById('balance_note').value;
    document.querySelector('input[name="is_active"]').value = document.getElementById('is_active').checked ? '1' : '0';

    document.getElementById('mainForm').submit();
}

function addRateRow() {
    const tbody = document.getElementById('ratesBody');
    const noRow = document.getElementById('noRatesRow');
    if (noRow) noRow.remove();

    const services = @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->price]));

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="rates[${rateIndex}][service_id]" class="form-select form-select-sm service-select" required>
                <option value="">Select Service</option>
                ${services.map(s => `<option value="${s.id}">${s.name} ($${s.price})</option>`).join('')}
            </select>
        </td>
        <td class="text-muted default-price">-</td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" step="0.0001" name="rates[${rateIndex}][price]"
                       class="form-control" placeholder="Custom price">
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRateRow(this)">
                <i class="fa fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    rateIndex++;
}

function removeRateRow(btn) {
    btn.closest('tr').remove();
    const tbody = document.getElementById('ratesBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = '<tr id="noRatesRow"><td colspan="4" class="text-center text-muted py-3">No custom rates set. Click "Add Rate" to create one.</td></tr>';
    }
}

function showBalanceHistory(e) {
    e.preventDefault();
    new bootstrap.Modal(document.getElementById('balanceHistoryModal')).show();
}

function deleteUser() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Update default price when service is selected
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('service-select')) {
        const services = @json($services->keyBy('id'));
        const serviceId = e.target.value;
        const row = e.target.closest('tr');
        const priceCell = row.querySelector('.default-price');

        if (services[serviceId]) {
            priceCell.textContent = '$' + parseFloat(services[serviceId].price).toFixed(4);
        } else {
            priceCell.textContent = '-';
        }
    }
});
</script>
@endpush
