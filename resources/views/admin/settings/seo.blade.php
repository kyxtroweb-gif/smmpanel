@extends('layouts.admin')
@section('title', 'SEO Settings - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">SEO Settings</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">SEO</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-3">
        {{-- Settings Navigation --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Settings</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.settings.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                    <i class="fa fa-cog me-2"></i> General
                </a>
                <a href="{{ route('admin.settings.seo') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}">
                    <i class="fa fa-search me-2"></i> SEO
                </a>
                <a href="{{ route('admin.settings.theme') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.theme') ? 'active' : '' }}">
                    <i class="fa fa-palette me-2"></i> Theme
                </a>
                <a href="{{ route('admin.settings.api') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.api') ? 'active' : '' }}">
                    <i class="fa fa-key me-2"></i> API Keys
                </a>
                <a href="{{ route('admin.settings.email') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.email') ? 'active' : '' }}">
                    <i class="fa fa-envelope me-2"></i> Email
                </a>
                <a href="{{ route('admin.settings.payment') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.payment') ? 'active' : '' }}">
                    <i class="fa fa-credit-card me-2"></i> Payment
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            {{-- Meta Tags --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-tag me-2 text-primary"></i>Meta Tags</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="seo_title" class="form-label">SEO Title</label>
                        <input type="text" name="seo_title" id="seo_title"
                               class="form-control @error('seo_title') is-invalid @enderror"
                               value="{{ old('seo_title', $settings['seo_title'] ?? '') }}"
                               placeholder="Best SMM Services - Fast Delivery"
                               maxlength="70">
                        <div class="form-text">
                            <span id="seoTitleCount">0</span>/70 characters.
                            Recommended: 50-60 characters.
                        </div>
                        @error('seo_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="seo_description" class="form-label">SEO Description</label>
                        <textarea name="seo_description" id="seo_description"
                                  class="form-control @error('seo_description') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Get the best SMM services with instant delivery..."
                                  maxlength="160">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
                        <div class="form-text">
                            <span id="seoDescCount">0</span>/160 characters.
                            Recommended: 150-160 characters.
                        </div>
                        @error('seo_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="seo_keywords" class="form-label">SEO Keywords</label>
                        <textarea name="seo_keywords" id="seo_keywords"
                                  class="form-control"
                                  rows="2"
                                  placeholder="smm panel, social media marketing, instagram followers, cheap likes">{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}</textarea>
                        <div class="form-text">Comma-separated keywords</div>
                    </div>
                </div>
            </div>

            {{-- Search Engine Verification --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-check-circle me-2 text-primary"></i>Search Engine Verification</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="google_analytics_id" class="form-label">
                                <i class="fab fa-google text-danger me-1"></i> Google Analytics ID
                            </label>
                            <input type="text" name="google_analytics_id" id="google_analytics_id"
                                   class="form-control font-monospace"
                                   value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                                   placeholder="G-XXXXXXXXXX">
                            <div class="form-text">Find this in your GA4 property settings</div>
                        </div>
                        <div class="col-md-6">
                            <label for="google_tag_manager_id" class="form-label">
                                <i class="fab fa-google text-danger me-1"></i> Google Tag Manager ID
                            </label>
                            <input type="text" name="google_tag_manager_id" id="google_tag_manager_id"
                                   class="form-control font-monospace"
                                   value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id'] ?? '') }}"
                                   placeholder="GTM-XXXXXXX">
                            <div class="form-text">GTM container ID</div>
                        </div>
                        <div class="col-md-6">
                            <label for="bing_verification" class="form-label">
                                <i class="fab fa-microsoft text-info me-1"></i> Bing Webmaster
                            </label>
                            <input type="text" name="bing_verification" id="bing_verification"
                                   class="form-control font-monospace"
                                   value="{{ old('bing_verification', $settings['bing_verification'] ?? '') }}"
                                   placeholder="Bing verification code">
                        </div>
                        <div class="col-md-6">
                            <label for="yandex_verification" class="form-label">
                                <i class="fab fa-yandex text-warning me-1"></i> Yandex
                            </label>
                            <input type="text" name="yandex_verification" id="yandex_verification"
                                   class="form-control font-monospace"
                                   value="{{ old('yandex_verification', $settings['yandex_verification'] ?? '') }}"
                                   placeholder="Yandex verification code">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Sharing --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-share-alt me-2 text-primary"></i>Social Sharing</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="og_image" class="form-label">Open Graph Image</label>
                        @if(!empty($settings['og_image']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['og_image']) }}"
                                     alt="OG Image" class="img-thumbnail" style="max-height: 100px;">
                                <div class="form-text">Currently uploaded</div>
                            </div>
                        @endif
                        <input type="file" name="og_image" id="og_image"
                               class="form-control" accept="image/*">
                        <div class="form-text">Recommended size: 1200x630px</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="social_facebook" class="form-label">
                                <i class="fab fa-facebook text-primary me-1"></i> Facebook URL
                            </label>
                            <input type="url" name="social_facebook" id="social_facebook"
                                   class="form-control"
                                   value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}"
                                   placeholder="https://facebook.com/yourpage">
                        </div>
                        <div class="col-md-6">
                            <label for="social_twitter" class="form-label">
                                <i class="fab fa-twitter text-info me-1"></i> Twitter URL
                            </label>
                            <input type="url" name="social_twitter" id="social_twitter"
                                   class="form-control"
                                   value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}"
                                   placeholder="https://twitter.com/yourpage">
                        </div>
                        <div class="col-md-6">
                            <label for="social_instagram" class="form-label">
                                <i class="fab fa-instagram text-danger me-1"></i> Instagram URL
                            </label>
                            <input type="url" name="social_instagram" id="social_instagram"
                                   class="form-control"
                                   value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}"
                                   placeholder="https://instagram.com/yourpage">
                        </div>
                        <div class="col-md-6">
                            <label for="social_telegram" class="form-label">
                                <i class="fab fa-telegram text-info me-1"></i> Telegram URL
                            </label>
                            <input type="url" name="social_telegram" id="social_telegram"
                                   class="form-control"
                                   value="{{ old('social_telegram', $settings['social_telegram'] ?? '') }}"
                                   placeholder="https://t.me/yourchannel">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Robots & Sitemap --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-robot me-2 text-primary"></i>Robots & Sitemap</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="allow_indexing"
                                       id="allow_indexing" value="1"
                                       {{ old('allow_indexing', $settings['allow_indexing'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_indexing">
                                    <strong>Allow Search Engine Indexing</strong>
                                    <div class="text-muted small">Add meta robots noindex,nofollow if disabled</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sitemap_auto"
                                       id="sitemap_auto" value="1"
                                       {{ old('sitemap_auto', $settings['sitemap_auto'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sitemap_auto">
                                    <strong>Auto-generate Sitemap</strong>
                                    <div class="text-muted small">Regenerate on service/category changes</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Save SEO Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('seo_title').addEventListener('input', function() {
    document.getElementById('seoTitleCount').textContent = this.value.length;
});

document.getElementById('seo_description').addEventListener('input', function() {
    document.getElementById('seoDescCount').textContent = this.value.length;
});

// Initialize counts
document.getElementById('seoTitleCount').textContent = document.getElementById('seo_title').value.length;
document.getElementById('seoDescCount').textContent = document.getElementById('seo_description').value.length;
</script>
@endpush
