@extends('layouts.auth')

@section('title', 'Reset Password')

@section('branding-title', 'Forgot Password?')
@section('branding-description', 'No worries, we\'ll send you reset instructions')

@section('form-title', 'Reset Password')
@section('form-subtitle', 'Enter your email address and we\'ll send you a link to reset your password')

@section('content')
<form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
    @csrf

    <!-- Email Field -->
    <div class="mb-4">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>Email Address
        </label>
        <input type="email"
               class="form-control form-control-lg @error('email') is-invalid @enderror"
               id="email"
               name="email"
               value="{{ old('email') }}"
               placeholder="Enter your email address"
               required
               autofocus
               autocomplete="email">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">We'll send a password reset link to this email address</small>
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-send me-2"></i>Send Reset Link
        </button>
    </div>

    <!-- Back to Login -->
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Back to Sign In
        </a>
    </div>
</form>
@endsection

@section('footer')
<div class="text-center">
    <p class="text-muted mb-0">
        Remember your password?
        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in</a>
    </p>
</div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
            forgotPasswordForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;

                if (!email) {
                    e.preventDefault();
                    alert('Please enter your email address');
                    return false;
                }

                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return false;
                }
            });
        }
    });
</script>
@endpush
