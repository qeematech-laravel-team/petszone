@extends('layouts.app')

@php
    $page = 'branches';
@endphp

@section('title', __('Branch Details'))

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
                <h1 class="h3 mb-0">{{ __('Branch Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View branch information') }}</p>
            </div>
            <div class="d-flex gap-2">
                @if(vendorCan('edit-branches') || vendorCan('manage-branches'))
                    <a href="{{ route('vendor.branches.edit', $branch) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>{{ __('Edit Branch') }}
                    </a>
                @endif
                <a href="{{ route('vendor.branches.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Branch Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Branch Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Name (English)') }}</label>
                                <p class="mb-0"><strong>{{ $branch->getTranslation('name', 'en') }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Name (Arabic)') }}</label>
                                <p class="mb-0"><strong>{{ $branch->getTranslation('name', 'ar') }}</strong></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Status') }}</label>
                                <p class="mb-0">
                                    @if($branch->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">{{ __('Address') }}</label>
                            <p class="mb-0">{{ $branch->address }}</p>
                        </div>

                        @if($branch->phone)
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('Phone') }}</label>
                                <p class="mb-0">
                                    <a href="tel:{{ $branch->phone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i>{{ $branch->phone }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if($branch->latitude && $branch->longitude)
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('Location') }}</label>
                                <p class="mb-0">
                                    <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-geo-alt me-1"></i>{{ __('View on Google Maps') }}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        {{ __('Latitude') }}: {{ $branch->latitude }}, {{ __('Longitude') }}: {{ $branch->longitude }}
                                    </small>
                                </p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Created At') }}</label>
                                <p class="mb-0">{{ $branch->created_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $branch->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Updated At') }}</label>
                                <p class="mb-0">{{ $branch->updated_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $branch->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Preview -->
                @if($branch->latitude && $branch->longitude)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-map me-2"></i>{{ __('Location Map') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="ratio ratio-16x9">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3620.0!2d{{ $branch->longitude }}!3d{{ $branch->latitude }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z{{ $branch->latitude }}%2C{{ $branch->longitude }}!5e0!3m2!1sen!2s!4v1234567890!5m2!1sen!2s"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                @endif
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
                            @if(vendorCan('edit-branches') || vendorCan('manage-branches'))
                                <a href="{{ route('vendor.branches.edit', $branch) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-2"></i>{{ __('Edit Branch') }}
                                </a>
                            @endif
                            @if(vendorCan('delete-branches') || vendorCan('manage-branches'))
                                <button type="button" class="btn btn-outline-danger delete-branch-btn"
                                        data-branch-id="{{ $branch->id }}"
                                        data-branch-name="{{ $branch->getTranslation('name', app()->getLocale()) }}"
                                        data-delete-url="{{ route('vendor.branches.destroy', $branch) }}">
                                    <i class="bi bi-trash me-2"></i>{{ __('Delete Branch') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status -->
                @if(vendorCan('edit-branches') || vendorCan('manage-branches'))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Status') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ __('Active') }}:</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-active-btn" type="checkbox"
                                    id="toggleActive{{ $branch->id }}"
                                    data-branch-id="{{ $branch->id }}"
                                    data-toggle-url="{{ route('vendor.branches.toggle-active', $branch) }}"
                                    {{ $branch->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleActive{{ $branch->id }}"></label>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

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
                        window.location.href = '{{ route('vendor.branches.index') }}';
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
