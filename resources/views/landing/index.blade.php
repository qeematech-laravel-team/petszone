@extends('layouts.landing')

@section('title', __('Home'))

@section('content')
    <section class="bg-body-tertiary border-bottom">
        <div class="container py-5 py-lg-6">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="badge text-bg-primary-subtle text-primary-emphasis border border-primary-subtle mb-3">
                        <i class="bi bi-stars me-1"></i>{{ __('Multi-vendor commerce, done right') }}
                    </span>
                    <h1 class="display-6 fw-bold mb-3">
                        {{ __('Grow your marketplace with a structured, scalable multi-vendor system') }}
                    </h1>
                    <p class="lead text-muted mb-4">
                        {{ __('Powerful vendor dashboards, commission logic, order workflows, refunds, withdrawals, analytics and more — built for real operations.') }}
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('vendor.register') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-shop me-1"></i>{{ __('Register as Vendor') }}
                        </a>
                        <a href="{{ route('landing.features') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-grid-3x3-gap me-1"></i>{{ __('Explore Features') }}
                        </a>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4 text-muted">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check"></i>
                            <span>{{ __('Role & permission based access') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up"></i>
                            <span>{{ __('Business analytics dashboards') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-translate"></i>
                            <span>{{ __('Arabic / English ready') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-3 p-2">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('Designed for multi-vendor scale') }}</div>
                                            <div class="text-muted small">{{ __('Orders split by vendor, with clear logs and workflows') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success-subtle text-success rounded-3 p-2">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('Earnings & withdrawals') }}</div>
                                            <div class="text-muted small">{{ __('Vendor balance tracking, withdrawal requests, approvals') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-warning-subtle text-warning rounded-3 p-2">
                                            <i class="bi bi-bell"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('Notifications') }}</div>
                                            <div class="text-muted small">{{ __('Email + database notifications for key actions') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-info-subtle text-info rounded-3 p-2">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('Inventory alerts') }}</div>
                                            <div class="text-muted small">{{ __('Low stock thresholds and alerts for admins and vendors') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small">{{ __('Get started in minutes') }}</div>
                                    <div class="fw-semibold">{{ __('Create your vendor account and submit products') }}</div>
                                </div>
                                <a href="{{ route('vendor.register') }}" class="btn btn-primary">
                                    {{ __('Start Now') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h2 class="h3 fw-bold mb-2">{{ __('What you get') }}</h2>
                    <p class="text-muted mb-3">
                        {{ __('A complete operational system for admins and vendors: product management, orders, refunds, withdrawals, analytics, and compliance tools.') }}
                    </p>

                    <div class="card border-0 bg-body-tertiary">
                        <div class="card-body">
                            <h3 class="h6 fw-semibold mb-3">{{ __('Who is this for?') }}</h3>
                            <ul class="text-muted mb-0">
                                <li>{{ __('Marketplace owners who manage multiple vendors') }}</li>
                                <li>{{ __('Retail brands with branches and separate vendors') }}</li>
                                <li>{{ __('Teams that need clear roles, permissions and auditability') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-receipt text-primary fs-4"></i>
                                        <div class="fw-semibold">{{ __('Order workflows & invoices') }}</div>
                                    </div>
                                    <div class="text-muted">
                                        {{ __('Structured statuses, vendor order sync, PDF invoices, and detailed actions history.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-cash-coin text-success fs-4"></i>
                                        <div class="fw-semibold">{{ __('Commission & settlements') }}</div>
                                    </div>
                                    <div class="text-muted">
                                        {{ __('Commission-based profit type support, vendor balances, and transaction history.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-graph-up-arrow text-info fs-4"></i>
                                        <div class="fw-semibold">{{ __('Analytics dashboards') }}</div>
                                    </div>
                                    <div class="text-muted">
                                        {{ __('Sales KPIs, earnings dashboards, product & vendor performance with filters.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-star-half text-warning fs-4"></i>
                                        <div class="fw-semibold">{{ __('Ratings & reports') }}</div>
                                    </div>
                                    <div class="text-muted">
                                        {{ __('Product & vendor rating/report moderation for admins and vendors.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('landing.features') }}" class="text-decoration-none">
                            {{ __('See all features') }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('landing.pricing') }}" class="text-decoration-none">
                            {{ __('View pricing plans') }} <i class="bi bi-currency-exchange ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-top">
        <div class="container py-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <h2 class="h4 fw-bold mb-3">{{ __('How it works') }}</h2>
                    <p class="text-muted mb-0">
                        {{ __('From vendor registration to payouts, each step is guided and logged so you can scale with confidence.') }}
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 bg-body-tertiary">
                                <div class="card-body">
                                    <div class="badge text-bg-primary mb-2">1</div>
                                    <div class="fw-semibold mb-1">{{ __('Vendors register') }}</div>
                                    <div class="text-muted small">
                                        {{ __('Vendors submit their details and get verified by admin.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 bg-body-tertiary">
                                <div class="card-body">
                                    <div class="badge text-bg-primary mb-2">2</div>
                                    <div class="fw-semibold mb-1">{{ __('Products & inventory') }}</div>
                                    <div class="text-muted small">
                                        {{ __('Vendors add products, branches and stock with clear limits.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 bg-body-tertiary">
                                <div class="card-body">
                                    <div class="badge text-bg-primary mb-2">3</div>
                                    <div class="fw-semibold mb-1">{{ __('Orders, refunds, payouts') }}</div>
                                    <div class="text-muted small">
                                        {{ __('System handles orders, refunds and vendor withdrawals with logs.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-body-tertiary border-top border-bottom">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h2 class="h3 fw-bold mb-2">{{ __('Register as a vendor') }}</h2>
                    <p class="text-muted mb-0">
                        {{ __('Create your vendor account, verify email, then manage products, branches, orders, customers, and earnings from one dashboard.') }}
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                        <a href="{{ route('vendor.register') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-shop me-1"></i>{{ __('Register as Vendor') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('Login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h2 class="h4 fw-bold mb-3">{{ __('Frequently asked questions') }}</h2>
                    <p class="text-muted mb-0">
                        {{ __('Short answers to the most common questions from vendors and marketplace owners.') }}
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="accordion" id="landingFaq">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqOneHeading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                                    {{ __('How do vendors get paid?') }}
                                </button>
                            </h2>
                            <div id="faqOne" class="accordion-collapse collapse show" aria-labelledby="faqOneHeading"
                                 data-bs-parent="#landingFaq">
                                <div class="accordion-body">
                                    {{ __('The system tracks vendor balances for each order, supports immediate pay, and lets vendors request withdrawals that admins can approve or reject with full history.') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqTwoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                    {{ __('Can admins control ratings and reports?') }}
                                </button>
                            </h2>
                            <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqTwoHeading"
                                 data-bs-parent="#landingFaq">
                                <div class="accordion-body">
                                    {{ __('Yes, admins and vendors can moderate product and vendor ratings, and handle customer reports with statuses like reviewed or ignored.') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqThreeHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                    {{ __('Is the system ready for Arabic and English?') }}
                                </button>
                            </h2>
                            <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqThreeHeading"
                                 data-bs-parent="#landingFaq">
                                <div class="accordion-body">
                                    {{ __('The whole system supports Arabic and English, including RTL layout and translatable content for products, plans and vendors.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

