@extends('layouts.app')

@php
    $page = 'sliders';
@endphp

@section('title', __('Edit Slider'))

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">{{ __('Sliders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Slider') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Edit Slider') }}</h1>
                <p class="text-muted mb-0">{{ __('Update slider image') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Slider Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Slider Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data" id="sliderForm">
                            @csrf
                            @method('PUT')

                            <!-- Current Image -->
                            @if($slider->image)
                                <div class="mb-4">
                                    <label class="form-label">{{ __('Current Image') }}</label>
                                    <div class="border rounded p-3 bg-light">
                                        <img src="{{ asset('storage/' . $slider->image) }}" 
                                             alt="{{ __('Current Slider Image') }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 300px;">
                                    </div>
                                </div>
                            @endif

                            <!-- Image Upload -->
                            <div class="mb-4">
                                <label for="image" class="form-label">{{ __('New Slider Image') }}</label>
                                <input type="file" 
                                       class="form-control @error('image') is-invalid @enderror" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('Leave empty to keep current image. Recommended size: 1920x600px.') }}</div>
                            </div>

                            <!-- Image Preview -->
                            <div class="mb-4" id="imagePreviewContainer" style="display: none;">
                                <label class="form-label">{{ __('New Image Preview') }}</label>
                                <div class="border rounded p-3 bg-light">
                                    <img id="imagePreview" src="" alt="{{ __('Preview') }}" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Update Slider') }}
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
                                <strong>{{ __('Image Size') }}:</strong> {{ __('Use high-quality images for best results') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Aspect Ratio') }}:</strong> {{ __('16:9 or 3:1 works best for sliders') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('File Size') }}:</strong> {{ __('Keep images under 2MB for faster loading') }}
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Format') }}:</strong> {{ __('JPG or PNG recommended') }}
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
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
