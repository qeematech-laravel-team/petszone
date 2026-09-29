@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('Variants'))

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
                <h1 class="h3 mb-0">{{ __('Variants') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage product variants and options') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
                <a href="{{ route('admin.variants.export', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>{{ __('Export') }}
                </a>
                <a href="{{ route('admin.variants.import') }}" class="btn btn-info text-white">
                    <i class="bi bi-upload me-2"></i>{{ __('Import') }}
                </a>
                <a href="{{ route('admin.variants.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Variant') }}
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="searchInput"
                                   placeholder="{{ __('Search variants by name...') }}"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-danger w-100" id="clearFiltersBtn">
                            <i class="bi bi-x-circle me-2"></i>{{ __('Clear Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants Table -->
        <div class="card">
            <div class="card-body" id="variantsTableContainer">
                @include('admin.variants.partials.table', ['variants' => $variants])
            </div>
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('admin.variants.partials.pagination', ['variants' => $variants])
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Variants') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="filterForm">
            <!-- Status Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Status') }}</label>
                <select class="form-select" id="filterStatus" name="status">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>

            <!-- Required Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Required') }}</label>
                <select class="form-select" id="filterRequired" name="required">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" {{ ($filters['required'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Required') }}</option>
                    <option value="0" {{ ($filters['required'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Optional') }}</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-2"></i>{{ __('Apply Filters') }}
                </button>
                <a href="{{ route('admin.variants.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const filterForm = document.getElementById('filterForm');
    let searchTimeout;

    // Debounced search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        });
    }

    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            window.location.href = '{{ route('admin.variants.index') }}';
        });
    }

    // Filter form submission
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }

    function applyFilters() {
        const params = new URLSearchParams();
        const search = searchInput.value.trim();
        const status = document.getElementById('filterStatus')?.value || '';
        const required = document.getElementById('filterRequired')?.value || '';

        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (required) params.set('required', required);

        const url = '{{ route('admin.variants.index') }}' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }

    // Delete variant functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-variant-btn')) {
            const btn = e.target.closest('.delete-variant-btn');
            const variantName = btn.dataset.variantName;
            const deleteUrl = btn.dataset.deleteUrl;

            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: '{{ __('You are about to delete variant') }}: ' + variantName,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __('Yes, delete it!') }}',
                cancelButtonText: '{{ __('Cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('{{ __('Deleted!') }}', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('{{ __('Error!') }}', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('{{ __('Error!') }}', '{{ __('Something went wrong') }}', 'error');
                    });
                }
            });
        }
    });

    // Toggle active status
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('toggle-active')) {
            const checkbox = e.target;
            const toggleUrl = checkbox.dataset.toggleUrl;
            const originalChecked = checkbox.checked;

            checkbox.disabled = true;

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                checkbox.disabled = false;
                if (data.success) {
                    const label = checkbox.nextElementSibling;
                    if (label) {
                        if (checkbox.checked) {
                            label.innerHTML = '<span class="badge bg-success">{{ __('Active') }}</span>';
                        } else {
                            label.innerHTML = '<span class="badge bg-secondary">{{ __('Inactive') }}</span>';
                        }
                    }
                } else {
                    checkbox.checked = !originalChecked;
                    Swal.fire('{{ __('Error!') }}', data.message, 'error');
                }
            })
            .catch(error => {
                checkbox.disabled = false;
                checkbox.checked = !originalChecked;
                Swal.fire('{{ __('Error!') }}', '{{ __('Something went wrong') }}', 'error');
            });
        }
    });

    // Toggle required status
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('toggle-required')) {
            const checkbox = e.target;
            const toggleUrl = checkbox.dataset.toggleUrl;
            const originalChecked = checkbox.checked;

            checkbox.disabled = true;

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                checkbox.disabled = false;
                if (data.success) {
                    const label = checkbox.nextElementSibling;
                    if (label) {
                        if (checkbox.checked) {
                            label.innerHTML = '<span class="badge bg-warning">{{ __('Required') }}</span>';
                        } else {
                            label.innerHTML = '<span class="badge bg-secondary">{{ __('Optional') }}</span>';
                        }
                    }
                } else {
                    checkbox.checked = !originalChecked;
                    Swal.fire('{{ __('Error!') }}', data.message, 'error');
                }
            })
            .catch(error => {
                checkbox.disabled = false;
                checkbox.checked = !originalChecked;
                Swal.fire('{{ __('Error!') }}', '{{ __('Something went wrong') }}', 'error');
            });
        }
    });
});
</script>
@endpush
