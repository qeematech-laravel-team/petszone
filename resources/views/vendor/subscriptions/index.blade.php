@extends('layouts.app')

@php
    $page = 'subscriptions';
@endphp

@section('title', __('Subscriptions'))

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
                <h1 class="h3 mb-0">{{ __('Subscriptions') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage Your Subscriptions and Plans') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-2"></i>{{ __('Filters') }}
                </button>

            </div>
        </div>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        {{-- <div class="input-group"> --}}
                            {{-- <span class="input-group-text"><i class="bi bi-search"></i></span> --}}
                            <input type="hidden"
                                   class="form-control"
                                   id="searchInput"
                                   value="{{ $filters['search'] ?? '' }}">
                        {{-- </div> --}}
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-danger w-100" id="clearFiltersBtn">
                            <i class="bi bi-x-circle me-2"></i>{{ __('Clear Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="card">
            <div class="card-body" id="subscriptionsTableContainer">
                @include('vendor.subscriptions.partials.table', ['subscriptions' => $subscriptions])
            </div>
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('vendor.subscriptions.partials.pagination', ['subscriptions' => $subscriptions])
        </div>

    </div>

@endsection

@push('modals')
<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>{{ __('Filter Subscriptions') }}
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
                    <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>


            <!-- Filter Actions -->
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Apply Filters') }}
                </button>
                <a href="{{ route('vendor.subscriptions.index') }}" class="btn btn-outline-secondary">
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
    const subscriptionsTableContainer = document.getElementById('subscriptionsTableContainer');
    const paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;

    function loadSubscriptions(currentFilters) {
        const params = new URLSearchParams();
        Object.keys(currentFilters).forEach(key => {
            if (currentFilters[key] !== '' && currentFilters[key] !== null && currentFilters[key] !== undefined) {
                params.append(key, currentFilters[key]);
            }
        });
        const url = '{{ route('vendor.subscriptions.index') }}' + (params.toString() ? '?' + params.toString() : '');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            subscriptionsTableContainer.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            attachEventListeners();
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            subscriptionsTableContainer.innerHTML = '<div class="alert alert-danger">{{ __('An error occurred while loading subscriptions.') }}</div>';
        });
    }

    function getCurrentFilters() {
        return {
            search: searchInput.value.trim(),
            status: document.getElementById('filterStatus')?.value || '',
        };
    }

    function applyFilters() {
        loadSubscriptions(getCurrentFilters());
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
            window.location.href = '{{ route('vendor.subscriptions.index') }}';
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

            const newUrl = '{{ route('vendor.subscriptions.index') }}' + (params.toString() ? '?' + params.toString() : '');
            loadSubscriptions(Object.fromEntries(params.entries()));
        }
    });

    // Attach event listeners for delete and toggle buttons
    function attachEventListeners() {


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
