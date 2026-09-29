@extends('layouts.app')

@php
    $page = 'dashboard';
@endphp

@php
    use Illuminate\Support\Str;
@endphp

@section('title', __('Vendor Dashboard'))

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
            <h1 class="h3 mb-0">{{ __('Vendor Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ __('Welcome back! Here\'s what\'s happening with your store.') }}</p>
        </div>
        <div>
            <a href="{{ route('vendor.reports.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i>{{ __('View Full Reports') }}
            </a>
        </div>
    </div>

    <!-- KPI Cards - Primary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Current Balance') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($vendor->balance ?? 0, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('Available for withdrawal') }}</small>
                        </div>
                        <div class="text-primary fs-2">
                            <i class="bi bi-wallet2"></i>
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
                            <div class="text-muted small mb-1">{{ __('Monthly Revenue') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($monthRevenue, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ $monthOrders }} {{ __('Orders') }}</small>
                        </div>
                        <div class="text-success fs-2">
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
                            <div class="text-muted small mb-1">{{ __('Net Earnings') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($monthNetEarnings, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('This Month') }}</small>
                        </div>
                        <div class="text-info fs-2">
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
                            <div class="text-muted small mb-1">{{ __('Platform Commission') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($monthCommission, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('This Month') }}</small>
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
                            <div class="text-muted small mb-1">{{ __('Delivered Orders') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($monthDelivered) }}</div>
                            <small class="text-muted">{{ __('This Month') }}</small>
                        </div>
                        <div class="text-success fs-3">
                            <i class="bi bi-check-circle"></i>
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
                            <div class="text-muted small mb-1">{{ __('Year Revenue') }}</div>
                            <div class="h5 mb-0 fw-semibold">{{ number_format($yearRevenue, 2) }} {{ setting('currency', 'EGP') }}</div>
                            <small class="text-muted">{{ __('This Year') }}</small>
                        </div>
                        <div class="text-primary fs-3">
                            <i class="bi bi-calendar-year"></i>
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
    @if($pendingOrders > 0 || $pendingWithdrawals > 0 || $pendingProducts > 0)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('Action Required') }}:</strong>
                    <div class="mt-2">
                        @if($pendingOrders > 0)
                            <a href="{{ route('vendor.orders.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingOrders }} {{ __('Pending Orders') }}
                            </a>
                        @endif
                        @if($pendingProducts > 0)
                            <a href="{{ route('vendor.products.index', ['is_approved' => '0']) }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingProducts }} {{ __('Pending Products') }}
                            </a>
                        @endif
                        @if($pendingWithdrawals > 0)
                            <a href="{{ route('vendor.withdrawals.index') }}" class="badge bg-warning text-dark me-2">
                                {{ $pendingWithdrawals }} {{ __('Pending Withdrawals') }}
                                ({{ number_format($pendingWithdrawalsTotal, 2) }} {{ setting('currency', 'EGP') }})
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
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>{{ __('Recent Orders') }}
                        </h5>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">
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
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $vendorOrder)
                                        <tr>
                                            <td>
                                                <strong>#{{ $vendorOrder->order->id }}</strong>
                                            </td>
                                            <td>
                                                <div>{{ $vendorOrder->order->user->name ?? __('Guest') }}</div>
                                                <small class="text-muted">{{ $vendorOrder->order->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                            </td>
                                            <td>
                                                @if($vendorOrder->order->payment_status === 'paid')
                                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                                @elseif($vendorOrder->order->payment_status === 'pending')
                                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($vendorOrder->order->payment_status) }}</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ ucfirst($vendorOrder->status) }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $vendorOrder->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $vendorOrder->order) }}" class="btn btn-sm btn-outline-primary">
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
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Products') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Products & Quick Stats -->
        <div class="col-lg-4">
            <!-- Top Products -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-trophy me-2"></i>{{ __('Top Products') }}
                        </h5>
                        <small class="text-muted">{{ __('This Month') }}</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($topProducts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topProducts as $index => $product)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge bg-primary me-2">#{{ $index + 1 }}</span>
                                                <strong>{{ $product['product'] ? $product['product']->getTranslation('name', app()->getLocale()) : __('Product #') . $product['product_id'] }}</strong>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ number_format($product['revenue'], 2) }} {{ setting('currency', 'EGP') }}
                                                · {{ $product['quantity'] }} {{ __('Sold') }}
                                            </small>
                                        </div>
                                        <a href="{{ route('vendor.products.show', $product['product_id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">{{ __('No product sales this month.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>{{ __('Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.orders.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                            <i class="bi bi-clock-history me-2"></i>{{ __('Pending Orders') }}
                            @if($pendingOrders > 0)
                                <span class="badge bg-warning text-dark ms-2">{{ $pendingOrders }}</span>
                            @endif
                        </a>
                        <a href="{{ route('vendor.withdrawals.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-cash-coin me-2"></i>{{ __('Withdrawals') }}
                            @if($pendingWithdrawals > 0)
                                <span class="badge bg-info ms-2">{{ $pendingWithdrawals }}</span>
                            @endif
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-box-seam me-2"></i>{{ __('Manage Products') }}
                            @if($pendingProducts > 0)
                                <span class="badge bg-primary ms-2">{{ $pendingProducts }}</span>
                            @endif
                        </a>
                        <a href="{{ route('vendor.reports.earnings') }}" class="btn btn-outline-success">
                            <i class="bi bi-wallet2 me-2"></i>{{ __('Earnings Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
<!-- Request Category Modal -->
<div class="modal fade" id="requestCategoryModal" tabindex="-1" aria-labelledby="requestCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestCategoryModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>{{ __('Request New Category') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.category-requests.store') }}" method="POST" id="categoryRequestForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">{{ __('Submit a request to add a new category. Admin will review and approve it.') }}</p>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">{{ __('Category Name (English)') }} *</label>
                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                            id="name_en" name="name[en]" value="{{ old('name.en', '') }}" required>
                        @error('name.en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">{{ __('Category Name (Arabic)') }} *</label>
                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                            id="name_ar" name="name[ar]" value="{{ old('name.ar', '') }}" required>
                        @error('name.ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" rows="3"
                            placeholder="{{ __('Optional: Describe why this category is needed...') }}">{{ old('description', '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('Maximum 1000 characters') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>{{ __('Submit Request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission success
    const form = document.getElementById('categoryRequestForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Form will submit normally, modal will close via data-bs-dismiss if needed
        });
    }

    // Reset form when modal is closed
    const modal = document.getElementById('requestCategoryModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            form.reset();
            // Clear validation errors
            form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        });
    }
});
</script>
@endpush
