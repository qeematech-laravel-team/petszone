@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('Variants'))

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
                <h1 class="h3 mb-0">{{ __('Variants') }}</h1>
                <p class="text-muted mb-0">{{ __('Browse available product variants') }}</p>
            </div>
            @if(setting('profit_type') == 'commission' || ($currentVendor?->plan_id && setting('profit_type') == 'subscription'))
            <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestVariantModal">
                        <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Variant') }}
                    </button>
                </div>
            @endif
        </div>

        <!-- Variants List -->
        <div class="row g-4">
            @if(setting('profit_type') == 'commission' || ($currentVendor?->plan_id && setting('profit_type') == 'subscription'))
                @forelse($variants as $variant)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-2">
                                    {{ $variant->getTranslation('name', app()->getLocale()) }}
                                </h5>
                                <div class="mb-2">
                                    @if($variant->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                    @if($variant->is_required)
                                        <span class="badge bg-warning">{{ __('Required') }}</span>
                                    @endif
                                </div>

                                @if($variant->options->count() > 0)
                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1"><strong>{{ __('Options') }}:</strong></small>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($variant->options->take(5) as $option)
                                                <span class="badge bg-info">
                                                    {{ $option->getTranslation('name', app()->getLocale()) }}
                                                </span>
                                            @endforeach
                                            @if($variant->options->count() > 5)
                                                <span class="badge bg-secondary">+{{ $variant->options->count() - 5 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted d-block">{{ __('No options available') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-tags display-1 text-muted"></i>
                                <p class="text-muted mt-3">{{ __('No variants available yet.') }}</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#requestVariantModal">
                                    <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Variant') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforelse
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
    </div>
@endsection

@push('modals')
<!-- Request Variant Modal -->
<div class="modal fade" id="requestVariantModal" tabindex="-1" aria-labelledby="requestVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestVariantModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>{{ __('Request New Variant') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.variant-requests.store') }}" method="POST" id="variantRequestForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">{{ __('Submit a request to add a new variant. Admin will review and approve it.') }}</p>

                    <div class="mb-3">
                        <label for="variant_name_en" class="form-label">{{ __('Variant Name (English)') }} *</label>
                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                            id="variant_name_en" name="name[en]" value="{{ old('name.en', '') }}" required>
                        @error('name.en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="variant_name_ar" class="form-label">{{ __('Variant Name (Arabic)') }} *</label>
                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                            id="variant_name_ar" name="name[ar]" value="{{ old('name.ar', '') }}" required>
                        @error('name.ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Variant Options -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0"><strong>{{ __('Variant Options') }}</strong></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addVariantOptionBtn">
                                <i class="bi bi-plus"></i> {{ __('Add Option') }}
                            </button>
                        </div>
                        <small class="text-muted d-block mb-3">{{ __('Optional: Add options for this variant') }}</small>
                        <div id="variantOptionsContainer"></div>
                    </div>

                    <div class="mb-3">
                        <label for="variant_description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="variant_description" name="description" rows="3"
                            placeholder="{{ __('Optional: Describe why this variant is needed...') }}">{{ old('description', '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('Maximum 1000 characters') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>{{ __('Submit Request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let variantOptionIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('variantRequestForm');
    const modal = document.getElementById('requestVariantModal');
    const addOptionBtn = document.getElementById('addVariantOptionBtn');
    const optionsContainer = document.getElementById('variantOptionsContainer');

    // Add option functionality
    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', function() {
            const optionRow = document.createElement('div');
            optionRow.className = 'card mb-2';
            optionRow.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <small class="text-muted">{{ __('Option') }} #${variantOptionIndex + 1}</small>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-variant-option-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control"
                                   name="options[${variantOptionIndex}][name][en]"
                                   placeholder="{{ __('Name (English)') }}" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control"
                                   name="options[${variantOptionIndex}][name][ar]"
                                   placeholder="{{ __('Name (Arabic)') }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control"
                                   name="options[${variantOptionIndex}][code]"
                                   placeholder="{{ __('Code') }}">
                        </div>
                    </div>
                </div>
            `;
            optionsContainer.appendChild(optionRow);
            variantOptionIndex++;

            // Remove option functionality
            optionRow.querySelector('.remove-variant-option-btn')?.addEventListener('click', function() {
                optionRow.remove();
            });
        });
    }

    // Remove option functionality for existing options
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-variant-option-btn')) {
            e.target.closest('.card')?.remove();
        }
    });

    // Reset form when modal is closed
    if (modal && form) {
        modal.addEventListener('hidden.bs.modal', function() {
            form.reset();
            optionsContainer.innerHTML = '';
            variantOptionIndex = 0;
            form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        });
    }
});
</script>
@endpush
