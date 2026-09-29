@extends('layouts.app')

@php
    $page = 'vendor-withdrawals';
@endphp

@section('title', __('Vendor Withdrawals'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Vendor Withdrawals') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Vendor Withdrawals') }}</h1>
                <p class="text-muted mb-0">{{ __('Review and process vendor withdrawal requests') }}</p>
            </div>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Withdrawal Requests') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Vendor') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Notes') }}</th>
                            <th>{{ __('Processed By') }}</th>
                            <th>{{ __('Processed At') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $withdrawal->vendor?->name ?? '-' }}</div>
                                    <small class="text-muted">ID: {{ $withdrawal->vendor_id }}</small>
                                </td>
                                <td>{{ number_format($withdrawal->amount, 2) }} {{ setting('currency', 'EGP') }}</td>
                                <td>
                                    @php $status = $withdrawal->status; @endphp
                                    <span class="badge
                                        @if($status === 'pending') bg-warning text-dark
                                        @elseif($status === 'approved') bg-success
                                        @else bg-danger @endif">
                                        {{ __($status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->method ?? '-' }}</td>
                                <td>{{ $withdrawal->notes ?? '-' }}</td>
                                <td>
                                    @if($withdrawal->processor)
                                        <span class="badge bg-secondary">{{ $withdrawal->processor->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ optional($withdrawal->processed_at)->format('Y-m-d H:i') ?? '-' }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    @if($withdrawal->status === 'pending')
                                        <form method="POST" action="{{ route('admin.vendor-withdrawals.approve', $withdrawal) }}" class="d-inline withdrawal-approve-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success withdrawal-approve-btn" data-id="{{ $withdrawal->id }}">
                                                <i class="bi bi-check2 me-1"></i>{{ __('Approve') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.vendor-withdrawals.reject', $withdrawal) }}" class="d-inline withdrawal-reject-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger withdrawal-reject-btn" data-id="{{ $withdrawal->id }}">
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
                                <td colspan="9" class="text-center text-muted py-4">{{ __('No withdrawals found.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $withdrawals->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">
                <i class="bi bi-funnel me-2"></i>{{ __('Filter Withdrawals') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('admin.vendor-withdrawals.index') }}" id="filterForm">
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
                    <label class="form-label">{{ __('Vendor ID') }}</label>
                    <input type="number" name="vendor_id" class="form-control" value="{{ $filters['vendor_id'] ?? '' }}">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.vendor-withdrawals.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
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

            confirmAction('.withdrawal-approve-btn', "{{ __('Approve withdrawal?') }}", "{{ __('This will deduct the amount from vendor balance.') }}");
            confirmAction('.withdrawal-reject-btn', "{{ __('Reject withdrawal?') }}", "{{ __('No balance will be changed.') }}");
        });
    </script>
@endpush

