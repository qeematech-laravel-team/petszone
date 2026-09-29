@extends('layouts.app')

@php
    $page = 'vendors';
@endphp

@section('title', __('Vendors'))

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
                <h1 class="h3 mb-0">{{ __('Vendors') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage vendors') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
                <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Vendor') }}
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
                                   placeholder="{{ __('Search vendors by name, phone, email...') }}"
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

        <!-- Vendors Table -->
        <div class="card">
            <div class="card-body" id="vendorsTableContainer">
                @include('admin.vendors.partials.table', ['vendors' => $vendors])
            </div>
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('admin.vendors.partials.pagination', ['vendors' => $vendors])
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Vendors') }}
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
                    <option value="">{{ __('All Vendors') }}</option>
                    <option value="1" {{ ($filters['featured'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Featured Only') }}</option>
                    <option value="0" {{ ($filters['featured'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Not Featured') }}</option>
                </select>
            </div>

            <!-- Plan Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Plan') }}</label>
                <select class="form-select" id="filterPlan" name="plan_id">
                    <option value="">{{ __('All Plans') }}</option>
                    @foreach($plans ?? [] as $plan)
                        <option value="{{ $plan->id }}" {{ ($filters['plan_id'] ?? '') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->getTranslation('name', app()->getLocale()) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Balance Range -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Balance Range') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMinBalance"
                               name="min_balance"
                               placeholder="{{ __('Min') }}"
                               value="{{ $filters['min_balance'] ?? '' }}">
                    </div>
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMaxBalance"
                               name="max_balance"
                               placeholder="{{ __('Max') }}"
                               value="{{ $filters['max_balance'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Commission Rate Range -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Commission Rate') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMinCommission"
                               name="min_commission_rate"
                               placeholder="{{ __('Min') }}"
                               value="{{ $filters['min_commission_rate'] ?? '' }}">
                    </div>
                    <div class="col-6">
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="filterMaxCommission"
                               name="max_commission_rate"
                               placeholder="{{ __('Max') }}"
                               value="{{ $filters['max_commission_rate'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Created Date -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Created Date') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" class="form-control" id="filterFromDate" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-6">
                        <input type="date" class="form-control" id="filterToDate" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Sort -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Sort') }}</label>
                <select class="form-select" id="filterSort" name="sort">
                    <option value="" {{ ($filters['sort'] ?? '') === '' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                    <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                    <option value="balance_desc" {{ ($filters['sort'] ?? '') === 'balance_desc' ? 'selected' : '' }}>{{ __('Balance (High → Low)') }}</option>
                    <option value="balance_asc" {{ ($filters['sort'] ?? '') === 'balance_asc' ? 'selected' : '' }}>{{ __('Balance (Low → High)') }}</option>
                    <option value="commission_desc" {{ ($filters['sort'] ?? '') === 'commission_desc' ? 'selected' : '' }}>{{ __('Commission (High → Low)') }}</option>
                    <option value="commission_asc" {{ ($filters['sort'] ?? '') === 'commission_asc' ? 'selected' : '' }}>{{ __('Commission (Low → High)') }}</option>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Apply Filters') }}
                </button>
                <button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('Reset') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterFeatured = document.getElementById('filterFeatured');
    const filterPlan = document.getElementById('filterPlan');
    const filterMinBalance = document.getElementById('filterMinBalance');
    const filterMaxBalance = document.getElementById('filterMaxBalance');
    const filterMinCommission = document.getElementById('filterMinCommission');
    const filterMaxCommission = document.getElementById('filterMaxCommission');
    const filterFromDate = document.getElementById('filterFromDate');
    const filterToDate = document.getElementById('filterToDate');
    const filterSort = document.getElementById('filterSort');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const vendorsTableContainer = document.getElementById('vendorsTableContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    // Function to load vendors with filters
    function loadVendors(filters = {}) {
        const params = new URLSearchParams();

        if (filters.search && filters.search.trim() !== '') {
            params.append('search', filters.search);
        }
        if (filters.status && filters.status !== '') {
            params.append('status', filters.status);
        }
        if (filters.featured && filters.featured !== '' && filters.featured !== 'undefined') {
            params.append('featured', filters.featured);
        }
        if (filters.plan_id && filters.plan_id !== '' && filters.plan_id !== 'undefined') {
            params.append('plan_id', filters.plan_id);
        }
        if (filters.min_balance && filters.min_balance !== '') {
            params.append('min_balance', filters.min_balance);
        }
        if (filters.max_balance && filters.max_balance !== '') {
            params.append('max_balance', filters.max_balance);
        }
        if (filters.min_commission_rate && filters.min_commission_rate !== '') {
            params.append('min_commission_rate', filters.min_commission_rate);
        }
        if (filters.max_commission_rate && filters.max_commission_rate !== '') {
            params.append('max_commission_rate', filters.max_commission_rate);
        }
        if (filters.from_date && filters.from_date !== '') {
            params.append('from_date', filters.from_date);
        }
        if (filters.to_date && filters.to_date !== '') {
            params.append('to_date', filters.to_date);
        }
        if (filters.sort && filters.sort !== '') {
            params.append('sort', filters.sort);
        }

        const url = '{{ route('admin.vendors.index') }}' + (params.toString() ? '?' + params.toString() : '');

        // Show loading state
        vendorsTableContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationContainer.innerHTML = '';

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            vendorsTableContainer.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;

            // Re-attach delete button event listeners
            attachDeleteListeners();

            // Update URL without reload
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            vendorsTableContainer.innerHTML = '<div class="alert alert-danger">{{ __('An error occurred while loading vendors.') }}</div>';
        });
    }

    // Function to get current filters
    function getCurrentFilters() {
        return {
            search: searchInput.value.trim(),
            status: filterStatus.value || '',
            featured: filterFeatured.value || '',
            plan_id: filterPlan.value || '',
            min_balance: filterMinBalance?.value || '',
            max_balance: filterMaxBalance?.value || '',
            min_commission_rate: filterMinCommission?.value || '',
            max_commission_rate: filterMaxCommission?.value || '',
            from_date: filterFromDate?.value || '',
            to_date: filterToDate?.value || '',
            sort: filterSort?.value || ''
        };
    }

    // Search input with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadVendors(getCurrentFilters());
        }, 500);
    });

    // Apply filters button
    applyFiltersBtn.addEventListener('click', function() {
        loadVendors(getCurrentFilters());
        // Close offcanvas
        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('filterOffcanvas'));
        if (offcanvas) {
            offcanvas.hide();
        }
    });

    // Reset filters button
    resetFiltersBtn.addEventListener('click', function() {
        filterStatus.value = '';
        filterFeatured.value = '';
        filterPlan.value = '';
        filterMinBalance.value = '';
        filterMaxBalance.value = '';
        filterMinCommission.value = '';
        filterMaxCommission.value = '';
        filterFromDate.value = '';
        filterToDate.value = '';
        filterSort.value = '';
        searchInput.value = '';
        // Clear URL parameters and reload
        window.history.pushState({}, '', '{{ route('admin.vendors.index') }}');
        loadVendors({});
    });

    // Clear all filters button
    clearFiltersBtn.addEventListener('click', function() {
        filterStatus.value = '';
        filterFeatured.value = '';
        filterPlan.value = '';
        filterMinBalance.value = '';
        filterMaxBalance.value = '';
        filterMinCommission.value = '';
        filterMaxCommission.value = '';
        filterFromDate.value = '';
        filterToDate.value = '';
        filterSort.value = '';
        searchInput.value = '';
        // Clear URL parameters and reload
        window.history.pushState({}, '', '{{ route('admin.vendors.index') }}');
        loadVendors({});
    });

    // Handle pagination links (delegate event)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('a').href;

            // Extract filters from current URL
            const currentFilters = getCurrentFilters();
            const params = new URLSearchParams(new URL(url).search);

            // Preserve filters in pagination
            if (currentFilters.search && currentFilters.search.trim() !== '') {
                params.set('search', currentFilters.search);
            }
            if (currentFilters.status && currentFilters.status !== '') {
                params.set('status', currentFilters.status);
            }
            if (currentFilters.featured && currentFilters.featured !== '' && currentFilters.featured !== 'undefined') {
                params.set('featured', currentFilters.featured);
            }
            if (currentFilters.plan_id && currentFilters.plan_id !== '' && currentFilters.plan_id !== 'undefined') {
                params.set('plan_id', currentFilters.plan_id);
            }
            if (currentFilters.min_balance && currentFilters.min_balance !== '') {
                params.set('min_balance', currentFilters.min_balance);
            }
            if (currentFilters.max_balance && currentFilters.max_balance !== '') {
                params.set('max_balance', currentFilters.max_balance);
            }
            if (currentFilters.min_commission_rate && currentFilters.min_commission_rate !== '') {
                params.set('min_commission_rate', currentFilters.min_commission_rate);
            }
            if (currentFilters.max_commission_rate && currentFilters.max_commission_rate !== '') {
                params.set('max_commission_rate', currentFilters.max_commission_rate);
            }
            if (currentFilters.from_date && currentFilters.from_date !== '') {
                params.set('from_date', currentFilters.from_date);
            }
            if (currentFilters.to_date && currentFilters.to_date !== '') {
                params.set('to_date', currentFilters.to_date);
            }
            if (currentFilters.sort && currentFilters.sort !== '') {
                params.set('sort', currentFilters.sort);
            }

            const newUrl = '{{ route('admin.vendors.index') }}' + (params.toString() ? '?' + params.toString() : '');

            fetch(newUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                vendorsTableContainer.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;

                // Re-attach delete button event listeners
                attachDeleteListeners();

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Update URL
                window.history.pushState({}, '', newUrl);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });

    // Function to attach delete button listeners
    function attachDeleteListeners() {
        const deleteButtons = document.querySelectorAll('.delete-vendor-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const vendorId = this.getAttribute('data-vendor-id');
                const vendorName = this.getAttribute('data-vendor-name');
                const deleteUrl = this.getAttribute('data-delete-url');
                const row = this.closest('tr');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    html: `<div class="text-center">
                        <p>{{ __('Are you sure you want to delete this vendor?') }}</p>
                        <p class="mb-0"><strong>${vendorName}</strong></p>
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
                                    throw new Error(data.message || '{{ __('Failed to delete vendor') }}');
                                });
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the vendor') }}');
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        // Remove the row from table with fade out animation
                        if (row) {
                            row.style.transition = 'opacity 0.3s ease';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();

                                // Check if table is empty, reload with current filters
                                const tbody = document.querySelector('table tbody');
                                if (tbody && tbody.children.length === 0) {
                                    loadVendors(getCurrentFilters());
                                }
                            }, 300);
                        }

                        // Show success message
                        Swal.fire({
                            title: '{{ __('Deleted!') }}',
                            text: result.value.message || '{{ __('Vendor has been deleted successfully.') }}',
                            icon: 'success',
                            confirmButtonText: '{{ __('OK') }}',
                            confirmButtonColor: '#2c7eb8',
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                });
            });
        });
    }

    // Initial attachment
    attachDeleteListeners();
});
</script>
@endpush
