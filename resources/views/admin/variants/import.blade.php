@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', 'Import Variants')

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
                <h1 class="h3 mb-0">{{ __('Import Variants') }}</h1>
                <p class="text-muted mb-0">{{ __('Import variants from Excel file') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.variants.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Variants') }}
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
                        <a href="{{ route('admin.variants.import.template') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-download me-1"></i>{{ __('Download Excel Template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <strong>{{ __('Fill the Template') }}:</strong>
                        <ul class="mt-2">
                            <li><strong>name_en</strong>: Variant name in English (Required)</li>
                            <li><strong>name_ar</strong>: Variant name in Arabic (Optional)</li>
                            <li><strong>is_required</strong>: true or false (dropdown - default: false)</li>
                            <li><strong>is_active</strong>: true or false (dropdown - default: true)</li>
                            <li><strong>options</strong>: Variant options in format: <code>opt1_en:opt1_ar:opt1_code|opt2_en:opt2_ar:opt2_code</code>
                                <ul class="mt-1">
                                    <li>Example: <code>Red:أحمر:red|Blue:أزرق:blue|Green:أخضر:green</code></li>
                                    <li>Each option: <code>english_name:arabic_name:code</code></li>
                                    <li>Separate options with: <code>|</code></li>
                                    <li>Code is optional (will be auto-generated if not provided)</li>
                                </ul>
                            </li>
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
                <form action="{{ route('admin.variants.import.store') }}" method="POST" enctype="multipart/form-data">
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
                            <i class="bi bi-upload me-2"></i>{{ __('Import Variants') }}
                        </button>
                        <a href="{{ route('admin.variants.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
