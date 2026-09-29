@extends('layouts.app')

@php
    $page = 'plans';
@endphp

@section('title', __('View Plan'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Plan Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View plan information') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Plan Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Plan Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 mt-4">{{ __('Name (English)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <strong>{{ is_array($plan->name) ? ($plan->name['en'] ?? '-') : $plan->name }}</strong>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Name (Arabic)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                {{ is_array($plan->name) ? ($plan->name['ar'] ?? '-') : '-' }}
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Description (English)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                {{ is_array($plan->description) ? ($plan->description['en'] ?? '-') : ($plan->description ?? '-') }}
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Description (Arabic)') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                {{ is_array($plan->description) ? ($plan->description['ar'] ?? '-') : '-' }}
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Price') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <span class="fw-bold text-primary fs-5">{{ $plan->getRawOriginal('price') }} {{ setting('currency', 'USD') }}</span>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Duration') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                <span class="badge bg-info fs-6">{{ $plan->duration_days }} {{ __('days') }}</span>
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Max Products Count') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($plan->max_products_count)
                                    <span class="badge bg-secondary">{{ $plan->max_products_count }}</span>
                                @else
                                    <span class="text-muted">{{ __('Unlimited') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Can Feature Products') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($plan->can_feature_products)
                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('No') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Status') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($plan->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Featured') }}</dt>
                            <dd class="col-sm-9 mt-4">
                                @if($plan->is_featured)
                                    <span class="badge bg-primary">{{ __('Yes') }}</span>
                                @else
                                    <span class="text-muted">{{ __('No') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3 mt-4">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $plan->created_at->format('F d, Y h:i A') }}</dd>

                            <dt class="col-sm-3 mt-4">{{ __('Updated At') }}</dt>
                            <dd class="col-sm-9 mt-4">{{ $plan->updated_at->format('F d, Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Plan') }}
                            </a>
                            <button type="button"
                                class="btn btn-danger w-100 delete-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->getTranslation('name', app()->getLocale()) }}"
                                data-delete-url="{{ route('admin.plans.destroy', $plan) }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Plan') }}
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
    // Handle plan deletion with SweetAlert2 and AJAX
    const deleteButton = document.querySelector('.delete-plan-btn');

    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();

            const planId = this.getAttribute('data-plan-id');
            const planName = this.getAttribute('data-plan-name');
            const deleteUrl = this.getAttribute('data-delete-url');

            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                html: `<div class="text-center">
                    <p>{{ __('Are you sure you want to delete this plan?') }}</p>
                    <p class="mb-0"><strong>${planName}</strong></p>
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
                                throw new Error(data.message || '{{ __('Failed to delete plan') }}');
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the plan') }}');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('Plan has been deleted successfully.') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('OK') }}',
                        confirmButtonColor: '#2c7eb8'
                    }).then(() => {
                        // Redirect to plans index page
                        window.location.href = '{{ route('admin.plans.index') }}';
                    });
                }
            });
        });
    }
});
</script>
@endpush
