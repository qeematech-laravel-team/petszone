@extends('layouts.auth')

@section('title', 'Register')

@section('branding-title', 'Create Account As Vendor!')
@section('branding-description', 'Join us today and start your journey')

@section('form-title', 'Sign Up')
@section('form-subtitle', 'Create a new account to get started')

@section('content')
    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <!-- Name Field -->
        <div class="mb-3">
            <label for="name" class="form-label">
                <i class="bi bi-person me-2"></i>Full Name
            </label>
            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus
                autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Field -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email Address
            </label>
            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" placeholder="Enter your email" required autocomplete="email">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-2"></i>Password
            </label>
            <div class="input-group">
                <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                    id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <small class="text-muted">Must be at least 8 characters</small>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                <i class="bi bi-lock-fill me-2"></i>Confirm Password
            </label>
            <div class="input-group">
                <input type="password" class="form-control form-control-lg" id="password_confirmation"
                    name="password_confirmation" placeholder="Confirm your password" required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                    <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
                </button>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms"
                    id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
                    and <a href="#" class="text-decoration-none">Privacy Policy</a>
                </label>
                @error('terms')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </div>
    </form>
@endsection

@section('footer')
    <div class="text-center">
        <p class="text-muted mb-0">
            Already have an account?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in</a>
        </p>
    </div>
@endsection



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            function setupPasswordToggle(toggleBtnId, passwordInputId, iconId) {
                const toggleBtn = document.getElementById(toggleBtnId);
                const passwordInput = document.getElementById(passwordInputId);
                const icon = document.getElementById(iconId);

                if (toggleBtn && passwordInput && icon) {
                    toggleBtn.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' :
                        'password';
                        passwordInput.setAttribute('type', type);

                        if (type === 'password') {
                            icon.classList.remove('bi-eye-slash');
                            icon.classList.add('bi-eye');
                        } else {
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        }
                    });
                }
            }

            setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
            setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation',
                'togglePasswordConfirmationIcon');

            // Form validation
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value;
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const passwordConfirmation = document.getElementById('password_confirmation').value;
                    const terms = document.getElementById('terms').checked;

                    if (!name || !email || !password || !passwordConfirmation) {
                        e.preventDefault();
                        alert('Please fill in all required fields');
                        return false;
                    }

                    if (password !== passwordConfirmation) {
                        e.preventDefault();
                        alert('Passwords do not match');
                        return false;
                    }

                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long');
                        return false;
                    }

                    if (!terms) {
                        e.preventDefault();
                        alert('Please agree to the Terms and Conditions');
                        return false;
                    }
                });
            }
        });
    </script>
@endpush
