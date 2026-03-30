@extends('layouts.app')
@section('title', 'User Dashboard - SMM Panel')

@push('styles')
<style>
    .sidebar {
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
        min-height: 100vh;
        width: 260px;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }
    .sidebar-brand {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .sidebar-brand h4 {
        color: #fff;
        margin: 0;
        font-weight: 700;
    }
    .sidebar-brand span {
        color: #667eea;
    }
    .user-info {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        float: left;
        margin-right: 12px;
    }
    .user-details h6 {
        color: #fff;
        margin: 0 0 4px 0;
        font-size: 14px;
    }
    .user-details small {
        color: rgba(255,255,255,0.6);
    }
    .balance-card {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .balance-amount {
        font-size: 28px;
        font-weight: 700;
        color: #10b981;
    }
    .balance-label {
        color: rgba(255,255,255,0.6);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .nav-menu {
        padding: 15px 0;
        list-style: none;
        margin: 0;
    }
    .nav-item {
        margin: 0;
    }
    .nav-link {
        padding: 12px 20px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    .nav-link:hover, .nav-link.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-left-color: #667eea;
    }
    .nav-link i {
        width: 24px;
        margin-right: 12px;
        font-size: 16px;
    }
    .nav-link .badge {
        margin-left: auto;
    }
    .main-content {
        margin-left: 260px;
        padding: 30px;
        background: #f5f7fa;
        min-height: 100vh;
    }
    @media (max-width: 991px) {
        .sidebar {
            transform: translateX(-100%);
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .main-content {
            margin-left: 0;
        }
    }
    .navbar-mobile {
        display: none;
        background: #1a1a2e;
        padding: 15px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
    }
    @media (max-width: 991px) {
        .navbar-mobile {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    }
    .mobile-toggle {
        background: none;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<!-- Mobile Navbar -->
<div class="navbar-mobile">
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <span class="text-white fw-bold">
        <i class="fas fa-share-alt me-2"></i>SMM Panel
    </span>
    <div class="dropdown">
        <a href="#" class="text-white" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle fa-lg"></i>
        </a>
    </div>
</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h4><i class="fas fa-share-alt me-2"></i><span>SMM</span> Panel</h4>
    </div>

    <div class="user-info clearfix">
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <div class="user-details">
            <h6>{{ auth()->user()->name ?? 'User' }}</h6>
            <small>{{ auth()->user()->email ?? '' }}</small>
        </div>
    </div>

    <div class="balance-card">
        <div class="balance-label">Available Balance</div>
        <div class="balance-amount">${{ number_format(auth()->user()->balance ?? 0, 2) }}</div>
        <a href="{{ route('user.deposit') }}" class="btn btn-success btn-sm mt-3 w-100">
            <i class="fas fa-plus-circle me-1"></i> Add Funds
        </a>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.orders') }}" class="nav-link {{ request()->routeIs('user.orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag"></i>
                <span>My Orders</span>
                @if(isset($pendingCount) && $pendingCount > 0)
                    <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.orders.new') }}" class="nav-link {{ request()->routeIs('user.orders.new') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>New Order</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.deposit') }}" class="nav-link {{ request()->routeIs('user.deposit*') ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i>
                <span>Deposits</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.tickets.index') }}" class="nav-link {{ request()->routeIs('user.tickets*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i>
                <span>Support Tickets</span>
                @if(isset($openTickets) && $openTickets > 0)
                    <span class="badge bg-danger">{{ $openTickets }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.api') }}" class="nav-link {{ request()->routeIs('user.api') ? 'active' : '' }}">
                <i class="fas fa-code"></i>
                <span>API Documentation</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.profile') }}" class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </a>
        </li>
    </ul>

    <div class="mt-auto p-3 border-top" style="border-color: rgba(255,255,255,0.1)!important;">
        <a href="{{ route('logout') }}" class="nav-link text-danger"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">
    @include('partials.flash')
    @yield('user_content')
</main>
@endsection

@push('scripts')
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Close sidebar on outside click on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>
@endpush
