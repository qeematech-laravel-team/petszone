@extends('layouts.app')

@php
    $page = 'vendors';
@endphp

@section('title', __('View Vendor'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Vendor Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View vendor information') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Vendor Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Vendor Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 mt-4">{{ __('Name (English)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <strong>{{ is_array($vendor->name) ? ($vendor->name['en'] ?? '-') : $vendor->name }}</strong>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Name (Arabic)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                {{ is_array($vendor->name) ? ($vendor->name['ar'] ?? '-') : '-' }}
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Owner') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($vendor->owner)
                                    <div>
                                        <div class="fw-medium">{{ $vendor->owner->name }}</div>
                                        <small class="text-muted">{{ $vendor->owner->email }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Phone') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $vendor->phone ?? '-' }}</dd>

                            <dt class="col-sm-3 mt-4">{{ __('Address') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $vendor->address ?? '-' }}</dd>

                            <dt class="col-sm-3 mt-4">{{ __('Subscription Plan') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($vendor->plan)
                                    <span class="badge bg-info">{{ $vendor->plan->getTranslation('name', app()->getLocale()) }}</span>
                                    @if($vendor->subscription_start && $vendor->subscription_end)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                {{ __('From') }}: {{ \Carbon\Carbon::parse($vendor->subscription_start)->format('M d, Y') }}<br>
                                                {{ __('To') }}: {{ \Carbon\Carbon::parse($vendor->subscription_end)->format('M d, Y') }}
                                            </small>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('No Plan') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Balance') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <span class="fw-bold text-success fs-5">{{ number_format($vendor->balance, 2) }} {{ setting('currency', 'USD') }}</span>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Commission Rate') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <span class="badge bg-secondary">{{ $vendor->commission_rate }}%</span>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Status') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($vendor->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Featured') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($vendor->is_featured)
                                    <span class="badge bg-primary">{{ __('Yes') }}</span>
                                @else
                                    <span class="text-muted">{{ __('No') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $vendor->created_at->format('F d, Y h:i A') }}</dd>

                            <dt class="col-sm-3 mt-4">{{ __('Updated At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $vendor->updated_at->format('F d, Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Vendor Image and Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Vendor Image') }}</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $vendor->image }}" alt="{{ is_array($vendor->name) ? reset($vendor->name) : $vendor->name }}"
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
                            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Vendor') }}
                            </a>
                            <button type="button"
                                class="btn btn-danger w-100 delete-vendor-btn"
                                data-vendor-id="{{ $vendor->id }}"
                                data-vendor-name="{{ $vendor->getTranslation('name', app()->getLocale()) }}"
                                data-delete-url="{{ route('admin.vendors.destroy', $vendor) }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Vendor') }}
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
    // Handle vendor deletion with SweetAlert2 and AJAX
    const deleteButton = document.querySelector('.delete-vendor-btn');

    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();

            const vendorId = this.getAttribute('data-vendor-id');
            const vendorName = this.getAttribute('data-vendor-name');
            const deleteUrl = this.getAttribute('data-delete-url');

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
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('Vendor has been deleted successfully.') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('OK') }}',
                        confirmButtonColor: '#2c7eb8'
                    }).then(() => {
                        // Redirect to vendors index page
                        window.location.href = '{{ route('admin.vendors.index') }}';
                    });
                }
            });
        });
    }
});
</script>
@endpush
