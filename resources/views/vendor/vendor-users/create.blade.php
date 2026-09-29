@extends('layouts.app')

@php
    $page = 'vendor-users';
@endphp

@section('title', __('Add Vendor User'))

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
                <h1 class="h3 mb-0">{{ __('Add Vendor User') }}</h1>
                <p class="text-muted mb-0">{{ __('Add a new user to your vendor account') }}</p>
            </div>
            <div>
                <a href="{{ route('vendor.vendor-users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Vendor User Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('vendor.vendor-users.store') }}" method="POST" id="vendorUserForm">
                            @csrf

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-8">
                                    <!-- Basic Information -->
                                    <h5 class="mb-3">{{ __('Basic Information') }}</h5>

                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">{{ __('Name') }} *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name"
                                            value="{{ old('name', '') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('Email') }}</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email"
                                            value="{{ old('email', '') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Optional: At least email or phone is required') }}</small>
                                    </div>

                                    <!-- Phone -->
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone"
                                            value="{{ old('phone', '') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Optional: At least email or phone is required') }}</small>
                                    </div>

                                    <!-- Password -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label">{{ __('Password') }} *</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Minimum 8 characters') }}</small>
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }} *</label>
                                        <input type="password" class="form-control"
                                            id="password_confirmation" name="password_confirmation" required>
                                    </div>

                                    <!-- User Type -->
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">{{ __('User Type') }} *</label>
                                        <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                            <option value="owner" {{ old('user_type', 'owner') === 'owner' ? 'selected' : '' }}>
                                                {{ __('Owner') }}
                                            </option>
                                            <option value="branch" {{ old('user_type', 'owner') === 'branch' ? 'selected' : '' }}>
                                                {{ __('Branch') }}
                                            </option>
                                        </select>
                                        @error('user_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Select whether this user is an owner or branch user') }}</small>
                                    </div>

                                    <!-- Branch Selection (shown only when user_type is branch) -->
                                    <div class="mb-3" id="branch_selection" style="display: {{ old('user_type', 'owner') === 'branch' ? 'block' : 'none' }};">
                                        <label for="branch_id" class="form-label">{{ __('Branch') }} <span id="branch_required" style="display: {{ old('user_type', 'owner') === 'branch' ? 'inline' : 'none' }};">*</span></label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                            <option value="">{{ __('Select a branch') }}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Select the branch for this user') }}</small>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-4">
                                    <!-- Status Toggle -->
                                    <h5 class="mb-3">{{ __('Status') }}</h5>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active"
                                                name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active') }}
                                            </label>
                                        </div>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Active users can access the vendor account') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions Section -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <h5 class="mb-3">{{ __('Permissions') }}</h5>
                                    <p class="text-muted mb-3">{{ __('Select the permissions for this user') }}</p>

                                    @if($errors->has('permissions.*'))
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach($errors->get('permissions.*') as $error)
                                                    <li>{{ $error[0] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="row">
                                        @forelse($permissions as $group => $groupPermissions)
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">{{ __($group) }}</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($groupPermissions as $permission)
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[]"
                                                                    value="{{ $permission->name }}"
                                                                    id="permission_{{ $permission->id }}"
                                                                    {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label mx-2" for="permission_{{ $permission->id }}">
                                                                    {{ __(App\Services\VendorUserService::PERMISSIONS[$permission->name]['label'] ?? str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-warning mb-0">
                                                    {{ __('You do not have any permissions available to assign.') }}
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('Add User') }}
                                </button>
                                <a href="{{ route('vendor.vendor-users.index') }}" class="btn btn-secondary">
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
document.addEventListener('DOMContentLoaded', function() {
    const userTypeSelect = document.getElementById('user_type');
    const branchSelection = document.getElementById('branch_selection');
    const branchIdSelect = document.getElementById('branch_id');
    const branchRequired = document.getElementById('branch_required');

    if (userTypeSelect && branchSelection) {
        userTypeSelect.addEventListener('change', function() {
            if (this.value === 'branch') {
                branchSelection.style.display = 'block';
                branchRequired.style.display = 'inline';
                branchIdSelect.setAttribute('required', 'required');
            } else {
                branchSelection.style.display = 'none';
                branchRequired.style.display = 'none';
                branchIdSelect.removeAttribute('required');
                branchIdSelect.value = '';
            }
        });
    }
});
</script>
@endpush
