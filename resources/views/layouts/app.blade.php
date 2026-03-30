<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KYXTRO SMM Panel')</title>
    <meta name="description" content="Professional Social Media Marketing Services - Grow your social media presence with KYXTRO">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-purple: #7c3aed;
            --primary-violet: #8b5cf6;
            --primary-dark: #5b21b6;
            --secondary-purple: #a78bfa;
            --light-purple: #ede9fe;
            --dark-bg: #1a1a2e;
            --darker-bg: #16213e;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #334155;
        }

        a { text-decoration: none; }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(124, 58, 237, 0.08);
        }

        .nav-link {
            font-weight: 500;
            color: #64748b !important;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-purple) !important;
            background: var(--light-purple);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
            color: white;
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-purple);
            color: var(--primary-purple);
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-purple);
            color: white;
        }

        .btn-outline-light {
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.15);
            border-color: white;
            color: white;
        }

        .balance-badge {
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));
            color: white;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
            color: #4a5568;
        }

        .dropdown-item:hover {
            background: var(--light-purple);
            color: var(--primary-purple);
        }

        .dropdown-item i {
            width: 20px;
            color: var(--primary-purple);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #f1f5f9;
        }

        main {
            flex: 1;
        }

        footer {
            background: var(--dark-bg);
            color: #94a3b8;
        }

        footer a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--primary-violet);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(124, 58, 237, 0.12);
        }

        .text-primary { color: var(--primary-purple) !important; }
        .bg-primary { background: var(--primary-purple) !important; }

        .page-header {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));
        }

        @yield('styles')
    </style>

    @stack('head')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fa-solid fa-chart-line me-1"></i> KYXTRO SMM
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services') }}">
                            <i class="fa-solid fa-server me-1"></i> Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">
                            <i class="fa-solid fa-tags me-1"></i> Pricing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">
                            <i class="fa-solid fa-question-circle me-1"></i> FAQ
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary-purple), var(--primary-violet));">
                                    <span class="text-white fw-bold small">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                                <span class="fw-medium d-none d-md-inline">{{ Auth::user()->name }}</span>
                                <span class="balance-badge ms-1">${{ number_format(Auth::user()->balance, 2) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('user.dashboard') }}"><i class="fa-solid fa-home me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.orders.new') }}"><i class="fa-solid fa-shopping-cart me-2"></i> New Order</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.deposit') }}"><i class="fa-solid fa-wallet me-2"></i> Deposit</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.tickets') }}"><i class="fa-solid fa-headset me-2"></i> Support</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fa-solid fa-user-cog me-2"></i> Profile</a></li>
                                @if(Auth::user()->isAdmin())
                                    <li><a class="dropdown-item text-danger" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-shield-halved me-2"></i> Admin Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-outline-primary btn-sm me-2" href="{{ route('login') }}">
                                <i class="fa-solid fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm" href="{{ route('register') }}">
                                <i class="fa-solid fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @include('partials.flash')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-3">
                        <i class="fa-solid fa-chart-line me-1 text-primary"></i> KYXTRO SMM
                    </h5>
                    <p class="small mb-0 text-white-50">The best SMM panel for social media growth. Fast delivery, affordable prices, 24/7 support.</p>
                </div>
                <div class="col-6 col-lg-2 mb-3 mb-lg-0">
                    <h6 class="text-white mb-2">Links</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('services') }}">Services</a></li>
                        <li><a href="{{ route('pricing') }}">Pricing</a></li>
                        <li><a href="{{ route('faq') }}">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 mb-3 mb-lg-0">
                    <h6 class="text-white mb-2">Legal</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('terms') }}">Terms</a></li>
                        <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white mb-2">Support</h6>
                    <p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $siteSettings['contact_email'] ?? 'support@kyxtro.com' }}</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white-50"><i class="fa-brands fa-telegram"></i></a>
                        <a href="#" class="text-white-50"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="#" class="text-white-50"><i class="fa-brands fa-discord"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: #1e293b;">
            <p class="text-center small mb-0">&copy; {{ date('Y') }} KYXTRO SMM. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>