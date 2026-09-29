@extends('layouts.app')

@section('title', __('Admin Team'))

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Admin Team') }}</h1>
            <p class="text-muted mb-0">{{ __('Manage dashboard employees and their access permissions.') }}</p>
        </div>
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>{{ __('Add Employee') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminUsers as $adminUser)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $adminUser->name }}</div>
                                <div class="small text-muted">{{ $adminUser->email }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $adminUser->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $adminUser->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ trans_choice(':count permission|:count permissions', $adminUser->permissions->count(), ['count' => $adminUser->permissions->count()]) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.admin-users.show', $adminUser) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.admin-users.edit', $adminUser) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.admin-users.toggle-active', $adminUser) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $adminUser->is_active ? __('Deactivate') : __('Activate') }}">
                                            <i class="bi {{ $adminUser->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.admin-users.destroy', $adminUser) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this employee?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                {{ __('No admin employees have been added yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adminUsers->hasPages())
            <div class="card-footer">
                {{ $adminUsers->links() }}
            </div>
        @endif
    </div>
@endsection
