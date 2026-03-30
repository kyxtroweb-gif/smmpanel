@extends('layouts.app')
@section('title', 'KYXTRO SMM - Grow Your Social Media Today')

@section('content')
<!-- Hero -->
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);" class="text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="badge bg-primary bg-opacity-25 text-white mb-3 px-3 py-2 rounded-pill" style="font-size:0.8rem;">
                    <i class="fa-solid fa-bolt me-1"></i> Fast Delivery • 24/7 Support
                </div>
                <h1 class="display-4 fw-bold mb-3">Grow Your <span class="text-primary">Social Media</span><br>Today</h1>
                <p class="lead text-white-50 mb-4">The most reliable SMM panel for Instagram, TikTok, YouTube, Twitter, Telegram & more. Real engagement, instant delivery, affordable prices.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ auth()->check() ? route('user.orders.new') : route('register') }}" class="btn btn-primary btn-lg px-4">
                        <i class="fa-solid fa-rocket me-1"></i> Get Started
                    </a>
                    <a href="{{ route('services') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="fa-solid fa-list me-1"></i> View Services
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div><strong class="text-primary">50K+</strong><span class="text-white-50 ms-1 small">Orders</span></div>
                    <div><strong class="text-primary">10K+</strong><span class="text-white-50 ms-1 small">Happy Users</span></div>
                    <div><strong class="text-primary">50+</strong><span class="text-white-50 ms-1 small">Services</span></div>
                    <div><strong class="text-primary">99.9%</strong><span class="text-white-50 ms-1 small">Uptime</span></div>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div style="background: rgba(99,102,241,0.15); border-radius: 20px; padding: 2rem;">
                    <i class="fa-solid fa-chart-line fa-5x text-primary mb-3"></i>
                    <h4 class="text-white">Real Results</h4>
                    <p class="text-white-50 small mb-0">Watch your social accounts grow with real, high-quality engagement</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose KYXTRO SMM?</h2>
            <p class="text-muted">Everything you need to grow your social presence</p>
        </div>
        <div class="row g-4">
            @foreach([['icon'=>'fa-bolt','title'=>'Instant Delivery','desc'=>'Most orders start within minutes. No waiting around for your growth.'],['icon'=>'fa-shield-halved','title'=>'Secure & Safe','desc'=>'We use industry-standard security. Your accounts stay 100% safe.'],['icon'=>'fa-headset','title'=>'24/7 Support','desc'=>'Got questions? Our team is available round the clock via tickets.'],['icon'=>'fa-wallet','title'=>'Easy Payments','desc'=>'Pay with UPI, PayTM, Bank Transfer, Stripe, Crypto & more.'],['icon'=>'fa-tags','title'=>'Best Prices','desc'=>'Unbeatable rates starting from just $0.01 per 1K. Reseller-friendly.'],['icon'=>'fa-arrows-rotate','title'=>'Refill Guaranteed','desc'=>'Most services come with refill guarantee. We stand behind our quality.']]
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 text-center">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:60px;height:60px;">
                                <i class="fa-solid {{ $loop->first ? 'fa-bolt' : '' }} {{ $loop->index == 1 ? 'fa-shield-halved' : '' }} {{ $loop->index == 2 ? 'fa-headset' : '' }} {{ $loop->index == 3 ? 'fa-wallet' : '' }} {{ $loop->index == 4 ? 'fa-tags' : '' }} {{ $loop->index == 5 ? 'fa-arrows-rotate' : '' }} fa-2x text-primary"></i>
                            </div>
                            <h5 class="fw-bold">{{ $item['title'] ?? '' }}</h5>
                            <p class="text-muted small mb-0">{{ $item['desc'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5" style="background:#f8fafc;">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="text-muted">Get started in 3 simple steps</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;font-size:1.5rem;font-weight:700;">1</div>
                <h5 class="fw-bold">Choose a Service</h5>
                <p class="text-muted small">Browse our catalog and pick the social media service you need. We cover all major platforms.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;font-size:1.5rem;font-weight:700;">2</div>
                <h5 class="fw-bold">Place Your Order</h5>
                <p class="text-muted small">Enter your profile/post link, select quantity, pay securely. Your order is processed instantly.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;font-size:1.5rem;font-weight:700;">3</div>
                <h5 class="fw-bold">Watch It Grow</h5>
                <p class="text-muted small">Sit back and watch your social accounts grow with real, high-quality engagement.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Services</h2>
            <p class="text-muted">High-quality social media services at unbeatable prices</p>
        </div>
        <div class="row g-3">
            @foreach([
                ['icon'=>'fab fa-instagram','name'=>'Instagram Followers','price'=>'0.90','color'=>'#E4405F'],
                ['icon'=>'fab fa-tiktok','name'=>'TikTok Followers','price'=>'1.50','color'=>'#000'],
                ['icon'=>'fab fa-youtube','name'=>'YouTube Views','price'=>'0.50','color'=>'#FF0000'],
                ['icon'=>'fab fa-twitter','name'=>'Twitter Followers','price'=>'1.80','color'=>'#1DA1F2'],
                ['icon'=>'fab fa-telegram','name'=>'Telegram Members','price'=>'1.20','color'=>'#0088cc'],
                ['icon'=>'fab fa-facebook','name'=>'Facebook Followers','price'=>'1.50','color'=>'#1877F2'],
            ] as $svc)
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px;background:rgba(0,0,0,0.05);">
                                <i class="{{ $svc['icon'] }} fa-lg" style="color:{{ $svc['color'] }}"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">{{ $svc['name'] }}</h6>
                                <small class="text-muted">From <strong class="text-primary">${{ $svc['price'] }}</strong> / 1K</small>
                            </div>
                            <a href="{{ route('services') }}" class="btn btn-sm btn-outline-primary ms-auto"><i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('services') }}" class="btn btn-primary px-4"><i class="fa-solid fa-list me-1"></i> View All Services</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);" class="py-5 text-white text-center">
    <div class="container py-4">
        <h2 class="fw-bold mb-3">Ready to Grow?</h2>
        <p class="mb-4 opacity-75">Join thousands of users who trust KYXTRO SMM for their social media growth.</p>
        <a href="{{ auth()->check() ? route('user.dashboard') : route('register') }}" class="btn btn-light btn-lg px-5 fw-semibold">
            @auth Get Started Now @else Create Free Account @endauth
        </a>
    </div>
</section>
@endsection
