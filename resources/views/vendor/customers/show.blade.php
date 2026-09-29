@extends('layouts.app')

@php
    $page = 'vendor_customers';
@endphp

@section('title', __('Customer Details'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.customers.index') }}">{{ __('Customers') }}</a></li>
                        <li class="breadcrumb-item active">{{ $customer->name }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ $customer->name }}</h1>
                <p class="text-muted mb-0">{{ __('Customer orders with your store') }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Customer Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ __('Name') }}:</strong> {{ $customer->name }}</p>
                        <p class="mb-1"><strong>{{ __('Email') }}:</strong> {{ $customer->email ?? '-' }}</p>
                        <p class="mb-1"><strong>{{ __('Phone') }}:</strong> {{ $customer->phone ?? '-' }}</p>
                        <p class="mb-1">
                            <strong>{{ __('Orders Count (for this vendor)') }}:</strong>
                            <span class="badge bg-secondary">{{ $customer->orders_count_for_vendor ?? 0 }}</span>
                        </p>
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
                                                    <small class="text-muted">{{ optional($order->created_at)->format('Y-m-d H:i') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    @if(vendorCan('view-orders'))
                                                        <a href="{{ route('vendor.orders.show', $order->vendorOrders->first()?->id) }}" class="btn btn-sm btn-outline-info">
                                                            <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                                        </a>
                                                    @endif
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
                            <p class="text-muted mb-0">{{ __('No orders found for this customer with your store.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

