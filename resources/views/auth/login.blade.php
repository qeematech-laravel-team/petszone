@extends('layouts.auth')

@section('title', 'Login')

@section('branding-title', 'Welcome Back!')
@section('branding-description', 'Sign in to continue to your account')

@section('form-title', 'Sign In')
@section('form-subtitle', 'Enter your credentials to access your account')

@section('content')
    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Email Field -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email Address
            </label>
            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus
                autocomplete="email">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="password" class="form-label mb-0">
                    <i class="bi bi-lock me-2"></i>Password
                </label>
                <a href="{{ route('password.request') }}" class="text-decoration-none small">
                    Forgot password?
                </a>
            </div>
            <div class="input-group">
                <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                    id="password" name="password" placeholder="Enter your password" required
                    autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </div>
    </form>
@endsection

@section('footer')
    <div class="text-center">
        <p class="text-muted mb-0">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Sign up</a>
        </p>
    </div>
@endsection



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePassword && passwordInput && togglePasswordIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle icon
                    if (type === 'password') {
                        togglePasswordIcon.classList.remove('bi-eye-slash');
                        togglePasswordIcon.classList.add('bi-eye');
                    } else {
                        togglePasswordIcon.classList.remove('bi-eye');
                        togglePasswordIcon.classList.add('bi-eye-slash');
                    }
                });
            }

            // Form validation
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;

                    if (!email || !password) {
                        e.preventDefault();
                        alert('Please fill in all required fields');
                        return false;
                    }
                });
            }
        });
    </script>
@endpush
