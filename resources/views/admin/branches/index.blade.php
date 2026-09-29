@extends('layouts.app')

@php
    $page = 'branches';
@endphp

@section('title', __('Branches'))

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
                <h1 class="h3 mb-0">{{ __('Branches') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage vendor branches') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>
                <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Branch') }}
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
                                   placeholder="{{ __('Search branches by name, address, or phone...') }}"
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

        <!-- Branches Table -->
        <div class="card">
            <div class="card-body" id="branchesTableContainer">
                @include('admin.branches.partials.table', ['branches' => $branches])
            </div>
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('admin.branches.partials.pagination', ['branches' => $branches])
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Branches') }}
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

            <!-- Vendor Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('Vendor') }}</label>
                <select class="form-select" id="filterVendor" name="vendor_id">
                    <option value="">{{ __('All Vendors') }}</option>
                    @foreach($vendors ?? [] as $vendor)
                        <option value="{{ $vendor->id }}" {{ ($filters['vendor_id'] ?? '') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->getTranslation('name', app()->getLocale()) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Apply Filters') }}
                </button>
                <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">
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
    const branchesTableContainer = document.getElementById('branchesTableContainer');
    const paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;

    function loadBranches(currentFilters) {
        const params = new URLSearchParams();
        Object.keys(currentFilters).forEach(key => {
            if (currentFilters[key] !== '' && currentFilters[key] !== null && currentFilters[key] !== undefined) {
                params.append(key, currentFilters[key]);
            }
        });
        const url = '{{ route('admin.branches.index') }}' + (params.toString() ? '?' + params.toString() : '');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            branchesTableContainer.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            attachEventListeners();
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            branchesTableContainer.innerHTML = '<div class="alert alert-danger">{{ __('An error occurred while loading branches.') }}</div>';
        });
    }

    function getCurrentFilters() {
        return {
            search: searchInput.value.trim(),
            status: document.getElementById('filterStatus')?.value || '',
            vendor_id: document.getElementById('filterVendor')?.value || ''
        };
    }

    function applyFilters() {
        loadBranches(getCurrentFilters());
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
            window.location.href = '{{ route('admin.branches.index') }}';
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

            const newUrl = '{{ route('admin.branches.index') }}' + (params.toString() ? '?' + params.toString() : '');
            loadBranches(Object.fromEntries(params.entries()));
        }
    });

    // Attach event listeners for delete and toggle buttons
    function attachEventListeners() {
        // Delete button listener
        document.querySelectorAll('.delete-branch-btn').forEach(button => {
            button.onclick = function() {
                const branchId = this.dataset.branchId;
                const branchName = this.dataset.branchName;
                const deleteUrl = this.dataset.deleteUrl;
                const row = this.closest('tr');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    html: `<p>{{ __('You are about to delete the branch:') }} <strong>${branchName}</strong>.</p><p class="text-danger">{{ __('This action cannot be undone!') }}</p>`,
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
                                    throw new Error(data.message || '{{ __('Failed to delete branch') }}');
                                });
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the branch') }}');
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
                                const tbody = branchesTableContainer.querySelector('table tbody');
                                if (!tbody || tbody.children.length === 0) {
                                    loadBranches(getCurrentFilters());
                                }
                            }, 300);
                        }
                        Swal.fire({
                            title: '{{ __('Deleted!') }}',
                            text: result.value.message || '{{ __('Branch has been deleted successfully.') }}',
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
    }

    // Initial attachment of event listeners
    attachEventListeners();
});
</script>
@endpush
