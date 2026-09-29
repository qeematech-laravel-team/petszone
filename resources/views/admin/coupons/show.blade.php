@extends('layouts.app')

@php
    $page = 'coupons';
@endphp

@section('title', __('Coupon Details'))

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">{{ __('Coupons') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Coupon Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ __('Coupon') }}: <code>{{ $coupon->code }}</code></h1>
                <p class="text-muted mb-0">
                    @if($coupon->is_active)
                        <span class="badge bg-success me-2">
                            <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                        </span>
                    @else
                        <span class="badge bg-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Inactive') }}
                        </span>
                    @endif
                    @if($coupon->isValid())
                        <span class="badge bg-info">{{ __('Valid') }}</span>
                    @else
                        <span class="badge bg-warning">{{ __('Invalid') }}</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Coupon Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Coupon Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('Code') }}</dt>
                            <dd class="col-sm-9"><code class="fs-5">{{ $coupon->code }}</code></dd>

                            <dt class="col-sm-3 mt-3">{{ __('Type') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($coupon->type === 'percentage')
                                    <span class="badge bg-info">{{ __('Percentage') }}</span>
                                @else
                                    <span class="badge bg-primary">{{ __('Fixed Amount') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Discount Value') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <strong class="fs-5">
                                    @if($coupon->type === 'percentage')
                                        {{ number_format($coupon->discount_value, 0) }}%
                                    @else
                                        {{ number_format($coupon->discount_value, 2) }} {{ setting('currency', 'EGP') }}
                                    @endif
                                </strong>
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Minimum Cart Amount') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($coupon->min_cart_amount > 0)
                                    {{ number_format($coupon->min_cart_amount, 2) }} {{ setting('currency', 'EGP') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Usage Limit Per User') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($coupon->usage_limit_per_user)
                                    {{ $coupon->usage_limit_per_user }} {{ __('times') }}
                                @else
                                    <span class="text-muted">{{ __('Unlimited') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Validity Period') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($coupon->start_date || $coupon->end_date)
                                    @if($coupon->start_date)
                                        <strong>{{ __('From') }}:</strong> {{ $coupon->start_date->format('M d, Y H:i') }}<br>
                                    @endif
                                    @if($coupon->end_date)
                                        <strong>{{ __('To') }}:</strong> {{ $coupon->end_date->format('M d, Y H:i') }}
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('No date restrictions') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Status') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                @if($coupon->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                                @if($coupon->isValid())
                                    <span class="badge bg-info ms-2">{{ __('Valid') }}</span>
                                @else
                                    <span class="badge bg-warning ms-2">{{ __('Invalid') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Total Orders') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <span class="badge bg-secondary">{{ $coupon->orders->count() }}</span>
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $coupon->created_at->format('M d, Y H:i') }}</small>
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Updated At') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $coupon->updated_at->format('M d, Y H:i') }}</small>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Quick Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Coupon') }}
                            </a>
                            <button type="button"
                                    class="btn btn-danger delete-coupon-btn"
                                    data-id="{{ $coupon->id }}"
                                    data-code="{{ $coupon->code }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Coupon') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coupon Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Coupon Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt class="small text-muted">{{ __('ID') }}</dt>
                            <dd><code>#{{ $coupon->id }}</code></dd>

                            <dt class="small text-muted mt-3">{{ __('Code') }}</dt>
                            <dd><code>{{ $coupon->code }}</code></dd>

                            <dt class="small text-muted mt-3">{{ __('Type') }}</dt>
                            <dd>
                                @if($coupon->type === 'percentage')
                                    <span class="badge bg-info">{{ __('Percentage') }}</span>
                                @else
                                    <span class="badge bg-primary">{{ __('Fixed') }}</span>
                                @endif
                            </dd>

                            <dt class="small text-muted mt-3">{{ __('Discount') }}</dt>
                            <dd>
                                <strong>
                                    @if($coupon->type === 'percentage')
                                        {{ number_format($coupon->discount_value, 0) }}%
                                    @else
                                        {{ number_format($coupon->discount_value, 2) }} {{ setting('currency', 'EGP') }}
                                    @endif
                                </strong>
                            </dd>

                            <dt class="small text-muted mt-3">{{ __('Orders') }}</dt>
                            <dd><span class="badge bg-secondary">{{ $coupon->orders->count() }}</span></dd>

                            <dt class="small text-muted mt-3">{{ __('Created') }}</dt>
                            <dd><small>{{ $coupon->created_at->format('M d, Y') }}</small></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButton = document.querySelector('.delete-coupon-btn');

        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const couponId = this.getAttribute('data-id');
                const couponCode = this.getAttribute('data-code');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    text: '{{ __('You are about to delete coupon') }}: ' + couponCode + '. {{ __('This action cannot be undone!') }}',
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
                        form.action = '{{ route('admin.coupons.destroy', ':id') }}'.replace(':id', couponId);
                        form.innerHTML = '@csrf @method('DELETE')';
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
