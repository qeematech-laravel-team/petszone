@extends('layouts.app')

@php
    $page = 'variant-requests';
@endphp

@php
    use Illuminate\Support\Str;
@endphp

@section('title', __('My Variant Requests'))

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
                <h1 class="h3 mb-0">{{ __('My Variant Requests') }}</h1>
                <p class="text-muted mb-0">{{ __('Track your variant requests status') }}</p>
            </div>
            <div>
                @if(vendorCan('create-variant-requests'))
                    <a href="{{ route('vendor.variants.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Variant') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="card mb-4">
            <div class="card-body">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}"
                           href="{{ route('vendor.variant-requests.index', ['status' => 'all']) }}">
                            {{ __('All') }}
                            <span class="badge bg-secondary ms-2">{{ $counts['all'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                           href="{{ route('vendor.variant-requests.index', ['status' => 'pending']) }}">
                            {{ __('Pending') }}
                            <span class="badge bg-warning ms-2">{{ $counts['pending'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}"
                           href="{{ route('vendor.variant-requests.index', ['status' => 'approved']) }}">
                            {{ __('Approved') }}
                            <span class="badge bg-success ms-2">{{ $counts['approved'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}"
                           href="{{ route('vendor.variant-requests.index', ['status' => 'rejected']) }}">
                            {{ __('Rejected') }}
                            <span class="badge bg-danger ms-2">{{ $counts['rejected'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Variant Requests Table -->
        <div class="card">
            <div class="card-body">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Variant Name') }}</th>
                                    <th>{{ __('Options') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Submitted') }}</th>
                                    <th>{{ __('Reviewed') }}</th>
                                    <th>{{ __('Admin Notes') }}</th>
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
                                            @if($request->options && count($request->options) > 0)
                                                <small class="text-muted">
                                                    {{ count($request->options) }} {{ __('options') }}
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
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
                                            @if($request->admin_notes)
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#notesModal{{ $request->id }}">
                                                    <i class="bi bi-info-circle"></i> {{ __('View Notes') }}
                                                </button>
                                            @else
                                                <small class="text-muted">-</small>
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
                                {{ __('You have no pending variant requests.') }}
                            @elseif($status === 'approved')
                                {{ __('You have no approved variant requests.') }}
                            @elseif($status === 'rejected')
                                {{ __('You have no rejected variant requests.') }}
                            @else
                                {{ __('You haven\'t submitted any variant requests yet.') }}
                            @endif
                        </p>
                        @if(vendorCan('create-variant-requests'))
                            <a href="{{ route('vendor.variants.index') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('Request New Variant') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('modals')
<!-- Notes Modal -->
@foreach($requests as $request)
    @if($request->admin_notes)
        <div class="modal fade" id="notesModal{{ $request->id }}" tabindex="-1" aria-labelledby="notesModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notesModalLabel{{ $request->id }}">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Admin Notes') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ $request->admin_notes }}</p>
                        @if($request->reviewer)
                            <small class="text-muted">{{ __('Reviewed by') }}: {{ $request->reviewer->name }}</small>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endpush
