@extends('layouts.app')

@php
    $page = 'categories';
@endphp

@section('title', __('Categories'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Categories') }}</h1>
                <p class="text-muted mb-0">{{ __('Browse available product categories') }}</p>
            </div>
            @if(vendorCan('create-category-requests'))
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestCategoryModal">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Category') }}
                </button>
            </div>
            @endif
        </div>

        <!-- Categories Grid -->
        <div class="row g-4">
            @if($categories->count() > 0)
                @foreach($categories as $category)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 border">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <img src="{{ $category->image }}"
                                         alt="{{ $category->getTranslation('name', app()->getLocale()) }}"
                                         class="img-fluid rounded"
                                         style="max-height: 150px; width: auto; object-fit: cover;">
                                </div>
                                <h5 class="card-title mb-2">
                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                </h5>
                                @if($category->is_featured)
                                    <span class="badge bg-warning mb-2">{{ __('Featured') }}</span>
                                @endif
                                @if($category->parent)
                                    <small class="text-muted d-block mb-2">
                                        {{ __('Parent:') }} {{ $category->parent->getTranslation('name', app()->getLocale()) }}
                                    </small>
                                @else
                                    <small class="text-muted d-block mb-2">{{ __('Root Category') }}</small>
                                @endif
                                @if($category->children->count() > 0)
                                    <small class="text-muted d-block">
                                        {{ $category->children->count() }} {{ __('subcategories') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="text-muted mt-3">{{ __('No categories available yet.') }}</p>
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#requestCategoryModal">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Category') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('modals')
<!-- Request Category Modal -->
<div class="modal fade" id="requestCategoryModal" tabindex="-1" aria-labelledby="requestCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestCategoryModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>{{ __('Request New Category') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.category-requests.store') }}" method="POST" id="categoryRequestForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">{{ __('Submit a request to add a new category. Admin will review and approve it.') }}</p>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">{{ __('Category Name (English)') }} *</label>
                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                            id="name_en" name="name[en]" value="{{ old('name.en', '') }}" required>
                        @error('name.en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">{{ __('Category Name (Arabic)') }} *</label>
                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                            id="name_ar" name="name[ar]" value="{{ old('name.ar', '') }}" required>
                        @error('name.ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" rows="3"
                            placeholder="{{ __('Optional: Describe why this category is needed...') }}">{{ old('description', '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('Maximum 1000 characters') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>{{ __('Submit Request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission success
    const form = document.getElementById('categoryRequestForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Form will submit normally, modal will close via data-bs-dismiss if needed
        });
    }

    // Reset form when modal is closed
    const modal = document.getElementById('requestCategoryModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            form.reset();
            // Clear validation errors
            form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        });
    }
});
</script>
@endpush
