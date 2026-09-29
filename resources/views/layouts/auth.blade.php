<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" id="auth-html">

<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Authentication - {{ setting('app_name') }}">
    <meta name="author" content="{{ setting('app_name') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="{{ setting('app_icon') ? asset('storage/' . setting('app_icon')) : asset('dashboard/assets/icons/favicon.svg') }}">
    <link rel="icon" type="image/png"
        href="{{ setting('app_icon') ? asset('storage/' . setting('app_icon')) : asset('dashboard/assets/icons/favicon.png') }}">

    <!-- Title -->
    <title>@yield('title', 'Login') - {{ setting('app_name') }}</title>

    <!-- Theme Color -->
    <meta name="theme-color" content="#2c7eb8">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    <!-- Stylesheets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional styles -->
    @stack('styles')
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Left Side - Branding -->
            <div class="auth-branding d-none d-lg-flex {{ request()->is('vendor/register') ? 'd-lg-none' : '' }}">
                <div class="auth-branding-content">
                    <a href="{{ route('dashboard') }}" class="auth-logo mb-4">
                        <img src="{{ setting('app_logo') ? asset('storage/' . setting('app_logo')) : asset('dashboard/assets/images/logo.svg') }}"
                            alt="Logo" height="48">
                        <h1 class="h3 mb-0 fw-bold text-white mt-3">{{ setting('app_name') }}</h1>
                    </a>
                    <h2 class="h4 text-white mb-3">@yield('branding-title', 'Welcome Back!')</h2>
                    <p class="text-white-50 mb-0">@yield('branding-description', 'Sign in to continue to your account')</p>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-form-wrapper border {{ request()->is('vendor/register') ? 'w-100' : '' }}">
                <div class="auth-form-container border-danger
                {{ request()->is('vendor/register') ? 'w-100 max-w-full' : '' }}">


                    <!-- Mobile Logo -->
                    <div class="text-center mb-4 d-lg-none ">
                        <a href="{{ route('dashboard') }}" class="d-inline-block">
                            <img src="{{ setting('app_logo') ? asset('storage/' . setting('app_logo')) : asset('dashboard/assets/images/logo.svg') }}"
                                alt="Logo" height="40">
                        </a>
                    </div>

                    <!-- Form Card -->
                    <div class="auth-card ">
                        <div class="auth-card-header">
                            <h3 class="auth-card-title">@yield('form-title', 'Sign In')</h3>
                            <p class="auth-card-subtitle text-muted">@yield('form-subtitle', 'Enter your credentials to access your account')</p>
                        </div>

                        <div class="auth-card-body">
                            <!-- Display Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Error!</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Display Success Messages -->
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    {{ session('status') }}
                                </div>
                            @endif

                            @yield('content')
                        </div>

                        <div class="auth-card-footer">
                            @yield('footer')
                        </div>
                    </div>

                    <!-- Additional Links -->
                    <div class="auth-footer-links text-center mt-4">
                        @yield('footer-links')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <!-- Theme Detection Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for saved theme preference or default to light
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.getElementById('auth-html');
            if (html) {
                html.setAttribute('data-bs-theme', savedTheme);
            }
        });
    </script>
</body>

</html>
