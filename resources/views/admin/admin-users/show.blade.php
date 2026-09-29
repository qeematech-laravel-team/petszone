@extends('layouts.app')

@section('title', __('Admin Employee Details'))

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $adminUser->name }}</h1>
            <p class="text-muted mb-0">{{ __('Admin employee details and dashboard permissions.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.admin-users.edit', $adminUser) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Employee Information') }}</h5></div>
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <div class="small text-muted">{{ __('Email') }}</div>
                        <div>{{ $adminUser->email }}</div>
                    </div>
                    @if($adminUser->phone)
                        <div>
                            <div class="small text-muted">{{ __('Phone') }}</div>
                            <div>{{ $adminUser->phone }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="small text-muted">{{ __('Status') }}</div>
                        <span class="badge {{ $adminUser->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $adminUser->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                    <div>
                        <div class="small text-muted">{{ __('Created At') }}</div>
                        <div>{{ $adminUser->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Dashboard Permissions') }}</h5></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($adminUser->permissions as $permission)
                            <span class="badge bg-primary-subtle text-primary-emphasis p-2">
                                {{ __(App\Services\AdminUserService::PERMISSIONS[$permission->name] ?? str_replace('-', ' ', $permission->name)) }}
                            </span>
                        @empty
                            <p class="text-muted mb-0">{{ __('This employee has no dashboard permissions.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
