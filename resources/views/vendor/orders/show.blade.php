@extends('layouts.app')

@php
    $page = 'orders';
@endphp

@section('title', __('Vendor Order Details'))

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
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.orders.index') }}">{{ __('Orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ __('Vendor Order') }} #{{ $vendorOrder->id }}</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-{{ $vendorOrder->status === 'pending' ? 'warning' : ($vendorOrder->status === 'processing' ? 'info' : ($vendorOrder->status === 'shipped' ? 'primary' : ($vendorOrder->status === 'delivered' ? 'success' : 'danger'))) }} me-2">
                        <i class="bi bi-{{ $vendorOrder->status === 'pending' ? 'clock' : ($vendorOrder->status === 'processing' ? 'gear' : ($vendorOrder->status === 'shipped' ? 'truck' : ($vendorOrder->status === 'delivered' ? 'check-circle' : 'x-circle'))) }} me-1"></i>
                        {{ ucfirst($vendorOrder->status) }}
                    </span>
                    <code class="text-muted">#{{ $vendorOrder->id }}</code>
                    @if($vendorOrder->order)
                        <code class="text-muted ms-2">Order #{{ $vendorOrder->order->id }}</code>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Vendor Order Details -->
            <div class="col-lg-8">
                <!-- Vendor Order Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Vendor Order Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('Vendor Order ID') }}</dt>
                            <dd class="col-sm-9"><code>#{{ $vendorOrder->id }}</code></dd>

                            @if($vendorOrder->order)
                                <dt class="col-sm-3 mt-3">{{ __('Order ID') }}</dt>
                                <dd class="col-sm-9 mt-3"><code>#{{ $vendorOrder->order->id }}</code></dd>

                                <dt class="col-sm-3 mt-3">{{ __('Customer') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    @if($vendorOrder->order->user)
                                        <strong>{{ $vendorOrder->order->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $vendorOrder->order->user->email }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Branch') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($vendorOrder->branch)
                                    <span class="badge bg-info">{{ $vendorOrder->branch->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Sub Total') }}</dt>
                            <dd class="col-sm-9 mt-3">{{ number_format($vendorOrder->subtotal, 2) }} {{ setting('currency', 'EGP') }}</dd>

                            @if($vendorOrder->discount > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Discount') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="text-danger">-{{ number_format($vendorOrder->discount, 2) }} {{ setting('currency', 'EGP') }}</span>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Shipping Cost') }}</dt>
                            <dd class="col-sm-9 mt-3">{{ number_format($vendorOrder->shipping_cost, 2) }} {{ setting('currency', 'EGP') }}</dd>

                            @if($vendorOrder->commission > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Commission') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <span class="badge bg-success">{{ number_format($vendorOrder->commission, 2) }} {{ setting('currency', 'EGP') }}</span>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Total') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <strong class="fs-5">{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                            </dd>

                            @if($vendorOrder->order && $vendorOrder->order->address)
                                <dt class="col-sm-3 mt-3">{{ __('Shipping Address') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    {{ $vendorOrder->order->address->address_line_1 }}<br>
                                    @if($vendorOrder->order->address->address_line_2)
                                        {{ $vendorOrder->order->address->address_line_2 }}<br>
                                    @endif
                                    {{ $vendorOrder->order->address->city }}, {{ $vendorOrder->order->address->state }}<br>
                                    {{ $vendorOrder->order->address->postal_code }}
                                </dd>
                            @endif

                            @if($vendorOrder->notes)
                                <dt class="col-sm-3 mt-3">{{ __('Notes') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <div class="p-3 rounded">
                                        {{ $vendorOrder->notes }}
                                    </div>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $vendorOrder->created_at->format('M d, Y H:i') }}</small>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Items') }} ({{ $vendorOrder->items->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($vendorOrder->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
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
                                                        <strong>{{ $item->product->name }}</strong>
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
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>{{ __('Total') }}</strong></td>
                                            <td class="text-end"><strong>{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-cart-x fs-3"></i>
                                <p class="mt-2">{{ __('No items found.') }}</p>
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
                                'processing' => ['label' => __('Processing'), 'class' => 'btn-outline-info', 'icon' => 'bi-gear'],
                                'shipped' => ['label' => __('Shipped'), 'class' => 'btn-outline-primary', 'icon' => 'bi-truck'],
                                'delivered' => ['label' => __('Delivered'), 'class' => 'btn-outline-success', 'icon' => 'bi-check-circle'],
                                'cancelled' => ['label' => __('Cancel'), 'class' => 'btn-outline-danger', 'icon' => 'bi-x-circle'],
                            ];

                            $actionsByStatus = [
                                'pending' => ['processing', 'cancelled'],
                                'processing' => ['shipped', 'cancelled', 'invoice'],
                                'shipped' => ['delivered', 'cancelled', 'invoice'],
                                'delivered' => ['invoice'],
                                'cancelled' => [],
                            ];

                            $availableActions = $actionsByStatus[$vendorOrder->status] ?? [];
                        @endphp

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($availableActions as $action)
                                @if($action === 'invoice')
                                    @if(vendorCan('view-order-invoices'))
                                        <a href="{{ route('vendor.orders.invoice', $vendorOrder->id) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-receipt me-1"></i>{{ __('Invoice') }}
                                        </a>
                                    @endif
                                    @continue
                                @endif

                                @php
                                    $meta = $statusButtons[$action];
                                    $isCurrent = $vendorOrder->status === $action;
                                @endphp

                                @if(vendorCan('update-order-status'))
                                    <form action="{{ route('vendor.orders.update-status', $vendorOrder->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $action }}">
                                        <button type="submit"
                                                class="btn {{ $meta['class'] }} btn-sm {{ $isCurrent ? 'active disabled' : '' }} vendor-order-action-btn"
                                                data-action="{{ $action }}"
                                                data-action-label="{{ $meta['label'] }}">
                                            <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Vendor Order Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt class="small text-muted">{{ __('Vendor Order ID') }}</dt>
                            <dd><code>#{{ $vendorOrder->id }}</code></dd>

                            @if($vendorOrder->order)
                                <dt class="small text-muted mt-3">{{ __('Order ID') }}</dt>
                                <dd><code>#{{ $vendorOrder->order->id }}</code></dd>

                                <dt class="small text-muted mt-3">{{ __('Customer') }}</dt>
                                <dd>{{ $vendorOrder->order->user->name ?? __('Unknown') }}</dd>
                            @endif

                            <dt class="small text-muted mt-3">{{ __('Total') }}</dt>
                            <dd><strong>{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong></dd>

                            @if($vendorOrder->branch)
                                <dt class="small text-muted mt-3">{{ __('Branch') }}</dt>
                                <dd><span class="badge bg-info">{{ $vendorOrder->branch->name }}</span></dd>
                            @endif

                            <dt class="small text-muted mt-3">{{ __('Items') }}</dt>
                            <dd><span class="badge bg-secondary">{{ $vendorOrder->items->count() }}</span></dd>

                            <dt class="small text-muted mt-3">{{ __('Created') }}</dt>
                            <dd><small>{{ $vendorOrder->created_at->format('M d, Y') }}</small></dd>
                        </dl>
                    </div>
                </div>

                <!-- Order Logs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Order Logs') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($vendorOrder->logs->count())
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
                                        @foreach($vendorOrder->logs->sortByDesc('created_at')->take(20) as $log)
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
    document.addEventListener('DOMContentLoaded', function () {
        const actionButtons = document.querySelectorAll('.vendor-order-action-btn');

        actionButtons.forEach((button) => {
            button.addEventListener('click', function (e) {
                const action = this.getAttribute('data-action');
                const actionLabel = this.getAttribute('data-action-label') || '';

                // Don't prompt for disabled/current buttons
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
