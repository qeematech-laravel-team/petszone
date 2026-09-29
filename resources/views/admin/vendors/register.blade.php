@extends('layouts.app')

@php
    $page = 'vendors';
@endphp

@section('title', __('Register as Vendor'))

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
                <h1 class="h3 mb-0">{{ __('Register as Vendor') }}</h1>
                <p class="text-muted mb-0">{{ __('Create your vendor account') }}</p>
            </div>
            <div>
                @if(auth()->check())
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Login') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Vendor Registration Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Vendor Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.register.submit') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @if(!auth()->check())
                                <!-- User Account Information (if not authenticated) -->
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">{{ __('Account Information') }}</h6>
                                    
                                    <div class="mb-3">
                                        <label for="owner_name" class="form-label">{{ __('Full Name') }} *</label>
                                        <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                                            id="owner_name" name="owner_name" value="{{ old('owner_name', '') }}" required>
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('Email') }} *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', '') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">{{ __('Password') }} *</label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                    id="password" name="password" required>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }} *</label>
                                                <input type="password" class="form-control"
                                                    id="password_confirmation" name="password_confirmation" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Vendor Name (Translatable) -->
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('Vendor Details') }}</h6>
                                
                                <div class="mb-3">
                                    <label for="name_en" class="form-label">{{ __('Vendor Name (English)') }} *</label>
                                    <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                        id="name_en" name="name[en]"
                                        value="{{ old('name.en', '') }}" required>
                                    @error('name.en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('Vendor Name (Arabic)') }} *</label>
                                    <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                        id="name_ar" name="name[ar]"
                                        value="{{ old('name.ar', '') }}" required>
                                    @error('name.ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone and Address -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">{{ __('Address') }}</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                                            id="address" name="address" value="{{ old('address', '') }}">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor Image -->
                            <div class="mb-3">
                                <label for="image" class="form-label">{{ __('Vendor Image') }}</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Recommended size: 300x300px. Max size: 3MB') }}</small>

                                <!-- Image Preview -->
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>

                            <!-- Plan Selection -->
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">{{ __('Subscription Plan') }}</label>
                                <select class="form-select @error('plan_id') is-invalid @enderror"
                                    id="plan_id" name="plan_id">
                                    <option value="">{{ __('No Plan (Select Later)') }}</option>
                                    @foreach($plans ?? [] as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->getTranslation('name', app()->getLocale()) }} - {{ $plan->getRawOriginal('price') }} {{ setting('currency', 'USD') }} ({{ $plan->duration_days }} {{ __('days') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('You can select a plan later if you skip this step') }}</small>
                            </div>

                            <!-- Info Alert -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>{{ __('Note:') }}</strong> {{ __('Your vendor account will be reviewed by admin before activation. You will be notified once approved.') }}
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Register as Vendor') }}
                                </button>
                                @if(auth()->check())
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                @endif
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
<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewDiv = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            previewDiv.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}
</script>
@endpush
