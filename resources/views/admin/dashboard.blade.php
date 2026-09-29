@extends('layouts.app')

@php
    $page = 'dashboard';
@endphp

@php
    use Illuminate\Support\Str;
@endphp

@section('title', __('Admin Dashboard'))

@section('content')
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
            <h1 class="h3 mb-0">{{ __('Admin Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ __('Welcome back! Here\'s what\'s happening with your marketplace.') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i>{{ __('View Full Reports') }}
            </a>
        </div>
    </div>

    <!-- KPI Cards - Overall Stats -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Revenue') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($yearRevenue, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('This Year') }}</small>
                        </div>
                        <div class="text-primary fs-2">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Orders') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($monthOrders) }}</div>
                            <small class="text-muted">{{ __('This Month') }}</small>
                        </div>
                        <div class="text-success fs-2">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Vendors') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($totalVendors) }}</div>
                            <small class="text-muted">{{ $activeVendors }} {{ __('Active') }}</small>
                        </div>
                        <div class="text-info fs-2">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Products') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($totalProducts) }}</div>
                            <small class="text-muted">{{ $activeProducts }} {{ __('Active') }}</small>
                        </div>
                        <div class="text-warning fs-2">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Monthly Revenue') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($monthRevenue, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ $monthDelivered }} {{ __('Delivered') }}</small>
                        </div>
                        <div class="text-success fs-3">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Platform Commission') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($yearCommission, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('This Year') }}</small>
                        </div>
                        <div class="text-warning fs-3">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Customers') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($totalCustomers) }}</div>
                            <small class="text-muted">{{ $activeCustomers }} {{ __('Active') }}</small>
                        </div>
                        <div class="text-primary fs-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Today\'s Revenue') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($todayRevenue, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ $todayOrders }} {{ __('Orders') }}</small>
                        </div>
                        <div class="text-info fs-3">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Actions Alert -->
    @if($pendingCategoryRequests > 0 || $pendingVariantRequests > 0 || $pendingRefundRequests > 0 || $pendingWithdrawals > 0 || $pendingOrders > 0 || $pendingProducts > 0)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('Action Required') }}:</strong>
                    <div class="mt-2">
                        @if($pendingCategoryRequests > 0)
                            <a href="{{ route('admin.category-requests.index') }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingCategoryRequests }} {{ __('Category Requests') }}
                            </a>
                        @endif
                        @if($pendingVariantRequests > 0)
                            <a href="{{ route('admin.variant-requests.index') }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingVariantRequests }} {{ __('Variant Requests') }}
                            </a>
                        @endif
                        @if($pendingRefundRequests > 0)
                            <a href="{{ route('admin.order-refund-requests.index') }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingRefundRequests }} {{ __('Refund Requests') }}
                            </a>
                        @endif
                        @if($pendingWithdrawals > 0)
                            <a href="{{ route('admin.vendor-withdrawals.index') }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingWithdrawals }} {{ __('Withdrawals') }}
                            </a>
                        @endif
                        @if($pendingOrders > 0)
                            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingOrders }} {{ __('Pending Orders') }}
                            </a>
                        @endif
                        @if($pendingProducts > 0)
                            <a href="{{ route('admin.products.index', ['is_approved' => '0']) }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingProducts }} {{ __('Pending Products') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header   border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>{{ __('Recent Orders') }}
                        </h5>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View All') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Order #') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Vendors') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->id }}</strong>
                                            </td>
                                            <td>
                                                <div>{{ $order->user->name ?? __('Guest') }}</div>
                                                <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                @foreach($order->vendorOrders->take(2) as $vo)
                                                    <span class="badge bg-info">{{ $vo->vendor?->getTranslation('name', app()->getLocale()) }}</span>
                                                @endforeach
                                                @if($order->vendorOrders->count() > 2)
                                                    <small class="text-muted">+{{ $order->vendorOrders->count() - 2 }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                            </td>
                                            <td>
                                                @if($order->payment_status === 'paid')
                                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                                @elseif($order->payment_status === 'pending')
                                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ ucfirst($order->status) }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x display-4 text-muted"></i>
                            <p class="text-muted mt-3">{{ __('No orders yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Vendors & Quick Stats -->
        <div class="col-lg-4">
            <!-- Top Vendors -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header   border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-trophy me-2"></i>{{ __('Top Vendors') }}
                        </h5>
                        <small class="text-muted">{{ __('This Month') }}</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($topVendors->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topVendors as $index => $item)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge bg-primary me-2">#{{ $index + 1 }}</span>
                                                <strong>{{ $item['vendor']->getTranslation('name', app()->getLocale()) }}</strong>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ number_format($item['total_sales'], 2) }} {{ setting('currency', 'EGP') }}
                                                · {{ $item['orders_count'] }} {{ __('Orders') }}
                                            </small>
                                        </div>
                                        <a href="{{ route('admin.vendors.show', $item['vendor']->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">{{ __('No vendor sales this month.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header   border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>{{ __('Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                            <i class="bi bi-clock-history me-2"></i>{{ __('Pending Orders') }}
                            @if($pendingOrders > 0)
                                <span class="badge bg-warning text-dark ms-2">{{ $pendingOrders }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.order-refund-requests.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('Refund Requests') }}
                            @if($pendingRefundRequests > 0)
                                <span class="badge bg-danger ms-2">{{ $pendingRefundRequests }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.vendor-withdrawals.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-cash-coin me-2"></i>{{ __('Vendor Withdrawals') }}
                            @if($pendingWithdrawals > 0)
                                <span class="badge bg-info ms-2">{{ $pendingWithdrawals }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.category-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-inbox me-2"></i>{{ __('Category Requests') }}
                            @if($pendingCategoryRequests > 0)
                                <span class="badge bg-secondary ms-2">{{ $pendingCategoryRequests }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Requests Section (if any pending) -->
    @if($pendingRequests->count() > 0)
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header  border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>{{ __('Pending Category Requests') }}
                        </h5>
                        <span class="badge bg-warning">{{ $pendingRequests->count() }} {{ __('pending') }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Category Name') }}</th>
                                        <th>{{ __('Vendor') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Requested') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                        <tr>
                                            <td>
                                                <strong>{{ $request->getTranslation('name', app()->getLocale()) }}</strong>
                                            </td>
                                            <td>{{ $request->vendor->getTranslation('name', app()->getLocale()) }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Str::limit($request->description ?? __('No description'), 50) }}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#approveModal{{ $request->id }}">
                                                        <i class="bi bi-check-lg"></i> {{ __('Approve') }}
                                                    </button>
                                                    <button type="button" class="btn btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{ $request->id }}">
                                                        <i class="bi bi-x-lg"></i> {{ __('Reject') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('modals')
@foreach($pendingRequests as $request)
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">
                        <i class="bi bi-check-circle text-success me-2"></i>{{ __('Approve Category Request') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.category-requests.approve', $request) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>{{ __('Category Name') }}:</strong></label>
                            <p>{{ $request->getTranslation('name', 'en') }} / {{ $request->getTranslation('name', 'ar') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>{{ __('Vendor') }}:</strong></label>
                            <p>{{ $request->vendor->getTranslation('name', app()->getLocale()) }}</p>
                        </div>
                        @if($request->description)
                            <div class="mb-3">
                                <label class="form-label"><strong>{{ __('Description') }}:</strong></label>
                                <p class="text-muted">{{ $request->description }}</p>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="admin_notes{{ $request->id }}" class="form-label">{{ __('Admin Notes') }}</label>
                            <textarea class="form-control" id="admin_notes{{ $request->id }}" name="admin_notes" rows="3"
                                placeholder="{{ __('Optional notes for the vendor...') }}"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create_category{{ $request->id }}" name="create_category" value="1" checked>
                            <label class="form-check-label" for="create_category{{ $request->id }}">
                                {{ __('Create category immediately') }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-2"></i>{{ __('Approve') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $request->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel{{ $request->id }}">
                        <i class="bi bi-x-circle text-danger me-2"></i>{{ __('Reject Category Request') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.category-requests.reject', $request) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>{{ __('Category Name') }}:</strong></label>
                            <p>{{ $request->getTranslation('name', 'en') }} / {{ $request->getTranslation('name', 'ar') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>{{ __('Vendor') }}:</strong></label>
                            <p>{{ $request->vendor->getTranslation('name', app()->getLocale()) }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="reject_notes{{ $request->id }}" class="form-label">{{ __('Rejection Reason') }} *</label>
                            <textarea class="form-control" id="reject_notes{{ $request->id }}" name="admin_notes" rows="3"
                                placeholder="{{ __('Please provide a reason for rejection...') }}" required></textarea>
                            <small class="text-muted">{{ __('This will be visible to the vendor.') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-2"></i>{{ __('Reject') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endpush
