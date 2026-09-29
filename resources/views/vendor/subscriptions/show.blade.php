@extends('layouts.app')

@php
    $page = 'subscriptions';
@endphp

@section('title', __('Subscription Details'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.subscriptions.index') }}">{{ __('Subscriptions') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Subscription Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ __('Subscription Details') }}</h1>
                <p class="text-muted mb-0">
                    @php
                        $now = now();
                        $startDate = \Carbon\Carbon::parse($subscription->start_date);
                        $endDate = \Carbon\Carbon::parse($subscription->end_date);
                        $isActive = $now->between($startDate, $endDate);
                        $isExpired = $now->gt($endDate);
                    @endphp
                    <span class="badge bg-{{ $isActive ? 'success' : ($isExpired ? 'danger' : 'secondary') }} me-2">
                        <i class="bi bi-{{ $isActive ? 'check-circle' : ($isExpired ? 'x-circle' : 'clock') }} me-1"></i>
                        {{ $isActive ? __('Active') : ($isExpired ? __('Expired') : __('Inactive')) }}
                    </span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.subscriptions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 rounded">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-tag fs-4 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">{{ __('Plan') }}</small>
                                        <strong class="d-block">
                                            @if($subscription->plan)
                                                {{ $subscription->plan->getTranslation('name', app()->getLocale()) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex align-items-center p-3 rounded">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-currency-dollar fs-4 text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">{{ __('Price') }}</small>
                                        <strong class="d-block">{{ number_format($subscription->price, 2) }} {{ setting('currency') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Subscription Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-info-circle text-primary me-2"></i>{{ __('Subscription Information') }}
                        </h5>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border-start border-success border-3 ps-3">
                                    <label class="form-label text-muted small mb-1">{{ __('Plan') }}</label>
                                    <p class="mb-0 fw-semibold">
                                        @if($subscription->plan)
                                            {{ $subscription->plan->getTranslation('name', app()->getLocale()) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-info border-3 ps-3">
                                    <label class="form-label text-muted small mb-1">{{ __('Start Date') }}</label>
                                    <p class="mb-0 fw-semibold">
                                        {{ \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') }}
                                    </p>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($subscription->start_date)->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-warning border-3 ps-3">
                                    <label class="form-label text-muted small mb-1">{{ __('End Date') }}</label>
                                    <p class="mb-0 fw-semibold">
                                        {{ \Carbon\Carbon::parse($subscription->end_date)->format('M d, Y') }}
                                    </p>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($subscription->end_date)->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-success border-3 ps-3">
                                    <label class="form-label text-muted small mb-1">{{ __('Price') }}</label>
                                    <p class="mb-0 fw-semibold">{{ number_format($subscription->price, 2) }} {{ setting('currency') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-danger border-3 ps-3">
                                    <label class="form-label text-muted small mb-1">{{ __('Commission Rate') }}</label>
                                    <p class="mb-0 fw-semibold">{{ number_format($subscription->commission_rate, 2) }}%</p>
                                </div>
                            </div>
                            @if($subscription->plan)
                                <div class="col-md-6">
                                    <div class="border-start border-secondary border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Duration') }}</label>
                                        <p class="mb-0 fw-semibold">{{ $subscription->plan->duration_days }} {{ __('Days') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-info border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Max Products') }}</label>
                                        <p class="mb-0 fw-semibold">
                                            @if($subscription->plan->max_products_count)
                                                {{ $subscription->plan->max_products_count }}
                                            @else
                                                <span class="text-muted">{{ __('Unlimited') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Plan Details -->
                @if($subscription->plan)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-tag text-success me-2"></i>{{ __('Plan Details') }}
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="border-start border-primary border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Plan Name (English)') }}</label>
                                        <p class="mb-0 fw-semibold">{{ $subscription->plan->getTranslation('name', 'en') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-success border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Plan Name (Arabic)') }}</label>
                                        <p class="mb-0 fw-semibold">{{ $subscription->plan->getTranslation('name', 'ar') }}</p>
                                    </div>
                                </div>
                                @if($subscription->plan->description)
                                    <div class="col-12">
                                        <div class="border-start border-secondary border-3 ps-3">
                                            <label class="form-label text-muted small mb-2">{{ __('Description (English)') }}</label>
                                            <p class="mb-3">{{ $subscription->plan->getTranslation('description', 'en') ?: '-' }}</p>
                                            <label class="form-label text-muted small mb-2">{{ __('Description (Arabic)') }}</label>
                                            <p class="mb-0">{{ $subscription->plan->getTranslation('description', 'ar') ?: '-' }}</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="border-start border-info border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Plan Price') }}</label>
                                        <p class="mb-0 fw-semibold">{{ $subscription->plan->price }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-warning border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Duration') }}</label>
                                        <p class="mb-0 fw-semibold">{{ $subscription->plan->duration_days }} {{ __('Days') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-success border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Can Feature Products') }}</label>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $subscription->plan->can_feature_products ? 'success' : 'secondary' }}">
                                                {{ $subscription->plan->can_feature_products ? __('Yes') : __('No') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-danger border-3 ps-3">
                                        <label class="form-label text-muted small mb-1">{{ __('Max Products Count') }}</label>
                                        <p class="mb-0 fw-semibold">
                                            @if($subscription->plan->max_products_count)
                                                {{ $subscription->plan->max_products_count }}
                                            @else
                                                <span class="text-muted">{{ __('Unlimited') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Timestamps -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-clock-history text-secondary me-2"></i>{{ __('Timestamps') }}
                        </h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                            <i class="bi bi-calendar-plus text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">{{ __('Created At') }}</div>
                                        <div class="fw-semibold">{{ $subscription->created_at->format('M d, Y H:i') }}</div>
                                        <div class="small text-muted">{{ $subscription->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                            <i class="bi bi-pencil-square text-info"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">{{ __('Updated At') }}</div>
                                        <div class="fw-semibold">{{ $subscription->updated_at->format('M d, Y H:i') }}</div>
                                        <div class="small text-muted">{{ $subscription->updated_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-toggle-on text-primary me-2"></i>{{ __('Status') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @php
                                $now = now();
                                $startDate = \Carbon\Carbon::parse($subscription->start_date);
                                $endDate = \Carbon\Carbon::parse($subscription->end_date);
                                $isActive = $now->between($startDate, $endDate);
                                $isExpired = $now->gt($endDate);
                                $daysRemaining = $now->diffInDays($endDate, false);
                            @endphp
                            <div class="mb-3">
                                @if($isActive)
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                    <h4 class="mt-2 text-success">{{ __('Active') }}</h4>
                                    @if($daysRemaining > 0)
                                        <p class="text-muted mb-0">{{ __('Days Remaining') }}: <strong>{{ round($daysRemaining) }}</strong></p>
                                    @endif
                                @elseif($isExpired)
                                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                                    <h4 class="mt-2 text-danger">{{ __('Expired') }}</h4>
                                    <p class="text-muted mb-0">{{ __('Expired') }} {{ abs($daysRemaining) }} {{ __('days ago') }}</p>
                                @else
                                    <i class="bi bi-clock-fill text-secondary" style="font-size: 3rem;"></i>
                                    <h4 class="mt-2 text-secondary">{{ __('Inactive') }}</h4>
                                    <p class="text-muted mb-0">{{ __('Starts in') }} {{ round($daysRemaining) }} {{ __('days') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <span class="fw-semibold">{{ __('Start Date') }}</span>
                                    </div>
                                    <span class="badge bg-primary">{{ \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-calendar-x text-danger me-2"></i>
                                        <span class="fw-semibold">{{ __('End Date') }}</span>
                                    </div>
                                    <span class="badge bg-danger">{{ \Carbon\Carbon::parse($subscription->end_date)->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-charge text-warning me-2"></i>{{ __('Quick Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('vendor.subscriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Subscriptions') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subscription Stats -->
                <div class="card shadow-sm border-0">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up text-success me-2"></i>{{ __('Statistics') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $startDate = \Carbon\Carbon::parse($subscription->start_date);
                            $endDate = \Carbon\Carbon::parse($subscription->end_date);
                            $totalDays = $startDate->diffInDays($endDate);
                            $now = now();
                            $elapsedDays = $now->diffInDays($startDate);
                            $daysRemaining = $now->diffInDays($endDate, false);
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">{{ __('Total Duration') }}</small>
                                <strong class="fs-5">{{ $totalDays }} {{ __('Days') }}</strong>
                            </div>
                            <i class="bi bi-calendar-range fs-3 text-primary"></i>
                        </div>
                        @if($isActive)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <small class="text-muted d-block">{{ __('Days Remaining') }}</small>
                                    <strong class="fs-5 text-success">{{ round($daysRemaining) }} {{ __('Days') }}</strong>
                                </div>
                                <i class="bi bi-hourglass-split fs-3 text-success"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
