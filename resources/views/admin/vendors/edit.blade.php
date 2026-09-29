@extends('layouts.app')

@php
    $page = 'vendors';
    $profitType = setting('profit_type', 'subscription');
@endphp

@section('title', __('Edit Vendor'))

@push('styles')
<style>
    .wizard-steps {
        margin-bottom: 2rem;
    }

    .wizard-step {
        flex: 1;
        position: relative;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wizard-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 2rem;
        right: 0;
        left: 50%;
        height: 2px;
        background: var(--bs-border-color);
        z-index: 0;
        transition: background-color 0.3s ease;
    }

    .wizard-step.completed:not(:last-child)::after {
        background: var(--bs-success);
    }

    .wizard-step.active:not(:last-child)::after {
        background: var(--bs-primary);
    }

    .step-number {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        background: var(--bs-light);
        border: 2px solid var(--bs-border-color);
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        color: var(--bs-primary);
    }

    .wizard-step.active .step-number {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }

    .wizard-step.completed .step-number {
        background: var(--bs-success);
        border-color: var(--bs-success);
        color: white;
    }

    .step-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .step-description {
        font-size: 0.875rem;
    }

    .wizard-content {
        min-height: 400px;
    }

    .wizard-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--bs-border-color);
    }
</style>
@endpush

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

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Edit Vendor') }}</h1>
                <p class="text-muted mb-0">{{ __('Update vendor information step by step') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Vendor Form Wizard -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div x-data="vendorWizard()" x-init="init()">
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ __('Progress') }}</span>
                                    <span class="text-muted" x-text="`${currentStep} / ${totalSteps}`"></span>
                                </div>

                            </div>

                            <!-- Step Indicators -->
                            <div class="wizard-steps mb-4">
                                <div class="d-flex justify-content-between">
                                    <div
                                        class="wizard-step text-center"
                                        :class="{
                                            'active': currentStep === 1,
                                            'completed': currentStep > 1
                                        }"
                                        @click="goToStep(1)"
                                    >
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 1"></i>
                                            <span x-show="currentStep === 1">1</span>
                                        </div>
                                        <div class="step-title">{{ __('Owner Account') }}</div>
                                        <small class="step-description text-muted">{{ __('Update owner account') }}</small>
                                    </div>

                                    <div
                                        class="wizard-step text-center"
                                        :class="{
                                            'active': currentStep === 2,
                                            'completed': currentStep > 2
                                        }"
                                        @click="goToStep(2)"
                                    >
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 2"></i>
                                            <span x-show="currentStep <= 2">2</span>
                                        </div>
                                        <div class="step-title">{{ __('Vendor Details') }}</div>
                                        <small class="step-description text-muted">{{ __('Vendor information') }}</small>
                                    </div>

                                    <div
                                        class="wizard-step text-center"
                                        :class="{
                                            'active': currentStep === 3,
                                            'completed': currentStep > 3
                                        }"
                                        @click="goToStep(3)"
                                    >
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 3"></i>
                                            <span x-show="currentStep <= 3">3</span>
                                        </div>
                                        <div class="step-title" x-text="step3Title"></div>
                                        <small class="step-description text-muted" x-text="step3Description"></small>
                                    </div>

                                    <div
                                        class="wizard-step text-center"
                                        :class="{
                                            'active': currentStep === 4,
                                            'completed': currentStep > 4
                                        }"
                                        @click="goToStep(4)"
                                    >
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 4"></i>
                                            <span x-show="currentStep <= 4">4</span>
                                        </div>
                                        <div class="step-title">{{ __('Finalize') }}</div>
                                        <small class="step-description text-muted">{{ __('Balance & Status') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Content -->
                            <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" enctype="multipart/form-data" id="vendorForm">
                                @csrf
                                @method('PUT')

                                <!-- Step 1: Owner Account Information -->
                                <div x-show="currentStep === 1" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-person-circle me-2"></i>{{ __('Owner Account Information') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Update the vendor owner account information') }}</p>

                                    @if($vendor->owner)
                                        <div class="alert alert-info mb-3">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>{{ __('Current Owner:') }}</strong> {{ $vendor->owner->name }} ({{ $vendor->owner->email }})
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label for="owner_name" class="form-label">{{ __('Owner Name') }} *</label>
                                        <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                                            id="owner_name" name="owner_name" value="{{ old('owner_name', $vendor->owner->name ?? '') }}" required>
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="owner_email" class="form-label">{{ __('Owner Email') }} *</label>
                                        <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                                            id="owner_email" name="owner_email" value="{{ old('owner_email', $vendor->owner->email ?? '') }}" required>
                                        @error('owner_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="owner_password" class="form-label">{{ __('Password') }}</label>
                                        <input type="password" class="form-control @error('owner_password') is-invalid @enderror"
                                            id="owner_password" name="owner_password">
                                        @error('owner_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Leave empty to keep current password') }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="owner_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                        <input type="password" class="form-control"
                                            id="owner_password_confirmation" name="owner_password_confirmation">
                                    </div>
                                </div>

                                <!-- Step 2: Vendor Details -->
                                <div x-show="currentStep === 2" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-shop me-2"></i>{{ __('Vendor Details') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Update vendor information') }}</p>

                                    <div class="mb-3">
                                        <label for="name_en" class="form-label">{{ __('Vendor Name (English)') }} *</label>
                                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                            id="name_en" name="name[en]"
                                            value="{{ old('name.en', $vendor->getTranslation('name', 'en')) }}" required>
                                        @error('name.en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="name_ar" class="form-label">{{ __('Vendor Name (Arabic)') }} *</label>
                                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                            id="name_ar" name="name[ar]"
                                            value="{{ old('name.ar', $vendor->getTranslation('name', 'ar')) }}" required>
                                        @error('name.ar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                    id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">{{ __('Address') }}</label>
                                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                                    id="address" name="address" value="{{ old('address', $vendor->address) }}">
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">{{ __('Vendor Image') }}</label>

                                        @if($vendor->image)
                                            <div class="mb-2">
                                                <img src="{{ $vendor->image }}" alt="Current image"
                                                    class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                <p class="text-muted small mt-1">{{ __('Current image') }}</p>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="image_removed" id="image_removed" value="1">
                                                    <label class="form-check-label" for="image_removed">
                                                        {{ __('Remove current image') }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endif

                                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                                            id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Leave empty to keep current image. Recommended size: 300x300px. Max size: 3MB') }}</small>

                                        <!-- Image Preview -->
                                        <div id="imagePreview" class="mt-3" style="display: none;">
                                            <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                            <p class="text-muted small mt-1">{{ __('New image preview') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Plan & Subscription OR Commission -->
                                <div x-show="currentStep === 3" x-transition.opacity class="wizard-content">
                                    @if($profitType === 'subscription')
                                        <h5 class="mb-3"><i class="bi bi-calendar-check me-2"></i>{{ __('Plan & Subscription') }}</h5>
                                        <p class="text-muted mb-4">{{ __('Update subscription plan and dates') }}</p>

                                        <div class="mb-3">
                                            <label for="plan_id" class="form-label">{{ __('Subscription Plan') }}</label>
                                            <select class="form-select @error('plan_id') is-invalid @enderror"
                                                id="plan_id" name="plan_id">
                                                <option value="">{{ __('No Plan') }}</option>
                                                @if(isset($plans) && is_iterable($plans))
                                                    @foreach($plans as $plan)
                                                        <option value="{{ $plan->id }}" {{ old('plan_id', $vendor->plan_id) == $plan->id ? 'selected' : '' }}>
                                                            {{ $plan->getTranslation('name', app()->getLocale()) }} - {{ $plan->getRawOriginal('price') }} {{ setting('currency', 'USD') }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('plan_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="subscription_start" class="form-label">{{ __('Subscription Start') }}</label>
                                                    <input type="date" class="form-control @error('subscription_start') is-invalid @enderror"
                                                        id="subscription_start" name="subscription_start" value="{{ old('subscription_start', $vendor->subscription_start ? \Carbon\Carbon::parse($vendor->subscription_start)->format('Y-m-d') : '') }}">
                                                    @error('subscription_start')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="subscription_end" class="form-label">{{ __('Subscription End') }}</label>
                                                    <input type="date" class="form-control @error('subscription_end') is-invalid @enderror"
                                                        id="subscription_end" name="subscription_end" value="{{ old('subscription_end', $vendor->subscription_end ? \Carbon\Carbon::parse($vendor->subscription_end)->format('Y-m-d') : '') }}">
                                                    @error('subscription_end')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="commission_rate" class="form-label">{{ __('Commission Rate (%)') }}</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" max="100" class="form-control @error('commission_rate') is-invalid @enderror"
                                                    id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $vendor->commission_rate ?? setting('profit_value', 0)) }}">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('commission_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">{{ __('Commission rate for this vendor') }}</small>
                                        </div>
                                    @elseif($profitType === 'commission')
                                        <h5 class="mb-3"><i class="bi bi-percent me-2"></i>{{ __('Commission Settings') }}</h5>
                                        <p class="text-muted mb-4">{{ __('Update commission rate for this vendor') }}</p>

                                        <div class="mb-3">
                                            <label for="commission_rate" class="form-label">{{ __('Commission Rate (%)') }} *</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" max="100" class="form-control @error('commission_rate') is-invalid @enderror"
                                                    id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $vendor->commission_rate ?? setting('profit_value', 0)) }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('commission_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">{{ __('Default commission rate:') }} {{ setting('profit_value', 0) }}%</small>
                                        </div>
                                    @endif
                                </div>

                                <!-- Step 4: Balance & Status -->
                                <div x-show="currentStep === 4" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-check-circle me-2"></i>{{ __('Finalize') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Update balance and status settings') }}</p>

                                    <div class="mb-3">
                                        <label for="balance" class="form-label">{{ __('Balance') }}</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" class="form-control @error('balance') is-invalid @enderror"
                                                id="balance" name="balance" value="{{ old('balance', $vendor->balance) }}">
                                            <span class="input-group-text">{{ setting('currency', 'USD') }}</span>
                                        </div>
                                        @error('balance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active"
                                                name="is_active" value="1" {{ old('is_active', $vendor->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active') }}
                                            </label>
                                        </div>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Active vendors will be visible') }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_featured') is-invalid @enderror" type="checkbox" id="is_featured"
                                                name="is_featured" value="1" {{ old('is_featured', $vendor->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                {{ __('Featured') }}
                                            </label>
                                        </div>
                                        @error('is_featured')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Featured vendors will be highlighted') }}</small>
                                    </div>
                                </div>

                                <!-- Wizard Actions -->
                                <div class="wizard-actions">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" @click="previousStep()" x-show="currentStep > 1">
                                            <i class="bi bi-arrow-left me-2"></i>{{ __('Previous') }}
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" @click="nextStep()" x-show="currentStep < totalSteps">
                                            {{ __('Next') }}<i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                        <button type="submit" class="btn btn-primary" x-show="currentStep === totalSteps">
                                            <i class="bi bi-check-lg me-2"></i>{{ __('Update Vendor') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
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
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewDiv = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            previewDiv.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('vendorWizard', () => ({
        currentStep: 1,
        totalSteps: 4,
        profitType: '{{ $profitType }}',

        get step3Title() {
            return this.profitType === 'subscription'
                ? '{{ __('Plan & Subscription') }}'
                : '{{ __('Commission') }}';
        },

        get step3Description() {
            return this.profitType === 'subscription'
                ? '{{ __('Select plan') }}'
                : '{{ __('Set commission') }}';
        },

        init() {
            // Initialize wizard
        },

        nextStep() {
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        goToStep(step) {
            // Allow going back to completed steps
            if (step <= this.currentStep || step === this.currentStep + 1) {
                this.currentStep = step;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        validateCurrentStep() {
            const form = document.getElementById('vendorForm');
            let isValid = true;

            // Get required fields for current step
            let requiredFields = [];

            if (this.currentStep === 1) {
                requiredFields = ['owner_name', 'owner_email'];
                // Password is optional for edit, but if provided, confirmation is required
                const password = form.querySelector('[name="owner_password"]');
                const passwordConfirmation = form.querySelector('[name="owner_password_confirmation"]');
                if (password && password.value.trim() && passwordConfirmation) {
                    if (password.value !== passwordConfirmation.value) {
                        passwordConfirmation.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        passwordConfirmation.classList.remove('is-invalid');
                    }
                }
            } else if (this.currentStep === 2) {
                requiredFields = ['name_en', 'name_ar'];
            } else if (this.currentStep === 3) {
                if (this.profitType === 'commission') {
                    requiredFields = ['commission_rate'];
                }
            }

            // Validate required fields
            requiredFields.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"], [name="${fieldName.replace('_', '[').replace('_', ']')}"], [id="${fieldName}"]`);
                if (field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }
            });

            return isValid;
        }
    }));
});
</script>
@endpush
