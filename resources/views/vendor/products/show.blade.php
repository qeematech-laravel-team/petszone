@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', __('Product Details'))

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
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.products.index') }}">{{ __('Products') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Product Details') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">{{ $product->getTranslation('name', app()->getLocale()) }}</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }} me-2">
                        <i class="bi bi-{{ $product->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>{{ $product->is_active ? __('Active') : __('Inactive') }}
                    </span>
                    @if($product->is_featured)
                        <span class="badge bg-warning me-2">
                            <i class="bi bi-star-fill me-1"></i>{{ __('Featured') }}
                        </span>
                    @endif
                    @if($product->is_approved)
                        <span class="badge bg-success me-2">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ __('Approved') }}
                        </span>
                    @else
                        <span class="badge bg-warning me-2">
                            <i class="bi bi-clock me-1"></i>{{ __('Pending') }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                @if(canCreateProducts() && (vendorCan('edit-products') || vendorCan('manage-products')))
                    <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>{{ __('Edit Product') }}
                    </a>
                @endif
                @if(canCreateProducts() && (vendorCan('delete-products') || vendorCan('manage-products')))
                    <button type="button" class="btn btn-danger delete-product-btn"
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->getTranslation('name', app()->getLocale()) }}"
                            data-delete-url="{{ route('vendor.products.destroy', $product) }}">
                        <i class="bi bi-trash me-2"></i>{{ __('Delete') }}
                    </button>
                @endif
                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $product->thumbnail }}"
                                 alt="{{ $product->getTranslation('name', app()->getLocale()) }}"
                                 class="img-fluid rounded shadow-sm"
                                 style="max-width: 200px; max-height: 200px; object-fit: cover; border: 3px solid #f0f0f0;">
                            @if($product->is_featured)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <div class="d-flex align-items-center p-3   rounded">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-currency-dollar fs-4 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">{{ __('Price') }}</small>
                                        @php
                                            $currentBranch = currentBranch();
                                            $branchPrice = null;
                                            $branchFinalPrice = null;
                                            if ($currentBranch && $product->type === 'simple') {
                                                $branchStockRecord = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                                if ($branchStockRecord) {
                                                    $branchPrice = $branchStockRecord->price ?? $product->price;
                                                    $branchFinalPrice = $branchStockRecord->final_price ?? $product->final_price;
                                                } else {
                                                    $branchPrice = $product->price;
                                                    $branchFinalPrice = $product->final_price;
                                                }
                                            } else {
                                                $branchPrice = $product->manager()->price();
                                                $branchFinalPrice = $product->manager()->finalPrice();
                                            }
                                        @endphp
                                        <strong class="d-block">{{ number_format($branchPrice, 2) }} {{ setting('currency') }}</strong>
                                        @if($branchFinalPrice < $branchPrice)
                                            <small class="text-success">
                                                <i class="bi bi-arrow-down"></i> {{ number_format($branchFinalPrice, 2) }} {{ setting('currency') }}
                                            </small>
                                        @endif
                                        @if($currentBranch && vendorSetting('allow_branch_user_to_edit_price', false) && (vendorCan('manage-product-prices') || vendorCan('manage-products')))
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#editPriceModal">
                                                <i class="bi bi-pencil me-1"></i>{{ __('Edit Price') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="d-flex align-items-center p-3   rounded">
                                    <div class="flex-shrink-0">
                                        @php
                                            $currentBranch = currentBranch();
                                            $branchStock = 0;
                                            $isInStock = false;

                                            if ($currentBranch) {
                                                // Calculate stock only for current branch
                                                if ($product->type === 'simple') {
                                                    $branchStockRecord = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                                    $branchStock = $branchStockRecord ? $branchStockRecord->quantity : 0;
                                                } else {
                                                    $branchStock = $product->variants->sum(function($variant) use ($currentBranch) {
                                                        $stock = $variant->branchVariantStocks->where('branch_id', $currentBranch->id)->first();
                                                        return $stock ? $stock->quantity : 0;
                                                    });
                                                }
                                                $isInStock = $branchStock > 0;
                                            } else {
                                                // Calculate total stock across all branches for owner users
                                                if ($product->type === 'simple') {
                                                    $branchStock = $product->branchProductStocks->sum('quantity');
                                                } else {
                                                    $branchStock = $product->variants->sum(function($variant) {
                                                        return $variant->branchVariantStocks->sum('quantity');
                                                    });
                                                }
                                                $isInStock = $branchStock > 0;
                                            }
                                        @endphp
                                        <i class="bi bi-{{ $isInStock ? 'check-circle' : 'x-circle' }} fs-4 text-{{ $isInStock ? 'success' : 'danger' }}"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">
                                            {{ $currentBranch ? __('Branch Stock') : __('Stock') }}
                                            @if($currentBranch)
                                                <span class="badge bg-info ms-1">{{ $currentBranch->name }}</span>
                                            @endif
                                        </small>
                                        @if($isInStock)
                                            <strong class="d-block text-success">{{ $branchStock }} {{ __('in stock') }}</strong>
                                        @else
                                            <strong class="d-block text-danger">{{ __('Out of Stock') }}</strong>
                                        @endif
                                        @if($currentBranch && vendorSetting('allow_branch_user_to_edit_stock', false) && (vendorCan('manage-product-stock') || vendorCan('manage-products')))
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#editStockModal">
                                                <i class="bi bi-pencil me-1"></i>{{ __('Edit Stock') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="d-flex align-items-center p-3   rounded">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-{{ $product->type === 'variable' ? 'tags' : 'box' }} fs-4 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">{{ __('Type') }}</small>
                                        <strong class="d-block">
                                            @if($product->type === 'variable')
                                                <span class="badge bg-primary">{{ __('Variable') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Simple') }}</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="d-flex align-items-center p-3   rounded">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-shop fs-4 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted d-block">{{ __('Vendor') }}</small>
                                        <strong class="d-block">
                                            @if($product->vendor)
                                                <span class="badge bg-info">{{ $product->vendor->getTranslation('name', app()->getLocale()) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Product Details Tabs -->
                <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>{{ __('Details') }}
                        </button>
                    </li>
                    @if($product->images && $product->images->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">
                                <i class="bi bi-images me-2"></i>{{ __('Images') }} <span class="badge bg-primary">{{ $product->images->count() }}</span>
                            </button>
                        </li>
                    @endif
                    @if($product->isVariable() && $product->variants && $product->variants->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button" role="tab">
                                <i class="bi bi-tags me-2"></i>{{ __('Variants') }} <span class="badge bg-primary">{{ $product->variants->count() }}</span>
                            </button>
                        </li>
                    @endif
                    @if($product->categories && $product->categories->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                                <i class="bi bi-grid me-2"></i>{{ __('Categories') }} <span class="badge bg-primary">{{ $product->categories->count() }}</span>
                            </button>
                        </li>
                    @endif
                    @php
                    // check if the current user is the owner of the vendor or the vendor user type is owner
                        $currentVendorUser = currentVendorUser();
                        $isOwner = ($currentVendorUser && $currentVendorUser->user_type == 'owner') || auth()->user()->id == $product->vendor->owner_id;
                        $currentBranch = currentBranch();
                    @endphp

                    @if($isOwner || $currentBranch)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="branch-stock-tab" data-bs-toggle="tab" data-bs-target="#branch-stock" type="button" role="tab">
                                <i class="bi bi-shop me-2"></i>{{ __('Branch Stock') }}
                            </button>
                        </li>

                    @endif
                </ul>

                <div class="tab-content" id="productTabsContent">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-info-circle text-primary me-2"></i>{{ __('Product Information') }}
                                </h5>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="border-start border-primary border-3 ps-3">
                                            <label class="form-label text-muted small mb-1">{{ __('Name (English)') }}</label>
                                            <p class="mb-0 fw-semibold">{{ $product->getTranslation('name', 'en') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border-start border-success border-3 ps-3">
                                            <label class="form-label text-muted small mb-1">{{ __('Name (Arabic)') }}</label>
                                            <p class="mb-0 fw-semibold">{{ $product->getTranslation('name', 'ar') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border-start border-info border-3 ps-3">
                                            <label class="form-label text-muted small mb-1">{{ __('SKU') }}</label>
                                            <p class="mb-0">
                                                <code class="  px-2 py-1 rounded">{{ $product->sku }}</code>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border-start border-warning border-3 ps-3">
                                            <label class="form-label text-muted small mb-1">{{ __('Slug') }}</label>
                                            <p class="mb-0">
                                                <code class="  px-2 py-1 rounded">{{ $product->slug }}</code>
                                            </p>
                                        </div>
                                    </div>
                                    @if($product->description)
                                        <div class="col-12">
                                            <div class="border-start border-secondary border-3 ps-3">
                                                <label class="form-label text-muted small mb-2">{{ __('Description (English)') }}</label>
                                                <p class="mb-3">{{ $product->getTranslation('description', 'en') ?: '-' }}</p>
                                                <label class="form-label text-muted small mb-2">{{ __('Description (Arabic)') }}</label>
                                                <p class="mb-0">{{ $product->getTranslation('description', 'ar') ?: '-' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Details -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-currency-dollar text-success me-2"></i>{{ __('Pricing Information') }}
                                </h5>
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="text-center p-3   rounded">
                                            <i class="bi bi-tag fs-3 text-primary mb-2"></i>
                                            <div class="small text-muted">{{ __('Base Price') }}</div>
                                            <div class="h5 mb-0 fw-bold">{{ number_format($product->manager()->price(), 2) }} {{ setting('currency') }}</div>
                                        </div>
                                    </div>
                                    @if($product->hasDiscount())
                                        <div class="col-md-4">
                                            <div class="text-center p-3   rounded">
                                                <i class="bi bi-percent fs-3 text-success mb-2"></i>
                                                <div class="small text-muted">{{ __('Discount') }}</div>
                                                <div class="h5 mb-0 fw-bold text-success">
                                                    @if($product->discount_type === 'percentage')
                                                        {{ $product->discount }}%
                                                    @else
                                                        {{ number_format($product->discount, 2) }} {{ setting('currency') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                                <i class="bi bi-check-circle fs-3 text-success mb-2"></i>
                                                <div class="small text-muted">{{ __('Final Price') }}</div>
                                                <div class="h5 mb-0 fw-bold text-success">{{ number_format($product->manager()->finalPrice(), 2) }} {{ setting('currency') }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-clock-history text-secondary me-2"></i>{{ __('Timestamps') }}
                                </h5>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                                    <i class="bi bi-calendar-plus text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="small text-muted">{{ __('Created At') }}</div>
                                                <div class="fw-semibold">{{ $product->created_at->format('M d, Y H:i') }}</div>
                                                <div class="small text-muted">{{ $product->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                                    <i class="bi bi-pencil-square text-info"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="small text-muted">{{ __('Updated At') }}</div>
                                                <div class="fw-semibold">{{ $product->updated_at->format('M d, Y H:i') }}</div>
                                                <div class="small text-muted">{{ $product->updated_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Images Tab -->
                    @if($product->images && $product->images->count() > 0)
                        <div class="tab-pane fade" id="images" role="tabpanel">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-images text-primary me-2"></i>{{ __('Product Images') }} ({{ $product->images->count() }})
                                    </h5>
                                    <div class="row g-3">
                                        @foreach($product->images as $image)
                                            <div class="col-md-4 col-lg-3">
                                                <div class="position-relative">
                                                    <img src="{{ $image->image_path }}"
                                                         alt="Product Image"
                                                         class="img-fluid rounded shadow-sm w-100"
                                                         style="height: 200px; object-fit: cover; cursor: pointer;"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imageModal{{ $image->id }}">
                                                    <div class="position-absolute top-0 end-0 m-2">
                                                        <span class="badge bg-dark bg-opacity-75">{{ $loop->iteration }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Variants Tab -->
                    @if($product->isVariable() && $product->variants && $product->variants->count() > 0)
                        <div class="tab-pane fade" id="variants" role="tabpanel">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-tags text-primary me-2"></i>{{ __('Product Variants') }} ({{ $product->variants->count() }})
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('SKU') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                    @php
                                                        $currentVendorUser = currentVendorUser();
                                                        $isOwner = $currentVendorUser && $currentVendorUser->user_type === 'owner';
                                                        $currentBranch = currentBranch();
                                                    @endphp
                                                    @if($isOwner && isset($vendorBranches) && $vendorBranches->count() > 0)
                                                        @foreach($vendorBranches as $branch)
                                                            <th class="text-center">{{ __('Stock') }}<br><small class="text-muted">{{ $branch->name }}</small></th>
                                                            <th class="text-center">{{ __('Price') }}<br><small class="text-muted">{{ $branch->name }}</small></th>
                                                        @endforeach
                                                        <th class="text-center">{{ __('Total') }}</th>
                                                    @else
                                                        <th>{{ __('Stock') }}</th>
                                                    @endif
                                                    <th class="text-center">{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $variant)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($variant->thumbnail)
                                                                    <img src="{{ $variant->thumbnail }}"
                                                                         alt="{{ $variant->getTranslation('name', app()->getLocale()) }}"
                                                                         class="rounded me-2"
                                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                                @endif
                                                                <span class="fw-semibold">{{ $variant->getTranslation('name', app()->getLocale()) }}</span>
                                                            </div>
                                                        </td>
                                                        <td><code class="px-2 py-1 rounded">{{ $variant->sku }}</code></td>
                                                        <td>
                                                            <strong>{{ number_format($variant->price, 2) }} {{ setting('currency') }}</strong>
                                                        </td>
                                                        @if($isOwner && isset($vendorBranches) && $vendorBranches->count() > 0)
                                                            @foreach($vendorBranches as $branch)
                                                                <td class="text-center">
                                                                    @php
                                                                        $variantStock = $variant->branchVariantStocks->where('branch_id', $branch->id)->first();
                                                                        $branchStock = $variantStock ? $variantStock->quantity : 0;
                                                                    @endphp
                                                                    @if($branchStock > 0)
                                                                        <span class="badge bg-success">
                                                                            <i class="bi bi-check-circle me-1"></i>{{ $branchStock }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-danger">
                                                                            <i class="bi bi-x-circle me-1"></i>0
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @php
                                                                        $variantStock = $variant->branchVariantStocks->where('branch_id', $branch->id)->first();
                                                                        $variantPrice = $variantStock && $variantStock->price !== null ? $variantStock->price : $variant->price;
                                                                        $variantFinalPrice = $variantStock ? $variantStock->final_price : ($variant->discount && $variant->discount > 0 ? ($variant->discount_type === 'percentage' ? $variant->price - (($variant->price * $variant->discount) / 100) : $variant->price - $variant->discount) : $variant->price);
                                                                    @endphp
                                                                    <div>
                                                                        <strong class="d-block">{{ number_format($variantPrice, 2) }} {{ setting('currency_symbol', '$') }}</strong>
                                                                        @if($variantStock && $variantStock->price !== null)
                                                                            <small class="text-muted">{{ __('Branch') }}</small>
                                                                        @else
                                                                            <small class="text-muted">{{ __('Default') }}</small>
                                                                        @endif
                                                                    </div>
                                                                    @if($variantStock && $variantStock->hasDiscount())
                                                                        <div class="mt-1">
                                                                            <small class="text-success">
                                                                                <i class="bi bi-tag me-1"></i>
                                                                                <strong>{{ number_format($variantFinalPrice, 2) }} {{ setting('currency_symbol', '$') }}</strong>
                                                                                @if($variantStock->discount_type === 'percentage')
                                                                                    <br><small>{{ $variantStock->discount }}% {{ __('Off') }}</small>
                                                                                @else
                                                                                    <br><small>{{ number_format($variantStock->discount, 2) }} {{ setting('currency_symbol', '$') }} {{ __('Off') }}</small>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @elseif($variant->discount && $variant->discount > 0)
                                                                        <div class="mt-1">
                                                                            <small class="text-success">
                                                                                <i class="bi bi-tag me-1"></i>
                                                                                <strong>{{ number_format($variantFinalPrice, 2) }} {{ setting('currency_symbol', '$') }}</strong>
                                                                                @if($variant->discount_type === 'percentage')
                                                                                    <br><small>{{ $variant->discount }}% {{ __('Off') }}</small>
                                                                                @else
                                                                                    <br><small>{{ number_format($variant->discount, 2) }} {{ setting('currency_symbol', '$') }} {{ __('Off') }}</small>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                            <td class="text-center">
                                                                @php
                                                                    $totalVariantStock = $variant->branchVariantStocks->sum('quantity');
                                                                @endphp
                                                                @if($totalVariantStock > 0)
                                                                    <span class="badge bg-primary">
                                                                        <i class="bi bi-check-circle me-1"></i>{{ $totalVariantStock }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger">
                                                                        <i class="bi bi-x-circle me-1"></i>{{ __('Out of Stock') }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        @else
                                                            <td>
                                                                @php
                                                                    if ($currentBranch) {
                                                                        $variantStock = $variant->branchVariantStocks->where('branch_id', $currentBranch->id)->first();
                                                                        $variantBranchStock = $variantStock ? $variantStock->quantity : 0;
                                                                    } else {
                                                                        $variantBranchStock = $variant->branchVariantStocks->sum('quantity');
                                                                    }
                                                                @endphp
                                                                @if($variantBranchStock > 0)
                                                                    <span class="badge bg-success">
                                                                        <i class="bi bi-check-circle me-1"></i>{{ $variantBranchStock }}
                                                                        @if($currentBranch)
                                                                            <small class="ms-1">({{ $currentBranch->name }})</small>
                                                                        @endif
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger">
                                                                        <i class="bi bi-x-circle me-1"></i>{{ __('Out of Stock') }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td class="text-center">
                                                            @if($variant->is_active)
                                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Categories Tab -->
                    @if($product->categories && $product->categories->count() > 0)
                        <div class="tab-pane fade" id="categories" role="tabpanel">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-grid text-primary me-2"></i>{{ __('Product Categories') }} ({{ $product->categories->count() }})
                                    </h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($product->categories as $category)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2">
                                                <i class="bi bi-folder me-1"></i>{{ $category->getTranslation('name', app()->getLocale()) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Branch Stock Tab (for owner users) -->
                    @php
                        $currentVendorUser = currentVendorUser();
                        $isOwner = ($currentVendorUser && $currentVendorUser->user_type == 'owner') || auth()->user()->id == $product->vendor->owner_id;
                        $currentBranch = currentBranch();
                    @endphp
                    @if($isOwner)
                        <div class="tab-pane fade" id="branch-stock" role="tabpanel">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-shop text-primary me-2"></i>{{ __('Branch Stock & Prices Breakdown') }}
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Branch Name') }}</th>
                                                    @if($product->type === 'simple')
                                                        <th class="text-end">{{ __('Stock') }}</th>
                                                        <th class="text-end">{{ __('Price') }}</th>
                                                        <th class="text-end">{{ __('Final Price') }}</th>
                                                    @else
                                                        <th class="text-end">{{ __('Total Stock') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->vendor->branches as $branch)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-shop me-2 text-primary"></i>
                                                                <strong>{{ $branch->name }}</strong>
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            @php
                                                                if ($product->type === 'simple') {
                                                                    $branchStockRecord = $product->branchProductStocks->where('branch_id', $branch->id)->first();
                                                                    $branchStock = $branchStockRecord ? $branchStockRecord->quantity : 0;
                                                                } else {
                                                                    $branchStock = $product->variants->sum(function($variant) use ($branch) {
                                                                        $stock = $variant->branchVariantStocks->where('branch_id', $branch->id)->first();
                                                                        return $stock ? $stock->quantity : 0;
                                                                    });
                                                                }
                                                            @endphp
                                                            @if($branchStock > 0)
                                                                <span class="badge bg-success fs-6">
                                                                    <i class="bi bi-check-circle me-1"></i>{{ $branchStock }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-danger fs-6">
                                                                    <i class="bi bi-x-circle me-1"></i>{{ __('Out of Stock') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        @if($product->type === 'simple')
                                                            @php
                                                                $branchStockRecord = $product->branchProductStocks->where('branch_id', $branch->id)->first();
                                                                $branchPrice = $branchStockRecord && $branchStockRecord->price !== null ? $branchStockRecord->price : $product->price;
                                                                $branchFinalPrice = $branchStockRecord ? $branchStockRecord->final_price : $product->final_price;
                                                            @endphp
                                                            <td class="text-end">
                                                                <span class="fw-bold">{{ number_format($branchPrice, 2) }} {{ setting('currency_symbol', '$') }}</span>
                                                                @if($branchStockRecord && $branchStockRecord->price !== null)
                                                                    <small class="text-muted d-block">{{ __('Branch Price') }}</small>
                                                                @else
                                                                    <small class="text-muted d-block">{{ __('Default Price') }}</small>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold text-primary">{{ number_format($branchFinalPrice, 2) }} {{ setting('currency_symbol', '$') }}</span>
                                                                @if($branchStockRecord && $branchStockRecord->hasDiscount())
                                                                    <small class="text-success d-block">
                                                                        <i class="bi bi-tag me-1"></i>
                                                                        @if($branchStockRecord->discount_type === 'percentage')
                                                                            {{ $branchStockRecord->discount }}% {{ __('Off') }}
                                                                        @else
                                                                            {{ number_format($branchStockRecord->discount, 2) }} {{ setting('currency_symbol', '$') }} {{ __('Off') }}
                                                                        @endif
                                                                    </small>
                                                                @elseif($product->hasDiscount())
                                                                    <small class="text-success d-block">
                                                                        <i class="bi bi-tag me-1"></i>
                                                                        @if($product->discount_type === 'percentage')
                                                                            {{ $product->discount }}% {{ __('Off') }}
                                                                        @else
                                                                            {{ number_format($product->discount, 2) }} {{ setting('currency_symbol', '$') }} {{ __('Off') }}
                                                                        @endif
                                                                    </small>
                                                                @endif
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="text-end">
                                                        @php
                                                            if ($product->type === 'simple') {
                                                                $totalStock = $product->branchProductStocks->sum('quantity');
                                                            } else {
                                                                $totalStock = $product->variants->sum(function($variant) {
                                                                    return $variant->branchVariantStocks->sum('quantity');
                                                                });
                                                            }
                                                        @endphp
                                                        <span class="badge bg-primary fs-6">{{ $totalStock }}</span>
                                                    </th>
                                                    @if($product->type === 'simple')
                                                        <th class="text-end">-</th>
                                                        <th class="text-end">-</th>
                                                    @endif
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{--
                    check if the current user is a branch user
                    if yes, show the branch stock and prices breakdown for the current branch
                    if no, show a message saying that the user is not authorized to view the branch stock and prices breakdown
                    --}}
                    @elseif(currentBranch())
                        <div class="tab-pane fade" id="branch-stock" role="tabpanel">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-shop text-primary me-2"></i>{{ __('Branch Stock & Prices Breakdown') }}
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Branch Name') }}</th>
                                                    <th class="text-end">{{ __('Stock') }}</th>
                                                    <th class="text-end">{{ __('Price') }}</th>
                                                    <th class="text-end">{{ __('Final Price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-shop me-2 text-primary"></i>
                                                            <strong>{{ currentBranch()->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $branchStock = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                                            $branchStock = $branchStock ? $branchStock->quantity : 0;
                                                        @endphp
                                                        <span class="badge bg-success fs-6">
                                                            <i class="bi bi-check-circle me-1"></i>{{ $branchStock }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $branchPrice = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                                            $branchPrice = $branchPrice ? $branchPrice->price : $product->price;
                                                        @endphp
                                                        <span class="fw-bold">{{ number_format($branchPrice, 2) }} {{ setting('currency_symbol', '$') }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $branchFinalPrice = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                                            $branchFinalPrice = $branchFinalPrice ? $branchFinalPrice->final_price : $product->final_price;
                                                        @endphp
                                                        <span class="fw-bold text-primary">{{ number_format($branchFinalPrice, 2) }} {{ setting('currency_symbol', '$') }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header   border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-toggle-on text-primary me-2"></i>{{ __('Status & Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0   py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-power text-{{ $product->is_active ? 'success' : 'secondary' }} me-2"></i>
                                        <span class="fw-semibold">{{ __('Active') }}</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-active-btn" type="checkbox"
                                            id="toggleActive{{ $product->id }}"
                                            data-product-id="{{ $product->id }}"
                                            data-toggle-url="{{ route('vendor.products.toggle-active', $product) }}"
                                            @disabled(! vendorCan('edit-products') && ! vendorCan('manage-products'))
                                            {{ $product->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0   py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-star text-{{ $product->is_featured ? 'warning' : 'secondary' }} me-2"></i>
                                        <span class="fw-semibold">{{ __('Featured') }}</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-featured-btn" type="checkbox"
                                            id="toggleFeatured{{ $product->id }}"
                                            data-product-id="{{ $product->id }}"
                                            data-toggle-url="{{ route('vendor.products.toggle-featured', $product) }}"
                                            @disabled(! vendorCan('edit-products') && ! vendorCan('manage-products'))
                                            {{ $product->is_featured ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0   py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-{{ $product->is_approved ? 'check-circle' : 'clock' }} text-{{ $product->is_approved ? 'success' : 'warning' }} me-2"></i>
                                        <span class="fw-semibold">{{ __('Approved') }}</span>
                                    </div>
                                    <span class="badge bg-{{ $product->is_approved ? 'success' : 'warning' }}">
                                        {{ $product->is_approved ? __('Approved') : __('Pending') }}
                                    </span>
                                </div>
                            </div>
                            <div class="list-group-item border-0   py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-{{ $product->is_new ? 'sparkles' : 'circle' }} text-{{ $product->is_new ? 'info' : 'secondary' }} me-2"></i>
                                        <span class="fw-semibold">{{ __('New Product') }}</span>
                                    </div>
                                    <span class="badge bg-{{ $product->is_new ? 'info' : 'secondary' }}">
                                        {{ $product->is_new ? __('Yes') : __('No') }}
                                    </span>
                                </div>
                            </div>
                            <div class="list-group-item border-0   py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-{{ $product->is_bookable ? 'calendar-check' : 'calendar-x' }} text-{{ $product->is_bookable ? 'success' : 'secondary' }} me-2"></i>
                                        <span class="fw-semibold">{{ __('Bookable') }}</span>
                                    </div>
                                    <span class="badge bg-{{ $product->is_bookable ? 'success' : 'secondary' }}">
                                        {{ $product->is_bookable ? __('Yes') : __('No') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header   border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-charge text-warning me-2"></i>{{ __('Quick Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if(canCreateProducts() && (vendorCan('edit-products') || vendorCan('manage-products')))
                                <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-2"></i>{{ __('Edit Product') }}
                                </a>
                            @endif
                            @if(canCreateProducts() && (vendorCan('delete-products') || vendorCan('manage-products')))
                                <button type="button" class="btn btn-outline-danger delete-product-btn"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->getTranslation('name', app()->getLocale()) }}"
                                        data-delete-url="{{ route('vendor.products.destroy', $product) }}">
                                    <i class="bi bi-trash me-2"></i>{{ __('Delete Product') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Product Stats -->
                <div class="card shadow-sm border-0">
                    <div class="card-header   border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up text-success me-2"></i>{{ __('Statistics') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">{{ __('Total Images') }}</small>
                                <strong class="fs-5">{{ $product->images->count() }}</strong>
                            </div>
                            <i class="bi bi-images fs-3 text-primary"></i>
                        </div>
                        @if($product->isVariable())
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <small class="text-muted d-block">{{ __('Total Variants') }}</small>
                                    <strong class="fs-5">{{ $product->variants->count() }}</strong>
                                </div>
                                <i class="bi bi-tags fs-3 text-info"></i>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">{{ __('Categories') }}</small>
                                <strong class="fs-5">{{ $product->categories->count() }}</strong>
                            </div>
                            <i class="bi bi-grid fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Edit Stock Modal (for branch users) -->
    @if(currentBranch() && vendorSetting('allow_branch_user_to_edit_stock', false) && (vendorCan('manage-product-stock') || vendorCan('manage-products')))
        <div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editStockForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStockModalLabel">{{ __('Edit Stock') }} - {{ currentBranch()->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if($product->type === 'simple')
                                @php
                                    $currentBranch = currentBranch();
                                    $branchStock = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                @endphp
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">{{ __('Stock Quantity') }}</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="quantity" value="{{ $branchStock ? $branchStock->quantity : 0 }}" min="0" required>
                                    <input type="hidden" name="branch_id" value="{{ $currentBranch->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="type" value="simple">
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Variant Stock') }}</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Variant') }}</th>
                                                    <th>{{ __('Stock') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentBranch = currentBranch();
                                                @endphp
                                                @foreach($product->variants as $variant)
                                                    @php
                                                        $variantStock = $variant->branchVariantStocks->where('branch_id', $currentBranch->id)->first();
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $variant->getTranslation('name', app()->getLocale()) }}</td>
                                                        <td>
                                                            <input type="number"
                                                                   class="form-control form-control-sm variant-stock-input"
                                                                   name="variants[{{ $variant->id }}]"
                                                                   value="{{ $variantStock ? $variantStock->quantity : 0 }}"
                                                                   min="0"
                                                                   data-variant-id="{{ $variant->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" name="branch_id" value="{{ $currentBranch->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="type" value="variable">
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Update Stock') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Price Modal (for branch users) -->
    @if(currentBranch() && vendorSetting('allow_branch_user_to_edit_price', false) && (vendorCan('manage-product-prices') || vendorCan('manage-products')))
        <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editPriceForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPriceModalLabel">{{ __('Edit Price') }} - {{ currentBranch()->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if($product->type === 'simple')
                                @php
                                    $currentBranch = currentBranch();
                                    $branchStock = $product->branchProductStocks->where('branch_id', $currentBranch->id)->first();
                                @endphp
                                <div class="mb-3">
                                    <label for="price_value" class="form-label">{{ __('Price') }} <small class="text-muted">({{ __('Leave empty to use default product price') }})</small></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price_value" name="price" value="{{ $branchStock && $branchStock->price !== null ? $branchStock->price : '' }}" placeholder="{{ number_format($product->price, 2) }}">
                                    <small class="text-muted">{{ __('Default price:') }} {{ number_format($product->price, 2) }} {{ setting('currency_symbol', '$') }}</small>
                                </div>
                                <div class="mb-3">
                                    <label for="price_discount" class="form-label">{{ __('Discount') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price_discount" name="discount" value="{{ $branchStock && $branchStock->discount !== null ? $branchStock->discount : '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="price_discount_type" class="form-label">{{ __('Discount Type') }}</label>
                                    <select class="form-select" id="price_discount_type" name="discount_type">
                                        <option value="">{{ __('None') }}</option>
                                        <option value="percentage" {{ $branchStock && $branchStock->discount_type === 'percentage' ? 'selected' : '' }}>{{ __('Percentage') }}</option>
                                        <option value="fixed" {{ $branchStock && $branchStock->discount_type === 'fixed' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                    </select>
                                </div>
                                <input type="hidden" name="branch_id" value="{{ $currentBranch->id }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="type" value="simple">
                            @else
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Variant Prices') }}</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Variant') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                    <th>{{ __('Discount') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentBranch = currentBranch();
                                                @endphp
                                                @foreach($product->variants as $variant)
                                                    @php
                                                        $variantStock = $variant->branchVariantStocks->where('branch_id', $currentBranch->id)->first();
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $variant->getTranslation('name', app()->getLocale()) }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0"
                                                                   class="form-control form-control-sm variant-price-input"
                                                                   name="variants[{{ $variant->id }}][price]"
                                                                   value="{{ $variantStock && $variantStock->price !== null ? $variantStock->price : '' }}"
                                                                   placeholder="{{ number_format($variant->price, 2) }}"
                                                                   data-variant-id="{{ $variant->id }}">
                                                            <small class="text-muted d-block">{{ __('Default:') }} {{ number_format($variant->price, 2) }}</small>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0"
                                                                   class="form-control form-control-sm variant-discount-input"
                                                                   name="variants[{{ $variant->id }}][discount]"
                                                                   value="{{ $variantStock && $variantStock->discount !== null ? $variantStock->discount : '' }}"
                                                                   data-variant-id="{{ $variant->id }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm variant-discount-type-input"
                                                                    name="variants[{{ $variant->id }}][discount_type]"
                                                                    data-variant-id="{{ $variant->id }}">
                                                                <option value="">{{ __('None') }}</option>
                                                                <option value="percentage" {{ $variantStock && $variantStock->discount_type === 'percentage' ? 'selected' : '' }}>{{ __('%') }}</option>
                                                                <option value="fixed" {{ $variantStock && $variantStock->discount_type === 'fixed' ? 'selected' : '' }}>{{ __('Fixed') }}</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" name="branch_id" value="{{ $currentBranch->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="type" value="variable">
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Update Price') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button listener
    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.onclick = function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const deleteUrl = this.dataset.deleteUrl;

            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                html: `<p>{{ __('You are about to delete the product:') }} <strong>${productName}</strong>.</p><p class="text-danger">{{ __('This action cannot be undone!') }}</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>{{ __('Yes, delete it!') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                reverseButtons: true,
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || '{{ __('Failed to delete product') }}');
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message || '{{ __('An error occurred while deleting the product') }}');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: '{{ __('Deleted!') }}',
                        text: result.value.message || '{{ __('Product has been deleted successfully.') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('OK') }}',
                        confirmButtonColor: '#2c7eb8',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '{{ route('vendor.products.index') }}';
                    });
                }
            });
        };
    });

    // Toggle active status listener
    document.querySelectorAll('.toggle-active-btn').forEach(button => {
        button.onchange = function() {
            const toggleUrl = this.dataset.toggleUrl;
            const isActive = this.checked;

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_active: isActive
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('Success') }}',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    this.checked = !isActive;
                    Swal.fire('{{ __('Error') }}', data.message, 'error');
                }
            })
            .catch(error => {
                this.checked = !isActive;
                Swal.fire('{{ __('Error') }}', '{{ __('Failed to update status.') }}', 'error');
                console.error('Error:', error);
            });
        };
    });

    // Toggle featured status listener
    document.querySelectorAll('.toggle-featured-btn').forEach(button => {
        button.onchange = function() {
            const toggleUrl = this.dataset.toggleUrl;
            const isFeatured = this.checked;

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_featured: isFeatured
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('Success') }}',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    this.checked = !isFeatured;
                    Swal.fire('{{ __('Error') }}', data.message, 'error');
                }
            })
            .catch(error => {
                this.checked = !isFeatured;
                Swal.fire('{{ __('Error') }}', '{{ __('Failed to update featured status.') }}', 'error');
                console.error('Error:', error);
            });
        };
    });

    // Edit Stock Form Handler
    @if(currentBranch() && vendorSetting('allow_branch_user_to_edit_stock', false))
    document.getElementById('editStockForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key.startsWith('variants[')) {
                if (!data.variants) data.variants = {};
                const variantId = key.match(/\[(\d+)\]/)[1];
                data.variants[variantId] = value;
            } else {
                data[key] = value;
            }
        });

        fetch('{{ route('vendor.products.update-branch-stock', $product) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('Success') }}',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('{{ __('Error') }}', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('{{ __('Error') }}', '{{ __('Failed to update stock.') }}', 'error');
            console.error('Error:', error);
        });
    });
    @endif

    // Edit Price Form Handler
    @if(currentBranch() && vendorSetting('allow_branch_user_to_edit_price', false))
    document.getElementById('editPriceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key.startsWith('variants[')) {
                if (!data.variants) data.variants = {};
                const match = key.match(/\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const variantId = match[1];
                    const field = match[2];
                    if (!data.variants[variantId]) data.variants[variantId] = {};
                    data.variants[variantId][field] = value;
                }
            } else {
                data[key] = value;
            }
        });

        fetch('{{ route('vendor.products.update-branch-price', $product) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('Success') }}',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('{{ __('Error') }}', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('{{ __('Error') }}', '{{ __('Failed to update price.') }}', 'error');
            console.error('Error:', error);
        });
    });
    @endif

    // Edit Price Form Handler
    @if(currentBranch() && vendorSetting('allow_branch_user_to_edit_price', false))
    document.getElementById('editPriceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key.startsWith('variants[')) {
                if (!data.variants) data.variants = {};
                const match = key.match(/\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const variantId = match[1];
                    const field = match[2];
                    if (!data.variants[variantId]) data.variants[variantId] = {};
                    data.variants[variantId][field] = value;
                }
            } else {
                data[key] = value;
            }
        });

        fetch('{{ route('vendor.products.update-branch-price', $product) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('Success') }}',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('{{ __('Error') }}', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('{{ __('Error') }}', '{{ __('Failed to update price.') }}', 'error');
            console.error('Error:', error);
        });
    });
    @endif

});
</script>
@endpush
