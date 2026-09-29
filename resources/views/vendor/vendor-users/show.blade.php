@extends('layouts.app')

@php
    $page = 'vendor-users';
@endphp

@section('title', __('Vendor User Details'))

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
                <h1 class="h3 mb-0">{{ __('Vendor User Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View user information') }}</p>
            </div>
            <div class="d-flex gap-2">
                @if(vendorCan('edit-vendor-users') || vendorCan('manage-vendor-users'))
                    <a href="{{ route('vendor.vendor-users.edit', $vendorUser) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>{{ __('Edit User') }}
                    </a>
                @endif
                <a href="{{ route('vendor.vendor-users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>{{ __('User Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Name') }}</label>
                                <p class="mb-0"><strong>{{ $vendorUser->user->name }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Status') }}</label>
                                <p class="mb-0">
                                    @if($vendorUser->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($vendorUser->user->email)
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('Email') }}</label>
                                <p class="mb-0">
                                    <a href="mailto:{{ $vendorUser->user->email }}" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i>{{ $vendorUser->user->email }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if($vendorUser->user->phone)
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('Phone') }}</label>
                                <p class="mb-0">
                                    <a href="tel:{{ $vendorUser->user->phone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i>{{ $vendorUser->user->phone }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Created At') }}</label>
                                <p class="mb-0">{{ $vendorUser->created_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $vendorUser->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Updated At') }}</label>
                                <p class="mb-0">{{ $vendorUser->updated_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $vendorUser->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>{{ __('Permissions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($vendorUser->user->permissions as $permission)
                                <span class="badge bg-primary-subtle text-primary-emphasis p-2">
                                    {{ __(App\Services\VendorUserService::PERMISSIONS[$permission->name]['label'] ?? str_replace('-', ' ', $permission->name)) }}
                                </span>
                            @empty
                                <p class="text-muted mb-0">{{ __('This user has no permissions assigned.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Quick Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if(vendorCan('edit-vendor-users') || vendorCan('manage-vendor-users'))
                                <a href="{{ route('vendor.vendor-users.edit', $vendorUser) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-2"></i>{{ __('Edit User') }}
                                </a>
                            @endif
                            @if((vendorCan('delete-vendor-users') || vendorCan('manage-vendor-users')) && $vendorUser->user_id !== auth()->id())
                                <button type="button" class="btn btn-outline-danger delete-vendor-user-btn"
                                        data-vendor-user-id="{{ $vendorUser->id }}"
                                        data-vendor-user-name="{{ $vendorUser->user->name }}"
                                        data-delete-url="{{ route('vendor.vendor-users.destroy', $vendorUser) }}">
                                    <i class="bi bi-trash me-2"></i>{{ __('Delete User') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Status') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ __('Active') }}:</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-active-btn" type="checkbox"
                                    id="toggleActive{{ $vendorUser->id }}"
                                    data-vendor-user-id="{{ $vendorUser->id }}"
                                    data-toggle-url="{{ route('vendor.vendor-users.toggle-active', $vendorUser) }}"
                                    {{ $vendorUser->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleActive{{ $vendorUser->id }}"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        window.location.href = '{{ route('vendor.vendor-users.index') }}';
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
