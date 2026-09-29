@extends('layouts.app')

@php
    $page = 'sliders';
@endphp

@section('title', __('Slider Details'))

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
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">{{ __('Sliders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Slider Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Slider Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View slider information') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Slider Image -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Slider Image') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($slider->image)
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $slider->image) }}"
                                     alt="{{ __('Slider Image') }}"
                                     class="img-fluid rounded shadow-sm"
                                     style="max-height: 500px;">
                            </div>
                        @else
                            <div class="text-center py-5 bg-light rounded">
                                <i class="bi bi-image fs-1 text-muted"></i>
                                <p class="text-muted mt-3">{{ __('No image available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Slider Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 mt-3">{{ __('ID') }}</dt>
                            <dd class="col-sm-7 mt-3">
                                <code>#{{ $slider->id }}</code>
                            </dd>

                            <dt class="col-sm-5 mt-3">{{ __('Image') }}</dt>
                            <dd class="col-sm-7 mt-3">
                                @if($slider->image)
                                    <a href="{{ asset('storage/' . $slider->image) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Open') }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5 mt-3">{{ __('Created At') }}</dt>
                            <dd class="col-sm-7 mt-3">
                                <small class="text-muted">{{ $slider->created_at->format('M d, Y H:i') }}</small>
                            </dd>

                            <dt class="col-sm-5 mt-3">{{ __('Updated At') }}</dt>
                            <dd class="col-sm-7 mt-3">
                                <small class="text-muted">{{ $slider->updated_at->format('M d, Y H:i') }}</small>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Quick Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Slider') }}
                            </a>
                            <button type="button"
                                    class="btn btn-danger delete-slider-btn"
                                    data-id="{{ $slider->id }}"
                                    data-image="{{ $slider->image }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Slider') }}
                            </button>
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
        const deleteButton = document.querySelector('.delete-slider-btn');

        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const sliderId = this.getAttribute('data-id');
                const sliderImage = this.getAttribute('data-image');

                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    text: '{{ __('You are about to delete this slider. This action cannot be undone!') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __('Yes, delete it!') }}',
                    cancelButtonText: '{{ __('Cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('admin.sliders.destroy', ':id') }}'.replace(':id', sliderId);
                        form.innerHTML = '@csrf @method('DELETE')';
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
