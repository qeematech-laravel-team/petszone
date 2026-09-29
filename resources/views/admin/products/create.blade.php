@extends('layouts.app')

@php
    $page = 'products';
    // Prepare variants data for JavaScript
    $variantsData = ($variants ?? collect())
        ->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'is_required' => $v->is_required,
                'options' => ($v->options ?? collect())
                    ->map(function ($o) {
                        return ['id' => $o->id, 'name' => $o->name, 'code' => $o->code ?? ''];
                    })
                    ->toArray(),
            ];
        })
        ->toArray();
@endphp

@section('title', __('Create Product'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Success/Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Create Product') }}</h1>
                <p class="text-muted mb-0">{{ __('Add a new product step by step') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Product Form Wizard -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div x-data="productWizard()" x-init="init()">
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ __('Progress') }}</span>
                                    <span class="text-muted" x-text="`${currentStep} / ${totalSteps}`"></span>
                                </div>

                            </div>

                            <!-- Step Indicators -->
                            <div class="wizard-steps mb-4">
                                <div class="d-flex justify-content-between">
                                    <div class="wizard-step text-center"
                                        :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }"
                                        @click="goToStep(1)">
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 1"></i>
                                            <span x-show="currentStep === 1">1</span>
                                        </div>
                                        <div class="step-title">{{ __('Basic Info') }}</div>
                                        <small class="step-description text-muted">{{ __('Product details') }}</small>
                                    </div>

                                    <div class="wizard-step text-center"
                                        :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }"
                                        @click="goToStep(2)">
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 2"></i>
                                            <span x-show="currentStep <= 2">2</span>
                                        </div>
                                        <div class="step-title">{{ __('Pricing & Stock') }}</div>
                                        <small class="step-description text-muted">{{ __('Price and inventory') }}</small>
                                    </div>

                                    <div class="wizard-step text-center"
                                        :class="{ 'active': currentStep === 3, 'completed': currentStep > 3 }"
                                        @click="goToStep(3)">
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 3"></i>
                                            <span x-show="currentStep <= 3">3</span>
                                        </div>
                                        <div class="step-title">{{ __('Categories') }}</div>
                                        <small class="step-description text-muted">{{ __('Select categories') }}</small>
                                    </div>

                                    <div class="wizard-step text-center"
                                        :class="{ 'active': currentStep === 4, 'completed': currentStep > 4 }"
                                        @click="goToStep(4)">
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 4"></i>
                                            <span x-show="currentStep <= 4">4</span>
                                        </div>
                                        <div class="step-title">{{ __('Images') }}</div>
                                        <small class="step-description text-muted">{{ __('Product images') }}</small>
                                    </div>

                                    <div class="wizard-step text-center"
                                        :class="{ 'active': currentStep === 5, 'completed': currentStep > 5 }"
                                        @click="goToStep(5)">
                                        <div class="step-number">
                                            <i class="bi bi-check" x-show="currentStep > 5"></i>
                                            <span x-show="currentStep <= 5">5</span>
                                        </div>
                                        <div class="step-title">{{ __('Related Products') }}</div>
                                        <small class="step-description text-muted">{{ __('Cross-sell & upsell') }}</small>
                                    </div>
                                </div>
                            </div>

                                <!-- Form Content -->
                                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
                                    id="productForm" @submit.prevent="ensureFilesBeforeSubmit(); $el.submit();">
                                @csrf

                                <!-- Step 1: Basic Information -->
                                <div x-show="currentStep === 1" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i
                                            class="bi bi-info-circle me-2"></i>{{ __('Product Basic Information') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Enter the basic product details') }}</p>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="vendor_id" class="form-label">{{ __('Vendor') }} *</label>
                                            <select class="form-select @error('vendor_id') is-invalid @enderror"
                                                id="vendor_id" name="vendor_id" x-model="formData.vendor_id"
                                                @change="onVendorChange()" required>
                                                <option value="">{{ __('Select Vendor') }}</option>
                                                @foreach ($vendors ?? [] as $vendor)
                                                    <option value="{{ $vendor->id }}"
                                                        {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                        {{ $vendor->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vendor_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="type" class="form-label">{{ __('Product Type') }} *</label>
                                            <select class="form-select @error('type') is-invalid @enderror" id="type"
                                                name="type" x-model="formData.type" @change="onTypeChange()" required>
                                                <option value="simple"
                                                    {{ old('type', 'simple') === 'simple' ? 'selected' : '' }}>
                                                    {{ __('Simple') }}</option>
                                                <option value="variable"
                                                    {{ old('type') === 'variable' ? 'selected' : '' }}>
                                                    {{ __('Variable') }}</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small
                                                class="text-muted">{{ __('Simple: Single product. Variable: Product with variants') }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="name_en" class="form-label">{{ __('Product Name (English)') }}
                                            *</label>
                                        <input type="text" class="form-control @error('name.en') is-invalid @enderror"
                                            id="name_en" name="name[en]" x-model="formData.name_en"
                                            @input="generateSlug()" value="{{ old('name.en', '') }}" required>
                                        @error('name.en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="name_ar" class="form-label">{{ __('Product Name (Arabic)') }}
                                            *</label>
                                        <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                            id="name_ar" name="name[ar]" x-model="formData.name_ar"
                                            value="{{ old('name.ar', '') }}" required>
                                        @error('name.ar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="slug" class="form-label">{{ __('Slug') }}</label>
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                            id="slug" name="slug" x-model="formData.slug"
                                            value="{{ old('slug', '') }}"
                                            placeholder="{{ __('Auto-generated from English name') }}">
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small
                                            class="text-muted">{{ __('Auto-generated from English name if left empty') }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="sku" class="form-label">{{ __('SKU') }}</label>
                                        <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                            id="sku" name="sku" x-model="formData.sku"
                                            @input="updateVariationSkus()" value="{{ old('sku', '') }}"
                                            placeholder="{{ __('Auto-generated if left empty') }}">
                                        @error('sku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted" x-show="formData.type === 'variable'">
                                            {{ __('Variation SKUs will be auto-generated as: ProductSKU-OptionCode1-OptionCode2') }}
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description_en"
                                            class="form-label">{{ __('Description (English)') }}</label>
                                        <textarea class="form-control @error('description.en') is-invalid @enderror" id="description_en"
                                            name="description[en]" rows="4" x-model="formData.description_en">{{ old('description.en', '') }}</textarea>
                                        @error('description.en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description_ar"
                                            class="form-label">{{ __('Description (Arabic)') }}</label>
                                        <textarea class="form-control @error('description.ar') is-invalid @enderror" id="description_ar"
                                            name="description[ar]" rows="4" x-model="formData.description_ar">{{ old('description.ar', '') }}</textarea>
                                        @error('description.ar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Step 2: Pricing & Stock -->
                                <div x-show="currentStep === 2" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i
                                            class="bi bi-currency-dollar me-2"></i>{{ __('Pricing & Stock') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Set product pricing and inventory') }}</p>

                                    <div class="row">
                                        <div class="col-md-6 mb-3" x-show="formData.type === 'simple'">
                                            <label for="price" class="form-label">{{ __('Price') }}
                                                ({{ setting('currency') }}) *</label>
                                            <input type="number" step="0.01" min="0"
                                                class="form-control @error('price') is-invalid @enderror" id="price"
                                                name="price" x-model="formData.price" value="{{ old('price', '0') }}"
                                                :required="formData.type === 'simple'">
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="discount" class="form-label">{{ __('Discount') }}</label>
                                            <div class="row g-2">
                                                <div class="col-8">
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('discount') is-invalid @enderror"
                                                        id="discount" name="discount" x-model="formData.discount"
                                                        value="{{ old('discount', '0') }}">
                                                    @error('discount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-4">
                                                    <select
                                                        class="form-select @error('discount_type') is-invalid @enderror"
                                                        id="discount_type" name="discount_type"
                                                        x-model="formData.discount_type">
                                                        <option value="percentage"
                                                            {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>
                                                            %</option>
                                                        <option value="fixed"
                                                            {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>
                                                            ({{ setting('currency') }}) </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Variant Selection (only for variable products) -->
                                    <div x-show="formData.type === 'variable'" x-transition class="mb-4 mt-4">
                                        <h6 class="mb-3">{{ __('Select Variants') }}</h6>
                                        <p class="text-muted small mb-3">
                                            {{ __('Select which variants this product will have') }}</p>
                                        <div class="row g-4">
                                            <template x-for="variant in variants" :key="variant.id">
                                                <div class="col-md-6">
                                                    <div class="card border">
                                                        <div class="card-body">
                                                            <label class="form-label fw-bold text-primary mb-3">
                                                                <span x-text="getVariantName(variant)"></span>
                                                                <span x-show="variant.is_required"
                                                                    class="text-danger">*</span>
                                                            </label>
                                                            <div class="border rounded p-3" style="min-height: 100px;">
                                                                <template
                                                                    x-if="variant.options && variant.options.length > 0">
                                                                    <div>
                                                                        <template x-for="option in variant.options"
                                                                            :key="option.id">
                                                                            <div class="form-check mb-2">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox"
                                                                                    :id="`variant_${variant.id}_option_${option.id}`"
                                                                                    :value="option.id"
                                                                                    :checked="(formData.variantOptions[variant
                                                                                        .id] || []).includes(option.id
                                                                                        .toString())"
                                                                                    @change="toggleVariantOption(variant.id, option.id, $event.target.checked)">
                                                                                <label class="form-check-label"
                                                                                    :for="`variant_${variant.id}_option_${option.id}`">
                                                                                    <span
                                                                                        x-text="getOptionName(option)"></span>
                                                                                    <span class="text-muted ms-1"
                                                                                        x-show="option.code"
                                                                                        x-text="`(${option.code})`"></span>
                                                                                </label>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                                <div x-show="!variant.options || variant.options.length === 0"
                                                                    class="text-muted small">
                                                                    {{ __('No options available') }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="variants.length === 0" class="col-12">
                                                <div class="alert alert-info">
                                                    {{ __('No variants available. Please create variants first.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Variations Table (only for variable products with selected variants) -->
                                    <div x-show="formData.type === 'variable' && variations.length > 0" x-transition
                                        class="mb-4">
                                        <h6 class="mb-3">{{ __('Product Variations') }}</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 15%;">{{ __('Variation') }}</th>
                                                        <th style="width: 12%;">{{ __('Name (EN)') }}</th>
                                                        <th style="width: 12%;">{{ __('Name (AR)') }}</th>
                                                        <th style="width: 10%;">{{ __('SKU') }}</th>
                                                        <th style="width: 10%;">{{ __('Price') }}</th>
                                                        <th style="width: 10%;">{{ __('Thumbnail') }}</th>
                                                        <th style="width: 31%;">{{ __('Branch Stock') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(variation, index) in variations"
                                                        :key="index">
                                                        <tr>
                                                            <td>
                                                                <span class="small" x-text="variation.name"></span>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    :name="`variations[${index}][name][en]`"
                                                                    x-model="variation.name_en"
                                                                    placeholder="{{ __('English name') }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    :name="`variations[${index}][name][ar]`"
                                                                    x-model="variation.name_ar"
                                                                    placeholder="{{ __('Arabic name') }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    :name="`variations[${index}][sku]`"
                                                                    x-model="variation.sku"
                                                                    placeholder="{{ __('Auto') }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0"
                                                                    class="form-control form-control-sm"
                                                                    :name="`variations[${index}][price]`"
                                                                    x-model="variation.price" required>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <input type="file"
                                                                        class="form-control form-control-sm mb-2"
                                                                        :id="`variation_thumbnail_${index}`"
                                                                        :name="`variations[${index}][thumbnail]`"
                                                                        accept="image/*"
                                                                        @change="handleVariationThumbnailChange(index, $event)"
                                                                        style="font-size: 0.75rem;">
                                                                    <div x-show="variation.thumbnailPreview"
                                                                        class="position-relative">
                                                                        <img :src="variation.thumbnailPreview"
                                                                            alt="Preview" class="img-thumbnail"
                                                                            style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0"
                                                                            style="width: 18px; height: 18px; font-size: 10px; line-height: 1;"
                                                                            @click="removeVariationThumbnail(index)">
                                                                            <i class="bi bi-x"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div x-show="vendorBranches.length > 0"
                                                                    style="max-height: 200px; overflow-y: auto;">
                                                                    <template x-for="branch in vendorBranches"
                                                                        :key="branch.id">
                                                                        <div class="mb-2 p-2 border rounded">
                                                                            <label class="small d-block mb-1 fw-bold"
                                                                                x-text="branch.name"></label>
                                                                            <div class="mb-1">
                                                                                <label class="small text-muted d-block">{{ __('Stock') }}</label>
                                                                                <input type="number" min="0"
                                                                                    class="form-control form-control-sm"
                                                                                    :name="`variations[${index}][branch_stocks][${branch.id}]`"
                                                                                    :value="variation.branchStocks && variation
                                                                                        .branchStocks[branch.id] ? variation
                                                                                        .branchStocks[branch.id] : 0"
                                                                                    @input="updateBranchStock(variation, branch.id, $event.target.value)"
                                                                                    style="width: 100%;">
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <label class="small text-muted d-block">{{ __('Price') }}</label>
                                                                                <input type="number" step="0.01" min="0"
                                                                                    class="form-control form-control-sm"
                                                                                    :name="`variations[${index}][branch_prices][${branch.id}][price]`"
                                                                                    :value="variation.branchPrices && variation.branchPrices[branch.id] ? variation.branchPrices[branch.id].price : ''"
                                                                                    placeholder="{{ __('Default') }}"
                                                                                    style="width: 100%;">
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <label class="small text-muted d-block">{{ __('Discount') }}</label>
                                                                                <input type="number" step="0.01" min="0"
                                                                                    class="form-control form-control-sm"
                                                                                    :name="`variations[${index}][branch_prices][${branch.id}][discount]`"
                                                                                    :value="variation.branchPrices && variation.branchPrices[branch.id] ? variation.branchPrices[branch.id].discount : ''"
                                                                                    style="width: 100%;">
                                                                            </div>
                                                                            <div>
                                                                                <label class="small text-muted d-block">{{ __('Type') }}</label>
                                                                                <select class="form-select form-select-sm"
                                                                                    :name="`variations[${index}][branch_prices[${branch.id}][discount_type]`">
                                                                                    <option value="">{{ __('None') }}</option>
                                                                                    <option value="percentage" :selected="variation.branchPrices && variation.branchPrices[branch.id] && variation.branchPrices[branch.id].discount_type === 'percentage'">{{ __('%') }}</option>
                                                                                    <option value="fixed" :selected="variation.branchPrices && variation.branchPrices[branch.id] && variation.branchPrices[branch.id].discount_type === 'fixed'">{{ __('Fixed') }}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                                <div x-show="vendorBranches.length === 0"
                                                                    class="text-muted small">
                                                                    {{ __('No branches') }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <!-- Hidden inputs for variation values (outside table row for proper form submission) -->
                                                        <template x-for="(value, vIndex) in variation.values"
                                                            :key="vIndex">
                                                            <input type="hidden"
                                                                :name="`variations[${index}][values][${vIndex}][variant_id]`"
                                                                :value="value.variant_id">
                                                            <input type="hidden"
                                                                :name="`variations[${index}][values][${vIndex}][option_id]`"
                                                                :value="value.option_id">
                                                        </template>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Branch Stock (only when vendor is selected) -->
                                    <div x-show="formData.vendor_id && formData.type === 'simple'" x-transition
                                        class="mb-4">
                                        <h6 class="mb-3">{{ __('Branch Stock') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Set stock quantity for each branch') }}
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Branch') }}</th>
                                                        <th>{{ __('Stock Quantity') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="branch in vendorBranches" :key="branch.id">
                                                        <tr>
                                                            <td>
                                                                <span x-text="branch.name"></span>
                                                            </td>
                                                            <td>
                                                                <input type="number" min="0"
                                                                    class="form-control form-control-sm"
                                                                    :name="`branch_stocks[${branch.id}]`"
                                                                    x-model="branch.stock" :value="branch.stock || 0">
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="vendorBranches.length === 0">
                                                        <td colspan="2" class="text-center text-muted">
                                                            {{ __('No branches found for this vendor') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Branch Prices (only when vendor is selected) -->
                                    <div x-show="formData.vendor_id && formData.type === 'simple'" x-transition
                                        class="mb-4">
                                        <h6 class="mb-3">{{ __('Branch Prices') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Set custom price for each branch (leave empty to use default product price)') }}
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Branch') }}</th>
                                                        <th>{{ __('Price') }}</th>
                                                        <th>{{ __('Discount') }}</th>
                                                        <th>{{ __('Discount Type') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="branch in vendorBranches" :key="branch.id">
                                                        <tr>
                                                            <td>
                                                                <span x-text="branch.name"></span>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0"
                                                                    class="form-control form-control-sm"
                                                                    :name="`branch_prices[${branch.id}][price]`"
                                                                    x-model="branch.price" :value="branch.price || ''"
                                                                    placeholder="{{ __('Default Price') }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0"
                                                                    class="form-control form-control-sm"
                                                                    :name="`branch_prices[${branch.id}][discount]`"
                                                                    x-model="branch.discount" :value="branch.discount || ''">
                                                            </td>
                                                            <td>
                                                                <select class="form-select form-select-sm"
                                                                    :name="`branch_prices[${branch.id}][discount_type]`"
                                                                    x-model="branch.discount_type">
                                                                    <option value="">{{ __('None') }}</option>
                                                                    <option value="percentage">{{ __('Percentage') }}</option>
                                                                    <option value="fixed">{{ __('Fixed') }}</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="vendorBranches.length === 0">
                                                        <td colspan="4" class="text-center text-muted">
                                                            {{ __('No branches found for this vendor') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Status Toggles -->
                                    <div class="row mt-4">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input @error('is_active') is-invalid @enderror"
                                                    type="checkbox" id="is_active" name="is_active" value="1"
                                                    x-model="formData.is_active"
                                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    {{ __('Active') }}
                                                </label>
                                            </div>
                                            @error('is_active')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input @error('is_featured') is-invalid @enderror"
                                                    type="checkbox" id="is_featured" name="is_featured" value="1"
                                                    x-model="formData.is_featured"
                                                    {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_featured">
                                                    {{ __('Featured') }}
                                                </label>
                                            </div>
                                            @error('is_featured')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input @error('is_new') is-invalid @enderror"
                                                    type="checkbox" id="is_new" name="is_new" value="1"
                                                    x-model="formData.is_new" {{ old('is_new') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_new">
                                                    {{ __('New Product') }}
                                                </label>
                                            </div>
                                            @error('is_new')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input @error('is_approved') is-invalid @enderror"
                                                    type="checkbox" id="is_approved" name="is_approved" value="1"
                                                    x-model="formData.is_approved"
                                                    {{ old('is_approved') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_approved">
                                                    {{ __('Approved') }}
                                                </label>
                                            </div>
                                            @error('is_approved')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input @error('is_bookable') is-invalid @enderror"
                                                    type="checkbox" id="is_bookable" name="is_bookable" value="1"
                                                    x-model="formData.is_bookable"
                                                    {{ old('is_bookable') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_bookable">
                                                    {{ __('Bookable') }}
                                                </label>
                                            </div>
                                            @error('is_bookable')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Categories -->
                                <div x-show="currentStep === 3" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-grid me-2"></i>{{ __('Categories') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Select product categories') }}</p>

                                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                        @foreach ($categories ?? [] as $category)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="categories[]"
                                                    id="category_{{ $category->id }}" value="{{ $category->id }}"
                                                    x-model="formData.categories"
                                                    {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="category_{{ $category->id }}">
                                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                                </label>
                                            </div>
                                            @if ($category->children && $category->children->count() > 0)
                                                @foreach ($category->children as $child)
                                                    <div class="form-check mb-2 ms-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="categories[]" id="category_{{ $child->id }}"
                                                            value="{{ $child->id }}" x-model="formData.categories"
                                                            {{ in_array($child->id, old('categories', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="category_{{ $child->id }}">
                                                            — {{ $child->getTranslation('name', app()->getLocale()) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                    @error('categories.*')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Step 4: Images -->
                                <div x-show="currentStep === 4" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-images me-2"></i>{{ __('Product Images') }}</h5>
                                    <p class="text-muted mb-4">{{ __('Upload product images') }}</p>

                                    <!-- Thumbnail -->
                                    <div class="mb-4">
                                        <label for="thumbnail" class="form-label">{{ __('Thumbnail') }}</label>
                                        <input type="file"
                                            class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail"
                                            name="thumbnail" accept="image/*" @change="handleThumbnailChange($event)">
                                        @error('thumbnail')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('Max size: 5MB') }}</small>
                                        <div x-show="thumbnailPreview" class="mt-3">
                                            <img :src="thumbnailPreview" alt="Thumbnail Preview" class="img-thumbnail"
                                                style="max-width: 200px; max-height: 200px;">
                                            <button type="button" class="btn btn-sm btn-danger ms-2"
                                                @click="removeThumbnail()">
                                                <i class="bi bi-trash"></i> {{ __('Remove') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Product Images -->
                                    <div class="mb-3">
                                        <label for="images" class="form-label">{{ __('Product Images') }}</label>
                                        <input type="file"
                                            class="form-control @error('images.*') is-invalid @enderror" id="images"
                                            name="images[]" accept="image/*" multiple
                                            @change="handleImagesChange($event)">
                                        @error('images.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small
                                            class="text-muted">{{ __('You can select multiple images. Max size: 5MB each') }}</small>
                                    </div>

                                    <!-- Image Preview with Remove -->
                                    <div x-show="selectedImages.length > 0" class="mt-3">
                                        <h6>{{ __('Selected Images') }}</h6>
                                        <div class="row g-3">
                                            <template x-for="(image, index) in selectedImages" :key="index">
                                                <div class="col-md-3">
                                                    <div class="position-relative">
                                                        <img :src="image.preview" alt="Preview"
                                                            class="img-thumbnail w-100"
                                                            style="height: 150px; object-fit: cover;">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                            @click="removeImage(index)">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Related Products -->
                                <div x-show="currentStep === 5" x-transition.opacity class="wizard-content">
                                    <h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>{{ __('Related Products') }}
                                    </h5>
                                    <p class="text-muted mb-4">
                                        {{ __('Select related, cross-sell, and upsell products') }}</p>

                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <h6>{{ __('Related Products') }}</h6>
                                            <select class="form-select" multiple size="10" name="related_products[]"
                                                x-model="formData.relatedProducts">
                                                @foreach ($allProducts ?? [] as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                                        ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                        </div>

                                        <div class="col-md-4 mb-4">
                                            <h6>{{ __('Cross-Sell Products') }}</h6>
                                            <select class="form-select" multiple size="10"
                                                name="cross_sell_products[]" x-model="formData.crossSellProducts">
                                                @foreach ($allProducts ?? [] as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                                        ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                        </div>

                                        <div class="col-md-4 mb-4">
                                            <h6>{{ __('Upsell Products') }}</h6>
                                            <select class="form-select" multiple size="10" name="upsell_products[]"
                                                x-model="formData.upsellProducts">
                                                @foreach ($allProducts ?? [] as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                                        ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary" @click="previousStep()"
                                        :disabled="currentStep === 1">
                                        <i class="bi bi-arrow-left me-2"></i>{{ __('Previous') }}
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary" @click="goToStep(1)"
                                            x-show="currentStep < totalSteps">
                                            {{ __('Reset') }}
                                        </button>
                                        <button type="button" class="btn btn-primary" @click="nextStep()"
                                            x-show="currentStep < totalSteps" :disabled="!canProceed()">
                                            {{ __('Next') }}
                                            <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                        <button type="submit" class="btn btn-success"
                                            x-show="currentStep === totalSteps" :disabled="!canProceed()">
                                            <i class="bi bi-check-lg me-2"></i>{{ __('Create Product') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productWizard', () => ({
                currentStep: 1,
                totalSteps: 5,
                formData: {
                    vendor_id: '{{ old('vendor_id', '') }}',
                    type: '{{ old('type', 'simple') }}',
                    name_en: '{{ old('name.en', '') }}',
                    name_ar: '{{ old('name.ar', '') }}',
                    slug: '{{ old('slug', '') }}',
                    sku: '{{ old('sku', '') }}',
                    description_en: '{{ old('description.en', '') }}',
                    description_ar: '{{ old('description.ar', '') }}',
                    price: '{{ old('price', '0') }}',
                    discount: '{{ old('discount', '0') }}',
                    discount_type: '{{ old('discount_type', 'percentage') }}',
                    categories: {{ json_encode(old('categories', [])) }},
                    is_active: {{ old('is_active', true) ? 'true' : 'false' }},
                    is_featured: {{ old('is_featured') ? 'true' : 'false' }},
                    is_new: {{ old('is_new') ? 'true' : 'false' }},
                    is_approved: {{ old('is_approved') ? 'true' : 'false' }},
                    is_bookable: {{ old('is_bookable') ? 'true' : 'false' }},
                    selectedVariants: [],
                    variantOptions: {}, // Store selected options per variant: { variantId: [optionIds] }
                    relatedProducts: [],
                    crossSellProducts: [],
                    upsellProducts: []
                },
                thumbnailPreview: null,
                thumbnailFile: null,
                selectedImages: [],
                vendorBranches: [],
                variations: [],
                variants: @json($variantsData),

                init() {
                    // Initialize form data from old values if exists
                    if (this.formData.vendor_id) {
                        this.loadVendorBranches(this.formData.vendor_id);
                    }
                },

                generateSlug() {
                    if (!this.formData.slug || this.formData.slug === '') {
                        const nameEn = this.formData.name_en || '';
                        const slug = nameEn
                            .toLowerCase()
                            .trim()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                        this.formData.slug = slug;
                    }
                },

                updateVariationSkus() {
                    // Update all variation SKUs when product SKU changes
                    if (this.formData.type === 'variable' && this.variations.length > 0) {
                        this.variations.forEach(variation => {
                            // Only update if SKU is empty or matches the old pattern
                            if (!variation.sku || variation.sku === '' || variation.sku
                                .startsWith(this.formData.sku || '')) {
                                const optionCodes = variation.values.map(value => {
                                    // Find the option from variants data
                                    const variant = this.variants.find(v => v.id ===
                                        value.variant_id);
                                    if (variant && variant.options) {
                                        const option = variant.options.find(opt => opt
                                            .id === value.option_id);
                                        if (option) {
                                            return option.code || '';
                                        }
                                    }
                                    return '';
                                }).filter(code => code).join('-');

                                const productSku = this.formData.sku || '';
                                variation.sku = productSku && optionCodes ?
                                    `${productSku}-${optionCodes}` :
                                    (productSku || optionCodes || '');
                            }
                        });
                    }
                },

                async onVendorChange() {
                    if (this.formData.vendor_id) {
                        await this.loadVendorBranches(this.formData.vendor_id);
                    } else {
                        this.vendorBranches = [];
                    }
                },

                async loadVendorBranches(vendorId) {
                    try {
                        const response = await fetch(`/admin/branches/by-vendor/${vendorId}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.vendorBranches = data.branches.map(branch => ({
                                id: branch.id,
                                name: branch.name[document.documentElement.lang] ||
                                    branch.name.en || branch.name,
                                stock: 0,
                                price: '',
                                discount: '',
                                discount_type: ''
                            }));
                            // Update existing variations with branch stocks
                            this.variations.forEach(variation => {
                                if (!variation.branchStocks) {
                                    variation.branchStocks = {};
                                }
                                this.vendorBranches.forEach(branch => {
                                    if (!variation.branchStocks.hasOwnProperty(
                                            branch.id)) {
                                        variation.branchStocks[branch.id] = 0;
                                    }
                                });
                            });
                        }
                    } catch (error) {
                        console.error('Error loading branches:', error);
                    }
                },

                onTypeChange() {
                    if (this.formData.type === 'simple') {
                        this.formData.selectedVariants = [];
                        this.formData.variantOptions = {};
                        this.variations = [];
                    }
                },

                getVariantName(variant) {
                    const locale = document.documentElement.lang || 'en';
                    if (typeof variant.name === 'object') {
                        return variant.name[locale] || variant.name.en || Object.values(variant.name)[
                        0];
                    }
                    return variant.name;
                },

                getOptionName(option) {
                    const locale = document.documentElement.lang || 'en';
                    if (typeof option.name === 'object') {
                        return option.name[locale] || option.name.en || Object.values(option.name)[0];
                    }
                    return option.name;
                },

                toggleVariantOption(variantId, optionId, isChecked) {
                    // Initialize variant options array if it doesn't exist
                    if (!this.formData.variantOptions[variantId]) {
                        this.formData.variantOptions = {
                            ...this.formData.variantOptions,
                            [variantId]: []
                        };
                    }

                    const optionIdStr = optionId.toString();
                    const currentOptions = this.formData.variantOptions[variantId] || [];

                    if (isChecked) {
                        // Add option if not already in array
                        if (!currentOptions.includes(optionIdStr)) {
                            this.formData.variantOptions[variantId] = [...currentOptions, optionIdStr];
                        }
                    } else {
                        // Remove option from array
                        this.formData.variantOptions[variantId] = currentOptions.filter(id => id !==
                            optionIdStr);
                    }

                    // Update selected variants list
                    const selectedOptions = this.formData.variantOptions[variantId] || [];
                    if (selectedOptions.length > 0 && !this.formData.selectedVariants.includes(variantId
                            .toString())) {
                        this.formData.selectedVariants.push(variantId.toString());
                    } else if (selectedOptions.length === 0) {
                        this.formData.selectedVariants = this.formData.selectedVariants.filter(id =>
                            id !== variantId.toString());
                    }

                    // Generate variations
                    this.generateVariations();
                },

                generateVariations() {
                    if (this.formData.type !== 'variable') {
                        this.variations = [];
                        return;
                    }

                    // Check if any variants have selected options
                    const hasSelectedOptions = Object.values(this.formData.variantOptions).some(
                        options => options && options.length > 0);

                    if (!hasSelectedOptions) {
                        this.variations = [];
                        return;
                    }

                    // Store existing variation data before regenerating
                    const existingVariations = this.variations.map(v => ({
                        name_en: v.name_en || '',
                        name_ar: v.name_ar || '',
                        sku: v.sku || '',
                        price: v.price || parseFloat(this.formData.price) || 0,
                        thumbnailPreview: v.thumbnailPreview || null,
                        thumbnailFile: v.thumbnailFile || null,
                        branchStocks: v.branchStocks || {}
                    }));

                    // Generate all combinations
                    this.variations = this.cartesianProduct();

                    // Restore existing data if variations count matches
                    if (existingVariations.length === this.variations.length) {
                        this.variations.forEach((variation, index) => {
                            if (existingVariations[index]) {
                                variation.name_en = existingVariations[index].name_en;
                                variation.name_ar = existingVariations[index].name_ar;
                                // Only restore SKU if it was manually edited (doesn't match auto-generated pattern)
                                const autoSku = this.generateVariationSku(variation);
                                if (existingVariations[index].sku && existingVariations[index]
                                    .sku !== autoSku) {
                                    variation.sku = existingVariations[index].sku;
                                } else {
                                    variation.sku = autoSku;
                                }
                                variation.price = existingVariations[index].price;
                                variation.thumbnailPreview = existingVariations[index]
                                    .thumbnailPreview;
                                variation.thumbnailFile = existingVariations[index]
                                    .thumbnailFile;
                                variation.branchStocks = existingVariations[index].branchStocks;
                            }
                        });
                    } else {
                        // New variations generated - update SKUs
                        this.variations.forEach(variation => {
                            variation.sku = this.generateVariationSku(variation);
                        });
                    }
                },

                generateVariationSku(variation) {
                    const productSku = this.formData.sku || '';
                    const optionCodes = variation.values.map(value => {
                        // Find the option from variants data
                        const variant = this.variants.find(v => v.id === value.variant_id);
                        if (variant && variant.options) {
                            const option = variant.options.find(opt => opt.id === value
                                .option_id);
                            if (option && option.code) {
                                return option.code;
                            }
                        }
                        return '';
                    }).filter(code => code).join('-');

                    if (productSku && optionCodes) {
                        return `${productSku}-${optionCodes}`;
                    } else if (productSku) {
                        return productSku;
                    } else if (optionCodes) {
                        return optionCodes;
                    }
                    return '';
                },

                updateVariationSkus() {
                    // Update all variation SKUs when product SKU changes (only if SKU matches auto-generated pattern)
                    if (this.formData.type === 'variable' && this.variations.length > 0) {
                        this.variations.forEach(variation => {
                            const autoSku = this.generateVariationSku(variation);
                            // Only update if SKU is empty or matches the old auto-generated pattern
                            if (!variation.sku || variation.sku === '' || variation.sku ===
                                autoSku || variation.sku.startsWith((this.formData.sku || '') +
                                    '-')) {
                                variation.sku = autoSku;
                            }
                        });
                    }
                },

                cartesianProduct() {
                    const combinations = [];

                    // Get variants that have selected options
                    const variantsWithOptions = this.variants.filter(v => {
                        const selectedOptions = this.formData.variantOptions[v.id] || [];
                        return selectedOptions.length > 0;
                    });

                    if (variantsWithOptions.length === 0) return [];

                    // Build option arrays with only selected options
                    const optionArrays = variantsWithOptions.map(variant => {
                        const selectedOptionIds = (this.formData.variantOptions[variant.id] ||
                        []).map(id => id.toString());
                        return (variant.options || []).filter(opt => selectedOptionIds.includes(
                            opt.id.toString()));
                    });

                    const generateCombinations = (index, current) => {
                        if (index === optionArrays.length) {
                            const locale = document.documentElement.lang || 'en';

                            // Generate SKU from product SKU and option codes
                            const productSku = this.formData.sku || '';
                            const optionCodes = current.map(c => {
                                // Get option code from the option object
                                if (c.code) {
                                    return c.code;
                                }
                                // Fallback: try to get code from option name if code doesn't exist
                                const optName = c.name;
                                if (typeof optName === 'object') {
                                    return (optName.en || Object.values(optName)[0] || '')
                                        .substring(0, 3).toUpperCase();
                                }
                                return (optName || '').substring(0, 3).toUpperCase();
                            }).filter(code => code).join('-');

                            const defaultSku = productSku && optionCodes ?
                                `${productSku}-${optionCodes}` :
                                (productSku || optionCodes || '');

                            const variation = {
                                name: current.map(c => {
                                    const optName = c.name;
                                    if (typeof optName === 'object') {
                                        return optName[locale] || optName.en || Object
                                            .values(optName)[0];
                                    }
                                    return optName;
                                }).join(' / '),
                                name_en: '',
                                name_ar: '',
                                values: current.map(c => ({
                                    variant_id: c.variant_id,
                                    option_id: c.id
                                })),
                                sku: defaultSku,
                                price: parseFloat(this.formData.price) || 0,
                                thumbnailPreview: null,
                                thumbnailFile: null,
                                branchStocks: {}
                            };
                            // Initialize branch stocks
                            this.vendorBranches.forEach(branch => {
                                variation.branchStocks[branch.id] = 0;
                            });
                            combinations.push(variation);
                            return;
                        }

                        optionArrays[index].forEach(option => {
                            const variant = variantsWithOptions[index];
                            generateCombinations(index + 1, [...current, {
                                ...option,
                                variant_id: variant.id
                            }]);
                        });
                    };

                    generateCombinations(0, []);
                    return combinations;
                },

                handleThumbnailChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.thumbnailFile = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.thumbnailPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeThumbnail() {
                    this.thumbnailPreview = null;
                    this.thumbnailFile = null;
                    document.getElementById('thumbnail').value = '';
                },

                handleImagesChange(event) {
                    const files = Array.from(event.target.files || []);
                    files.forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.selectedImages.push({
                                    file: file,
                                    preview: e.target.result
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                    // Update the file input to include all selected images
                    this.updateFileInput();
                },

                removeImage(index) {
                    this.selectedImages.splice(index, 1);
                    this.updateFileInput();
                },

                updateFileInput() {
                    const input = document.getElementById('images');
                    if (!input) return;

                    const dataTransfer = new DataTransfer();
                    this.selectedImages.forEach(img => {
                        if (img.file) {
                            dataTransfer.items.add(img.file);
                        }
                    });
                    input.files = dataTransfer.files;
                },

                ensureFilesBeforeSubmit() {
                    // Ensure file inputs are properly set before form submission
                    this.updateFileInput();

                    // Also ensure thumbnail file is set
                    const thumbnailInput = document.getElementById('thumbnail');
                    if (thumbnailInput && this.thumbnailFile) {
                        const thumbnailDataTransfer = new DataTransfer();
                        thumbnailDataTransfer.items.add(this.thumbnailFile);
                        thumbnailInput.files = thumbnailDataTransfer.files;
                    }

                    // Ensure variation values hidden inputs are properly set
                    const form = document.getElementById('productForm');
                    if (form) {
                        // Remove all existing variation value inputs
                        form.querySelectorAll('input[name^="variations["][name*="[values]"]').forEach(input => {
                            input.remove();
                        });

                        // Recreate variation value inputs to ensure they're properly included
                        this.variations.forEach((variation, index) => {
                            if (variation.values && Array.isArray(variation.values)) {
                                variation.values.forEach((value, vIndex) => {
                                    if (value.variant_id && value.option_id) {
                                        const variantIdInput = document.createElement('input');
                                        variantIdInput.type = 'hidden';
                                        variantIdInput.name = `variations[${index}][values][${vIndex}][variant_id]`;
                                        variantIdInput.value = value.variant_id;
                                        form.appendChild(variantIdInput);

                                        const optionIdInput = document.createElement('input');
                                        optionIdInput.type = 'hidden';
                                        optionIdInput.name = `variations[${index}][values][${vIndex}][option_id]`;
                                        optionIdInput.value = value.option_id;
                                        form.appendChild(optionIdInput);
                                    }
                                });
                            }
                        });
                    }
                },


                handleVariationThumbnailChange(variationIndex, event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.variations[variationIndex].thumbnailPreview = e.target.result;
                            this.variations[variationIndex].thumbnailFile = file;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeVariationThumbnail(variationIndex) {
                    this.variations[variationIndex].thumbnailPreview = null;
                    this.variations[variationIndex].thumbnailFile = null;
                    const input = document.getElementById(`variation_thumbnail_${variationIndex}`);
                    if (input) {
                        input.value = '';
                    }
                },

                updateBranchStock(variation, branchId, value) {
                    if (!variation.branchStocks) {
                        variation.branchStocks = {};
                    }
                    variation.branchStocks[branchId] = parseInt(value) || 0;
                },

                nextStep() {
                    if (this.validateCurrentStep()) {
                        if (this.currentStep < this.totalSteps) {
                            this.currentStep++;
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    }
                },

                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                goToStep(step) {
                    if (step <= this.currentStep || step === this.currentStep + 1) {
                        this.currentStep = step;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                validateCurrentStep() {
                    const form = document.getElementById('productForm');
                    let isValid = true;

                    if (this.currentStep === 1) {
                        if (!this.formData.vendor_id || !this.formData.name_en || !this.formData
                            .name_ar) {
                            isValid = false;
                        }
                    } else if (this.currentStep === 2) {
                        // Only validate price for simple products
                        if (this.formData.type === 'simple') {
                            if (!this.formData.price || parseFloat(this.formData.price) < 0) {
                                isValid = false;
                            }
                        }
                        // Validate variants for variable products
                        if (this.formData.type === 'variable') {
                            const hasSelectedOptions = Object.values(this.formData.variantOptions).some(
                                options => options && options.length > 0);
                        }
                    }

                    return isValid;
                },

                canProceed() {
                    return this.validateCurrentStep();
                }
            }));
        });
    </script>
@endpush
