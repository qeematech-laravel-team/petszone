@extends('layouts.landing')

@section('title', __('Pricing'))

@section('content')
    <section class="border-bottom bg-body-tertiary">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h1 class="h3 fw-bold mb-2">{{ __('Pricing Plans') }}</h1>
                    <p class="text-muted mb-0">
                        {{ __('Choose a plan that fits your business. Upgrade anytime as you grow.') }}
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('vendor.register') }}" class="btn btn-primary">
                        <i class="bi bi-shop me-1"></i>{{ __('Register as Vendor') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container py-5">
            @if(($plans ?? collect())->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('No active plans found. Please contact the administrator.') }}
                </div>
            @else
                <div class="row g-3">
                    @foreach($plans as $plan)
                        @php
                            $isFeatured = (bool) ($plan->is_featured ?? false);
                            $priceValue = (float) $plan->getRawOriginal('price');
                        @endphp

                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 {{ $isFeatured ? 'border-primary shadow-sm' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $plan->getTranslation('name', app()->getLocale()) }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $plan->getTranslation('description', app()->getLocale()) }}
                                            </div>
                                        </div>
                                        @if($isFeatured)
                                            <span class="badge text-bg-primary">
                                                <i class="bi bi-stars me-1"></i>{{ __('Popular') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="my-4">
                                        <div class="display-6 fw-bold">
                                            {{ number_format($priceValue, 2) }} <span class="fs-6 text-muted">{{ setting('currency', 'EGP') }}</span>
                                        </div>
                                        <div class="text-muted">
                                            {{ __('Duration') }}: {{ (int) $plan->duration_days }} {{ __('days') }}
                                        </div>
                                    </div>

                                    <ul class="list-unstyled text-muted mb-4">
                                        <li class="d-flex gap-2 mb-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            <span>{{ __('Max products') }}: {{ (int) $plan->max_products_count }}</span>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            <span>
                                                {{ __('Product featuring') }}:
                                                {{ $plan->can_feature_products ? __('Enabled') : __('Disabled') }}
                                            </span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            <span>{{ __('Vendor dashboard + reports') }}</span>
                                        </li>
                                    </ul>

                                    <a href="{{ route('vendor.register') }}" class="btn {{ $isFeatured ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                                        <i class="bi bi-arrow-right me-1"></i>{{ __('Start with this plan') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-5">
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="fw-semibold mb-2">{{ __('What is included in every plan?') }}</div>
                                <ul class="text-muted mb-0">
                                    <li>{{ __('Access to vendor dashboard and product management') }}</li>
                                    <li>{{ __('Order management, invoices and refunds') }}</li>
                                    <li>{{ __('Earnings dashboard and withdrawal requests') }}</li>
                                    <li>{{ __('Reports: product performance, earnings, and more') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <div class="fw-semibold mb-1">{{ __('Need a custom plan?') }}</div>
                                    <div class="text-muted mb-3">
                                        {{ __('We can tailor limits and features to your business needs.') }}
                                    </div>
                                </div>
                                <a href="{{ route('vendor.register') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-chat-dots me-1"></i>{{ __('Contact us after registration') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

