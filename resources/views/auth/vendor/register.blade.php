@extends('layouts.auth')

@section('title', 'Register As Vendor')

@section('branding-title', 'Create Account As Vendor!')
@section('branding-description', 'Join us today and start your journey')

@section('form-title', 'Sign Up As Vendor')
@section('form-subtitle', 'Create a new account as vendor to get started')

@section('content')
    <div x-data="vendorWizard()" x-init="init()">
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
                    <div class="step-title">{{ __(' Owner Account') }}</div>
                    <small class="step-description text-muted">{{ __('Create owner account') }}</small>
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
            </div>
        </div>
        <form method="POST" action="{{ route('vendor.register') }}" id="registerForm" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Owner Account Information -->
            <div x-show="currentStep === 1" x-transition.opacity class="wizard-content">
                <h5 class="mb-3"><i
                        class="bi bi-person-circle me-2"></i>{{ __('Owner Account Information') }}</h5>
                <p class="text-muted mb-4">{{ __('Create the account for the vendor owner') }}</p>

                <div class="mb-3">
                    <label for="owner_name" class="form-label">{{ __('Owner Name') }} *</label>
                    <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                        id="owner_name" name="owner_name" value="{{ old('owner_name', '') }}" required>
                    @error('owner_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="owner_email" class="form-label">{{ __('Owner Email') }} *</label>
                    <input type="email"
                        class="form-control @error('owner_email') is-invalid @enderror" id="owner_email"
                        name="owner_email" value="{{ old('owner_email', '') }}" required>
                    @error('owner_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="owner_phone" class="form-label">{{ __('Owner Phone') }} *</label>
                    <input type="text" class="form-control @error('owner_phone') is-invalid @enderror"
                        id="owner_phone" name="owner_phone" value="{{ old('owner_phone', '') }}" required>
                    @error('owner_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="owner_password" class="form-label">{{ __('Password') }}
                                *</label>
                            <input type="password"
                                class="form-control @error('owner_password') is-invalid @enderror"
                                id="owner_password" name="owner_password" required>
                            @error('owner_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="owner_password_confirmation"
                                class="form-label">{{ __('Confirm Password') }} *</label>
                            <input type="password" class="form-control"
                                id="owner_password_confirmation" name="owner_password_confirmation"
                                required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Vendor Details -->
            <div x-show="currentStep === 2" x-transition.opacity class="wizard-content">
                <h5 class="mb-3"><i class="bi bi-shop me-2"></i>{{ __('Vendor Details') }}</h5>
                <p class="text-muted mb-4">{{ __('Enter vendor information') }}</p>

                <div class="mb-3">
                    <label for="name_en" class="form-label">{{ __('Vendor Name (English)') }}
                        *</label>
                    <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                        id="name_en" name="name[en]" value="{{ old('name.en', '') }}" required>
                    @error('name.en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_ar" class="form-label">{{ __('Vendor Name (Arabic)') }}
                        *</label>
                    <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                        id="name_ar" name="name[ar]" value="{{ old('name.ar', '') }}" required>
                    @error('name.ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="text"
                                class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone', '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <input type="text"
                                class="form-control @error('address') is-invalid @enderror"
                                id="address" name="address" value="{{ old('address', '') }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">{{ __('Vendor Image') }}</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                        id="image" name="image" accept="image/*"
                        onchange="previewImage(this)">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small
                        class="text-muted">{{ __('Recommended size: 300x300px. Max size: 3MB') }}</small>

                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <img id="preview" src="" alt="Preview" class="img-thumbnail"
                            style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
            </div>

            <!-- Wizard Actions -->
            <div class="wizard-actions">
                <div>
                    <button type="button" class="btn btn-outline-secondary" @click="previousStep()"
                        x-show="currentStep > 1">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Previous') }}
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" @click="nextStep()"
                        x-show="currentStep < totalSteps">
                        {{ __('Next') }}<i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="submit" class="btn btn-primary"
                        x-show="currentStep === totalSteps">
                        <i class="bi bi-check-lg me-2"></i>{{ __('Create Vendor') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <div class="text-center">
        <p class="text-muted mb-0">
            Already have an account?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in</a>
        </p>
    </div>
@endsection



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            function setupPasswordToggle(toggleBtnId, passwordInputId, iconId) {
                const toggleBtn = document.getElementById(toggleBtnId);
                const passwordInput = document.getElementById(passwordInputId);
                const icon = document.getElementById(iconId);

                if (toggleBtn && passwordInput && icon) {
                    toggleBtn.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' :
                        'password';
                        passwordInput.setAttribute('type', type);

                        if (type === 'password') {
                            icon.classList.remove('bi-eye-slash');
                            icon.classList.add('bi-eye');
                        } else {
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        }
                    });
                }
            }

            setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
            setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation',
                'togglePasswordConfirmationIcon');

            // Form validation
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value;
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const passwordConfirmation = document.getElementById('password_confirmation').value;
                    const terms = document.getElementById('terms').checked;

                    if (!name || !email || !password || !passwordConfirmation) {
                        e.preventDefault();
                        alert('Please fill in all required fields');
                        return false;
                    }

                    if (password !== passwordConfirmation) {
                        e.preventDefault();
                        alert('Passwords do not match');
                        return false;
                    }

                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long');
                        return false;
                    }

                    if (!terms) {
                        e.preventDefault();
                        alert('Please agree to the Terms and Conditions');
                        return false;
                    }
                });
            }
        });

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
                totalSteps: 2,


                init() {
                    // Initialize wizard
                },

                nextStep() {
                    if (this.currentStep < this.totalSteps) {
                        this.currentStep++;
                        // Scroll to top
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        // Scroll to top
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                goToStep(step) {
                    // Allow going back to completed steps
                    if (step <= this.currentStep || step === this.currentStep + 1) {
                        this.currentStep = step;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                validateCurrentStep() {
                    const form = document.getElementById('vendorForm');
                    let isValid = true;

                    // Get required fields for current step
                    let requiredFields = [];

                    if (this.currentStep === 1) {
                        requiredFields = ['owner_name', 'owner_email', 'owner_password',
                            'owner_password_confirmation'
                        ];
                    } else if (this.currentStep === 2) {
                        requiredFields = ['name_en', 'name_ar'];
                    }

                    // Validate required fields
                    requiredFields.forEach(fieldName => {
                        const field = form.querySelector(
                            `[name="${fieldName}"], [name="${fieldName.replace('_', '[').replace('_', ']')}"]`
                            );
                        if (field) {
                            if (!field.value.trim()) {
                                field.classList.add('is-invalid');
                                isValid = false;
                            } else {
                                field.classList.remove('is-invalid');
                            }
                        }
                    });

                    // Validate password confirmation
                    if (this.currentStep === 1) {
                        const password = form.querySelector('[name="owner_password"]');
                        const passwordConfirmation = form.querySelector(
                            '[name="owner_password_confirmation"]');
                        if (password && passwordConfirmation && password.value !== passwordConfirmation
                            .value) {
                            passwordConfirmation.classList.add('is-invalid');
                            isValid = false;
                        } else if (passwordConfirmation) {
                            passwordConfirmation.classList.remove('is-invalid');
                        }
                    }

                    return isValid;
                }
            }));
        });
    </script>
@endpush
