@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', 'Import Products')

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
                <h1 class="h3 mb-0">{{ __('Import Products') }}</h1>
                <p class="text-muted mb-0">{{ __('Import products from Excel file') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Products') }}
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
                        <a href="{{ route('admin.products.import.template') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-download me-1"></i>{{ __('Download Excel Template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <strong>{{ __('Fill the Template') }}:</strong>
                        <ul class="mt-2">
                            <li><strong>vendor_id</strong>: Vendor ID or name (dropdown - Required)</li>
                            <li><strong>type</strong>: simple or variable (dropdown - Required)</li>
                            <li><strong>name_en</strong>: Product name in English (Required)</li>
                            <li><strong>name_ar</strong>: Product name in Arabic (Optional)</li>
                            <li><strong>description_en</strong>: Product description in English (Optional)</li>
                            <li><strong>description_ar</strong>: Product description in Arabic (Optional)</li>
                            <li><strong>sku</strong>: Product SKU (Optional - will be auto-generated if not provided)</li>
                            <li><strong>slug</strong>: Product slug (Optional - will be auto-generated if not provided)</li>
                            <li><strong>price</strong>: Product price (Required)</li>
                            <li><strong>discount</strong>: Discount amount (Optional)</li>
                            <li><strong>discount_type</strong>: percentage or fixed (dropdown - Optional)</li>
                            <li><strong>thumbnail_url</strong>: Thumbnail image URL (Optional)</li>
                            <li><strong>image_urls</strong>: Product images URLs separated by comma (Optional)
                                <ul class="mt-1">
                                    <li>Example: <code>https://example.com/image1.jpg,https://example.com/image2.jpg</code></li>
                                </ul>
                            </li>
                            <li><strong>categories</strong>: Category IDs or names separated by comma (Optional)
                                <ul class="mt-1">
                                    <li>Example with IDs: <code>1,2,3</code></li>
                                    <li>Example with names: <code>Electronics,Clothing,Books</code></li>
                                    <li>Mixed: <code>1,Electronics,3</code></li>
                                    <li>You can copy values from <strong>categories_list</strong> dropdown and paste into <strong>categories</strong> column</li>
                                </ul>
                            </li>
                            <li><strong>categories_list</strong>: Reference dropdown showing all available categories (Optional - for reference only)
                                <ul class="mt-1">
                                    <li>This is a dropdown column showing all categories in format: <code>ID (Name)</code></li>
                                    <li>Use this dropdown to see available categories and copy IDs to the <strong>categories</strong> column</li>
                                    <li>This column is for reference only and will not be imported</li>
                                </ul>
                            </li>
                            <li><strong>is_active</strong>: true or false (dropdown - default: true)</li>
                            <li><strong>is_featured</strong>: true or false (dropdown - default: false)</li>
                            <li><strong>is_new</strong>: true or false (dropdown - default: false)</li>
                            <li><strong>is_approved</strong>: true or false (dropdown - default: false)</li>
                            <li><strong>is_bookable</strong>: true or false (dropdown - default: false)</li>
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
                <form action="{{ route('admin.products.import.store') }}" method="POST" enctype="multipart/form-data">
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
                            <i class="bi bi-upload me-2"></i>{{ __('Import Products') }}
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
