<div class="option-row card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">{{ __('Option') }} #{{ $index + 1 }}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">{{ __('Name (English)') }} *</label>
                <input type="text" 
                       class="form-control @error('options.' . $index . '.name.en') is-invalid @enderror" 
                       name="options[{{ $index }}][name][en]" 
                       value="{{ old('options.' . $index . '.name.en', $option['name']['en'] ?? '') }}" 
                       required>
                @error('options.' . $index . '.name.en')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-5">
                <label class="form-label">{{ __('Name (Arabic)') }} *</label>
                <input type="text" 
                       class="form-control @error('options.' . $index . '.name.ar') is-invalid @enderror" 
                       name="options[{{ $index }}][name][ar]" 
                       value="{{ old('options.' . $index . '.name.ar', $option['name']['ar'] ?? '') }}" 
                       required>
                @error('options.' . $index . '.name.ar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Code') }}</label>
                <input type="text" 
                       class="form-control @error('options.' . $index . '.code') is-invalid @enderror" 
                       name="options[{{ $index }}][code]" 
                       value="{{ old('options.' . $index . '.code', $option['code'] ?? '') }}"
                       placeholder="{{ __('Auto') }}">
                @error('options.' . $index . '.code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">{{ __('Optional') }}</small>
            </div>
        </div>
        @if(isset($option['id']) && !empty($option['id']))
            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] }}">
        @endif
    </div>
</div>
