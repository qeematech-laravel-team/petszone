@extends('layouts.app')

@php
    $page = 'coupons';
@endphp

@section('title', __('Edit Coupon'))

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">{{ __('Coupons') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Coupon') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Edit Coupon') }}</h1>
                <p class="text-muted mb-0">{{ __('Update coupon information') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Coupon Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Coupon Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" id="couponForm">
                            @csrf
                            @method('PUT')

                            <!-- Code -->
                            <div class="mb-4">
                                <label for="code" class="form-label">{{ __('Coupon Code') }} *</label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $coupon->code) }}"
                                       placeholder="SUMMER2024"
                                       required
                                       maxlength="50">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('The code will be automatically converted to uppercase.') }}</div>
                            </div>

                            <!-- Type -->
                            <div class="mb-4">
                                <label for="type" class="form-label">{{ __('Discount Type') }} *</label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">{{ __('Select type') }}</option>
                                    <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>{{ __('Percentage') }}</option>
                                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Discount Value -->
                            <div class="mb-4">
                                <label for="discount_value" class="form-label">{{ __('Discount Value') }} *</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('discount_value') is-invalid @enderror" 
                                           id="discount_value" 
                                           name="discount_value" 
                                           value="{{ old('discount_value', $coupon->discount_value) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    <span class="input-group-text" id="discountUnit">{{ $coupon->type === 'percentage' ? '%' : setting('currency', 'EGP') }}</span>
                                </div>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="discountHelp">{{ $coupon->type === 'percentage' ? __('Enter the percentage discount (e.g., 10 for 10%)') : __('Enter the fixed discount amount') }}</div>
                            </div>

                            <!-- Min Cart Amount -->
                            <div class="mb-4">
                                <label for="min_cart_amount" class="form-label">{{ __('Minimum Cart Amount') }}</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('min_cart_amount') is-invalid @enderror" 
                                           id="min_cart_amount" 
                                           name="min_cart_amount" 
                                           value="{{ old('min_cart_amount', $coupon->min_cart_amount) }}"
                                           step="0.01"
                                           min="0">
                                    <span class="input-group-text">{{ setting('currency', 'EGP') }}</span>
                                </div>
                                @error('min_cart_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('Minimum order amount required to use this coupon. Leave 0 for no minimum.') }}</div>
                            </div>

                            <!-- Usage Limit Per User -->
                            <div class="mb-4">
                                <label for="usage_limit_per_user" class="form-label">{{ __('Usage Limit Per User') }}</label>
                                <input type="number" 
                                       class="form-control @error('usage_limit_per_user') is-invalid @enderror" 
                                       id="usage_limit_per_user" 
                                       name="usage_limit_per_user" 
                                       value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}"
                                       min="1"
                                       placeholder="{{ __('Leave empty for unlimited') }}">
                                @error('usage_limit_per_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('Maximum number of times a user can use this coupon. Leave empty for unlimited.') }}</div>
                            </div>

                            <!-- Date Range -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('Leave empty for no start date') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('Leave empty for no expiration') }}</div>
                                </div>
                            </div>

                            <!-- Is Active -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('Inactive coupons cannot be used by customers') }}</div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Update Coupon') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Code') }}:</strong> {{ __('Use clear, memorable codes') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Percentage') }}:</strong> {{ __('Best for general discounts') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Fixed') }}:</strong> {{ __('Best for specific amount discounts') }}
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Dates') }}:</strong> {{ __('Set validity period for time-limited offers') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const discountValueInput = document.getElementById('discount_value');
        const discountUnit = document.getElementById('discountUnit');
        const discountHelp = document.getElementById('discountHelp');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'percentage') {
                discountUnit.textContent = '%';
                discountHelp.textContent = '{{ __('Enter the percentage discount (e.g., 10 for 10%)') }}';
                discountValueInput.setAttribute('max', '100');
            } else if (this.value === 'fixed') {
                discountUnit.textContent = '{{ setting('currency', 'EGP') }}';
                discountHelp.textContent = '{{ __('Enter the fixed discount amount') }}';
                discountValueInput.removeAttribute('max');
            }
        });

        // Convert code to uppercase on input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endpush
