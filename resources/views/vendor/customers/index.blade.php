@extends('layouts.app')

@php
    $page = 'vendor_customers';
@endphp

@section('title', __('Customers'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Customers') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Customers') }}</h1>
                <p class="text-muted mb-0">{{ __('Customers who placed orders with you') }}</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           class="form-control form-control-sm"
                           placeholder="{{ __('Search by name, email or phone') }}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i>{{ __('Search') }}
                    </button>
                </form>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-1"></i>{{ __('Filters') }}
                </button>
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
                                    <th>{{ __('Orders Count') }}</th>
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
                                            <span class="badge bg-secondary">{{ $customer->orders_count_for_vendor ?? 0 }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('vendor.customers.show', $customer) }}" class="btn btn-sm btn-outline-info">
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
        <form method="GET" action="{{ route('vendor.customers.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">

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
                <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary flex-fill">
                    <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endpush
