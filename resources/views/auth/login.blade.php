@extends('layouts.slim')
@section('title', 'Login - SMM Panel')
@section('content')
<div class="container">
    <div class="row justify-content-center my-5">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary">
                            <i class="fas fa-share-alt me-2"></i>SMM Panel
                        </h3>
                        <p class="text-muted mb-0">Welcome Back</p>
                    </div>
                    @include('partials.flash')
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                                       required autofocus value="{{ old('email') }}" placeholder="name@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0 @error('password') is-invalid @enderror"
                                       required placeholder="Enter your password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <a href="{{ route('password.request') }}" class="text-decoration-none">
                            <i class="fas fa-key me-1"></i>Forgot password?
                        </a>
                        <span class="mx-2 text-muted">|</span>
                        <a href="{{ route('register') }}" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Create account
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3 text-white">
                <small>&copy; {{ date('Y') }} SMM Panel. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>
@endsection
