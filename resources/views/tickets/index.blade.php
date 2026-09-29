@extends('layouts.app')

@php
    $page = 'tickets';
    use Illuminate\Support\Str;
@endphp

@section('title', __('Tickets'))

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
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        @if(auth()->user()->hasRole('admin'))
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active">{{ __('Tickets') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Tickets') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your support tickets') }}</p>
            </div>
            <div>
                <a href="{{ route('vendor.tickets.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('New Ticket') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('vendor.tickets.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   placeholder="{{ __('Search by subject or description...') }}"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ticket_from" class="form-label">{{ __('Ticket From') }}</label>
                            <select class="form-select" id="ticket_from" name="ticket_from">
                                <option value="">{{ __('All') }}</option>
                                <option value="vendor" {{ request('ticket_from') === 'vendor' ? 'selected' : '' }}>{{ __('Vendor') }}</option>
                                <option value="user" {{ request('ticket_from') === 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                                </button>
                                <a href="{{ route('vendor.tickets.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card">
            <div class="card-body">
                @if($tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('From') }}</th>
                                    <th>{{ __('Messages') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <code>#{{ $ticket->id }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ Str::limit($ticket->subject, 50) }}</strong>
                                            @if($ticket->attachments && count($ticket->attachments) > 0)
                                                <i class="bi bi-paperclip text-muted ms-2" title="{{ __('Has attachments') }}"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>{{ __('Pending') }}
                                                </span>
                                            @elseif($ticket->status === 'resolved')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>{{ __('Resolved') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle me-1"></i>{{ __('Closed') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->ticket_from === 'vendor')
                                                <span class="badge bg-primary">{{ __('Vendor') }}</span>
                                            @else
                                                <span class="badge bg-info">{{ __('User') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $ticket->messages->count() }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $ticket->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                @if(auth()->user()->hasRole('admin'))
                                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('View') }}">
                                                        <i class="bi bi-eye mx-0"></i>
                                                    </a>
                                                @else
                                                <a href="{{ route('vendor.tickets.show', $ticket->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View') }}">
                                                    <i class="bi bi-eye mx-0"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
                        <p class="text-muted mt-3">{{ __('No tickets found.') }}</p>
                        <a href="{{ route('vendor.tickets.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('Create Your First Ticket') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
