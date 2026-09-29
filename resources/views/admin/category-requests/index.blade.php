@extends('layouts.app')

@php
    $page = 'category-requests';
@endphp

@php
    use Illuminate\Support\Str;
@endphp

@section('title', __('Category Requests'))

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
                <h1 class="h3 mb-0">{{ __('Category Requests') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage category requests from vendors') }}</p>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="card mb-4">
            <div class="card-body">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
                           href="{{ route('admin.category-requests.index', ['status' => 'all']) }}">
                            {{ __('All') }}
                            <span class="badge bg-secondary ms-2">{{ $counts['all'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
                           href="{{ route('admin.category-requests.index', ['status' => 'pending']) }}">
                            {{ __('Pending') }}
                            <span class="badge bg-warning ms-2">{{ $counts['pending'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" 
                           href="{{ route('admin.category-requests.index', ['status' => 'approved']) }}">
                            {{ __('Approved') }}
                            <span class="badge bg-success ms-2">{{ $counts['approved'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
                           href="{{ route('admin.category-requests.index', ['status' => 'rejected']) }}">
                            {{ __('Rejected') }}
                            <span class="badge bg-danger ms-2">{{ $counts['rejected'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Category Requests Table -->
        <div class="card">
            <div class="card-body">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Category Name') }}</th>
                                    <th>{{ __('Vendor') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Requested') }}</th>
                                    <th>{{ __('Reviewed') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ $request->getTranslation('name', app()->getLocale()) }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $request->getTranslation('name', 'en') }} / {{ $request->getTranslation('name', 'ar') }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ $request->vendor->getTranslation('name', app()->getLocale()) }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ Str::limit($request->description ?? __('No description'), 60) }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $request->created_at->format('M d, Y') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($request->reviewed_at)
                                                <small class="text-muted">{{ $request->reviewed_at->format('M d, Y') }}</small>
                                                <br>
                                                @if($request->reviewer)
                                                    <small class="text-muted">{{ __('by') }} {{ $request->reviewer->name }}</small>
                                                @endif
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal{{ $request->id }}">
                                                        <i class="bi bi-check-lg"></i> {{ __('Approve') }}
                                                    </button>
                                                    <button type="button" class="btn btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $request->id }}">
                                                        <i class="bi bi-x-lg"></i> {{ __('Reject') }}
                                                    </button>
                                                </div>
                                            @else
                                                @if($request->admin_notes)
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ $request->admin_notes }}">
                                                        <i class="bi bi-info-circle"></i> {{ __('Notes') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">
                            @if($status === 'pending')
                                {{ __('No pending category requests.') }}
                            @elseif($status === 'approved')
                                {{ __('No approved category requests.') }}
                            @elseif($status === 'rejected')
                                {{ __('No rejected category requests.') }}
                            @else
                                {{ __('No category requests found.') }}
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('modals')
@foreach($requests as $request)
    @if($request->status === 'pending')
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">
                            <i class="bi bi-check-circle text-success me-2"></i>{{ __('Approve Category Request') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.category-requests.approve', $request) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label"><strong>{{ __('Category Name') }}:</strong></label>
                                <p>{{ $request->getTranslation('name', 'en') }} / {{ $request->getTranslation('name', 'ar') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>{{ __('Vendor') }}:</strong></label>
                                <p>{{ $request->vendor->getTranslation('name', app()->getLocale()) }}</p>
                            </div>
                            @if($request->description)
                                <div class="mb-3">
                                    <label class="form-label"><strong>{{ __('Description') }}:</strong></label>
                                    <p class="text-muted">{{ $request->description }}</p>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="admin_notes{{ $request->id }}" class="form-label">{{ __('Admin Notes') }}</label>
                                <textarea class="form-control" id="admin_notes{{ $request->id }}" name="admin_notes" rows="3" 
                                    placeholder="{{ __('Optional notes for the vendor...') }}"></textarea>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="create_category{{ $request->id }}" name="create_category" value="1" checked>
                                <label class="form-check-label" for="create_category{{ $request->id }}">
                                    {{ __('Create category immediately') }}
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-2"></i>{{ __('Approve') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel{{ $request->id }}">
                            <i class="bi bi-x-circle text-danger me-2"></i>{{ __('Reject Category Request') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.category-requests.reject', $request) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label"><strong>{{ __('Category Name') }}:</strong></label>
                                <p>{{ $request->getTranslation('name', 'en') }} / {{ $request->getTranslation('name', 'ar') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>{{ __('Vendor') }}:</strong></label>
                                <p>{{ $request->vendor->getTranslation('name', app()->getLocale()) }}</p>
                            </div>
                            <div class="mb-3">
                                <label for="reject_notes{{ $request->id }}" class="form-label">{{ __('Rejection Reason') }} *</label>
                                <textarea class="form-control" id="reject_notes{{ $request->id }}" name="admin_notes" rows="3" 
                                    placeholder="{{ __('Please provide a reason for rejection...') }}" required></textarea>
                                <small class="text-muted">{{ __('This will be visible to the vendor.') }}</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-lg me-2"></i>{{ __('Reject') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endpush
