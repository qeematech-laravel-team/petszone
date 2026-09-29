@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', __('Products'))

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
                <h1 class="h3 mb-0">{{ __('Products') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your products') }}</p>
            </div>

            @if(setting('profit_type') == 'commission' || ($currentVendor?->plan_id && setting('profit_type') == 'subscription'))
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
                @if($can_add_products && canCreateProducts() && (vendorCan('create-products') || vendorCan('manage-products')))
                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Product') }}
                </a>
                @endif
            </div>
            @endif
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
                                   placeholder="{{ __('Search products by name or SKU...') }}"
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

        <!-- Products Table -->
        <div class="card">
            @if(setting('profit_type') == 'commission' || ($currentVendor?->plan_id && setting('profit_type') == 'subscription'))
                <div class="card-body" id="productsTableContainer">
                    @include('vendor.products.partials.table', ['products' => $products])
                </div>
            @else
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-shop display-1 text-muted"></i>
                            <p class="text-muted mt-3">{{ __("You haven't subscribed to any plan yet.") }}</p>
                            <a href="{{ route('vendor.plans.index') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('Subscribe to a Plan') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('vendor.products.partials.pagination', ['products' => $products])
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Products') }}
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

            <!-- Featured Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Featured') }}</label>
                <select class="form-select" id="filterFeatured" name="featured">
                    <option value="">{{ __('All Products') }}</option>
                    <option value="1" {{ ($filters['featured'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Featured Only') }}</option>
                    <option value="0" {{ ($filters['featured'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Not Featured') }}</option>
                </select>
            </div>

            <!-- Approved Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Approved') }}</label>
                <select class="form-select" id="filterApproved" name="approved">
                    <option value="">{{ __('All Products') }}</option>
                    <option value="1" {{ ($filters['approved'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Approved Only') }}</option>
                    <option value="0" {{ ($filters['approved'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Not Approved') }}</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Type') }}</label>
                <select class="form-select" id="filterType" name="type">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="simple" {{ ($filters['type'] ?? '') === 'simple' ? 'selected' : '' }}>{{ __('Simple') }}</option>
                    <option value="variable" {{ ($filters['type'] ?? '') === 'variable' ? 'selected' : '' }}>{{ __('Variable') }}</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Category') }}</label>
                <select class="form-select" id="filterCategory" name="category_id">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->getTranslation('name', app()->getLocale()) }}
                        </option>
                        @if($category->children && $category->children->count() > 0)
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}" {{ ($filters['category_id'] ?? '') == $child->id ? 'selected' : '' }}>
                                    &nbsp;&nbsp;— {{ $child->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Stock Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Stock') }}</label>
                <select class="form-select" id="filterStock" name="stock">
                    <option value="">{{ __('All Products') }}</option>
                    <option value="in_stock" {{ ($filters['stock'] ?? '') === 'in_stock' ? 'selected' : '' }}>{{ __('In Stock') }}</option>
                    <option value="out_of_stock" {{ ($filters['stock'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
                </select>
            </div>

            <!-- Price Range -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Price Range') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMinPrice"
                               name="min_price"
                               placeholder="{{ __('Min') }}"
                               value="{{ $filters['min_price'] ?? '' }}">
                    </div>
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMaxPrice"
                               name="max_price"
                               placeholder="{{ __('Max') }}"
                               value="{{ $filters['max_price'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Is New Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('New') }}</label>
                <select class="form-select" id="filterIsNew" name="is_new">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" {{ ($filters['is_new'] ?? '') === '1' ? 'selected' : '' }}>{{ __('New Only') }}</option>
                    <option value="0" {{ ($filters['is_new'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Not New') }}</option>
                </select>
            </div>

            <!-- Bookable Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Bookable') }}</label>
                <select class="form-select" id="filterIsBookable" name="is_bookable">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" {{ ($filters['is_bookable'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Bookable Only') }}</option>
                    <option value="0" {{ ($filters['is_bookable'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Not Bookable') }}</option>
                </select>
            </div>

            <!-- Sort -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Sort') }}</label>
                <select class="form-select" id="filterSort" name="sort">
                    <option value="" {{ ($filters['sort'] ?? '') === '' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                    <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                    <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price (Low → High)') }}</option>
                    <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price (High → Low)') }}</option>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Apply Filters') }}
                </button>
                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">
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
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const productsTableContainer = document.getElementById('productsTableContainer');
    const paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;

    function loadProducts(currentFilters) {
        const params = new URLSearchParams();
        Object.keys(currentFilters).forEach(key => {
            if (currentFilters[key] !== '' && currentFilters[key] !== null && currentFilters[key] !== undefined) {
                params.append(key, currentFilters[key]);
            }
        });
        const url = '{{ route('vendor.products.index') }}' + (params.toString() ? '?' + params.toString() : '');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            productsTableContainer.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            attachEventListeners();
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            productsTableContainer.innerHTML = '<div class="alert alert-danger">{{ __('An error occurred while loading products.') }}</div>';
        });
    }

    function getCurrentFilters() {
        return {
            search: searchInput.value.trim(),
            status: document.getElementById('filterStatus')?.value || '',
            featured: document.getElementById('filterFeatured')?.value || '',
            approved: document.getElementById('filterApproved')?.value || '',
            type: document.getElementById('filterType')?.value || '',
            category_id: document.getElementById('filterCategory')?.value || '',
            stock: document.getElementById('filterStock')?.value || '',
            min_price: document.getElementById('filterMinPrice')?.value || '',
            max_price: document.getElementById('filterMaxPrice')?.value || '',
            is_new: document.getElementById('filterIsNew')?.value || '',
            is_bookable: document.getElementById('filterIsBookable')?.value || '',
            sort: document.getElementById('filterSort')?.value || ''
        };
    }

    function applyFilters() {
        loadProducts(getCurrentFilters());
        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('filterOffcanvas'));
        if (offcanvas) {
            offcanvas.hide();
        }
    }

    // Search input with debounce
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
            window.location.href = '{{ route('vendor.products.index') }}';
        });
    }

    // Apply filters button
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            applyFilters();
        });
    }

    // Handle pagination links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('a').href;
            const currentFilters = getCurrentFilters();
            const params = new URLSearchParams(new URL(url).search);

            Object.keys(currentFilters).forEach(key => {
                if (currentFilters[key]) {
                    params.set(key, currentFilters[key]);
                }
            });

            const newUrl = '{{ route('vendor.products.index') }}' + (params.toString() ? '?' + params.toString() : '');
            loadProducts(Object.fromEntries(params.entries()));
        }
    });

    // Attach event listeners for delete and toggle buttons
    function attachEventListeners() {
        // Delete button listener
        document.querySelectorAll('.delete-product-btn').forEach(button => {
            button.onclick = function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const deleteUrl = this.dataset.deleteUrl;
                const row = this.closest('tr');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    html: `<p>{{ __('You are about to delete the product:') }} <strong>${productName}</strong>.</p><p class="text-danger">{{ __('This action cannot be undone!') }}</p>`,
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
                                    throw new Error(data.message || '{{ __('Failed to delete product') }}');
                                });
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the product') }}');
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        if (row) {
                            row.style.transition = 'opacity 0.3s ease';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                const tbody = productsTableContainer.querySelector('table tbody');
                                if (!tbody || tbody.children.length === 0) {
                                    loadProducts(getCurrentFilters());
                                }
                            }, 300);
                        }
                        Swal.fire({
                            title: '{{ __('Deleted!') }}',
                            text: result.value.message || '{{ __('Product has been deleted successfully.') }}',
                            icon: 'success',
                            confirmButtonText: '{{ __('OK') }}',
                            confirmButtonColor: '#2c7eb8',
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                });
            };
        });

        // Toggle active status listener
        document.querySelectorAll('.toggle-active-btn').forEach(button => {
            button.onchange = function() {
                const toggleUrl = this.dataset.toggleUrl;
                const isActive = this.checked;

                fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        is_active: isActive
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(data.message);
                    } else {
                        this.checked = !isActive;
                        Swal.fire('{{ __('Error') }}', data.message, 'error');
                    }
                })
                .catch(error => {
                    this.checked = !isActive;
                    Swal.fire('{{ __('Error') }}', '{{ __('Failed to update status.') }}', 'error');
                    console.error('Error:', error);
                });
            };
        });

        // Toggle featured status listener
        document.querySelectorAll('.toggle-featured-btn').forEach(button => {
            button.onchange = function() {
                const toggleUrl = this.dataset.toggleUrl;
                const isFeatured = this.checked;

                fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        is_featured: isFeatured
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(data.message);
                    } else {
                        this.checked = !isFeatured;
                        Swal.fire('{{ __('Error') }}', data.message, 'error');
                    }
                })
                .catch(error => {
                    this.checked = !isFeatured;
                    Swal.fire('{{ __('Error') }}', '{{ __('Failed to update featured status.') }}', 'error');
                    console.error('Error:', error);
                });
            };
        });

    }

    // Initial attachment of event listeners
    attachEventListeners();
});
</script>
@endpush
