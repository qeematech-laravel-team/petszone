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
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Orders') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Orders') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage all orders') }}</p>
            </div>
        </div>

        <!-- Filters Trigger -->
        <div class="d-flex justify-content-end mb-4">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#orderFiltersOffcanvas" aria-controls="orderFiltersOffcanvas">
                <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
            </button>
        </div>

        <!-- Filters Offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="orderFiltersOffcanvas" aria-labelledby="orderFiltersOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="orderFiltersOffcanvasLabel">{{ __('Filter Orders') }}</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <form method="GET" action="{{ route('admin.orders.index') }}" id="filterForm">
                    <div class="mb-3">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="{{ __('Search by order ID, user name...') }}"
                               value="{{ request('search') }}">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                            <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>{{ __('Shipped') }}</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">{{ __('Payment Status') }}</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">{{ __('All') }}</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="refund_status" class="form-label">{{ __('Refund Status') }}</label>
                        <select class="form-select" id="refund_status" name="refund_status">
                            <option value="">{{ __('All') }}</option>
                            <option value="none" {{ request('refund_status') === 'none' ? 'selected' : '' }}>{{ __('Not Refunded') }}</option>
                            <option value="refunded" {{ request('refund_status') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }}</label>
                        <input type="text"
                               class="form-control"
                               id="payment_method"
                               name="payment_method"
                               placeholder="{{ __('e.g. COD, Visa, Wallet...') }}"
                               value="{{ request('payment_method') }}">
                    </div>
                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">{{ __('Vendor') }}</label>
                        <select class="form-select" id="vendor_id" name="vendor_id">
                            <option value="">{{ __('All Vendors') }}</option>
                            @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Total Range') }}</label>
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
                    <div class="mb-3">
                        <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="from_date"
                               name="from_date"
                               value="{{ request('from_date') }}">
                    </div>
                    <div class="mb-4">
                        <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="to_date"
                               name="to_date"
                               value="{{ request('to_date') }}">
                    </div>
                    <div class="mb-4">
                        <label for="sort" class="form-label">{{ __('Sort') }}</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="" {{ request('sort') === null || request('sort') === '' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                            <option value="total_asc" {{ request('sort') === 'total_asc' ? 'selected' : '' }}>{{ __('Total (Low → High)') }}</option>
                            <option value="total_desc" {{ request('sort') === 'total_desc' ? 'selected' : '' }}>{{ __('Total (High → Low)') }}</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Order ID') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Payment Status') }}</th>
                                    <th>{{ __('Vendors') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <code>#{{ $order->id }}</code>
                                        </td>
                                        <td>
                                            @if($order->user)
                                                {{ $order->user->name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                        </td>
                                        <td>
                                            @if($order->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>{{ __('Pending') }}
                                                </span>
                                            @elseif($order->status === 'processing')
                                                <span class="badge bg-info">
                                                    <i class="bi bi-gear me-1"></i>{{ __('Processing') }}
                                                </span>
                                            @elseif($order->status === 'shipped')
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-truck me-1"></i>{{ __('Shipped') }}
                                                </span>
                                            @elseif($order->status === 'delivered')
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
                                            @if($order->payment_status === 'paid')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>{{ __('Paid') }}
                                                </span>
                                            @elseif($order->payment_status === 'failed')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>{{ __('Failed') }}
                                                </span>
                                            @elseif($order->payment_status === 'refunded')
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Refunded') }}
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>{{ __('Pending') }}
                                                </span>
                                            @endif
                                            @if($order->payment_method)
                                                <br>
                                                <small class="text-muted text-capitalize">{{ $order->payment_method }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $order->vendorOrders->count() }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger delete-order-btn"
                                                        data-id="{{ $order->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $orders->links() }}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Delete order confirmation
        const deleteButtons = document.querySelectorAll('.delete-order-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-id');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    text: '{{ __('You are about to delete this order. This action cannot be undone!') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __('Yes, delete it!') }}',
                    cancelButtonText: '{{ __('Cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('admin.orders.destroy', ':id') }}'.replace(':id', orderId);
                        form.innerHTML = '@csrf @method('DELETE')';
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
