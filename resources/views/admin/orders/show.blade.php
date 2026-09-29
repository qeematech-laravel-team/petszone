@extends('layouts.app')

@php
    $page = 'orders';
@endphp

@section('title', __('Order Details'))

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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">{{ __('Orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ __('Order') }} #{{ $order->id }}</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'processing' ? 'info' : ($order->status === 'shipped' ? 'primary' : ($order->status === 'delivered' ? 'success' : 'danger'))) }} me-2">
                        <i class="bi bi-{{ $order->status === 'pending' ? 'clock' : ($order->status === 'processing' ? 'gear' : ($order->status === 'shipped' ? 'truck' : ($order->status === 'delivered' ? 'check-circle' : 'x-circle'))) }} me-1"></i>
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : ($order->payment_status === 'refunded' ? 'secondary' : 'warning')) }} me-2">
                        <i class="bi bi-credit-card me-1"></i>
                        @if($order->payment_status === 'paid')
                            {{ __('Paid') }}
                        @elseif($order->payment_status === 'failed')
                            {{ __('Failed') }}
                        @elseif($order->payment_status === 'refunded')
                            {{ __('Refunded') }}
                        @else
                            {{ __('Pending') }}
                        @endif
                    </span>
                    <code class="text-muted">#{{ $order->id }}</code>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Order Details -->
            <div class="col-lg-8">
                <!-- Order Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('Order ID') }}</dt>
                            <dd class="col-sm-9"><code>#{{ $order->id }}</code></dd>

                            <dt class="col-sm-3 mt-3">{{ __('User') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($order->user)
                                    <strong>{{ $order->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $order->user->email }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-3 mt-3">{{ __('Payment Status') }}</dt>
                            <dd class="col-sm-9 mt-3">
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
                            </dd>

                            @if($order->payment_method)
                                <dt class="col-sm-3 mt-3">{{ __('Payment Method') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="text-capitalize">{{ $order->payment_method }}</span>
                                </dd>
                            @endif

                            @if($order->paid_at)
                                <dt class="col-sm-3 mt-3">{{ __('Paid At') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <small class="text-muted">{{ $order->paid_at->format('M d, Y H:i') }}</small>
                                </dd>
                            @endif

                            @if($order->fawry_ref_number)
                                <dt class="col-sm-3 mt-3">{{ __('Fawry Reference') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <code>{{ $order->fawry_ref_number }}</code>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Sub Total') }}</dt>
                            <dd class="col-sm-9 mt-3">{{ number_format($order->sub_total, 2) }} {{ setting('currency', 'EGP') }}</dd>

                            <dt class="col-sm-3 mt-3">{{ __('Order Discount') }}</dt>
                            <dd class="col-sm-9 mt-3">{{ number_format($order->order_discount, 2) }} {{ setting('currency', 'EGP') }}</dd>

                            @if($order->coupon)
                                <dt class="col-sm-3 mt-3">{{ __('Coupon') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="badge bg-info">{{ $order->coupon->code }}</span>
                                    <span class="text-muted ms-2">(-{{ number_format($order->coupon_discount, 2) }} {{ setting('currency', 'EGP') }})</span>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Shipping') }}</dt>
                            <dd class="col-sm-9 mt-3">{{ number_format($order->total_shipping, 2) }} {{ setting('currency', 'EGP') }}</dd>

                            @if($order->points_discount > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Points Used') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="badge bg-warning">{{ number_format($order->points_discount, 0) }} {{ __('Points') }}</span>
                                </dd>
                            @endif

                            @if($order->wallet_used > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Wallet Used') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="badge bg-primary">{{ number_format($order->wallet_used, 2) }} {{ setting('currency', 'EGP') }}</span>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Total') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <strong class="fs-5">{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                            </dd>

                            @if($order->total_commission > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Commission') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="badge bg-success">{{ number_format($order->total_commission, 2) }} {{ setting('currency', 'EGP') }}</span>
                                </dd>
                            @endif

                            @if($order->address)
                                <dt class="col-sm-3 mt-3">{{ __('Shipping Address') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    {{ $order->address->address_line_1 }}<br>
                                    @if($order->address->address_line_2)
                                        {{ $order->address->address_line_2 }}<br>
                                    @endif
                                    {{ $order->address->city }}, {{ $order->address->state }}<br>
                                    {{ $order->address->postal_code }}
                                </dd>
                            @endif

                            @if($order->notes)
                                <dt class="col-sm-3 mt-3">{{ __('Notes') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <div class="p-3 rounded">
                                        {{ $order->notes }}
                                    </div>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Vendor Orders -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Vendor Orders') }} ({{ $order->vendorOrders->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($order->vendorOrders->count() > 0)
                            @foreach($order->vendorOrders as $vendorOrder)
                                <div class="card mb-3 border">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $vendorOrder->vendor->name ?? __('Unknown Vendor') }}</strong>
                                                @if($vendorOrder->branch)
                                                    <span class="badge bg-secondary ms-2">{{ $vendorOrder->branch->name }}</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-{{ $vendorOrder->status === 'pending' ? 'warning' : ($vendorOrder->status === 'processing' ? 'info' : ($vendorOrder->status === 'shipped' ? 'primary' : ($vendorOrder->status === 'delivered' ? 'success' : 'danger'))) }}">
                                                {{ ucfirst($vendorOrder->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('Subtotal') }}:</small>
                                                <strong>{{ number_format($vendorOrder->sub_total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('Shipping') }}:</small>
                                                <strong>{{ number_format($vendorOrder->shipping_cost, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                            </div>
                                            @if($vendorOrder->discount > 0)
                                                <div class="col-md-6 mt-2">
                                                    <small class="text-muted">{{ __('Discount') }}:</small>
                                                    <strong class="text-danger">-{{ number_format($vendorOrder->discount, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                                </div>
                                            @endif
                                            @if($vendorOrder->commission > 0)
                                                <div class="col-md-6 mt-2">
                                                    <small class="text-muted">{{ __('Commission') }}:</small>
                                                    <strong class="text-success">{{ number_format($vendorOrder->commission, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                                </div>
                                            @endif
                                            <div class="col-md-12 mt-2">
                                                <small class="text-muted">{{ __('Total') }}:</small>
                                                <strong class="fs-5">{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                                            </div>
                                        </div>

                                        <!-- Items -->
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>{{ __('Product') }}</th>
                                                        <th>{{ __('Variant') }}</th>
                                                        <th class="text-center">{{ __('Quantity') }}</th>
                                                        <th class="text-end">{{ __('Price') }}</th>
                                                        <th class="text-end">{{ __('Total') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($vendorOrder->items as $item)
                                                        <tr>
                                                            <td>
                                                                @if($item->product)
                                                                    {{ $item->product->name }}
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($item->variant)
                                                                    <small class="text-muted">{{ $item->variant->name }}</small>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ $item->quantity }}</td>
                                                            <td class="text-end">{{ number_format($item->price, 2) }} {{ setting('currency', 'EGP') }}</td>
                                                            <td class="text-end"><strong>{{ number_format($item->total, 2) }} {{ setting('currency', 'EGP') }}</strong></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-cart-x fs-3"></i>
                                <p class="mt-2">{{ __('No vendor orders found.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Status') }}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $statusButtons = [
                                'processing'=> ['label' => __('Processing'), 'class' => 'btn-outline-info', 'icon' => 'bi-gear'],
                                'shipped'   => ['label' => __('Shipped'), 'class' => 'btn-outline-primary', 'icon' => 'bi-truck'],
                                'delivered' => ['label' => __('Delivered'), 'class' => 'btn-outline-success', 'icon' => 'bi-check-circle'],
                                'cancelled' => ['label' => __('Cancelled'), 'class' => 'btn-outline-danger', 'icon' => 'bi-x-circle'],
                            ];

                            $actionsByStatus = [
                                'pending'    => ['processing', 'cancelled'],
                                'processing' => ['shipped', 'cancelled'],
                                'shipped'    => ['delivered', 'cancelled'],
                                'delivered'  => [],
                                'cancelled'  => [],
                            ];

                            $availableActions = $actionsByStatus[$order->status] ?? [];
                        @endphp

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($availableActions as $action)
                                @php
                                    $meta = $statusButtons[$action];
                                    $isCurrent = $order->status === $action;
                                @endphp

                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $action }}">
                                    <button type="submit"
                                            class="btn {{ $meta['class'] }} btn-sm {{ $isCurrent ? 'active disabled' : '' }} admin-order-action-btn"
                                            data-action="{{ $action }}"
                                            data-action-label="{{ $meta['label'] }}">
                                        <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                    </button>
                                </form>
                            @endforeach
                            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" rel="noopener"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-receipt me-1"></i>{{ __('Invoice') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt class="small text-muted">{{ __('Order ID') }}</dt>
                            <dd><code>#{{ $order->id }}</code></dd>

                            <dt class="small text-muted mt-3">{{ __('User') }}</dt>
                            <dd>{{ $order->user->name ?? __('Unknown') }}</dd>

                            <dt class="small text-muted mt-3">{{ __('Total') }}</dt>
                            <dd><strong>{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</strong></dd>

                            <dt class="small text-muted mt-3">{{ __('Vendors') }}</dt>
                            <dd><span class="badge bg-secondary">{{ $order->vendorOrders->count() }}</span></dd>

                            <dt class="small text-muted mt-3">{{ __('Created') }}</dt>
                            <dd><small>{{ $order->created_at->format('M d, Y') }}</small></dd>
                        </dl>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Quick Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button"
                                    class="btn btn-danger delete-order-btn"
                                    data-id="{{ $order->id }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Order') }}
                            </button>
                            @if($order->status === 'delivered' && $order->refund_status !== 'refunded')
                                <button type="button"
                                        class="btn btn-outline-warning refund-order-btn"
                                        data-id="{{ $order->id }}">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('Refund Order') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Logs -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Logs') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($order->logs->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('From') }}</th>
                                            <th>{{ __('To') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->logs->sortByDesc('created_at')->take(20) as $log)
                                            <tr>
                                                <td><small class="text-muted">{{ $log->created_at->format('Y-m-d H:i') }}</small></td>
                                                <td><small>{{ $log->user->name ?? '-' }}</small></td>
                                                <td><small>{{ ucfirst(str_replace('_', ' ', $log->type)) }}</small></td>
                                                <td><small>{{ $log->from_status ?? '-' }}</small></td>
                                                <td><small>{{ $log->to_status ?? '-' }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No logs yet for this order.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButton = document.querySelector('.delete-order-btn');
        const refundButton = document.querySelector('.refund-order-btn');
        const actionButtons = document.querySelectorAll('.admin-order-action-btn');

        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
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
        }

        if (refundButton) {
            refundButton.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-id');

                Swal.fire({
                    title: '{{ __('Refund this order?') }}',
                    text: '{{ __('This will refund wallet and points for this delivered order.') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('Yes, refund') }}',
                    cancelButtonText: '{{ __('No') }}'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('admin.orders.refund', ':id') }}'.replace(':id', orderId);
                    form.innerHTML = '@csrf';
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        }

        actionButtons.forEach((button) => {
            button.addEventListener('click', function (e) {
                const action = this.getAttribute('data-action');
                const actionLabel = this.getAttribute('data-action-label') || '';

                if (this.disabled || this.classList.contains('disabled')) {
                    return;
                }

                e.preventDefault();

                const isCancel = action === 'cancelled';
                const title = isCancel ? @json(__('Cancel this order?')) : @json(__('Confirm action'));
                const text = isCancel
                    ? @json(__('You are about to cancel this order. This action cannot be undone.'))
                    : @json(__('You are about to perform: :action', ['action' => ':action']));

                Swal.fire({
                    title,
                    text: text.replace(':action', actionLabel),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isCancel ? '#d33' : '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: isCancel ? @json(__('Yes, cancel')) : @json(__('Yes, continue')),
                    cancelButtonText: @json(__('No')),
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const form = button.closest('form');
                    if (form) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
