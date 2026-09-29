<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('Multi-vendor e-commerce platform for vendors and admins') }}">

    <link rel="icon" type="image/svg+xml" href="{{ setting('app_icon') ? asset('storage/' . setting('app_icon')) : asset('dashboard/assets/icons/favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ setting('app_icon') ? asset('storage/' . setting('app_icon')) : asset('dashboard/assets/icons/favicon.png') }}">

    <title>@yield('title', __('Home')) - {{ setting('app_name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}">
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <img src="{{ setting('app_logo') ? asset('storage/' . setting('app_logo')) : asset('dashboard/assets/images/logo.svg') }}" alt="Logo" height="28">
            <span class="fw-semibold">{{ setting('app_name') }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('landing.features') ? 'active' : '' }}" href="{{ route('landing.features') }}">{{ __('Features') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('landing.pricing') ? 'active' : '' }}" href="{{ route('landing.pricing') }}">{{ __('Pricing') }}</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-translate me-1"></i>{{ app()->getLocale() === 'ar' ? __('Arabic') : __('English') }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'en']) }}">English</a></li>
                        <li><a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'ar']) }}">العربية</a></li>
                    </ul>
                </li>

                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-secondary" href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('Login') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="{{ route('vendor.register') }}">
                        <i class="bi bi-shop me-1"></i>{{ __('Register as Vendor') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="border-top bg-body-tertiary">
    <div class="container py-4 d-flex flex-column flex-lg-row justify-content-between gap-2">
        <div class="text-muted">
            © {{ now()->year }} {{ setting('app_name') }} — {{ __('All rights reserved.') }}
        </div>
        <div class="d-flex gap-3">
            <a class="text-decoration-none" href="{{ route('landing.features') }}">{{ __('Features') }}</a>
            <a class="text-decoration-none" href="{{ route('landing.pricing') }}">{{ __('Pricing') }}</a>
            <a class="text-decoration-none" href="{{ route('vendor.register') }}">{{ __('Vendor Register') }}</a>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>

