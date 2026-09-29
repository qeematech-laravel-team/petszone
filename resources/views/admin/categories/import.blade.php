@extends('layouts.app')

@php
    $page = 'categories';
@endphp

@section('title', 'Import Categories')

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

        <!-- Import Failures -->
        @if(session('import_failures') && session('import_failures')->count() > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Import Warnings</h5>
                <p>Some rows failed to import. Please check the details below:</p>
                <ul class="mb-0">
                    @foreach(session('import_failures') as $failure)
                        <li>
                            Row {{ $failure->row() }}: 
                            @foreach($failure->errors() as $error)
                                {{ $error }}
                            @endforeach
                        </li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Import Categories') }}</h1>
                <p class="text-muted mb-0">{{ __('Import categories from Excel file') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Categories') }}
                </a>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('Import Instructions') }}</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li class="mb-2">
                        <strong>{{ __('Download Template') }}:</strong>
                        <a href="{{ route('admin.categories.import.template') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-download me-1"></i>{{ __('Download Excel Template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <strong>{{ __('Fill the Template') }}:</strong>
                        <ul class="mt-2">
                            <li><strong>name_en</strong>: Category name in English (Required)</li>
                            <li><strong>name_ar</strong>: Category name in Arabic (Optional)</li>
                            <li><strong>parent</strong>: Parent category (dropdown - select from "ID (Name)" format like "1 (Category Name)" or "Root" for root category)</li>
                            <li><strong>is_active</strong>: true or false (dropdown - default: true)</li>
                            <li><strong>is_featured</strong>: true or false (dropdown - default: false)</li>
                            <li><strong>image_url</strong>: Full URL to image or local path (Optional)</li>
                        </ul>
                    </li>
                    <li class="mb-2">
                        <strong>{{ __('Upload File') }}:</strong> Select your filled Excel file and click Import
                    </li>
                    <li>
                        <strong>{{ __('Note') }}:</strong> The file must be in .xlsx, .xls, or .csv format (max 10MB)
                    </li>
                </ol>
            </div>
        </div>

        <!-- Import Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>{{ __('Upload Excel File') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="file" class="form-label fw-bold">{{ __('Select Excel File') }}</label>
                        <input type="file" 
                               class="form-control @error('file') is-invalid @enderror" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv"
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            {{ __('Accepted formats: .xlsx, .xls, .csv (Max size: 10MB)') }}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i>{{ __('Import Categories') }}
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Available Categories Reference -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>{{ __('Available Parent Categories') }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">{{ __('Use these category options in the "parent" column dropdown (format: "ID (Name)"):') }}</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <span class="badge bg-primary me-1">Root</span>
                    </div>
                    @foreach($allCategories ?? [] as $category)
                        <div class="col-md-4 mb-2">
                            <span class="badge bg-secondary me-1">{{ $category->id }} ({{ $category->getTranslation('name', 'en', false) }})</span>
                        </div>
                    @endforeach
                </div>
                @if(empty($allCategories) || count($allCategories) === 0)
                    <p class="text-muted mb-0">{{ __('No categories available. You can leave parent column empty for root categories.') }}</p>
                @endif
            </div>
        </div>

    </div>

@endsection
