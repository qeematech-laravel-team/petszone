@extends('layouts.auth')

@section('title', 'Reset Password')

@section('branding-title', 'Reset Password')
@section('branding-description', 'Create a new password for your account')

@section('form-title', 'Reset Password')
@section('form-subtitle', 'Enter your new password below')

@section('content')
<form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $token }}">

    <!-- Email Field -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>Email Address
        </label>
        <input type="email"
               class="form-control form-control-lg @error('email') is-invalid @enderror"
               id="email"
               name="email"
               value="{{ $email ?? old('email') }}"
               placeholder="Enter your email"
               required
               autofocus
               autocomplete="email"
               readonly>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password Field -->
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-2"></i>New Password
        </label>
        <div class="input-group">
            <input type="password"
                   class="form-control form-control-lg @error('password') is-invalid @enderror"
                   id="password"
                   name="password"
                   placeholder="Enter new password"
                   required
                   autocomplete="new-password">
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
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">
            <i class="bi bi-lock-fill me-2"></i>Confirm New Password
        </label>
        <div class="input-group">
            <input type="password"
                   class="form-control form-control-lg"
                   id="password_confirmation"
                   name="password_confirmation"
                   placeholder="Confirm new password"
                   required
                   autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
            </button>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-key me-2"></i>Reset Password
        </button>
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
        // Toggle password visibility
        function setupPasswordToggle(toggleBtnId, passwordInputId, iconId) {
            const toggleBtn = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(passwordInputId);
            const icon = document.getElementById(iconId);

            if (toggleBtn && passwordInput && icon) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
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
        setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'togglePasswordConfirmationIcon');

        // Form validation
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        if (resetPasswordForm) {
            resetPasswordForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;

                if (!password || !passwordConfirmation) {
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
            });
        }
    });
</script>
@endpush
