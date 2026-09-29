@extends('layouts.app')

@php
    $page = 'branches';
    use Illuminate\Support\Str;
@endphp

@section('title', __('My Branches'))

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
                <h1 class="h3 mb-0">{{ __('My Branches') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your branches') }}</p>
            </div>
            <div>
                @if(setting('profit_type') == 'commission' || ($currentVendor?->plan_id && setting('profit_type') == 'subscription'))
                    @if(vendorCan('create-branches') || vendorCan('manage-branches'))
                        <a href="{{ route('vendor.branches.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Branch') }}
                        </a>
                    @endif
                @endif
            </div>
        </div>

        @if(setting('profit_type') == 'commission' || (auth()->user()->vendor()->plan_id && setting('profit_type') == 'subscription'))
        <!-- Branches List -->
        <div class="row g-4">
            @forelse($branches as $branch)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    {{ $branch->getTranslation('name', app()->getLocale()) }}
                                </h5>
                                @if(vendorCan('manage-branches') || vendorCan('edit-branches'))
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-active-btn" type="checkbox"
                                            id="toggleActive{{ $branch->id }}"
                                            data-branch-id="{{ $branch->id }}"
                                            data-toggle-url="{{ route('vendor.branches.toggle-active', $branch) }}"
                                            {{ $branch->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toggleActive{{ $branch->id }}"></label>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-2">
                                @if($branch->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </div>

                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($branch->address, 60) }}
                            </p>

                            @if($branch->phone)
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-telephone me-1"></i>
                                    <a href="tel:{{ $branch->phone }}" class="text-decoration-none">{{ $branch->phone }}</a>
                                </p>
                            @endif

                            @if($branch->latitude && $branch->longitude)
                                <p class="text-muted small mb-3">
                                    <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-map me-1"></i>{{ __('View on Map') }}
                                    </a>
                                </p>
                            @endif

                            <div class="d-flex gap-2">
                                @if(vendorCan('view-branches') || vendorCan('manage-branches'))
                                    <a href="{{ route('vendor.branches.show', $branch) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                    </a>
                                @endif
                                @if(vendorCan('edit-branches') || vendorCan('manage-branches'))
                                    <a href="{{ route('vendor.branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                                    </a>
                                @endif
                                @if(vendorCan('delete-branches') || vendorCan('manage-branches'))
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-branch-btn"
                                            data-branch-id="{{ $branch->id }}"
                                            data-branch-name="{{ $branch->getTranslation('name', app()->getLocale()) }}"
                                            data-delete-url="{{ route('vendor.branches.destroy', $branch) }}">
                                        <i class="bi bi-trash me-1"></i>{{ __('Delete') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-shop display-1 text-muted"></i>
                            <p class="text-muted mt-3">{{ __("You haven't added any branches yet.") }}</p>
                            @if(vendorCan('create-branches') || vendorCan('manage-branches'))
                                <a href="{{ route('vendor.branches.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First Branch') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforelse
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button listener
    document.querySelectorAll('.delete-branch-btn').forEach(button => {
        button.onclick = function() {
            const branchId = this.dataset.branchId;
            const branchName = this.dataset.branchName;
            const deleteUrl = this.dataset.deleteUrl;

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
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('Branch has been deleted successfully.') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('OK') }}',
                        confirmButtonColor: '#2c7eb8',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.reload();
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
});
</script>
@endpush
