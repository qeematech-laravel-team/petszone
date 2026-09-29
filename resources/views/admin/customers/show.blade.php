@extends('layouts.app')

@php
    $page = 'customers';
@endphp

@section('title', __('Customer Details'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">{{ __('Customers') }}</a></li>
                        <li class="breadcrumb-item active">{{ $customer->name }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ $customer->name }}</h1>
                <p class="text-muted mb-0">{{ __('Customer profile and orders') }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Customer Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-4"><strong>{{ __('Name') }}:</strong> {{ $customer->name }}</p>
                        <p class="mb-4"><strong>{{ __('Email') }}:</strong> {{ $customer->email ?? '-' }}</p>
                        <p class="mb-4"><strong>{{ __('Phone') }}:</strong> {{ $customer->phone ?? '-' }}</p>
                        <p class="mb-4">
                            <strong>{{ __('Status') }}:</strong>
                            @if($customer->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Blocked') }}</span>
                            @endif
                        </p>
                        <p class="mb-4">
                            <strong>{{ __('Orders Count') }}:</strong>
                            <span class="badge bg-secondary">{{ $customer->orders_count }}</span>
                        </p>
                        <p class="mb-4">
                            <strong>{{ __('Orders Total') }}:</strong>
                            {{ number_format($customer->orders_total ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                        </p>
                        <p class="mb-4">
                            <strong>{{ __('Points') }}:</strong>
                            {{ $customer->points }}
                        </p>
                        <p class="mb-0">
                            <strong>{{ __('Joined At') }}:</strong>
                            <small class="text-muted">{{ optional($customer->created_at)->format('Y-m-d H:i') }}</small>
                        </p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Actions') }}</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button type="button"
                                class="btn btn-outline-primary w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#editCustomerModal">
                            <i class="bi bi-pencil-square me-1"></i>{{ __('Edit Profile') }}
                        </button>

        <form method="POST" action="{{ route('admin.customers.toggle-active', $customer) }}" class="customer-toggle-form">
                            @csrf
                            <button type="button"
                                    class="btn {{ $customer->is_active ? 'btn-danger' : 'btn-success' }} customer-toggle-btn w-100"
                                    data-name="{{ $customer->name }}"
                                    data-action="{{ $customer->is_active ? 'block' : 'activate' }}">
                                @if($customer->is_active)
                                    <i class="bi bi-person-x me-1"></i>{{ __('Block Customer') }}
                                @else
                                    <i class="bi bi-person-check me-1"></i>{{ __('Activate Customer') }}
                                @endif
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-warning w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#setPasswordModal">
                            <i class="bi bi-shield-lock me-1"></i>{{ __('Set Password') }}
                        </button>

                        <button type="button"
                                class="btn btn-outline-secondary w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#resetLinkModal">
                            <i class="bi bi-envelope-arrow-up me-1"></i>{{ __('Send Reset Code') }}
                        </button>

                        <button type="button"
                                class="btn btn-primary w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#pointsModal">
                            <i class="bi bi-plus-slash-minus me-1"></i>{{ __('Update Points') }}
                        </button>

                        <button type="button"
                                class="btn btn-dark w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#notifyModal">
                            <i class="bi bi-send me-1"></i>{{ __('Send Notification') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Orders') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Payment') }}</th>
                                            <th>{{ __('Vendors') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
                                            <tr>
                                                <td>#{{ $order->id }}</td>
                                                <td>{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</td>
                                                <td>
                                                    <span class="badge bg-info text-dark text-capitalize">{{ $order->status }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary text-capitalize">{{ $order->payment_status ?? 'pending' }}</span>
                                                </td>
                                                <td>
                                                    @foreach($order->vendorOrders as $vo)
                                                        <span class="badge text-muted">{{ $vo->vendor?->name }}</span> @if(!$loop->last), @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ optional($order->created_at)->format('Y-m-d H:i') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {{ $orders->links() }}
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No orders found for this customer.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Modal --}}
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">{{ __('Edit Customer Profile') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>{{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Set Password Modal --}}
    <div class="modal fade" id="setPasswordModal" tabindex="-1" aria-labelledby="setPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setPasswordModalLabel">{{ __('Set Customer Password') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.customers.set-password', $customer) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('New Password') }}</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock me-1"></i>{{ __('Set Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reset Link Modal --}}
    <div class="modal fade" id="resetLinkModal" tabindex="-1" aria-labelledby="resetLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetLinkModalLabel">{{ __('Send Password Reset Code') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.customers.send-reset-link', $customer) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-2">
                            <strong>{{ __('Customer') }}:</strong> {{ $customer->name }}
                        </p>
                        <p class="mb-0">
                            <strong>{{ __('Email') }}:</strong> {{ $customer->email ?? '-' }}
                        </p>
                        @if (! $customer->email)
                            <p class="text-danger mt-2 mb-0">
                                {{ __('This customer does not have an email address, so reset link cannot be sent.') }}
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-outline-secondary" @if (! $customer->email) disabled @endif>
                            <i class="bi bi-envelope-arrow-up me-1"></i>{{ __('Send Reset Code') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Points Modal --}}
    <div class="modal fade" id="pointsModal" tabindex="-1" aria-labelledby="pointsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointsModalLabel">{{ __('Manage Customer Points') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.customers.adjust-points', $customer) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">
                            <strong>{{ __('Current Points') }}:</strong>
                            <span class="badge bg-primary">{{ (int) round((float) $customer->points) }}</span>
                        </p>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type" class="form-select">
                                    <option value="addition">{{ __('Add') }}</option>
                                    <option value="subtraction">{{ __('Deduct') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Amount') }}</label>
                                <input type="number" min="1" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <input type="text" name="notes" class="form-control">
                            </div>
                        </div>

                        @if($customer->pointTransactions?->count() > 0)
                            <h6 class="mb-2">{{ __('Recent Transactions') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                            <th>{{ __('Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->pointTransactions as $tx)
                                            <tr>
                                                <td>
                                                    <span class="badge {{ $tx->type === 'addition' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ __($tx->type) }}
                                                    </span>
                                                </td>
                                                <td>{{ $tx->amount }}</td>
                                                <td>{{ $tx->balance_after }}</td>
                                                <td><small class="text-muted">{{ optional($tx->created_at)->format('Y-m-d H:i') }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-slash-minus me-1"></i>{{ __('Update Points') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Manual Notification Modal --}}
    <div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notifyModalLabel">{{ __('Send Manual Notification') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.customers.notify', $customer) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Title') }}</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Message') }}</label>
                            <textarea name="message" rows="4" class="form-control" required>{{ old('message') }}</textarea>
                        </div>
                        <small class="text-muted d-block">
                            {{ __('Sent as database notification, and email if customer has an email address.') }}
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-send me-1"></i>{{ __('Send') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function confirmAndSubmit(btn, title, text) {
                const form = btn.closest('form');

                if (! form) {
                    return;
                }

                if (typeof Swal === 'undefined') {
                    form.submit();
                    return;
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            document.querySelectorAll('.customer-toggle-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const name = btn.dataset.name || '';
                    const action = btn.dataset.action || '';

                    confirmAndSubmit(
                        btn,
                        action === 'block' ? "{{ __('Block customer?') }}" : "{{ __('Activate customer?') }}",
                        name ? "{{ __('Customer') }}: " + name : ''
                    );
                });
            });

            // Other actions are handled via Bootstrap modals; only block/activate uses Swal confirmation here.
        });
    </script>
@endpush
