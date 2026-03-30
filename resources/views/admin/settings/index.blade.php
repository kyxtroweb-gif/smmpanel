@extends('layouts.admin')
@section('title', 'General Settings - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">General Settings</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">General</li>
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

            {{-- Site Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-globe me-2 text-primary"></i>Site Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                            <input type="text" name="site_name" id="site_name"
                                   class="form-control @error('site_name') is-invalid @enderror"
                                   value="{{ old('site_name', $settings['site_name'] ?? 'SMM Panel') }}" required>
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="site_tagline" class="form-label">Tagline</label>
                            <input type="text" name="site_tagline" id="site_tagline"
                                   class="form-control"
                                   value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                                   placeholder="Your catchy slogan">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email"
                                   class="form-control"
                                   value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="support_email" class="form-label">Support Email</label>
                            <input type="email" name="support_email" id="support_email"
                                   class="form-control"
                                   value="{{ old('support_email', $settings['support_email'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Regional Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-map-marker-alt me-2 text-primary"></i>Regional Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select name="timezone" id="timezone" class="form-select">
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $settings['timezone'] ?? 'UTC') === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="currency" class="form-label">Currency Code</label>
                            <select name="currency" id="currency" class="form-select">
                                <option value="USD" {{ old('currency', $settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency', $settings['currency'] ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ old('currency', $settings['currency'] ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP</option>
                                <option value="INR" {{ old('currency', $settings['currency'] ?? 'USD') === 'INR' ? 'selected' : '' }}>INR</option>
                                <option value="BRL" {{ old('currency', $settings['currency'] ?? 'USD') === 'BRL' ? 'selected' : '' }}>BRL</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="currency_symbol" class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" id="currency_symbol"
                                   class="form-control"
                                   value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}"
                                   maxlength="5">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Limits --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-sliders-h me-2 text-primary"></i>Transaction Limits</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="min_deposit" class="form-label">Minimum Deposit</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="min_deposit" id="min_deposit"
                                       class="form-control"
                                       value="{{ old('min_deposit', $settings['min_deposit'] ?? 1.00) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="min_order_value" class="form-label">Minimum Order Value</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="min_order_value" id="min_order_value"
                                       class="form-control"
                                       value="{{ old('min_order_value', $settings['min_order_value'] ?? 0.50) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="refill_days" class="form-label">Refill Period (Days)</label>
                            <input type="number" name="refill_days" id="refill_days"
                                   class="form-control"
                                   value="{{ old('refill_days', $settings['refill_days'] ?? 30) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Status --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-power-off me-2 text-primary"></i>System Status</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode"
                                       id="maintenance_mode" value="1"
                                       {{ old('maintenance_mode', $settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">
                                    <strong>Maintenance Mode</strong>
                                    <div class="text-muted small">Only admins can access the site</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="registration_enabled"
                                       id="registration_enabled" value="1"
                                       {{ old('registration_enabled', $settings['registration_enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="registration_enabled">
                                    <strong>Registration Enabled</strong>
                                    <div class="text-muted small">Allow new user signups</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="email_verification"
                                       id="email_verification" value="1"
                                       {{ old('email_verification', $settings['email_verification'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verification">
                                    <strong>Email Verification</strong>
                                    <div class="text-muted small">Require email confirmation</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="api_enabled"
                                       id="api_enabled" value="1"
                                       {{ old('api_enabled', $settings['api_enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="api_enabled">
                                    <strong>API Access</strong>
                                    <div class="text-muted small">Enable API for resellers</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fa fa-clock me-1"></i>
                            Last updated: {{ $lastUpdated ?? 'Never' }}
                        </div>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
