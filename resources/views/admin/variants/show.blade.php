@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('Variant Details'))

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
                <h1 class="h3 mb-0">{{ __('Variant Details') }}</h1>
                <p class="text-muted mb-0">{{ __('View variant information and options') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.variants.edit', $variant) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.variants.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Variant Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Variant Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Name (English)') }}</label>
                                <p class="mb-0"><strong>{{ $variant->getTranslation('name', 'en') }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Name (Arabic)') }}</label>
                                <p class="mb-0"><strong>{{ $variant->getTranslation('name', 'ar') }}</strong></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Status') }}</label>
                                <p class="mb-0">
                                    @if($variant->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Required') }}</label>
                                <p class="mb-0">
                                    @if($variant->is_required)
                                        <span class="badge bg-warning">{{ __('Required') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Optional') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Created At') }}</label>
                                <p class="mb-0">{{ $variant->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Updated At') }}</label>
                                <p class="mb-0">{{ $variant->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variant Options -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>{{ __('Variant Options') }}
                            <span class="badge bg-primary ms-2">{{ $variant->options->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($variant->options->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Name (English)') }}</th>
                                            <th>{{ __('Name (Arabic)') }}</th>
                                            <th>{{ __('Code') }}</th>
                                            <th>{{ __('Created') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($variant->options as $index => $option)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $option->getTranslation('name', 'en') }}</strong></td>
                                                <td><strong>{{ $option->getTranslation('name', 'ar') }}</strong></td>
                                                <td>
                                                    <code>{{ $option->code }}</code>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $option->created_at->format('M d, Y') }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-3">{{ __('No options added yet.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>{{ __('Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.variants.edit', $variant) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit Variant') }}
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-danger delete-variant-btn"
                                    data-variant-id="{{ $variant->id }}"
                                    data-variant-name="{{ $variant->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.variants.destroy', $variant) }}">
                                <i class="bi bi-trash me-2"></i>{{ __('Delete Variant') }}
                            </button>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quick Actions') }}</label>
                            <div class="d-grid gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success toggle-active-btn"
                                        data-variant-id="{{ $variant->id }}"
                                        data-toggle-url="{{ route('admin.variants.toggle-active', $variant) }}"
                                        data-current-status="{{ $variant->is_active ? '1' : '0' }}">
                                    <i class="bi bi-toggle-{{ $variant->is_active ? 'on' : 'off' }} me-2"></i>
                                    {{ $variant->is_active ? __('Deactivate') : __('Activate') }}
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-warning toggle-required-btn"
                                        data-variant-id="{{ $variant->id }}"
                                        data-toggle-url="{{ route('admin.variants.toggle-required', $variant) }}"
                                        data-current-status="{{ $variant->is_required ? '1' : '0' }}">
                                    <i class="bi bi-{{ $variant->is_required ? 'check' : 'dash' }}-circle me-2"></i>
                                    {{ $variant->is_required ? __('Make Optional') : __('Make Required') }}
                                </button>
                            </div>
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
// Delete variant functionality
document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-variant-btn')) {
        const btn = e.target.closest('.delete-variant-btn');
        const variantName = btn.dataset.variantName;
        const deleteUrl = btn.dataset.deleteUrl;

        Swal.fire({
            title: '{{ __('Are you sure?') }}',
            text: '{{ __('You are about to delete variant') }}: ' + variantName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ __('Yes, delete it!') }}',
            cancelButtonText: '{{ __('Cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('{{ __('Deleted!') }}', data.message, 'success')
                            .then(() => window.location.href = '{{ route('admin.variants.index') }}');
                    } else {
                        Swal.fire('{{ __('Error!') }}', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('{{ __('Error!') }}', '{{ __('Something went wrong') }}', 'error');
                });
            }
        });
    }
});

// Toggle active status
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-active-btn')) {
        const btn = e.target.closest('.toggle-active-btn');
        const toggleUrl = btn.dataset.toggleUrl;

        fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                Swal.fire('{{ __('Error!') }}', data.message, 'error');
            }
        });
    }
});

// Toggle required status
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-required-btn')) {
        const btn = e.target.closest('.toggle-required-btn');
        const toggleUrl = btn.dataset.toggleUrl;

        fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                Swal.fire('{{ __('Error!') }}', data.message, 'error');
            }
        });
    }
});
</script>
@endpush
