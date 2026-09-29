@extends('layouts.app')

@php
    $page = 'categories';
@endphp

@section('title', 'View Category')

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Category Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View category information') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Category Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Category Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 mt-4">{{ __('Name (English)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <strong>{{ is_array($category->name) ? ($category->name['en'] ?? '-') : $category->name }}</strong>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Name (Arabic)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                {{ is_array($category->name) ? ($category->name['ar'] ?? '-') : '-' }}
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Parent Category') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}">
                                        {{ is_array($category->parent->name) ? $category->parent->name[app()->getLocale()] ?? reset($category->parent->name) : $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('Root Category') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Status') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($category->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Featured') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($category->is_featured)
                                    <span class="badge bg-primary">{{ __('Yes') }}</span>
                                @else
                                    <span class="text-muted">{{ __('No') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Subcategories') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($category->children->count() > 0)
                                    <span class="badge bg-info">{{ $category->children->count() }} {{ __('subcategories') }}</span>
                                    <ul class="list-unstyled mt-2">
                                        @foreach($category->children as $child)
                                            <li>
                                                <a href="{{ route('admin.categories.show', $child) }}">
                                                    {{ is_array($child->name) ? $child->name[app()->getLocale()] ?? reset($child->name) : $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">{{ __('No subcategories') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $category->created_at->format('F d, Y h:i A') }}</dd>

                            <dt class="col-sm-3 mt-4">{{ __('Updated At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $category->updated_at->format('F d, Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Category Image -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Category Image') }}</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $category->image }}" alt="{{ is_array($category->name) ? reset($category->name) : $category->name }}"
                            class="img-fluid rounded">
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Category') }}
                            </a>
                            <button type="button"
                                class="btn btn-danger w-100 delete-category-btn"
                                data-category-id="{{ $category->id }}"
                                data-category-name="{{ $category->getTranslation('name', app()->getLocale()) }}"
                                data-delete-url="{{ route('admin.categories.destroy', $category) }}"
                                data-has-children="{{ $category->children->count() > 0 ? 'true' : 'false' }}"
                                data-children-count="{{ $category->children->count() }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Category') }}
                            </button>
                        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Handle category deletion with SweetAlert2 and AJAX
    const deleteButton = document.querySelector('.delete-category-btn');

    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();

            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            const deleteUrl = this.getAttribute('data-delete-url');
            const hasChildren = this.getAttribute('data-has-children') === 'true';
            const childrenCount = parseInt(this.getAttribute('data-children-count')) || 0;

            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                html: `<div class="text-center">
                    <p>{{ __('Are you sure you want to delete this category?') }}</p>
                    <p class="mb-0"><strong>${categoryName}</strong></p>
                    ${hasChildren ? `<p class="text-danger mt-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('This category has') }} ${childrenCount} {{ __('subcategories. Deleting it will also delete all subcategories.') }}</p>` : ''}
                    <p class="text-danger mt-2"><small>{{ __('This action cannot be undone!') }}</small></p>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>{{ __('Yes, delete it!') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                reverseButtons: true,
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || '{{ __('Failed to delete category') }}');
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the category') }}');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('Category has been deleted successfully.') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('OK') }}',
                        confirmButtonColor: '#2c7eb8'
                    }).then(() => {
                        // Redirect to categories index page
                        window.location.href = '{{ route('admin.categories.index') }}';
                    });
                }
            });
        });
    }
});
</script>
@endpush
