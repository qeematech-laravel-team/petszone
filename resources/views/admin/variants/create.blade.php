@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('Create Variant'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Success/Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $requestData = session('variant_request_data', []);
            $requestNameEn = $requestData['name_en'] ?? '';
            $requestNameAr = $requestData['name_ar'] ?? '';
            $requestOptions = $requestData['options'] ?? [];
        @endphp

        @if(session()->has('variant_request_data'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('This form is pre-filled from an approved variant request. Please complete the details and create the variant.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Create Variant') }}</h1>
                <p class="text-muted mb-0">{{ __('Add a new product variant with options') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.variants.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Variant Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.variants.store') }}" method="POST" id="variantForm">
                            @csrf

                            <!-- Variant Name (Translatable) -->
                            <div class="mb-3">
                                <label for="name_en" class="form-label">{{ __('Variant Name (English)') }} *</label>
                                <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                    id="name_en" name="name[en]"
                                    value="{{ old('name.en', $requestNameEn) }}" required>
                                @error('name.en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name_ar" class="form-label">{{ __('Variant Name (Arabic)') }} *</label>
                                <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                    id="name_ar" name="name[ar]"
                                    value="{{ old('name.ar', $requestNameAr) }}" required>
                                @error('name.ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Toggle -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" id="is_active"
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Active variants will be available for products') }}</small>
                            </div>

                            <!-- Required Toggle -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_required') is-invalid @enderror" 
                                           type="checkbox" id="is_required"
                                           name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_required">
                                        {{ __('Required') }}
                                    </label>
                                </div>
                                @error('is_required')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Required variants must be selected when creating products') }}</small>
                            </div>

                            <!-- Variant Options -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label mb-0">
                                        <strong>{{ __('Variant Options') }}</strong>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-primary" id="addOptionBtn">
                                        <i class="bi bi-plus-lg me-1"></i>{{ __('Add Option') }}
                                    </button>
                                </div>
                                <small class="text-muted d-block mb-3">{{ __('Add options for this variant (e.g., Size: Small, Medium, Large)') }}</small>

                                <div id="optionsContainer">
                                    @php
                                        $oldOptions = old('options', []);
                                        if (empty($oldOptions) && !empty($requestOptions)) {
                                            $oldOptions = $requestOptions;
                                        }
                                    @endphp
                                    @if(!empty($oldOptions))
                                        @foreach($oldOptions as $index => $option)
                                            @include('admin.variants.partials.option-row', [
                                                'index' => $index,
                                                'option' => $option,
                                                'isNew' => false
                                            ])
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Create Variant') }}
                                </button>
                                <a href="{{ route('admin.variants.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
let optionIndex = {{ old('options') ? count(old('options')) : (is_array($requestOptions) && count($requestOptions) > 0 ? count($requestOptions) : 0) }};

document.getElementById('addOptionBtn')?.addEventListener('click', function() {
    const container = document.getElementById('optionsContainer');
    const optionRow = document.createElement('div');
    optionRow.className = 'option-row card mb-3';
    optionRow.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0">{{ __('Option') }} #${optionIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">{{ __('Name (English)') }} *</label>
                    <input type="text" 
                           class="form-control" 
                           name="options[${optionIndex}][name][en]" 
                           required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">{{ __('Name (Arabic)') }} *</label>
                    <input type="text" 
                           class="form-control" 
                           name="options[${optionIndex}][name][ar]" 
                           required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Code') }}</label>
                    <input type="text" 
                           class="form-control" 
                           name="options[${optionIndex}][code]" 
                           placeholder="{{ __('Auto') }}">
                    <small class="text-muted">{{ __('Optional') }}</small>
                </div>
            </div>
        </div>
    `;
    container.appendChild(optionRow);
    optionIndex++;

    // Add remove functionality
    optionRow.querySelector('.remove-option-btn')?.addEventListener('click', function() {
        optionRow.remove();
        updateOptionNumbers();
    });
});

// Remove option functionality for existing options
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-option-btn')) {
        e.target.closest('.option-row')?.remove();
        updateOptionNumbers();
    }
});

function updateOptionNumbers() {
    const rows = document.querySelectorAll('.option-row');
    rows.forEach((row, index) => {
        const title = row.querySelector('h6');
        if (title) {
            title.textContent = '{{ __('Option') }} #' + (index + 1);
        }
    });
}
</script>
@endpush
