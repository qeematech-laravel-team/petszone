@php
    $selectedPermissions = old('permissions', isset($adminUser) ? $adminUser->permissions->pluck('name')->all() : []);
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Employee Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Name') }} *</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $adminUser->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }} *</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $adminUser->email ?? '') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">{{ __('Phone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $adminUser->phone ?? '') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">
                            {{ __('Password') }} {{ isset($adminUser) ? '' : '*' }}
                        </label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               {{ isset($adminUser) ? '' : 'required' }}>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @isset($adminUser)
                            <small class="text-muted">{{ __('Leave blank to keep the current password.') }}</small>
                        @endisset
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                               {{ isset($adminUser) ? '' : 'required' }}>
                    </div>
                </div>

                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $adminUser->is_active ?? true))>
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Dashboard Permissions') }}</h5>
                <button type="button" id="toggle-permissions" class="btn btn-sm btn-outline-primary">{{ __('Select All') }}</button>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    @foreach($permissions as $permission => $label)
                        <div class="form-check">
                            <input class="form-check-input permission-checkbox" type="checkbox"
                                   name="permissions[]" value="{{ $permission }}" id="permission-{{ $permission }}"
                                   @checked(in_array($permission, $selectedPermissions, true))>
                            <label class="form-check-label" for="permission-{{ $permission }}">
                                <span class="fw-semibold d-block">{{ __($label) }}</span>
                                <small class="text-muted">{{ __(str_replace('-', ' ', $permission)) }}</small>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}
    </button>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
</div>

@push('scripts')
    <script>
        document.getElementById('toggle-permissions')?.addEventListener('click', function () {
            const checkboxes = [...document.querySelectorAll('.permission-checkbox')];
            const shouldSelect = checkboxes.some(checkbox => !checkbox.checked);

            checkboxes.forEach(checkbox => checkbox.checked = shouldSelect);
            this.textContent = shouldSelect ? @json(__('Clear All')) : @json(__('Select All'));
        });
    </script>
@endpush
