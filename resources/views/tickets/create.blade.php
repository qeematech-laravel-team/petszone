@extends('layouts.app')

@php
    $page = 'tickets';
@endphp

@section('title', __('Create Ticket'))

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
                        <li class="breadcrumb-item active">{{ __('Create Ticket') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Create Ticket') }}</h1>
                <p class="text-muted mb-0">{{ __('Submit a new support ticket') }}</p>
            </div>
            <div>
                <a href="{{ route('vendor.tickets.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Ticket Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Ticket Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticketForm">
                            @csrf

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="subject" class="form-label">{{ __('Subject') }} *</label>
                                <input type="text" 
                                       class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" 
                                       name="subject" 
                                       value="{{ old('subject') }}"
                                       placeholder="{{ __('Enter ticket subject') }}"
                                       required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Description') }} *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="8"
                                          placeholder="{{ __('Describe your issue or question in detail...') }}"
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Attachments -->
                            <div class="mb-4">
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
                                <div class="form-text">{{ __('You can attach images or documents (PDF, DOC, DOCX). Maximum file size: 5MB per file.') }}</div>
                                
                                <!-- Preview Area -->
                                <div id="attachmentPreview" class="mt-3" style="display: none;">
                                    <div class="d-flex flex-wrap gap-2" id="previewContainer"></div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('vendor.tickets.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Create Ticket') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Be Specific') }}:</strong> {{ __('Provide detailed information about your issue') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Attach Files') }}:</strong> {{ __('Include screenshots or documents if relevant') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Response Time') }}:</strong> {{ __('We typically respond within 24 hours') }}
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>{{ __('Follow Up') }}:</strong> {{ __('You can add messages to your ticket anytime') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const attachmentsInput = document.getElementById('attachments');
        const previewContainer = document.getElementById('previewContainer');
        const attachmentPreview = document.getElementById('attachmentPreview');

        attachmentsInput.addEventListener('change', function(e) {
            const files = e.target.files;
            previewContainer.innerHTML = '';
            
            if (files.length > 0) {
                attachmentPreview.style.display = 'block';
                
                Array.from(files).forEach((file, index) => {
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'border rounded p-2 bg-light';
                    fileDiv.style.width = '100px';
                    
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = 'img-thumbnail';
                        img.style.width = '100%';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        fileDiv.appendChild(img);
                    } else {
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-file-earmark fs-1 d-block text-center text-muted';
                        fileDiv.appendChild(icon);
                    }
                    
                    const fileName = document.createElement('small');
                    fileName.className = 'd-block text-center text-truncate mt-1';
                    fileName.textContent = file.name;
                    fileName.style.maxWidth = '100px';
                    fileDiv.appendChild(fileName);
                    
                    previewContainer.appendChild(fileDiv);
                });
            } else {
                attachmentPreview.style.display = 'none';
            }
        });
    });
</script>
@endpush
