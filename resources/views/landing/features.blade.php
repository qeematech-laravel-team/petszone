@extends('layouts.landing')

@section('title', __('Features'))

@section('content')
    <section class="border-bottom bg-body-tertiary">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h1 class="h3 fw-bold mb-2">{{ __('System Features') }}</h1>
                    <p class="text-muted mb-0">
                        {{ __('Everything you need to operate a multi-vendor marketplace, with structured workflows and business-ready dashboards.') }}
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
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-box-seam text-primary fs-4"></i>
                                <div class="fw-semibold">{{ __('Products & inventory') }}</div>
                            </div>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Simple & variable products') }}</li>
                                <li>{{ __('Branches and stock per branch') }}</li>
                                <li>{{ __('Low stock alerts & thresholds') }}</li>
                                <li>{{ __('Product recommendations (related products)') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-receipt text-success fs-4"></i>
                                <div class="fw-semibold">{{ __('Orders & operations') }}</div>
                            </div>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Split orders by vendor') }}</li>
                                <li>{{ __('Status workflow with validations') }}</li>
                                <li>{{ __('Refund requests and processing') }}</li>
                                <li>{{ __('PDF invoices (admin & vendor)') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-wallet2 text-warning fs-4"></i>
                                <div class="fw-semibold">{{ __('Payments & settlements') }}</div>
                            </div>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Immediate pay method') }}</li>
                                <li>{{ __('Vendor balances & transactions') }}</li>
                                <li>{{ __('Withdrawals (vendor requests + admin approval)') }}</li>
                                <li>{{ __('Commission support (profit type)') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-graph-up text-info fs-4"></i>
                                <div class="fw-semibold">{{ __('Reports & analytics') }}</div>
                            </div>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Admin & vendor KPIs with date/status filters') }}</li>
                                <li>{{ __('Earnings dashboards') }}</li>
                                <li>{{ __('Product performance dashboards') }}</li>
                                <li>{{ __('Vendor performance dashboards + share-of-platform metrics') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-check text-secondary fs-4"></i>
                                <div class="fw-semibold">{{ __('Security & governance') }}</div>
                            </div>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Roles & permissions (vendor employees supported)') }}</li>
                                <li>{{ __('Email + database notifications') }}</li>
                                <li>{{ __('Ratings & reports moderation') }}</li>
                                <li>{{ __('Customer management (admin + vendor scoped)') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-top">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h2 class="h4 fw-bold mb-3">{{ __('For marketplace owners') }}</h2>
                    <ul class="text-muted mb-0">
                        <li>{{ __('Define commission logic once and apply per vendor') }}</li>
                        <li>{{ __('See platform-level KPIs, vendor rankings and product performance') }}</li>
                        <li>{{ __('Control visibility of ratings and handle reports centrally') }}</li>
                        <li>{{ __('Monitor withdrawals, refunds, and order logs for auditability') }}</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h2 class="h4 fw-bold mb-3">{{ __('For vendors') }}</h2>
                    <ul class="text-muted mb-0">
                        <li>{{ __('Full dashboard for products, stock, orders and customers') }}</li>
                        <li>{{ __('Clear earnings dashboard and withdrawal workflow') }}</li>
                        <li>{{ __('Inventory alerts to avoid overselling') }}</li>
                        <li>{{ __('Reports to understand top products and customers') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-body-tertiary border-top">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 class="h4 fw-bold mb-2">{{ __('Ready to join as a vendor?') }}</h2>
                    <p class="text-muted mb-0">{{ __('Create your vendor account and start selling with full operational support.') }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('vendor.register') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop me-1"></i>{{ __('Register as Vendor') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

