@extends('layouts.app')

@php
    $page = 'customers';
@endphp

@section('title', __('Customers'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Customers') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Customers') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage platform customers') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
            </div>
        </div>

        <!-- Search Bar (match vendor index style) -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       name="search"
                                       placeholder="{{ __('Search by name, email or phone') }}"
                                       value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>{{ __('Search') }}
                                </button>
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i>{{ __('Clear Filters') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($customers->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Customers') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Orders Count') }}</th>
                                    <th>{{ __('Orders Total') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->id }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->email ?? '-' }}</td>
                                        <td>{{ $customer->phone ?? '-' }}</td>
                                        <td>
                                            @if($customer->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Blocked') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $customer->orders_count }}</span>
                                        </td>
                                        <td>
                                            {{ number_format($customer->orders_total ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ optional($customer->created_at)->format('Y-m-d H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST"
                                                  action="{{ route('admin.customers.toggle-active', $customer) }}"
                                                  class="d-inline customer-toggle-form">
                                                @csrf
                                                <button type="button"
                                                        class="btn btn-sm {{ $customer->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} customer-toggle-btn"
                                                        data-name="{{ $customer->name }}"
                                                        data-action="{{ $customer->is_active ? 'block' : 'activate' }}">
                                                    @if($customer->is_active)
                                                        <i class="bi bi-person-x me-1"></i>{{ __('Block') }}
                                                    @else
                                                        <i class="bi bi-person-check me-1"></i>{{ __('Activate') }}
                                                    @endif
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted"></i>
                <p class="text-muted mt-3">{{ __('No customers found.') }}</p>
            </div>
        @endif
    </div>
@endsection

@push('modals')
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Customers') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ route('admin.customers.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">

            <div class="mb-3">
                <label class="form-label">{{ __('Status') }}</label>
                <select class="form-select" name="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Joined Date') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-6">
                        <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Orders Count') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" class="form-control" name="min_orders_count" placeholder="{{ __('Min') }}" value="{{ request('min_orders_count') }}">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control" name="max_orders_count" placeholder="{{ __('Max') }}" value="{{ request('max_orders_count') }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Orders Total') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" name="min_orders_total" placeholder="{{ __('Min') }}" value="{{ request('min_orders_total') }}">
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" name="max_orders_total" placeholder="{{ __('Max') }}" value="{{ request('max_orders_total') }}">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('Sort') }}</label>
                <select class="form-select" name="sort">
                    <option value="" {{ request('sort') === null || request('sort') === '' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                    <option value="orders_count_desc" {{ request('sort') === 'orders_count_desc' ? 'selected' : '' }}>{{ __('Orders Count (High → Low)') }}</option>
                    <option value="orders_total_desc" {{ request('sort') === 'orders_total_desc' ? 'selected' : '' }}>{{ __('Orders Total (High → Low)') }}</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-check-lg me-1"></i>{{ __('Apply') }}
                </button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary flex-fill">
                    <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.customer-toggle-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const form = btn.closest('form');
                    const name = btn.dataset.name || '';
                    const action = btn.dataset.action || '';

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: action === 'block' ? "{{ __('Block customer?') }}" : "{{ __('Activate customer?') }}",
                        text: name ? "{{ __('Customer') }}: " + name : '',
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
        });
    </script>
@endpush

