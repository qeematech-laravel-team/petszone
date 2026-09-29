@extends('layouts.app')

@php
    $page = 'dashboard';
@endphp

@section('title', __('Branch Dashboard'))

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
                <h1 class="h3 mb-0">{{ __('Branch Dashboard') }}</h1>
                <p class="text-muted mb-0">{{ __('Welcome to :branch branch', ['branch' => $branch->name]) }}</p>
            </div>
        </div>

        <!-- Branch Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('Branch Information') }}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>{{ __('Branch Name') }}:</strong> {{ $branch->name }}
                        </p>
                        <p class="mb-2">
                            <strong>{{ __('Address') }}:</strong> {{ $branch->address ?? __('Not specified') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>{{ __('Phone') }}:</strong> {{ $branch->phone ?? __('Not specified') }}
                        </p>
                        <p class="mb-2">
                            <strong>{{ __('Status') }}:</strong>
                            @if($branch->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">{{ __('Branch Products') }}</h6>
                                <h3 class="mb-0">{{ $branchProductsCount }}</h3>
                                <small class="text-muted">{{ __('Products in this branch') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-archive"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">{{ __('Total Stock') }}</h6>
                                <h3 class="mb-0">{{ number_format($totalStock) }}</h3>
                                <small class="text-muted">{{ __('Total items in stock') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">{{ __('Low Stock') }}</h6>
                                <h3 class="mb-0">{{ $lowStockCount }}</h3>
                                <small class="text-muted">{{ __('Items need restocking') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">{{ __('Out of Stock') }}</h6>
                                <h3 class="mb-0">{{ $outOfStockCount }}</h3>
                                <small class="text-muted">{{ __('Items unavailable') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('Quick Actions') }}</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('vendor.branches.show', $branch->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye me-2"></i>{{ __('View Branch Details') }}
                    </a>
                    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-box-seam me-2"></i>{{ __('Manage Products') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
