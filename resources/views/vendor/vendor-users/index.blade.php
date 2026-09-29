@extends('layouts.app')

@php
    $page = 'vendor-users';
@endphp

@section('title', __('Vendor Users'))

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
                <h1 class="h3 mb-0">{{ __('Vendor Users') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage users associated with your vendor account') }}</p>
            </div>
            <div>
                @if(vendorCan('create-vendor-users') || vendorCan('manage-vendor-users'))
                    <a href="{{ route('vendor.vendor-users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>{{ __('Add User') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Vendor Users List -->
        <div class="row g-4">
            @forelse($vendorUsers as $vendorUser)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    {{ $vendorUser->user->name }}
                                </h5>
                                @if((vendorCan('manage-vendor-users') || vendorCan('edit-vendor-users')) && $vendorUser->user_id !== auth()->id())
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-active-btn" type="checkbox"
                                            id="toggleActive{{ $vendorUser->id }}"
                                            data-vendor-user-id="{{ $vendorUser->id }}"
                                            data-toggle-url="{{ route('vendor.vendor-users.toggle-active', $vendorUser) }}"
                                            {{ $vendorUser->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toggleActive{{ $vendorUser->id }}"></label>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-2">
                                @if($vendorUser->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </div>

                            @if($vendorUser->user->email)
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-envelope me-1"></i>
                                    <a href="mailto:{{ $vendorUser->user->email }}" class="text-decoration-none">{{ $vendorUser->user->email }}</a>
                                </p>
                            @endif

                            @if($vendorUser->user->phone)
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-telephone me-1"></i>
                                    <a href="tel:{{ $vendorUser->user->phone }}" class="text-decoration-none">{{ $vendorUser->user->phone }}</a>
                                </p>
                            @endif

                            <div class="d-flex gap-2">
                                @if(vendorCan('view-vendor-users') || vendorCan('manage-vendor-users'))
                                    <a href="{{ route('vendor.vendor-users.show', $vendorUser) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                    </a>
                                @endif
                                @if(vendorCan('edit-vendor-users') || vendorCan('manage-vendor-users'))
                                    <a href="{{ route('vendor.vendor-users.edit', $vendorUser) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                                    </a>
                                @endif
                                @if((vendorCan('delete-vendor-users') || vendorCan('manage-vendor-users')) && $vendorUser->user_id !== auth()->id())
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-vendor-user-btn"
                                            data-vendor-user-id="{{ $vendorUser->id }}"
                                            data-vendor-user-name="{{ $vendorUser->user->name }}"
                                            data-delete-url="{{ route('vendor.vendor-users.destroy', $vendorUser) }}">
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
                            <i class="bi bi-people display-1 text-muted"></i>
                            <p class="text-muted mt-3">{{ __("You haven't added any users yet.") }}</p>
                            @if(vendorCan('create-vendor-users') || vendorCan('manage-vendor-users'))
                                <a href="{{ route('vendor.vendor-users.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First User') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button listener
    document.querySelectorAll('.delete-vendor-user-btn').forEach(button => {
        button.onclick = function() {
            const vendorUserId = this.dataset.vendorUserId;
            const vendorUserName = this.dataset.vendorUserName;
            const deleteUrl = this.dataset.deleteUrl;

            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                html: `<p>{{ __('You are about to delete the user:') }} <strong>${vendorUserName}</strong>.</p><p class="text-danger">{{ __('This action cannot be undone!') }}</p>`,
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
                                throw new Error(data.message || '{{ __('Failed to delete user') }}');
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the user') }}');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('User has been deleted successfully.') }}',
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
