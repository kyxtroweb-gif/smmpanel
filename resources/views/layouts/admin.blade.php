<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin Panel') - KYXTRO SMM</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Toastr Notifications -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --topbar-bg: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #334155;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #1e293b;
            color: white;
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .sidebar-brand:hover {
            color: #c7d2fe;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-divider {
            padding: 0.75rem 1.5rem 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #475569;
        }

        .sidebar-item {
            padding: 0.65rem 1.5rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .sidebar-item:hover {
            color: white;
            background: var(--sidebar-hover);
        }

        .sidebar-item.active {
            color: white;
            background: rgba(99, 102, 241, 0.15);
            border-left-color: var(--primary);
        }

        .sidebar-item i {
            width: 20px;
            text-align: center;
        }

        .sidebar-badge {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 0.15rem 0.45rem;
            border-radius: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-weight: 700;
            font-size: 1.4rem;
            margin: 0;
            color: #0f172a;
        }

        .breadcrumb-nav {
            font-size: 0.8rem;
        }

        .breadcrumb-nav a {
            color: #64748b;
            text-decoration: none;
        }

        .breadcrumb-nav a:hover {
            color: var(--primary);
        }

        .breadcrumb-nav .separator {
            margin: 0 0.5rem;
            color: #cbd5e1;
        }

        /* Content */
        .content-body {
            padding: 1.5rem;
            flex: 1;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Badges */
        .badge.bg-primary-subtle {
            background: var(--primary-light);
            color: #4338ca;
        }

        .badge.bg-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.bg-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge.bg-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.bg-cancelled {
            background: #f3f4f6;
            color: #374151;
        }

        .badge.bg-refunded {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.bg-partial {
            background: #ffedd5;
            color: #9a3412;
        }

        /* Tables */
        .table {
            font-size: 0.875rem;
        }

        .table thead th {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }

        .table td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Forms */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Timeline */
        .timeline {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 2rem;
            padding-bottom: 1.25rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.25rem;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-point {
            position: absolute;
            left: -0.4rem;
            top: 0.2rem;
            width: 0.85rem;
            height: 0.85rem;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--primary);
        }

        .timeline-point-secondary { border-color: #64748b; }
        .timeline-point-success { border-color: #10b981; }
        .timeline-point-danger { border-color: #ef4444; }
        .timeline-point-warning { border-color: #f59e0b; }

        /* Stats Cards */
        .stats-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Progress */
        .progress {
            border-radius: 6px;
            height: 8px;
        }

        /* Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .dropdown-item i {
            width: 20px;
            color: #64748b;
        }

        /* List Groups */
        .list-group-flush > .list-group-item {
            border-width: 0 0 1px;
        }

        /* Avatar */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .avatar-sm {
            width: 28px;
            height: 28px;
            font-size: 0.75rem;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        /* Toastr overrides */
        .toast {
            border-radius: 8px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-body {
                padding: 1rem;
            }
        }

        /* Dark mode */
        [data-bs-theme="dark"] body {
            background: #0f172a;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .sidebar {
            background: #0f172a;
        }

        [data-bs-theme="dark"] .topbar {
            background: #1e293b;
            border-color: #334155;
        }

        [data-bs-theme="dark"] .card {
            background: #1e293b;
        }

        [data-bs-theme="dark"] .table {
            color: #e2e8f0;
        }

        /* Font Awesome Fix */
        .fa, .fas, .far, .fab {
            display: inline-block;
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <i class="fa-solid fa-bolt text-primary"></i> KYXTRO SMM
        </a>

        <div class="sidebar-nav">
            <div class="sidebar-divider">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>

            <div class="sidebar-divider">Management</div>
            <a href="{{ route('admin.users') }}" class="sidebar-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Users
            </a>
            <a href="{{ route('admin.services') }}" class="sidebar-item {{ request()->routeIs('admin.services*') ? 'active' : '' }}">
                <i class="fa-solid fa-bars-progress"></i> Services
            </a>
            <a href="{{ route('admin.categories') }}" class="sidebar-item {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i> Categories
            </a>
            <a href="{{ route('admin.providers') }}" class="sidebar-item {{ request()->routeIs('admin.providers*') ? 'active' : '' }}">
                <i class="fa-solid fa-server"></i> Providers
            </a>
            <a href="{{ route('admin.orders') }}" class="sidebar-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <i class="fa-solid fa-shopping-cart"></i> Orders
                @if(isset($pendingOrders) && $pendingOrders > 0)
                    <span class="sidebar-badge bg-warning text-dark">{{ $pendingOrders }}</span>
                @endif
            </a>

            <div class="sidebar-divider">Finance</div>
            <a href="{{ route('admin.payments') }}" class="sidebar-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Deposits / Payments
                @if(isset($pendingPayments) && $pendingPayments > 0)
                    <span class="sidebar-badge bg-warning text-dark">{{ $pendingPayments }}</span>
                @endif
            </a>
            <a href="{{ route('admin.payment-methods') }}" class="sidebar-item {{ request()->routeIs('admin.payment-methods*') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card"></i> Payment Methods
            </a>

            <div class="sidebar-divider">Support</div>
            <a href="{{ route('admin.tickets') }}" class="sidebar-item {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
                <i class="fa-solid fa-headset"></i> Tickets
                @if(isset($openTickets) && $openTickets > 0)
                    <span class="sidebar-badge bg-danger">{{ $openTickets }}</span>
                @endif
            </a>

            <div class="sidebar-divider">Reports</div>
            <a href="{{ route('admin.reports.profit') }}" class="sidebar-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Reports
            </a>

            <div class="sidebar-divider">System</div>
            <a href="{{ route('admin.settings') }}" class="sidebar-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i> Settings
            </a>
            <a href="/" class="sidebar-item">
                <i class="fa-solid fa-arrow-left"></i> Back to Site
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary d-lg-none me-3" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    @hasSection('breadcrumb')
                        <nav class="breadcrumb-nav">
                            @yield('breadcrumb')
                        </nav>
                    @endif
                    <h1 class="page-title">@yield('page-title', 'Admin Panel')</h1>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                {{-- Quick Add Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-plus me-1"></i> Quick Add
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.services.create') ?? '#' }}">
                            <i class="fa-solid fa-plus me-2"></i>New Service
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.users.create') ?? '#' }}">
                            <i class="fa-solid fa-user-plus me-2"></i>New User
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.providers.create') ?? '#' }}">
                            <i class="fa-solid fa-server me-2"></i>New Provider
                        </a></li>
                    </ul>
                </div>

                {{-- Notifications --}}
                <button class="btn btn-link text-secondary position-relative p-0" type="button">
                    <i class="fa-solid fa-bell fa-lg"></i>
                    @if(isset($notifications) && $notifications > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">
                            {{ $notifications }}
                            <span class="visually-hidden">notifications</span>
                        </span>
                    @endif
                </button>

                {{-- User Menu --}}
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                        <div class="avatar bg-primary text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-semibold">{{ auth()->user()->name ?? 'Admin' }}</div>
                                <div class="text-muted small">{{ auth()->user()->email ?? '' }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('user.profile') ?? '#' }}">
                                <i class="fa-solid fa-user me-2"></i>My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.settings') ?? '#' }}">
                                <i class="fa-solid fa-gear me-2"></i>Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @include('partials.flash')

        <!-- Content -->
        <div class="content-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 mt-auto">
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>&copy; {{ date('Y') }} KYXTRO SMM. All rights reserved.</span>
                <span>Laravel {{ app()->version() }}</span>
            </div>
        </footer>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Toastr Notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Toastr Configuration
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            timeOut: 5000,
            extendedTimeOut: 2000,
        };

        // Flash Messages
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        @if(session('warning'))
            toastr.warning('{{ session('warning') }}');
        @endif

        @if(session('info'))
            toastr.info('{{ session('info') }}');
        @endif

        // Sidebar Toggle (Mobile)
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth < 992 &&
                !sidebar.contains(e.target) &&
                !toggle.contains(e.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });

        // Confirm delete actions
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-confirm]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
