@extends('layouts.app')

@php
    $page = 'plans';
@endphp

@section('title', __('Edit Plan'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Success/Error Messages -->
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
                <h1 class="h3 mb-0">{{ __('Edit Plan') }}</h1>
                <p class="text-muted mb-0">{{ __('Update plan information') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Plan Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Plan Name (Translatable) -->
                            <div class="mb-3">
                                <label for="name_en" class="form-label">{{ __('Plan Name (English)') }} *</label>
                                <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                    id="name_en" name="name[en]"
                                    value="{{ old('name.en', $plan->getTranslation('name', 'en')) }}" required>
                                @error('name.en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name_ar" class="form-label">{{ __('Plan Name (Arabic)') }} *</label>
                                <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                    id="name_ar" name="name[ar]"
                                    value="{{ old('name.ar', $plan->getTranslation('name', 'ar')) }}" required>
                                @error('name.ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Plan Description (Translatable) -->
                            <div class="mb-3">
                                <label for="description_en" class="form-label">{{ __('Description (English)') }}</label>
                                <textarea class="form-control @error('description.en') is-invalid @enderror"
                                    id="description_en" name="description[en]" rows="3">{{ old('description.en', $plan->getTranslation('description', 'en')) }}</textarea>
                                @error('description.en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description_ar" class="form-label">{{ __('Description (Arabic)') }}</label>
                                <textarea class="form-control @error('description.ar') is-invalid @enderror"
                                    id="description_ar" name="description[ar]" rows="3">{{ old('description.ar', $plan->getTranslation('description', 'ar')) }}</textarea>
                                @error('description.ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Price and Duration -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">{{ __('Price') }} *</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
                                                id="price" name="price" value="{{ old('price', $plan->getRawOriginal('price')) }}" required>
                                            <span class="input-group-text">{{ setting('currency', 'USD') }}</span>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="duration_days" class="form-label">{{ __('Duration (Days)') }} *</label>
                                        <input type="number" min="1" class="form-control @error('duration_days') is-invalid @enderror"
                                            id="duration_days" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required>
                                        @error('duration_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Number of days the plan is valid') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Products Count -->
                            <div class="mb-3">
                                <label for="max_products_count" class="form-label">{{ __('Max Products Count') }}</label>
                                <input type="number" min="0" class="form-control @error('max_products_count') is-invalid @enderror"
                                    id="max_products_count" name="max_products_count" value="{{ old('max_products_count', $plan->max_products_count) }}">
                                @error('max_products_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Leave empty for unlimited products') }}</small>
                            </div>

                            <!-- Can Feature Products -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('can_feature_products') is-invalid @enderror" type="checkbox" id="can_feature_products"
                                        name="can_feature_products" value="1" {{ old('can_feature_products', $plan->can_feature_products) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="can_feature_products">
                                        {{ __('Can Feature Products') }}
                                    </label>
                                </div>
                                @error('can_feature_products')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Allow vendors to feature their products with this plan') }}</small>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active"
                                        name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Active plans will be visible to vendors') }}</small>
                            </div>

                            <!-- Featured -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_featured') is-invalid @enderror" type="checkbox" id="is_featured"
                                        name="is_featured" value="1" {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        {{ __('Featured') }}
                                    </label>
                                </div>
                                @error('is_featured')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Featured plans will be highlighted') }}</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Update Plan') }}
                                </button>
                                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('modals')
@endpush

@push('scripts')
@endpush
