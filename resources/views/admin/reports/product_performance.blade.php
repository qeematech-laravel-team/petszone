@extends('layouts.app')

@php
    $page = 'reports';
@endphp

@section('title', __('Product Performance'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('Reports & Analytics') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Product Performance') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ __('Product Performance') }}</h1>
            <p class="text-muted mb-0">{{ __('Revenue, quantity, and net after commission (filtered)') }}</p>
        </div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
            <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
        </button>
    </div>

    @php
        $k = $report['kpis'];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Revenue (filtered)') }}</div>
                            <div class="fs-4 fw-semibold">{{ number_format($k['revenue'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('Orders') }}: {{ $k['orders_count'] ?? 0 }}</small>
                        </div>
                        <div class="text-success fs-4"><i class="bi bi-cash-stack"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Quantity Sold') }}</div>
                            <div class="fs-4 fw-semibold">{{ $k['quantity'] ?? 0 }}</div>
                        </div>
                        <div class="text-primary fs-4"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Net (after commission)') }}</div>
                            <div class="fs-4 fw-semibold">{{ number_format($k['net'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('Commission') }}: {{ number_format($k['commission'] ?? 0, 2) }}</small>
                        </div>
                        <div class="text-info fs-4"><i class="bi bi-graph-up"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Refunded') }}</div>
                            <div class="fs-4 fw-semibold">{{ number_format($k['refunded_amount'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('Refunded Orders') }}: {{ $k['refunded_orders'] ?? 0 }}</small>
                        </div>
                        <div class="text-danger fs-4"><i class="bi bi-arrow-counterclockwise"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Daily Paid Sales') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Revenue') }}</th>
                        <th class="text-end">{{ __('Qty') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($report['daily_sales'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="text-end">{{ number_format($row['total'], 2) }} {{ setting('currency', 'EGP') }}</td>
                            <td class="text-end">{{ $row['qty'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ __('No data.') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('modals')
        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filterOffcanvasLabel">{{ __('Filter Product Performance') }}</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <form method="GET" action="{{ route('admin.reports.product-performance') }}" id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Product') }}</label>
                        <select class="form-select" name="product_id">
                            <option value="">{{ __('All Products') }}</option>
                            @foreach(($products ?? []) as $p)
                                <option value="{{ $p->id }}" {{ (string) ($filters['product_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>
                                    #{{ $p->id }} - {{ $p->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Category') }}</label>
                        <select class="form-select" name="category_id">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach(($categories ?? []) as $category)
                                <option value="{{ $category->id }}" {{ (string) ($filters['category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                </option>
                                @if($category->children && $category->children->count() > 0)
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ (string) ($filters['category_id'] ?? '') === (string) $child->id ? 'selected' : '' }}>
                                            — {{ $child->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Vendor') }}</label>
                        <select class="form-select" name="vendor_id">
                            <option value="">{{ __('All Vendors') }}</option>
                            @foreach(($vendors ?? []) as $v)
                                <option value="{{ $v->id }}" {{ (string) ($filters['vendor_id'] ?? '') === (string) $v->id ? 'selected' : '' }}>
                                    #{{ $v->id }} - {{ $v->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Status') }}</label>
                        <select class="form-select" name="payment_status">
                            <option value="">{{ __('Paid (default)') }}</option>
                            <option value="pending" {{ ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                            <option value="failed" {{ ($filters['payment_status'] ?? '') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                            <option value="refunded" {{ ($filters['payment_status'] ?? '') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Order Status') }}</label>
                        <select class="form-select" name="order_status">
                            <option value="">{{ __('All') }}</option>
                            <option value="pending" {{ ($filters['order_status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="processing" {{ ($filters['order_status'] ?? '') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                            <option value="shipped" {{ ($filters['order_status'] ?? '') === 'shipped' ? 'selected' : '' }}>{{ __('Shipped') }}</option>
                            <option value="delivered" {{ ($filters['order_status'] ?? '') === 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                            <option value="cancelled" {{ ($filters['order_status'] ?? '') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="from_date"
                               name="from_date"
                               value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="to_date"
                               name="to_date"
                               value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.reports.product-performance') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection

