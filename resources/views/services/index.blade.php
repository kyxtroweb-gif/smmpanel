@extends('layouts.app')

@section('title', 'Services - KYXTRO SMM Panel')

@push('styles')
<style>
    .filter-sidebar {
        position: sticky;
        top: 100px;
    }

    .category-tabs {
        display: flex;
        gap: 0.75rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        flex-wrap: nowrap;
    }

    .category-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        background: white;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .category-tab:hover,
    .category-tab.active {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        border-color: transparent;
        color: white;
    }

    .service-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(124, 58, 237, 0.15);
    }

    .service-card-header {
        background: linear-gradient(135deg, #7c3aed10, #8b5cf610);
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .service-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .service-name {
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .service-description {
        font-size: 0.85rem;
        color: #718096;
    }

    .service-card-body {
        padding: 1.25rem;
    }

    .service-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .service-stat {
        text-align: center;
        padding: 0.75rem;
        background: #f8f9fc;
        border-radius: 12px;
    }

    .service-stat-label {
        font-size: 0.75rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .service-stat-value {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.95rem;
    }

    .service-price {
        font-size: 1.25rem;
        font-weight: 700;
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .btn-order {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        color: white;
    }

    .filter-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .filter-card-header {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        color: white;
        padding: 1rem 1.25rem;
        border-radius: 20px 20px 0 0;
    }

    .filter-item {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        color: #4a5568;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: between;
        transition: all 0.2s ease;
    }

    .filter-item:hover,
    .filter-item.active {
        background: #f3e8ff;
        color: #7c3aed;
    }

    .filter-item i {
        margin-right: 0.75rem;
        width: 20px;
    }

    .filter-count {
        margin-left: auto;
        background: #e2e8f0;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-left: 3rem;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1rem 0.75rem 3rem;
    }

    .search-box input:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 1rem;
    }

    @media (max-width: 991px) {
        .filter-sidebar {
            position: static;
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<section class="py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold text-white mb-3">Our Services</h1>
            <p class="text-white opacity-75 mb-0">Browse our wide range of social media marketing services</p>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Category Tabs -->
    <div class="category-tabs mb-4">
        <a href="{{ route('services.index') }}" class="category-tab {{ !request('category') ? 'active' : '' }}">
            <i class="fas fa-th-large me-2"></i>All Services
        </a>
        <a href="{{ route('services.index', ['category' => 'instagram']) }}" class="category-tab {{ request('category') == 'instagram' ? 'active' : '' }}">
            <i class="fab fa-instagram me-2"></i>Instagram
        </a>
        <a href="{{ route('services.index', ['category' => 'youtube']) }}" class="category-tab {{ request('category') == 'youtube' ? 'active' : '' }}">
            <i class="fab fa-youtube me-2"></i>YouTube
        </a>
        <a href="{{ route('services.index', ['category' => 'facebook']) }}" class="category-tab {{ request('category') == 'facebook' ? 'active' : '' }}">
            <i class="fab fa-facebook me-2"></i>Facebook
        </a>
        <a href="{{ route('services.index', ['category' => 'tiktok']) }}" class="category-tab {{ request('category') == 'tiktok' ? 'active' : '' }}">
            <i class="fab fa-tiktok me-2"></i>TikTok
        </a>
        <a href="{{ route('services.index', ['category' => 'twitter']) }}" class="category-tab {{ request('category') == 'twitter' ? 'active' : '' }}">
            <i class="fab fa-twitter me-2"></i>Twitter
        </a>
        <a href="{{ route('services.index', ['category' => 'telegram']) }}" class="category-tab {{ request('category') == 'telegram' ? 'active' : '' }}">
            <i class="fab fa-telegram me-2"></i>Telegram
        </a>
    </div>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="filter-sidebar">
                <!-- Search Box -->
                <div class="search-box mb-4">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search services...">
                </div>

                <!-- Categories Filter -->
                <div class="filter-card mb-4">
                    <div class="filter-card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2"></i>Categories</h6>
                    </div>
                    <div class="p-3">
                        <a href="{{ route('services.index') }}" class="filter-item d-block mb-2 {{ !request('category') ? 'active' : '' }}">
                            <i class="fas fa-globe"></i>All Categories
                            <span class="filter-count">156</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'instagram']) }}" class="filter-item d-block mb-2 {{ request('category') == 'instagram' ? 'active' : '' }}">
                            <i class="fab fa-instagram"></i>Instagram
                            <span class="filter-count">48</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'youtube']) }}" class="filter-item d-block mb-2 {{ request('category') == 'youtube' ? 'active' : '' }}">
                            <i class="fab fa-youtube"></i>YouTube
                            <span class="filter-count">32</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'facebook']) }}" class="filter-item d-block mb-2 {{ request('category') == 'facebook' ? 'active' : '' }}">
                            <i class="fab fa-facebook"></i>Facebook
                            <span class="filter-count">28</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'tiktok']) }}" class="filter-item d-block mb-2 {{ request('category') == 'tiktok' ? 'active' : '' }}">
                            <i class="fab fa-tiktok"></i>TikTok
                            <span class="filter-count">24</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'twitter']) }}" class="filter-item d-block mb-2 {{ request('category') == 'twitter' ? 'active' : '' }}">
                            <i class="fab fa-twitter"></i>Twitter
                            <span class="filter-count">18</span>
                        </a>
                        <a href="{{ route('services.index', ['category' => 'telegram']) }}" class="filter-item d-block {{ request('category') == 'telegram' ? 'active' : '' }}">
                            <i class="fab fa-telegram"></i>Telegram
                            <span class="filter-count">12</span>
                        </a>
                    </div>
                </div>

                <!-- Service Type Filter -->
                <div class="filter-card">
                    <div class="filter-card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Service Type</h6>
                    </div>
                    <div class="p-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="followers" checked>
                            <label class="form-check-label" for="followers">Followers</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="likes">
                            <label class="form-check-label" for="likes">Likes</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="views">
                            <label class="form-check-label" for="views">Views</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="comments">
                            <label class="form-check-label" for="comments">Comments</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shares">
                            <label class="form-check-label" for="shares">Shares</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="col-lg-9">
            <div class="row g-4">
                <!-- Service Card 1 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #E1306C, #F77737);">
                                    <i class="fab fa-instagram text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Instagram Followers</h5>
                                    <span class="service-description">High Quality</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">100</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">50K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$2.50</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">24 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Premium Quality</span>
                                <span class="service-price">$2.50</span>
                            </div>
                            <a href="{{ route('services.order', 1) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 2 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #E1306C, #F77737);">
                                    <i class="fas fa-heart text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Instagram Likes</h5>
                                    <span class="service-description">Real Looking</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">50</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">10K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$0.80</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">6 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Fast Delivery</span>
                                <span class="service-price">$0.80</span>
                            </div>
                            <a href="{{ route('services.order', 2) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 3 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #FF0000, #FF4444);">
                                    <i class="fab fa-youtube text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">YouTube Views</h5>
                                    <span class="service-description">Retention 30 Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">500</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">1M</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$1.20</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">48 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">High Retention</span>
                                <span class="service-price">$1.20</span>
                            </div>
                            <a href="{{ route('services.order', 3) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 4 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #1877F2, #0D6EFD);">
                                    <i class="fab fa-facebook text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Facebook Page Likes</h5>
                                    <span class="service-description">Worldwide</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">100</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">100K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$3.00</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">72 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Real Profiles</span>
                                <span class="service-price">$3.00</span>
                            </div>
                            <a href="{{ route('services.order', 4) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 5 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #00f2ea, #ff0050);">
                                    <i class="fab fa-tiktok text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">TikTok Followers</h5>
                                    <span class="service-description">Global</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">100</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">30K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$2.80</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">24 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Instant Start</span>
                                <span class="service-price">$2.80</span>
                            </div>
                            <a href="{{ route('services.order', 5) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 6 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #1DA1F2, #1A8CD8);">
                                    <i class="fab fa-twitter text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Twitter Followers</h5>
                                    <span class="service-description">High Quality</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">50</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">10K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$2.20</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">12 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Drop Protection</span>
                                <span class="service-price">$2.20</span>
                            </div>
                            <a href="{{ route('services.order', 6) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 7 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #0088CC, #0066AA);">
                                    <i class="fab fa-telegram text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Telegram Members</h5>
                                    <span class="service-description">Public Channels</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">500</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">50K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$1.50</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">48 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Real Users</span>
                                <span class="service-price">$1.50</span>
                            </div>
                            <a href="{{ route('services.order', 7) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 8 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #E1306C, #F77737);">
                                    <i class="fas fa-comment text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">Instagram Comments</h5>
                                    <span class="service-description">Custom Texts</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">10</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">1K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$8.00</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">24 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Random Texts</span>
                                <span class="service-price">$8.00</span>
                            </div>
                            <a href="{{ route('services.order', 8) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 9 -->
                <div class="col-md-6 col-xl-4">
                    <div class="card service-card h-100">
                        <div class="service-card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="service-icon" style="background: linear-gradient(135deg, #FF0000, #FF4444);">
                                    <i class="fas fa-thumbs-up text-white"></i>
                                </div>
                                <div>
                                    <h5 class="service-name">YouTube Likes</h5>
                                    <span class="service-description">Fast Delivery</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <div class="service-stats">
                                <div class="service-stat">
                                    <div class="service-stat-label">Min</div>
                                    <div class="service-stat-value">50</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Max</div>
                                    <div class="service-stat-value">20K</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Per 1K</div>
                                    <div class="service-stat-value">$5.00</div>
                                </div>
                                <div class="service-stat">
                                    <div class="service-stat-label">Avg Time</div>
                                    <div class="service-stat-value">12 hrs</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Instant Start</span>
                                <span class="service-price">$5.00</span>
                            </div>
                            <a href="{{ route('services.order', 9) }}" class="btn btn-order">
                                <i class="fas fa-shopping-cart me-2"></i>Order Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <nav class="mt-5" aria-label="Service pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); border-color: #7c3aed;">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection