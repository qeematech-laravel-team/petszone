@extends('layouts.app')

@php
    $page = 'order-refund-requests';
@endphp

@section('title', __('Order Refund Requests'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Refund Requests') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Order Refund Requests') }}</h1>
                <p class="text-muted mb-0">{{ __('Review and process user refund requests for delivered orders') }}</p>
            </div>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Requests') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Reason') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Requested At') }}</th>
                            <th>{{ __('Processed By') }}</th>
                            <th>{{ __('Processed At') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($requests as $requestModel)
                            <tr>
                                <td>{{ $requestModel->id }}</td>
                                <td>
                                    <div>
                                        <a href="{{ route('admin.orders.show', $requestModel->order_id) }}" class="fw-semibold">
                                            #{{ $requestModel->order_id }}
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        {{ number_format($requestModel->order?->total ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $requestModel->user?->name ?? '-' }}</div>
                                    <small class="text-muted">ID: {{ $requestModel->user_id }}</small>
                                </td>
                                <td>
                                    <div>{{ $requestModel->reason ?? '-' }}</div>
                                    @if($requestModel->details)
                                        <small class="text-muted d-block text-truncate" style="max-width: 220px;">
                                            {{ $requestModel->details }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @php $status = $requestModel->status; @endphp
                                    <span class="badge
                                        @if($status === 'pending') bg-warning text-dark
                                        @elseif($status === 'approved') bg-success
                                        @else bg-danger @endif">
                                        {{ __($status) }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ optional($requestModel->created_at)->format('Y-m-d H:i') }}</small></td>
                                <td>
                                    @if($requestModel->processor)
                                        <span class="badge bg-secondary">{{ $requestModel->processor->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ optional($requestModel->processed_at)->format('Y-m-d H:i') ?? '-' }}</small></td>
                                <td class="text-end">
                                    @if($requestModel->status === 'pending')
                                        <form method="POST" action="{{ route('admin.order-refund-requests.approve', $requestModel) }}" class="d-inline order-refund-approve-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success order-refund-approve-btn" data-id="{{ $requestModel->id }}">
                                                <i class="bi bi-check2 me-1"></i>{{ __('Approve & Refund') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.order-refund-requests.reject', $requestModel) }}" class="d-inline order-refund-reject-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger order-refund-reject-btn" data-id="{{ $requestModel->id }}">
                                                <i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">{{ __('No refund requests found.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">
                <i class="bi bi-funnel me-2"></i>{{ __('Filter Refund Requests') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('admin.order-refund-requests.index') }}" id="filterForm">
                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Order ID') }}</label>
                    <input type="number" name="order_id" class="form-control" value="{{ $filters['order_id'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('User ID') }}</label>
                    <input type="number" name="user_id" class="form-control" value="{{ $filters['user_id'] ?? '' }}">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.order-refund-requests.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>{{ __('Apply') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const confirmAction = (selector, title, text) => {
                document.querySelectorAll(selector).forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const form = btn.closest('form');
                        if (! form) return;
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
                    });
                });
            };

            confirmAction('.order-refund-approve-btn', "{{ __('Approve refund request?') }}", "{{ __('This will refund the order to the user and restore stock.') }}");
            confirmAction('.order-refund-reject-btn', "{{ __('Reject refund request?') }}", "{{ __('Order will not be refunded.') }}");
        });
    </script>
@endpush

