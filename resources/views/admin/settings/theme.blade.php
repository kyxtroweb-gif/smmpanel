@extends('layouts.admin')
@section('title', 'Theme Settings - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Theme Settings</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Theme</li>
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
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Color Scheme --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-palette me-2 text-primary"></i>Color Scheme</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <div class="input-group">
                                <input type="color" name="primary_color" id="primary_color"
                                       class="form-control form-control-color"
                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#0d6efd') }}"
                                       style="width: 60px;">
                                <input type="text" class="form-control font-monospace"
                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#0d6efd') }}"
                                       onchange="document.getElementById('primary_color').value = this.value">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="secondary_color" id="secondary_color"
                                       class="form-control form-control-color"
                                       value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }}"
                                       style="width: 60px;">
                                <input type="text" class="form-control font-monospace"
                                       value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }}"
                                       onchange="document.getElementById('secondary_color').value = this.value">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="accent_color" class="form-label">Accent Color</label>
                            <div class="input-group">
                                <input type="color" name="accent_color" id="accent_color"
                                       class="form-control form-control-color"
                                       value="{{ old('accent_color', $settings['accent_color'] ?? '#198754') }}"
                                       style="width: 60px;">
                                <input type="text" class="form-control font-monospace"
                                       value="{{ old('accent_color', $settings['accent_color'] ?? '#198754') }}"
                                       onchange="document.getElementById('accent_color').value = this.value">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="dark_mode" class="form-label">Default Theme</label>
                            <select name="dark_mode" id="dark_mode" class="form-select">
                                <option value="light" {{ old('dark_mode', $settings['dark_mode'] ?? 'light') === 'light' ? 'selected' : '' }}>
                                    Light Mode
                                </option>
                                <option value="dark" {{ old('dark_mode', $settings['dark_mode'] ?? 'light') === 'dark' ? 'selected' : '' }}>
                                    Dark Mode
                                </option>
                                <option value="auto" {{ old('dark_mode', $settings['dark_mode'] ?? 'light') === 'auto' ? 'selected' : '' }}>
                                    Auto (System Preference)
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logo Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-image me-2 text-primary"></i>Logo & Branding</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="logo" class="form-label">Main Logo</label>
                            @if(!empty($settings['logo']))
                                <div class="mb-2 p-3 bg-light rounded text-center">
                                    <img src="{{ asset('storage/' . $settings['logo']) }}"
                                         alt="Current Logo" style="max-height: 60px;">
                                    <div class="form-text mt-2">Currently uploaded</div>
                                </div>
                            @endif
                            <input type="file" name="logo" id="logo"
                                   class="form-control @error('logo') is-invalid @enderror"
                                   accept="image/png,image/svg+xml">
                            <div class="form-text">PNG or SVG. Recommended: 200x50px</div>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="logo_dark" class="form-label">Dark Mode Logo</label>
                            @if(!empty($settings['logo_dark']))
                                <div class="mb-2 p-3 bg-dark rounded text-center">
                                    <img src="{{ asset('storage/' . $settings['logo_dark']) }}"
                                         alt="Current Dark Logo" style="max-height: 60px; filter: brightness(10);">
                                    <div class="form-text mt-2 text-white-50">Currently uploaded</div>
                                </div>
                            @endif
                            <input type="file" name="logo_dark" id="logo_dark"
                                   class="form-control" accept="image/png,image/svg+xml">
                            <div class="form-text">For dark mode (optional)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="favicon" class="form-label">Favicon</label>
                            @if(!empty($settings['favicon']))
                                <div class="mb-2 p-3 bg-light rounded text-center">
                                    <img src="{{ asset('storage/' . $settings['favicon']) }}"
                                         alt="Current Favicon" style="max-height: 32px;">
                                    <div class="form-text mt-2">Currently uploaded</div>
                                </div>
                            @endif
                            <input type="file" name="favicon" id="favicon"
                                   class="form-control" accept="image/x-icon,image/png">
                            <div class="form-text">ICO or PNG. 32x32px recommended</div>
                        </div>
                        <div class="col-md-6">
                            <label for="logo_text" class="form-label">Logo Text (Text-based fallback)</label>
                            <input type="text" name="logo_text" id="logo_text"
                                   class="form-control"
                                   value="{{ old('logo_text', $settings['logo_text'] ?? 'SMM Panel') }}"
                                   placeholder="Your Brand Name">
                            <div class="form-text">Used when no logo is uploaded</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-th me-2 text-primary"></i>Footer Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="footer_text" class="form-label">Footer Copyright Text</label>
                        <input type="text" name="footer_text" id="footer_text"
                               class="form-control"
                               value="{{ old('footer_text', $settings['footer_text'] ?? '') }}"
                               placeholder="© 2024 SMM Panel. All rights reserved.">
                    </div>
                    <div class="mb-3">
                        <label for="footer_links" class="form-label">Footer Links (JSON)</label>
                        <textarea name="footer_links" id="footer_links"
                                  class="form-control font-monospace" rows="3"
                                  placeholder='[{"label": "Terms", "url": "/terms"}, {"label": "Privacy", "url": "/privacy"}]'>{{ old('footer_links', $settings['footer_links'] ?? '[]') }}</textarea>
                        <div class="form-text">JSON array of link objects</div>
                    </div>
                </div>
            </div>

            {{-- Custom CSS/JS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-code me-2 text-primary"></i>Custom Code</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="custom_css" class="form-label">Custom CSS</label>
                        <textarea name="custom_css" id="custom_css"
                                  class="form-control font-monospace" rows="6"
                                  placeholder=".my-custom-class { color: red; }">{{ old('custom_css', $settings['custom_css'] ?? '') }}</textarea>
                        <div class="form-text">Additional styles loaded after Bootstrap</div>
                    </div>
                    <div class="mb-3">
                        <label for="custom_js" class="form-label">Custom JavaScript</label>
                        <textarea name="custom_js" id="custom_js"
                                  class="form-control font-monospace" rows="6"
                                  placeholder="console.log('Hello');">{{ old('custom_js', $settings['custom_js'] ?? '') }}</textarea>
                        <div class="form-text">Additional JavaScript loaded in footer</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="custom_code_enabled"
                               id="custom_code_enabled" value="1"
                               {{ old('custom_code_enabled', $settings['custom_code_enabled'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="custom_code_enabled">
                            <strong>Enable Custom Code</strong>
                            <div class="text-muted small">Uncheck to disable without deleting</div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-eye me-2 text-primary"></i>Live Preview</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-4 bg-light">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="bg-primary rounded p-2" style="width: 40px; height: 40px;"></div>
                            </div>
                            <div>
                                <div class="fw-bold" id="previewLogoText">{{ old('logo_text', $settings['logo_text'] ?? 'SMM Panel') }}</div>
                                <div class="text-muted small" id="previewTagline">{{ $settings['site_tagline'] ?? '' }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-sm">Primary</button>
                            <button class="btn btn-secondary btn-sm">Secondary</button>
                            <button class="btn btn-success btn-sm">Success</button>
                            <button class="btn btn-danger btn-sm">Danger</button>
                        </div>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: 50%;"></div>
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
                            <i class="fa fa-save me-1"></i> Save Theme Settings
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
// Live preview updates
document.getElementById('logo_text').addEventListener('input', function() {
    document.getElementById('previewLogoText').textContent = this.value || 'SMM Panel';
});

document.getElementById('primary_color').addEventListener('input', function() {
    document.querySelectorAll('.bg-primary, .btn-primary').forEach(el => {
        el.style.backgroundColor = this.value;
    });
});
</script>
@endpush
