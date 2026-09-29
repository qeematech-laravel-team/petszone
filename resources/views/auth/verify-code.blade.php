@extends('layouts.auth')

@section('title', 'Verify Code')

@section('branding-title', 'Verify Your Email')
@section('branding-description', 'Enter the verification code sent to your email')

@section('form-title', 'Verify Code')
@section('form-subtitle', 'Enter the verification code sent to your email')

@section('content')
    <form method="POST" action="{{ route('auth.verification.submit') }}" id="verifyCodeForm">
        @csrf



        <!-- Code Field -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="code" class="form-label mb-0">
                    <i class="bi bi-lock me-2"></i>Code
                </label>
                <a href="{{ route('auth.verification.resend') }}" class="text-decoration-none small">
                    Resend code
                </a>
            </div>

            <div class="input-group">
                <input type="text" class="form-control form-control-lg @error('code') is-invalid @enderror"
                    id="code" name="code" placeholder="Enter your code" required autocomplete="code">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if (session('email'))
                <input type="hidden" name="email" value="{{ session('email') }}">
                <p class="text-gray-700 text-center">
                    We sent a verification code to <strong>{{ session('email') }}</strong>
                </p>
            @else
                <div class="mt-4">
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
            @endif
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Verify Code
            </button>
        </div>
    </form>
@endsection
