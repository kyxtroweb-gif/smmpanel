@extends('layouts.admin')
@section('title', 'Users - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">User Management</h1>
    <div class="btn-group">
        <a href="{{ route('admin.users.export') }}?{{ http_build_query(request()->except('page')) }}"
           class="btn btn-success btn-sm">
            <i class="fa fa-download me-1"></i> Export CSV
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Users</div>
                        <div class="fs-5 fw-bold">{{ number_format($stats['total']) }}</div>
                    </div>
                    <i class="fa fa-users fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Active Users</div>
                        <div class="fs-5 fw-bold text-success">{{ number_format($stats['active']) }}</div>
                    </div>
                    <i class="fa fa-user-check fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Suspended</div>
                        <div class="fs-5 fw-bold text-danger">{{ number_format($stats['suspended']) }}</div>
                    </div>
                    <i class="fa fa-user-slash fa-2x text-danger opacity-50"></i>
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
                        <div class="fs-5 fw-bold">${{ number_format($stats['balance'], 2) }}</div>
                    </div>
                    <i class="fa fa-wallet fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Name, Email, ID..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="role" class="form-label small text-muted">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="reseller" {{ request('role') === 'reseller' ? 'selected' : '' }}>Reseller</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted">Registered From</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-muted">Registered To</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['sort' => 'id', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}">
                                ID
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}">
                                User
                            </a>
                        </th>
                        <th>Email</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['sort' => 'balance', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}">
                                Balance
                            </a>
                        </th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['sort' => 'created_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}">
                                Registered
                            </a>
                        </th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="text-muted">#{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px; font-size: 14px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                    @if($user->is_admin)
                                        <i class="fa fa-shield-alt text-danger ms-1" title="Admin"></i>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </td>
                        <td class="fw-semibold">${{ number_format($user->balance, 4) }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'reseller' ? 'warning text-dark' : 'secondary') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.index', ['user_id' => $user->id]) }}">
                                {{ number_format($user->orders->count()) }}
                            </a>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block me-1">
                                <input class="form-check-input" type="checkbox"
                                       {{ $user->is_active ? 'checked' : '' }}
                                       onchange="toggleUser({{ $user->id }})"
                                       {{ !auth()->user()->can('admin.users.edit') || $user->id === auth()->id() ? 'disabled' : '' }}>
                            </div>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @can('admin.users.view')
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                <a href="{{ route('admin.users.login-as', $user->id) }}"
                                   class="btn btn-outline-secondary" title="Login as User"
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <i class="fa fa-sign-in-alt"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    @can('admin.users.delete')
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fa fa-users fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No users found.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
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
                    Are you sure you want to delete <strong id="deleteUserName"></strong>?
                </div>
                <p class="text-muted mb-0">This action will permanently remove the user and all their data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash me-1"></i> Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleUser(id) {
    fetch('/admin/users/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        toastr.success(data.success ? 'User status updated' : 'Failed to update');
    })
    .catch(() => toastr.error('An error occurred'));
}

function deleteUser(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/users/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
