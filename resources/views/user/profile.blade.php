@extends('user.layout')
@section('title', 'Profile - KYXTRO SMM')

@section('user_content')
<h5 class="fw-semibold mb-4"><i class="fa-solid fa-user me-1 text-primary"></i> My Profile</h5>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Personal Information</h6>
            </div>
            <div class="card-body">
                @include('partials.flash')
                <form method="POST" action="{{ route('user.profile') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="UTC" {{ auth()->user()->profile?->timezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Asia/Kolkata" {{ auth()->user()->profile?->timezone === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                <option value="America/New_York" {{ auth()->user()->profile?->timezone === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                <option value="Europe/London" {{ auth()->user()->profile?->timezone === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Language</label>
                            <select name="language" class="form-select">
                                <option value="en" {{ auth()->user()->profile?->language === 'en' ? 'selected' : '' }}>English</option>
                                <option value="tr" {{ auth()->user()->profile?->language === 'tr' ? 'selected' : '' }}>Türkçe</option>
                                <option value="ar" {{ auth()->user()->profile?->language === 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="ru" {{ auth()->user()->profile?->language === 'ru' ? 'selected' : '' }}>Русский</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-save me-1"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.change-password') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary mt-2"><i class="fa-solid fa-key me-1"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white;">
            <div class="card-body text-center">
                <i class="fa-solid fa-wallet fa-2x mb-2 opacity-50"></i>
                <h6 class="opacity-75">Current Balance</h6>
                <h2 class="fw-bold mb-0">${{ number_format(auth()->user()->balance, 2) }}</h2>
                <a href="{{ route('user.deposit') }}" class="btn btn-light btn-sm mt-2 w-100">
                    <i class="fa-solid fa-plus me-1"></i> Add Funds
                </a>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Account Summary</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="border-0 text-muted">Email</td><td class="border-0 text-end">{{ auth()->user()->email }}</td></tr>
                    <tr><td class="border-0 text-muted">Registered</td><td class="border-0 text-end">{{ auth()->user()->created_at->format('M d, Y') }}</td></tr>
                    <tr><td class="border-0 text-muted">Total Deposited</td><td class="border-0 text-end fw-bold text-success">${{ number_format(auth()->user()->total_deposited ?? 0, 2) }}</td></tr>
                    <tr><td class="border-0 text-muted">Total Orders</td><td class="border-0 text-end">{{ auth()->user()->orders()->count() }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
