@extends('layouts.app')

@php
    $page = 'orders';
@endphp

@section('title', __('Orders'))

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
                        <li class="breadcrumb-item active">{{ __('Orders') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Orders') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your orders') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        @if(
            request()->has('search')
            || request()->has('status')
            || request()->has('branch_id')
            || request()->has('from_date')
            || request()->has('to_date')
            || request()->has('payment_status')
            || request()->has('payment_method')
            || request()->has('min_total')
            || request()->has('max_total')
            || request()->has('sort')
        )
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">{{ __('Active Filters') }}:</small>
                            @if(request('search'))
                                <span class="badge bg-primary me-1">{{ __('Search') }}: {{ request('search') }}</span>
                            @endif
                            @if(request('status'))
                                <span class="badge bg-info me-1">{{ __('Status') }}: {{ ucfirst(request('status')) }}</span>
                            @endif
                            @if(request('branch_id') && $branches)
                                @php
                                    $selectedBranch = $branches->firstWhere('id', request('branch_id'));
                                @endphp
                                @if($selectedBranch)
                                    <span class="badge bg-success me-1">{{ __('Branch') }}: {{ $selectedBranch->name }}</span>
                                @endif
                            @endif
                            @if(request('from_date'))
                                <span class="badge bg-warning me-1">{{ __('From') }}: {{ request('from_date') }}</span>
                            @endif
                            @if(request('to_date'))
                                <span class="badge bg-warning me-1">{{ __('To') }}: {{ request('to_date') }}</span>
                            @endif
                            @if(request('payment_status'))
                                <span class="badge bg-secondary me-1">{{ __('Payment') }}: {{ ucfirst(request('payment_status')) }}</span>
                            @endif
                            @if(request('payment_method'))
                                <span class="badge bg-secondary me-1">{{ __('Method') }}: {{ request('payment_method') }}</span>
                            @endif
                            @if(request('min_total') || request('max_total'))
                                <span class="badge bg-dark me-1">{{ __('Total') }}: {{ request('min_total', '-') }} - {{ request('max_total', '-') }}</span>
                            @endif
                            @if(request('sort'))
                                <span class="badge bg-light text-dark me-1">{{ __('Sort') }}: {{ request('sort') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Clear Filters') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Vendor Orders Table -->
        <div class="card">
            <div class="card-body">
                @if($vendorOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Vendor Order ID') }}</th>
                                    <th>{{ __('Order ID') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendorOrders as $vendorOrder)
                                    <tr>
                                        <td>
                                            <code>#{{ $vendorOrder->id }}</code>
                                        </td>
                                        <td>
                                            <code>#{{ $vendorOrder->order_id }}</code>
                                        </td>
                                        <td>
                                            @if($vendorOrder->order && $vendorOrder->order->user)
                                                {{ $vendorOrder->order->user->name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vendorOrder->branch)
                                                <span class="badge bg-info">{{ $vendorOrder->branch->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                        </td>
                                        <td>
                                            @if($vendorOrder->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>{{ __('Pending') }}
                                                </span>
                                            @elseif($vendorOrder->status === 'processing')
                                                <span class="badge bg-info">
                                                    <i class="bi bi-gear me-1"></i>{{ __('Processing') }}
                                                </span>
                                            @elseif($vendorOrder->status === 'shipped')
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-truck me-1"></i>{{ __('Shipped') }}
                                                </span>
                                            @elseif($vendorOrder->status === 'delivered')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>{{ __('Delivered') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>{{ __('Cancelled') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $vendorOrder->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('vendor.orders.show', $vendorOrder->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $vendorOrders->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                        <p class="text-muted mt-3">{{ __('No orders found.') }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Orders') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ route('vendor.orders.index') }}" id="filterForm">
            <!-- Search Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Search') }}</label>
                <input type="text"
                       class="form-control"
                       id="search"
                       name="search"
                       placeholder="{{ __('Search by order ID, vendor order ID...') }}"
                       value="{{ request('search') }}">
            </div>

            <!-- Status Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>{{ __('Shipped') }}</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>

            @if($branches && $branches->count() > 0 && !currentBranch())
                <!-- Branch Filter -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('Branch') }}</label>
                    <select class="form-select" id="branch_id" name="branch_id">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Date Range Filters -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Date Range') }}</label>
                <div class="row g-2">
                    <div class="col-12">
                        <label for="from_date" class="form-label small">{{ __('From Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="from_date"
                               name="from_date"
                               value="{{ request('from_date') }}">
                    </div>
                    <div class="col-12">
                        <label for="to_date" class="form-label small">{{ __('To Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="to_date"
                               name="to_date"
                               value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>

            <!-- Payment Filters -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Payment') }}</label>
                <div class="row g-2">
                    <div class="col-12">
                        <label for="payment_status" class="form-label small">{{ __('Payment Status') }}</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">{{ __('All') }}</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="payment_method" class="form-label small">{{ __('Payment Method') }}</label>
                        <input type="text"
                               class="form-control"
                               id="payment_method"
                               name="payment_method"
                               placeholder="{{ __('e.g. COD, Visa...') }}"
                               value="{{ request('payment_method') }}">
                    </div>
                </div>
            </div>

            <!-- Total Range -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Total Range') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               name="min_total"
                               placeholder="{{ __('Min') }}"
                               value="{{ request('min_total') }}">
                    </div>
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               name="max_total"
                               placeholder="{{ __('Max') }}"
                               value="{{ request('max_total') }}">
                    </div>
                </div>
            </div>

            <!-- Sort -->
            <div class="mb-4">
                <label for="sort" class="form-label fw-bold">{{ __('Sort') }}</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="" {{ request('sort') === null || request('sort') === '' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                    <option value="total_asc" {{ request('sort') === 'total_asc' ? 'selected' : '' }}>{{ __('Total (Low → High)') }}</option>
                    <option value="total_desc" {{ request('sort') === 'total_desc' ? 'selected' : '' }}>{{ __('Total (High → Low)') }}</option>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Apply Filters') }}
                </button>
                <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Close offcanvas after form submission
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                // The form will submit normally, and the offcanvas will close on page reload
                // But we can also close it immediately for better UX
                setTimeout(function() {
                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('filterOffcanvas'));
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                }, 100);
            });
        }
    });
</script>
@endpush
