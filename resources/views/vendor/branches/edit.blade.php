@extends('layouts.app')

@php
    $page = 'branches';
@endphp

@section('title', __('Edit Branch'))

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
                <h1 class="h3 mb-0">{{ __('Edit Branch') }}</h1>
                <p class="text-muted mb-0">{{ __('Update branch information') }}</p>
            </div>
            <div>
                <a href="{{ route('vendor.branches.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Branch Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('vendor.branches.update', $branch) }}" method="POST" id="branchForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-8">
                                    <!-- Basic Information -->
                                    <h5 class="mb-3">{{ __('Basic Information') }}</h5>

                                    <!-- Branch Name (Translatable) -->
                                    <div class="mb-3">
                                        <label for="name_en" class="form-label">{{ __('Branch Name (English)') }} *</label>
                                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                            id="name_en" name="name[en]"
                                            value="{{ old('name.en', $branch->getTranslation('name', 'en')) }}" required>
                                        @error('name.en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="name_ar" class="form-label">{{ __('Branch Name (Arabic)') }} *</label>
                                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                            id="name_ar" name="name[ar]"
                                            value="{{ old('name.ar', $branch->getTranslation('name', 'ar')) }}" required>
                                        @error('name.ar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Address -->
                                    <div class="mb-3">
                                        <label for="address" class="form-label">{{ __('Address') }} *</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                            id="address" name="address" rows="3" required>{{ old('address', $branch->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Phone -->
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone', $branch->phone) }}"
                                            placeholder="{{ __('e.g., +966501234567') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-4">
                                    <!-- Location Information -->
                                    <h5 class="mb-3">{{ __('Location') }}</h5>

                                    <!-- Latitude -->
                                    <div class="mb-3">
                                        <label for="latitude" class="form-label">{{ __('Latitude') }}</label>
                                        <input type="text" class="form-control @error('latitude') is-invalid @enderror"
                                            id="latitude" name="latitude" value="{{ old('latitude', $branch->latitude) }}"
                                            placeholder="{{ __('e.g., 24.7136') }}">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Optional: For map integration') }}</small>
                                    </div>

                                    <!-- Longitude -->
                                    <div class="mb-3">
                                        <label for="longitude" class="form-label">{{ __('Longitude') }}</label>
                                        <input type="text" class="form-control @error('longitude') is-invalid @enderror"
                                            id="longitude" name="longitude" value="{{ old('longitude', $branch->longitude) }}"
                                            placeholder="{{ __('e.g., 46.6753') }}">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Optional: For map integration') }}</small>
                                    </div>

                                    <!-- Status Toggle -->
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active"
                                                name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active') }}
                                            </label>
                                        </div>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Active branches will be visible') }}</small>
                                    </div>

                                    <!-- Map Help -->
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <small>{{ __('To get coordinates, use Google Maps: Right-click on location → Copy coordinates') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Update Branch') }}
                                </button>
                                <a href="{{ route('vendor.branches.index') }}" class="btn btn-secondary">
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
