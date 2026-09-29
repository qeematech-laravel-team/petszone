@extends('layouts.app')

@php
    $page = 'categories';
@endphp

@section('title', 'Create Category')

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

        @if(session('category_request_data'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>{{ __('Category Request Approved') }}:</strong>
                {{ __('Category name has been pre-filled from the approved request. Please complete the form to create the category.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Create Category') }}</h1>
                <p class="text-muted mb-0">{{ __('Add a new category to your catalog') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Category Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Category Name (Translatable) -->
                            @php
                                $requestData = session('category_request_data', []);
                            @endphp
                            <div class="mb-3">
                                <label for="name_en" class="form-label">{{ __('Category Name (English)') }} *</label>
                                <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                    id="name_en" name="name[en]"
                                    value="{{ old('name.en', $requestData['name_en'] ?? '') }}" required>
                                @error('name.en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name_ar" class="form-label">{{ __('Category Name (Arabic)') }} *</label>
                                <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                    id="name_ar" name="name[ar]"
                                    value="{{ old('name.ar', $requestData['name_ar'] ?? '') }}" required>
                                @error('name.ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Parent Category -->
                            <div class="mb-3">
                                <label class="form-label">{{ __('Parent Category') }}</label>
                                <div id="parent_category_tree" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="parent_id" id="parent_none"
                                            value="" {{ old('parent_id', '') == '' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="parent_none">
                                            <strong>-- {{ __('None') }} --</strong>
                                        </label>
                                    </div>

                                    @foreach($allCategories as $category)
                                        @include('admin.categories.partials.category-tree-item', [
                                            'category' => $category,
                                            'selected' => old('parent_id'),
                                            'level' => 0
                                        ])
                                    @endforeach
                                </div>
                                @error('parent_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Select a parent category to create a subcategory') }}</small>
                            </div>

                            <!-- Category Image -->
                            <div class="mb-3">
                                <label for="image" class="form-label">{{ __('Category Image') }}</label>
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

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                                <small class="text-muted">{{ __('Active categories will be visible to customers') }}</small>
                            </div>

                            <!-- Featured -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured"
                                        name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        {{ __('Featured') }}
                                    </label>
                                </div>
                                <small class="text-muted">{{ __('Featured categories will be highlighted') }}</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Create Category') }}
                                </button>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
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

@push('styles')
<style>
    /* Override toggle switch styles for radio buttons in category tree */
    #parent_category_tree .form-check-input[type="radio"] {
        width: 1.25em !important;
        height: 1.25em !important;
        border-radius: 50% !important;
        background-color: var(--bs-body-bg) !important;
        border: 1px solid var(--bs-border-color) !important;
        appearance: auto !important;
        -webkit-appearance: radio !important;
        -moz-appearance: radio !important;
        margin-top: 0.25em !important;
        flex-shrink: 0 !important;
    }

    #parent_category_tree .form-check-input[type="radio"]::before {
        display: none !important;
        content: none !important;
    }

    #parent_category_tree .form-check-input[type="radio"]:checked {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='2' fill='%23fff'/%3e%3c/svg%3e") !important;
        background-size: 50% 50% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }

    #parent_category_tree .form-check-input[type="radio"]:checked::before {
        display: none !important;
        content: none !important;
        transform: none !important;
    }

    #parent_category_tree .form-check-input[type="radio"]:focus {
        border-color: var(--bs-primary) !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25) !important;
    }

    [data-bs-theme="dark"] #parent_category_tree .form-check-input[type="radio"] {
        background-color: var(--bs-body-bg) !important;
        border-color: var(--bs-border-color) !important;
    }

    [data-bs-theme="dark"] #parent_category_tree .form-check-input[type="radio"]:checked {
        background-color: var(--bs-body-bg) !important;
        border-color: var(--bs-primary) !important;
    }
</style>
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
