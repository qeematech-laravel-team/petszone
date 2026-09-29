@extends('layouts.app')

@php
    $page = 'tickets';
@endphp

@section('title', __('Ticket Details'))

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
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.tickets.index') }}">{{ __('Tickets') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Ticket Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ $ticket->subject }}</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-{{ $ticket->status === 'pending' ? 'warning' : ($ticket->status === 'resolved' ? 'success' : 'secondary') }} me-2">
                        <i class="bi bi-{{ $ticket->status === 'pending' ? 'clock' : ($ticket->status === 'resolved' ? 'check-circle' : 'x-circle') }} me-1"></i>
                        {{ ucfirst($ticket->status) }}
                    </span>
                    <span class="badge bg-{{ $ticket->ticket_from === 'vendor' ? 'primary' : 'info' }} me-2">
                        {{ $ticket->ticket_from === 'vendor' ? __('Vendor') : __('User') }}
                    </span>
                    <code class="text-muted">#{{ $ticket->id }}</code>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.tickets.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Ticket Details -->
            <div class="col-lg-8">
                <!-- Ticket Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Ticket Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('Subject') }}</dt>
                            <dd class="col-sm-9"><strong>{{ $ticket->subject }}</strong></dd>

                            <dt class="col-sm-3 mt-3">{{ __('Description') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <div class=" p-3 rounded">
                                    {{ $ticket->description }}
                                </div>
                            </dd>

                            @if($ticket->attachments && count($ticket->attachments) > 0)
                                <dt class="col-sm-3 mt-3">{{ __('Attachments') }}</dt>
                                <dd class="col-sm-9 mt-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($ticket->attachments as $attachment)
                                            <a href="{{ asset('storage/' . $attachment) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </dd>
                            @endif

                            <dt class="col-sm-3 mt-3">{{ __('Created At') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $ticket->created_at->format('M d, Y H:i') }}</small>
                            </dd>

                            <dt class="col-sm-3 mt-3">{{ __('Last Updated') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <small class="text-muted">{{ $ticket->updated_at->format('M d, Y H:i') }}</small>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Messages') }} ({{ $ticket->messages->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($ticket->messages->count() > 0)
                            <div class="messages-list">
                                @foreach($ticket->messages as $message)
                                    <div class="message-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $message->sender->name ?? __('Unknown') }}</strong>
                                                <span class="badge bg-{{ $message->sender_type === 'admin' ? 'danger' : ($message->sender_type === 'vendor' ? 'primary' : 'info') }} ms-2">
                                                    {{-- {{ $message->sender_type === 'vendor' ? __('Vendor') : __('User') }} --}}
                                                    {{ $message->sender_type === 'admin' ? __('Admin') : ($message->sender_type === 'vendor' ? __('Vendor') : __('User')) }}
                                                </span>
                                            </div>
                                            <small class="text-muted">{{ $message->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        <div class="p-3 rounded mb-2">
                                            {{ $message->message }}
                                        </div>
                                        @if($message->attachments && count($message->attachments) > 0)
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($message->attachments as $attachment)
                                                    <a href="{{ asset('storage/' . $attachment) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-chat-dots fs-3"></i>
                                <p class="mt-2">{{ __('No messages yet.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Add Message Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Add Message') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.tickets.add-message', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label">{{ __('Message') }} *</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                          id="message"
                                          name="message"
                                          rows="4"
                                          placeholder="{{ __('Type your message...') }}"
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="attachments" class="form-label">{{ __('Attachments') }}</label>
                                <input type="file"
                                       class="form-control @error('attachments.*') is-invalid @enderror"
                                       id="attachments"
                                       name="attachments[]"
                                       accept="image/*,.pdf,.doc,.docx"
                                       multiple>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ __('Optional: Attach files to your message') }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>{{ __('Send Message') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Status') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.tickets.update-status', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select class="form-select" name="status" id="statusSelect" onchange="this.form.submit()">
                                    <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ticket Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Ticket Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt class="small text-muted">{{ __('Ticket ID') }}</dt>
                            <dd><code>#{{ $ticket->id }}</code></dd>

                            <dt class="small text-muted mt-3">{{ __('From') }}</dt>
                            <dd>
                                <span class="badge bg-{{ $ticket->ticket_from === 'vendor' ? 'primary' : 'info' }}">
                                    {{ $ticket->ticket_from === 'vendor' ? __('Vendor') : ($ticket->ticket_from === 'admin' ? __('Admin') : __('User')) }}
                                </span>
                            </dd>

                            @if($ticket->user)
                                <dt class="small text-muted mt-3">{{ __('User') }}</dt>
                                <dd>{{ $ticket->user->name }}</dd>
                            @endif

                            @if($ticket->admin)
                                <dt class="small text-muted mt-3">{{ __('Admin') }}</dt>
                                <dd>{{ $ticket->admin->name }}</dd>
                            @endif

                            @if($ticket->vendor)
                                <dt class="small text-muted mt-3">{{ __('Vendor') }}</dt>
                                <dd>{{ $ticket->vendor->name }}</dd>
                            @endif

                            <dt class="small text-muted mt-3">{{ __('Messages') }}</dt>
                            <dd><span class="badge bg-secondary">{{ $ticket->messages->count() }}</span></dd>

                            <dt class="small text-muted mt-3">{{ __('Created') }}</dt>
                            <dd><small>{{ $ticket->created_at->format('M d, Y') }}</small></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
