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

    // Prepare existing product data for JavaScript
    $existingVariations = [];
    $selectedVariantOptions = [];
    if ($product->type === 'variable' && $product->variants && $product->variants->count() > 0) {
        foreach ($product->variants as $variant) {
            $optionIds = [];
            $variantId = null;

            // Get variant option IDs from variant values
            if ($variant->values && $variant->values->count() > 0) {
                foreach ($variant->values as $value) {
                    // Check if variantOption relationship is loaded
                    if ($value->relationLoaded('variantOption') && $value->variantOption) {
                        $variantId = $value->variantOption->variant_id;
                        $optionIds[] = $value->variant_option_id;
                    } elseif ($value->variant_option_id) {
                        // If relationship not loaded, try to load it
                        $value->load('variantOption');
                        if ($value->variantOption) {
                            $variantId = $value->variantOption->variant_id;
                            $optionIds[] = $value->variant_option_id;
                        }
                    }
                }
            }

            if (!empty($optionIds) && $variantId) {
                if (!isset($selectedVariantOptions[$variantId])) {
                    $selectedVariantOptions[$variantId] = [];
                }
                $selectedVariantOptions[$variantId] = array_unique(
                    array_merge($selectedVariantOptions[$variantId], $optionIds),
                );
            }

            // Prepare variation data - always add variant even if it has no values
            $branchStocks = [];
            if ($variant->branchVariantStocks && $variant->branchVariantStocks->count() > 0) {
                foreach ($variant->branchVariantStocks as $stock) {
                    $branchStocks[$stock->branch_id] = $stock->quantity;
                }
            }

            $values = [];
            if ($variant->values && $variant->values->count() > 0) {
                foreach ($variant->values as $value) {
                    // Ensure variantOption is loaded
                    if (!$value->relationLoaded('variantOption')) {
                        $value->load('variantOption');
                    }

                    if ($value->variantOption) {
                        $values[] = [
                            'variant_id' => $value->variantOption->variant_id,
                            'option_id' => $value->variant_option_id,
                        ];
                    }
                }
            }

            // Always add variant to existingVariations, even if it has no values
            $existingVariations[] = [
                'name' => $variant->getTranslation('name', app()->getLocale()),
                'name_en' => $variant->getTranslation('name', 'en'),
                'name_ar' => $variant->getTranslation('name', 'ar'),
                'sku' => $variant->sku ?? '',
                'price' => $variant->price ?? 0,
                'thumbnailPreview' => $variant->thumbnail ? $variant->thumbnail : null,
                'thumbnailFile' => null,
                'values' => $values,
                'branchStocks' => $branchStocks,
            ];
        }
    }

    // Prepare branch stocks for simple products
    $branchStocksData = [];
    if ($product->type === 'simple' && $product->branchProductStocks) {
        foreach ($product->branchProductStocks as $stock) {
            $branchStocksData[$stock->branch_id] = $stock->quantity;
        }
    }

    // Prepare related products
    $relatedProductsIds = $product->relations()->where('type', 'related')->pluck('related_product_id')->toArray();
    $crossSellProductsIds = $product->relations()->where('type', 'cross_sell')->pluck('related_product_id')->toArray();
    $upsellProductsIds = $product->relations()->where('type', 'upsell')->pluck('related_product_id')->toArray();

@endphp

@section('title', __('Edit Product'))

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
                <h1 class="h3 mb-0">{{ __('Edit Product') }}</h1>
                <p class="text-muted mb-0">{{ __('Update product information step by step') }}</p>
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
                    <div class="card-body" id="productWizardContainer">
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">{{ __('Progress') }}</span>
                                <span class="text-muted" id="progressText">1 / 5</span>
                            </div>

                        </div>

                        <!-- Step Indicators -->
                        <div class="wizard-steps mb-4">
                            <div class="d-flex justify-content-between">
                                <div class="wizard-step text-center active" data-step="1"
                                    onclick="productWizard.goToStep(1)" style="cursor: pointer;">
                                    <div class="step-number">
                                        <i class="bi bi-check" style="display: none;"></i>
                                        <span>1</span>
                                    </div>
                                    <div class="step-title">{{ __('Basic Info') }}</div>
                                    <small class="step-description text-muted">{{ __('Product details') }}</small>
                                </div>

                                <div class="wizard-step text-center" data-step="2" onclick="productWizard.goToStep(2)"
                                    style="cursor: pointer;">
                                    <div class="step-number">
                                        <i class="bi bi-check" style="display: none;"></i>
                                        <span>2</span>
                                    </div>
                                    <div class="step-title">{{ __('Pricing & Stock') }}</div>
                                    <small class="step-description text-muted">{{ __('Price and inventory') }}</small>
                                </div>

                                <div class="wizard-step text-center" data-step="3" onclick="productWizard.goToStep(3)"
                                    style="cursor: pointer;">
                                    <div class="step-number">
                                        <i class="bi bi-check" style="display: none;"></i>
                                        <span>3</span>
                                    </div>
                                    <div class="step-title">{{ __('Categories') }}</div>
                                    <small class="step-description text-muted">{{ __('Select categories') }}</small>
                                </div>

                                <div class="wizard-step text-center" data-step="4" onclick="productWizard.goToStep(4)"
                                    style="cursor: pointer;">
                                    <div class="step-number">
                                        <i class="bi bi-check" style="display: none;"></i>
                                        <span>4</span>
                                    </div>
                                    <div class="step-title">{{ __('Images') }}</div>
                                    <small class="step-description text-muted">{{ __('Product images') }}</small>
                                </div>

                                <div class="wizard-step text-center" data-step="5" onclick="productWizard.goToStep(5)"
                                    style="cursor: pointer;">
                                    <div class="step-number">
                                        <i class="bi bi-check" style="display: none;"></i>
                                        <span>5</span>
                                    </div>
                                    <div class="step-title">{{ __('Related Products') }}</div>
                                    <small class="step-description text-muted">{{ __('Cross-sell & upsell') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <form action="{{ route('admin.products.update', $product) }}" method="POST"
                            enctype="multipart/form-data" id="productForm"
                            onsubmit="return productWizard.handleSubmit(event);">
                            @csrf
                            @method('PUT')

                            <!-- Step 1: Basic Information -->
                            <div id="step-1" class="wizard-content">
                                <h5 class="mb-3"><i
                                        class="bi bi-info-circle me-2"></i>{{ __('Product Basic Information') }}</h5>
                                <p class="text-muted mb-4">{{ __('Enter the basic product details') }}</p>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vendor_id" class="form-label">{{ __('Vendor') }} *</label>
                                        <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id"
                                            name="vendor_id" onchange="productWizard.onVendorChange()" required>
                                            <option value="">{{ __('Select Vendor') }}</option>
                                            @foreach ($vendors ?? [] as $vendor)
                                                <option value="{{ $vendor->id }}"
                                                    {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>
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
                                            name="type" onchange="productWizard.onTypeChange()" required>
                                            <option value="simple"
                                                {{ old('type', $product->type) === 'simple' ? 'selected' : '' }}>
                                                {{ __('Simple') }}</option>
                                            <option value="variable"
                                                {{ old('type', $product->type) === 'variable' ? 'selected' : '' }}>
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
                                        id="name_en" name="name[en]" oninput="productWizard.generateSlug()"
                                        value="{{ old('name.en', $product->getTranslation('name', 'en')) }}" required>
                                    @error('name.en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('Product Name (Arabic)') }}
                                        *</label>
                                    <input type="text" class="form-control @error('name.ar') is-invalid @enderror"
                                        id="name_ar" name="name[ar]"
                                        value="{{ old('name.ar', $product->getTranslation('name', 'ar')) }}" required>
                                    @error('name.ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">{{ __('Slug') }}</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                        id="slug" name="slug" value="{{ old('slug', $product->slug) }}"
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
                                        id="sku" name="sku" oninput="productWizard.updateVariationSkus()"
                                        value="{{ old('sku', $product->sku) }}"
                                        placeholder="{{ __('Auto-generated if left empty') }}">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="sku-hint"
                                        style="display: {{ old('type', $product->type) === 'variable' ? 'block' : 'none' }};">
                                        {{ __('Variation SKUs will be auto-generated as: ProductSKU-OptionCode1-OptionCode2') }}
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="description_en"
                                        class="form-label">{{ __('Description (English)') }}</label>
                                    <textarea class="form-control @error('description.en') is-invalid @enderror" id="description_en"
                                        name="description[en]" rows="4">{{ old('description.en', $product->getTranslation('description', 'en')) }}</textarea>
                                    @error('description.en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description_ar"
                                        class="form-label">{{ __('Description (Arabic)') }}</label>
                                    <textarea class="form-control @error('description.ar') is-invalid @enderror" id="description_ar"
                                        name="description[ar]" rows="4">{{ old('description.ar', $product->getTranslation('description', 'ar')) }}</textarea>
                                    @error('description.ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 2: Pricing & Stock -->
                            <div id="step-2" class="wizard-content" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-currency-dollar me-2"></i>{{ __('Pricing & Stock') }}
                                </h5>
                                <p class="text-muted mb-4">{{ __('Set product pricing and inventory') }}</p>

                                <div class="row">
                                    <div class="col-md-6 mb-3" id="price-field"
                                        style="display: {{ old('type', $product->type) === 'simple' ? 'block' : 'none' }};">
                                        <label for="price" class="form-label">{{ __('Price') }}
                                            ({{ setting('currency') }}) *</label>
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('price') is-invalid @enderror" id="price"
                                            name="price" value="{{ old('price', $product->price) }}"
                                            {{ old('type', $product->type) === 'simple' ? 'required' : '' }}>
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
                                                    id="discount" name="discount"
                                                    value="{{ old('discount', $product->discount) }}">
                                                @error('discount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select @error('discount_type') is-invalid @enderror"
                                                    id="discount_type" name="discount_type">
                                                    <option value="percentage"
                                                        {{ old('discount_type', $product->discount_type) === 'percentage' ? 'selected' : '' }}>
                                                        %</option>
                                                    <option value="fixed"
                                                        {{ old('discount_type', $product->discount_type) === 'fixed' ? 'selected' : '' }}>
                                                        ({{ setting('currency') }}) </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Variant Selection (only for variable products) -->
                                <div x-show="window.formData.type === 'variable'" x-transition class="mb-4 mt-4">
                                    <h6 class="mb-3">{{ __('Select Variants') }}</h6>
                                    <p class="text-muted small mb-3">
                                        {{ __('Select which variants this product will have') }}</p>
                                    <div class="row g-4" x-data="{
                                        get variants() { return window.productWizard?.variants || []; },
                                        getVariantName(variant) { return window.productWizard?.getVariantName(variant) || ''; },
                                        getOptionName(option) { return window.productWizard?.getOptionName(option) || ''; },
                                        toggleOption(variantId, optionId, checked) {
                                            if (window.productWizard) {
                                                window.productWizard.toggleVariantOption(variantId, optionId, checked);
                                            }
                                        },
                                        isOptionChecked(variantId, optionId) {
                                            const options = window.formData?.variantOptions?.[variantId];
                                            if (!Array.isArray(options)) {
                                                return false;
                                            }
                                            return options.includes(Number(optionId));
                                        }
                                    }">
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
                                                            <template x-if="variant.options && variant.options.length > 0">
                                                                <div>
                                                                    <template x-for="option in variant.options"
                                                                        :key="option.id">
                                                                        <div class="form-check mb-2">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                :id="`variant_${variant.id}_option_${option.id}`"
                                                                                :value="option.id"
                                                                                :checked="isOptionChecked(variant.id, option.id)"
                                                                                @change="toggleOption(variant.id, option.id, $event.target.checked)">
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
                                <div id="variations-table-section" class="mb-4" x-data="{
                                    variations: window.productWizard?.variations || [],
                                    vendorBranches: window.productWizard?.vendorBranches || [],
                                    init() {
                                        // Watch for changes in vendorBranches
                                        const updateBranches = () => {
                                            this.vendorBranches = window.productWizard?.vendorBranches || [];
                                        };
                                        // Listen to custom events
                                        window.addEventListener('branches-loaded', updateBranches);
                                        // Initial load
                                        updateBranches();
                                    }
                                }"
                                    @variations-updated.window="variations = window.productWizard?.variations || []"
                                    x-show="window.formData.type === 'variable' && variations.length > 0" x-transition>
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
                                            <tbody id="variations-tbody">
                                                <template x-for="(variation, index) in variations" :key="index">
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
                                                                :name="`variations[${index}][sku]`" x-model="variation.sku"
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
                                                                    @change="window.productWizard.handleVariationThumbnailChange(index, $event)"
                                                                    style="font-size: 0.75rem;">
                                                                <div x-show="variation.thumbnailPreview"
                                                                    class="position-relative">
                                                                    <img :src="variation.thumbnailPreview" alt="Preview"
                                                                        class="img-thumbnail"
                                                                        style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0"
                                                                        style="width: 18px; height: 18px; font-size: 10px; line-height: 1;"
                                                                        @click="window.productWizard.removeVariationThumbnail(index)">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div x-show="vendorBranches && vendorBranches.length > 0"
                                                                style="max-height: 200px; overflow-y: auto;">
                                                                <template
                                                                    x-for="branch in vendorBranches"
                                                                    :key="branch.id">
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <label class="small me-2"
                                                                            style="width: 80px; font-size: 0.75rem; overflow: hidden; text-overflow: ellipsis;"
                                                                            x-text="branch.name"
                                                                            :title="branch.name"></label>
                                                                        <input type="number" min="0"
                                                                            class="form-control form-control-sm"
                                                                            :name="`variations[${index}][branch_stocks][${branch.id}]`"
                                                                            :value="variation.branchStocks && variation
                                                                                .branchStocks[branch.id] ? variation
                                                                                .branchStocks[branch.id] : 0"
                                                                            @input="window.productWizard.updateBranchStock(variation, branch.id, $event.target.value)"
                                                                            style="width: 80px;">
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            <div x-show="!vendorBranches || vendorBranches.length === 0"
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
                                <div x-data="{ variations: window.productWizard?.variations || [] }"
                                    @variations-updated.window="variations = window.productWizard?.variations || []"
                                    x-show="window.formData.type === 'variable' && variations.length === 0"
                                    class="text-center py-4 text-muted mb-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">
                                        {{ __('No variations added yet. Select variants above to create variations.') }}
                                    </p>
                                </div>

                                <!-- Branch Stock (only when vendor is selected) -->
                                <div id="branch-stock-section" class="mb-4"
                                    style="display: {{ old('vendor_id', $product->vendor_id) && old('type', $product->type) === 'simple' ? 'block' : 'none' }};">
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
                                            <tbody id="branch-stock-tbody">
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">
                                                        {{ __('Loading branches...') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Branch Prices (only when vendor is selected) -->
                                <div id="branch-prices-section" class="mb-4"
                                    style="display: {{ old('vendor_id', $product->vendor_id) && old('type', $product->type) === 'simple' ? 'block' : 'none' }};">
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
                                            <tbody id="branch-prices-tbody">
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        {{ __('Loading branches...') }}
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
                                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
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
                                                {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
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
                                                x-model="formData.is_new"
                                                {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
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
                                                {{ old('is_approved', $product->is_approved) ? 'checked' : '' }}>
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
                                                {{ old('is_bookable', $product->is_bookable) ? 'checked' : '' }}>
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
                            <div id="step-3" class="wizard-content" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-grid me-2"></i>{{ __('Categories') }}</h5>
                                <p class="text-muted mb-4">{{ __('Select product categories') }}</p>

                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    @foreach ($categories ?? [] as $category)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="categories[]"
                                                id="category_{{ $category->id }}" value="{{ $category->id }}"
                                                {{ in_array($category->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_{{ $category->id }}">
                                                {{ $category->getTranslation('name', app()->getLocale()) }}
                                            </label>
                                        </div>
                                        @if ($category->children && $category->children->count() > 0)
                                            @foreach ($category->children as $child)
                                                <div class="form-check mb-2 ms-4">
                                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                                        id="category_{{ $child->id }}" value="{{ $child->id }}"
                                                        {{ in_array($child->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="category_{{ $child->id }}">
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
                            <div id="step-4" class="wizard-content" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-images me-2"></i>{{ __('Product Images') }}</h5>
                                <p class="text-muted mb-4">{{ __('Upload product images') }}</p>

                                <!-- Thumbnail -->
                                <div class="mb-4">
                                    <label for="thumbnail" class="form-label">{{ __('Thumbnail') }}</label>
                                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror"
                                        id="thumbnail" name="thumbnail" accept="image/*"
                                        onchange="productWizard.handleThumbnailChange(event)">
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Max size: 5MB') }}</small>
                                    <div id="thumbnailPreview" class="mt-3" style="display: none;">
                                        <img id="thumbnailPreviewImg" src="" alt="Thumbnail Preview"
                                            class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2"
                                            onclick="productWizard.removeThumbnail()">
                                            <i class="bi bi-trash"></i> {{ __('Remove') }}
                                        </button>
                                    </div>
                                    @if ($product->thumbnail)
                                        <div class="mt-2">
                                            <small class="text-muted">{{ __('Current thumbnail:') }}</small>
                                            <img src="{{ $product->thumbnail }}" alt="Current thumbnail"
                                                class="img-thumbnail ms-2" style="max-width: 100px; max-height: 100px;">
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Images -->
                                <div class="mb-3">
                                    <label for="images" class="form-label">{{ __('Product Images') }}</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                        id="images" name="images[]" accept="image/*" multiple
                                        onchange="productWizard.handleImagesChange(event)">
                                    @error('images.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small
                                        class="text-muted">{{ __('You can select multiple images. Max size: 5MB each') }}</small>
                                </div>

                                <!-- All Images (Existing + New) -->
                                <div class="mt-3" x-data="{
                                    deletedImageIds: [],
                                    imageUpdateCounter: 0,
                                    get newImages() {
                                        // Force reactivity by including imageUpdateCounter
                                        const _ = this.imageUpdateCounter;
                                        return window.productWizard?.selectedImages?.filter(img => !img.id) || [];
                                    },
                                    get hasImages() {
                                        return ({{ count($product->images) }} > 0 && this.deletedImageIds.length < {{ count($product->images) }}) || this.newImages.length > 0;
                                    },
                                    init() {
                                        // Listen for changes in selectedImages
                                        const updateImages = () => {
                                            this.imageUpdateCounter++;
                                        };
                                        // Listen to custom events
                                        window.addEventListener('images-changed', updateImages);
                                    }
                                }">
                                    <h6>{{ __('Product Images') }}</h6>
                                    <p class="text-muted small mb-2">
                                        {{ __('Current product images. Click the remove button to delete an image.') }}
                                    </p>
                                    <div class="row g-3">
                                        <!-- Existing Images -->
                                        @foreach ($product->images as $image)
                                            <div class="col-md-3" x-show="!deletedImageIds.includes({{ $image->id }})" x-transition>
                                                <div class="position-relative border rounded p-2 bg-light">
                                                    <img src="{{ $image->image_path }}" alt="Preview"
                                                        class="img-thumbnail w-100"
                                                        style="height: 150px; object-fit: cover;">
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle"
                                                        style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                        @click="productWizard.deleteProductImage({{ $image->id }}, true); deletedImageIds.push({{ $image->id }})"
                                                        title="{{ __('Remove image') }}">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                    <div class="position-absolute bottom-0 start-0 m-1">
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-check-circle me-1"></i>{{ __('Current') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- New Images -->
                                        <template x-for="(image, index) in newImages" :key="`new-${index}-${image.preview}`">
                                            <div class="col-md-3" x-transition>
                                                <div class="position-relative border rounded p-2 bg-light">
                                                    <img :src="image.preview" alt="Preview"
                                                        class="img-thumbnail w-100"
                                                        style="height: 150px; object-fit: cover;">
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle"
                                                        style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                        @click="productWizard.deleteProductImage(index, false)"
                                                        title="{{ __('Remove new image') }}">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                    <div class="position-absolute bottom-0 start-0 m-1">
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-plus-circle me-1"></i>{{ __('New') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Related Products -->
                            <div id="step-5" class="wizard-content" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>{{ __('Related Products') }}
                                </h5>
                                <p class="text-muted mb-4">
                                    {{ __('Select related, cross-sell, and upsell products') }}</p>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <h6>{{ __('Related Products') }}</h6>
                                        <select class="form-select" multiple size="10" name="related_products[]">
                                            @foreach ($allProducts ?? [] as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ in_array($p->id, old('related_products', $relatedProductsIds)) ? 'selected' : '' }}>
                                                    {{ $p->getTranslation('name', app()->getLocale()) }}
                                                    ({{ $p->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <h6>{{ __('Cross-Sell Products') }}</h6>
                                        <select class="form-select" multiple size="10" name="cross_sell_products[]">
                                            @foreach ($allProducts ?? [] as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ in_array($p->id, old('cross_sell_products', $crossSellProductsIds)) ? 'selected' : '' }}>
                                                    {{ $p->getTranslation('name', app()->getLocale()) }}
                                                    ({{ $p->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <h6>{{ __('Upsell Products') }}</h6>
                                        <select class="form-select" multiple size="10" name="upsell_products[]">
                                            @foreach ($allProducts ?? [] as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ in_array($p->id, old('upsell_products', $upsellProductsIds)) ? 'selected' : '' }}>
                                                    {{ $p->getTranslation('name', app()->getLocale()) }}
                                                    ({{ $p->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" onclick="productWizard.previousStep()"
                                    id="prevBtn">
                                    <i class="bi bi-arrow-left me-2"></i>{{ __('Previous') }}
                                </button>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="productWizard.goToStep(1)" id="resetBtn" style="display: none;">
                                        {{ __('Reset') }}
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="productWizard.nextStep()"
                                        id="nextBtn">
                                        {{ __('Next') }}
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn"
                                        style="display: none;">
                                        <i class="bi bi-check-lg me-2"></i>{{ __('Update Product') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        // Initialize formData for Alpine.js (must be defined before Alpine.js processes the page)
        window.formData = {
            type: '{{ old('type', $product->type) }}',
            sku: '{{ old('sku', $product->sku) }}',
            price: '{{ old('price', $product->price) }}',
            discount: '{{ old('discount', $product->discount ?? 0) }}',
            discount_type: '{{ old('discount_type', $product->discount_type ?? 'percentage') }}',
            is_active: {{ old('is_active', $product->is_active) ? 'true' : 'false' }},
            is_featured: {{ old('is_featured', $product->is_featured) ? 'true' : 'false' }},
            is_new: {{ old('is_new', $product->is_new) ? 'true' : 'false' }},
            is_bookable: {{ old('is_bookable', $product->is_bookable ?? false) ? 'true' : 'false' }},
            variantOptions: @json($selectedVariantOptions ?: (object)[]),
            selectedVariants: []
        };

        // Initialize productWizard (must be defined before Alpine.js processes the page)
        window.productWizard = {
            currentStep: 1,
            totalSteps: 5,
            thumbnailFile: null,
            selectedImages: @json(
                $product->images->map(function ($img) {
                        return ['preview' => $img->image_path, 'file' => null, 'id' => $img->id];
                    })->toArray()),
            vendorBranches: [],
            variations: @json($existingVariations),
            variants: @json($variantsData ?? []),
            existingBranchStocks: @json($branchStocksData ?? []),
            existingBranchPrices: @json($branchPricesData ?? []),
            variantOptions: @json($selectedVariantOptions ?? []),

            init() {
                // Initialize formData.variantOptions as an object (not array)
                if (!window.formData.variantOptions || Array.isArray(window.formData.variantOptions)) {
                    window.formData.variantOptions = {};
                }

                // Convert variantOptions from backend to formData format
                // Convert all IDs to strings for consistency
                if (this.variantOptions) {
                    // Check if variantOptions is an array (empty array from backend)
                    if (Array.isArray(this.variantOptions)) {
                        // If it's an empty array, initialize as empty object
                        window.formData.variantOptions = {};
                    } else {
                        // If it's an object, convert keys and values to strings
                        Object.keys(this.variantOptions).forEach(variantId => {
                            const variantIdStr = variantId.toString();
                            window.formData.variantOptions[variantIdStr] = (this.variantOptions[variantId] || []).map(id => id.toString());
                        });
                    }
                }

                // If we have existing variations, ensure variant options are selected
                // This is the primary source of truth for selected options
                console.log('Checking variations:', this.variations);
                console.log('Variations length:', this.variations?.length);

                if (this.variations && this.variations.length > 0) {
                    console.log('Processing variations...');
                    // Extract variant option IDs from existing variations
                    this.variations.forEach((variation, index) => {
                        console.log(`Variation ${index}:`, variation);
                        console.log(`Variation ${index} values:`, variation.values);

                        if (variation.values && variation.values.length > 0) {
                            variation.values.forEach((value, valueIndex) => {
                                console.log(`  Value ${valueIndex}:`, value);

                                if (value.variant_id && value.option_id) {
                                    const variantId = value.variant_id.toString();
                                    const optionId = value.option_id.toString();

                                    console.log(`  Adding: variant_id=${variantId}, option_id=${optionId}`);

                                    // Initialize variantOptions if not exists
                                    if (!window.formData.variantOptions[variantId]) {
                                        window.formData.variantOptions[variantId] = [];
                                    }

                                    // Add option if not already in array
                                    if (!window.formData.variantOptions[variantId].includes(optionId)) {
                                        window.formData.variantOptions[variantId].push(optionId);
                                        console.log(`  Added option ${optionId} to variant ${variantId}`);
                                    } else {
                                        console.log(`  Option ${optionId} already exists in variant ${variantId}`);
                                    }
                                } else {
                                    console.log(`  Value ${valueIndex} missing variant_id or option_id`);
                                }
                            });
                        } else {
                            console.log(`Variation ${index} has no values`);
                        }
                    });
                } else {
                    console.log('No variations found or variations is empty');
                }

                // Debug: Log the final variantOptions
                console.log('Final variantOptions:', window.formData.variantOptions);
                console.log('Final variantOptions keys:', Object.keys(window.formData.variantOptions));

                this.updateStepDisplay();
                // Set initial visibility based on product type and vendor
                const typeInput = document.getElementById('type');
                const vendorInput = document.getElementById('vendor_id');
                if (typeInput) {
                    typeInput.addEventListener('change', () => this.onTypeChange());
                    this.onTypeChange();
                }
                if (vendorInput) {
                    vendorInput.addEventListener('change', () => this.onVendorChange());
                }

                // Load branches if vendor is selected
                const vendorId = vendorInput?.value;
                if (vendorId) {
                    this.loadVendorBranches(vendorId);
                } else {
                    this.renderBranchStockTable();
                }
            },

            updateStepDisplay() {
                // Hide all steps
                for (let i = 1; i <= this.totalSteps; i++) {
                    const stepEl = document.getElementById(`step-${i}`);
                    if (stepEl) stepEl.style.display = 'none';
                }
                // Show current step
                const currentStepEl = document.getElementById(`step-${this.currentStep}`);
                if (currentStepEl) currentStepEl.style.display = 'block';

                // Update progress text
                const progressText = document.getElementById('progressText');
                if (progressText) progressText.textContent = `${this.currentStep} / ${this.totalSteps}`;

                // Update step indicators
                document.querySelectorAll('.wizard-step').forEach((step, index) => {
                    const stepNum = index + 1;
                    step.classList.remove('active', 'completed');
                    if (stepNum === this.currentStep) {
                        step.classList.add('active');
                    } else if (stepNum < this.currentStep) {
                        step.classList.add('completed');
                    }
                    const checkIcon = step.querySelector('.bi-check');
                    const numberSpan = step.querySelector('.step-number span');
                    if (checkIcon && numberSpan) {
                        checkIcon.style.display = stepNum < this.currentStep ? 'inline' : 'none';
                        numberSpan.style.display = stepNum < this.currentStep ? 'none' : 'inline';
                    }
                });

                // Update navigation buttons
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');
                const resetBtn = document.getElementById('resetBtn');

                if (prevBtn) prevBtn.disabled = this.currentStep === 1;
                if (nextBtn) nextBtn.style.display = this.currentStep < this.totalSteps ? 'block' : 'none';
                if (submitBtn) submitBtn.style.display = this.currentStep === this.totalSteps ? 'block' : 'none';
                if (resetBtn) resetBtn.style.display = this.currentStep < this.totalSteps ? 'block' : 'none';
            },

            generateSlug() {
                const slugInput = document.getElementById('slug');
                const nameEnInput = document.getElementById('name_en');
                if (slugInput && nameEnInput && (!slugInput.value || slugInput.value === '')) {
                    const nameEn = nameEnInput.value || '';
                    const slug = nameEn
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    slugInput.value = slug;
                }
            },

            updateVariationSkus() {
                const typeInput = document.getElementById('type');
                const skuInput = document.getElementById('sku');
                if (typeInput && skuInput && typeInput.value === 'variable' && this.variations.length > 0) {
                    const productSku = skuInput.value || '';
                    this.variations.forEach(variation => {
                        if (!variation.sku || variation.sku === '' || variation.sku.startsWith(productSku)) {
                            const optionCodes = variation.values.map(value => {
                                const variant = this.variants.find(v => v.id == value.variant_id);
                                if (variant && variant.options) {
                                    const option = variant.options.find(opt => opt.id == value
                                        .option_id);
                                    return option ? (option.code || '') : '';
                                }
                                return '';
                            }).filter(code => code).join('-');
                            variation.sku = productSku && optionCodes ? `${productSku}-${optionCodes}` : (
                                productSku || optionCodes || '');
                        }
                    });
                }
            },

            async onVendorChange() {
                const vendorInput = document.getElementById('vendor_id');
                const vendorId = vendorInput?.value;
                if (vendorId) {
                    await this.loadVendorBranches(vendorId);
                } else {
                    this.vendorBranches = [];
                    // Dispatch event to update UI
                    window.dispatchEvent(new CustomEvent('branches-loaded'));
                    this.renderBranchStockTable();
                }
            },

            async loadVendorBranches(vendorId) {
                try {
                    if (!vendorId) {
                        this.renderBranchStockTable();
                        return;
                    }

                    console.log('Loading vendor branches for vendor:', vendorId);
                    const response = await fetch(`/admin/branches/by-vendor/${vendorId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    console.log('Response status:', response.status);

                    if (response.ok) {
                        const data = await response.json();
                        console.log('Branches data:', data);

                        if (data.branches && Array.isArray(data.branches)) {
                            this.vendorBranches = data.branches.map(branch => {
                                const branchName = typeof branch.name === 'object' ?
                                    (branch.name[document.documentElement.lang] || branch.name.en || Object
                                        .values(branch.name)[0]) :
                                    branch.name;

                                return {
                                    id: branch.id,
                                    name: branchName,
                                    stock: 0,
                                    price: '',
                                    discount: '',
                                    discount_type: ''
                                };
                            });

                            console.log('Processed branches:', this.vendorBranches);
                            console.log('Existing branch prices:', this.existingBranchPrices);

                            // Update branch stocks and prices for simple products
                            const typeInput = document.getElementById('type');
                            const productType = typeInput?.value;
                            if (productType === 'simple') {
                                this.vendorBranches.forEach(branch => {
                                    if (this.existingBranchStocks && this.existingBranchStocks[branch.id] !== undefined) {
                                        branch.stock = this.existingBranchStocks[branch.id];
                                    }
                                    if (this.existingBranchPrices && this.existingBranchPrices[branch.id]) {
                                        branch.price = this.existingBranchPrices[branch.id].price !== null ? this.existingBranchPrices[branch.id].price : '';
                                        branch.discount = this.existingBranchPrices[branch.id].discount !== null ? this.existingBranchPrices[branch.id].discount : '';
                                        branch.discount_type = this.existingBranchPrices[branch.id].discount_type !== null ? this.existingBranchPrices[branch.id].discount_type : '';
                                    }
                                });
                                console.log('Updated branches with prices:', this.vendorBranches);
                            }

                            // Update existing variations with branch stocks
                            this.variations.forEach(variation => {
                                if (!variation.branchStocks) {
                                    variation.branchStocks = {};
                                }
                                this.vendorBranches.forEach(branch => {
                                    if (!variation.branchStocks.hasOwnProperty(branch.id)) {
                                        variation.branchStocks[branch.id] = 0;
                                    }
                                });
                            });

                            // Dispatch event to update Alpine.js
                            window.dispatchEvent(new CustomEvent('branches-loaded'));

                            // Render branch stock table
                            this.renderBranchStockTable();
                        } else {
                            console.error('Invalid response format:', data);
                            // Dispatch event even on error to update UI
                            window.dispatchEvent(new CustomEvent('branches-loaded'));
                            this.renderBranchStockTable();
                        }
                    } else {
                        const errorData = await response.json().catch(() => ({}));
                        console.error('Failed to load branches:', response.status, errorData);
                        // Dispatch event even on error to update UI
                        window.dispatchEvent(new CustomEvent('branches-loaded'));
                        this.renderBranchStockTable();
                    }
                } catch (error) {
                    console.error('Error loading branches:', error);
                    // Dispatch event even on error to update UI
                    window.dispatchEvent(new CustomEvent('branches-loaded'));
                    this.renderBranchStockTable();
                }
            },

            renderBranchStockTable() {
                const tbody = document.getElementById('branch-stock-tbody');
                const pricesTbody = document.getElementById('branch-prices-tbody');
                if (!tbody) return;

                const vendorInput = document.getElementById('vendor_id');
                const typeInput = document.getElementById('type');
                const branchStockSection = document.getElementById('branch-stock-section');
                const branchPricesSection = document.getElementById('branch-prices-section');

                // Only render if vendor is selected and type is simple
                if (!vendorInput?.value || !typeInput || typeInput.value !== 'simple') {
                    if (branchStockSection) branchStockSection.style.display = 'none';
                    if (branchPricesSection) branchPricesSection.style.display = 'none';
                    tbody.innerHTML = '';
                    if (pricesTbody) pricesTbody.innerHTML = '';
                    return;
                }

                if (branchStockSection) branchStockSection.style.display = 'block';
                if (branchPricesSection) branchPricesSection.style.display = 'block';

                if (this.vendorBranches.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="2" class="text-center text-muted">{{ __('No branches found for this vendor') }}</td></tr>';
                    if (pricesTbody) {
                        pricesTbody.innerHTML =
                            '<tr><td colspan="4" class="text-center text-muted">{{ __('No branches found for this vendor') }}</td></tr>';
                    }
                    return;
                }

                tbody.innerHTML = this.vendorBranches.map(branch => `
                    <tr>
                        <td>${this.escapeHtml(branch.name)}</td>
                        <td>
                            <input type="number" min="0" class="form-control form-control-sm"
                                name="branch_stocks[${branch.id}]" value="${branch.stock || 0}">
                        </td>
                    </tr>
                `).join('');

                if (pricesTbody) {
                    pricesTbody.innerHTML = this.vendorBranches.map(branch => `
                        <tr>
                            <td>${this.escapeHtml(branch.name)}</td>
                            <td>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                    name="branch_prices[${branch.id}][price]"
                                    value="${branch.price || ''}"
                                    placeholder="{{ __('Default Price') }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                    name="branch_prices[${branch.id}][discount]"
                                    value="${branch.discount || ''}">
                            </td>
                            <td>
                                <select class="form-select form-select-sm"
                                    name="branch_prices[${branch.id}][discount_type]">
                                    <option value="">None</option>
                                    <option value="percentage" ${branch.discount_type === 'percentage' ? 'selected' : ''}>Percentage</option>
                                    <option value="fixed" ${branch.discount_type === 'fixed' ? 'selected' : ''}>Fixed</option>
                                </select>
                            </td>
                        </tr>
                    `).join('');
                }
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },

            onTypeChange() {
                const typeInput = document.getElementById('type');
                const vendorInput = document.getElementById('vendor_id');
                const priceField = document.getElementById('price-field');
                const skuHint = document.getElementById('sku-hint');
                // Variant selection section visibility is now handled by Alpine.js x-show
                // Variations table visibility is now handled by Alpine.js x-show
                const variationsTableSection = document.getElementById('variations-table-section');
                const branchStockSection = document.getElementById('branch-stock-section');

                if (typeInput) {
                    if (typeInput.value === 'simple') {
                        if (priceField) priceField.style.display = 'block';
                        if (skuHint) skuHint.style.display = 'none';
                        if (variationsTableSection) variationsTableSection.style.display = 'none';
                        if (branchStockSection && vendorInput?.value) {
                            branchStockSection.style.display = 'block';
                            this.renderBranchStockTable();
                        }
                        this.variantOptions = {};
                        this.variations = [];
                        window.dispatchEvent(new CustomEvent('variations-updated'));
                    } else {
                        if (priceField) priceField.style.display = 'none';
                        if (skuHint) skuHint.style.display = 'block';
                        if (branchStockSection) branchStockSection.style.display = 'none';
                        const branchPricesSection = document.getElementById('branch-prices-section');
                        if (branchPricesSection) branchPricesSection.style.display = 'none';
                    }
                }
            },

            getVariantName(variant) {
                const locale = document.documentElement.lang || 'en';
                if (typeof variant.name === 'object') {
                    return variant.name[locale] || variant.name.en || Object.values(variant.name)[0];
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
                if (!window.formData.variantOptions) {
                    window.formData.variantOptions = {};
                }
                const variantIdStr = variantId.toString();
                if (!window.formData.variantOptions[variantIdStr]) {
                    window.formData.variantOptions[variantIdStr] = [];
                }

                const optionIdStr = optionId.toString();
                const currentOptions = window.formData.variantOptions[variantIdStr] || [];

                if (isChecked) {
                    // Add option if not already in array
                    if (!currentOptions.includes(optionIdStr)) {
                        window.formData.variantOptions[variantIdStr] = [...currentOptions, optionIdStr];
                    }
                } else {
                    // Remove option from array
                    window.formData.variantOptions[variantIdStr] = currentOptions.filter(id => id !==
                        optionIdStr);
                }

                // Update selected variants list
                if (!window.formData.selectedVariants) {
                    window.formData.selectedVariants = [];
                }
                const selectedOptions = window.formData.variantOptions[variantIdStr] || [];
                if (selectedOptions.length > 0 && !window.formData.selectedVariants.includes(variantIdStr)) {
                    window.formData.selectedVariants.push(variantIdStr);
                } else if (selectedOptions.length === 0) {
                    window.formData.selectedVariants = window.formData.selectedVariants.filter(id =>
                        id !== variantIdStr);
                }

                // Generate variations
                this.generateVariations();

                // Force Alpine.js to re-evaluate by triggering a custom event
                window.dispatchEvent(new CustomEvent('variations-updated'));
            },

            generateVariations() {
                if (window.formData.type !== 'variable') {
                    this.variations = [];
                    window.dispatchEvent(new CustomEvent('variations-updated'));
                    return;
                }

                // Preserve existing variations if they exist and no options are selected yet
                const hasExistingVariations = this.variations && this.variations.length > 0;

                // Check if any variants have selected options
                const hasSelectedOptions = Object.values(window.formData.variantOptions || {}).some(
                    options => options && options.length > 0);

                // Only clear variations if we have no existing variations AND no selected options
                if (!hasSelectedOptions && !hasExistingVariations) {
                    this.variations = [];
                    window.dispatchEvent(new CustomEvent('variations-updated'));
                    return;
                }

                // If we have existing variations but no selected options, keep existing variations
                if (!hasSelectedOptions && hasExistingVariations) {
                    window.dispatchEvent(new CustomEvent('variations-updated'));
                    return;
                }

                // Store existing variation data before regenerating
                const existingVariations = this.variations.map(v => ({
                    name_en: v.name_en || '',
                    name_ar: v.name_ar || '',
                    sku: v.sku || '',
                    price: v.price || parseFloat(window.formData.price) || 0,
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

                // Force Alpine.js to re-evaluate by triggering a custom event
                window.dispatchEvent(new CustomEvent('variations-updated'));
            },

            generateVariationSku(variation) {
                const productSku = window.formData.sku || '';
                const optionCodes = variation.values.map(value => {
                    // Find the option from variants data
                    const variant = this.variants.find(v => v.id == value.variant_id);
                    if (variant && variant.options) {
                        const option = variant.options.find(opt => opt.id == value
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
                if (window.formData.type === 'variable' && this.variations.length > 0) {
                    this.variations.forEach(variation => {
                        const autoSku = this.generateVariationSku(variation);
                        // Only update if SKU is empty or matches the old auto-generated pattern
                        if (!variation.sku || variation.sku === '' || variation.sku ===
                            autoSku || variation.sku.startsWith((window.formData.sku || '') +
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
                    const variantIdStr = v.id.toString();
                    const selectedOptions = window.formData.variantOptions[variantIdStr] || [];
                    return selectedOptions.length > 0;
                });

                if (variantsWithOptions.length === 0) return [];

                // Build option arrays with only selected options
                const optionArrays = variantsWithOptions.map(variant => {
                    const variantIdStr = variant.id.toString();
                    const selectedOptionIds = (window.formData.variantOptions[variantIdStr] || []).map(id => id
                        .toString());
                    return (variant.options || []).filter(opt => selectedOptionIds.includes(
                        opt.id.toString()));
                });

                const generateCombinations = (index, current) => {
                    if (index === optionArrays.length) {
                        const locale = document.documentElement.lang || 'en';

                        // Generate SKU from product SKU and option codes
                        const productSku = window.formData.sku || '';
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
                            price: parseFloat(window.formData.price) || 0,
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
                                preview: e.target.result,
                                id: null // Mark as new image
                            });
                            // Dispatch event to update Alpine.js
                            window.dispatchEvent(new CustomEvent('images-changed'));
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
            deleteProductImage(imageIdOrIndex, isExistingImage = false) {
                if (isExistingImage) {
                    // Remove existing image by ID from selectedImages
                    // This ensures it won't be included in existing_images[] when form is submitted
                    this.selectedImages = this.selectedImages.filter(image => image.id !== imageIdOrIndex);
                } else {
                    // Remove new image by index
                    // Find the index of the new image (images without id)
                    const newImages = this.selectedImages.filter(img => !img.id);
                    if (newImages[imageIdOrIndex] !== undefined) {
                        const imageToRemove = newImages[imageIdOrIndex];
                        const actualIndex = this.selectedImages.findIndex(img =>
                            !img.id && img.preview === imageToRemove.preview
                        );
                        if (actualIndex !== -1) {
                            this.selectedImages.splice(actualIndex, 1);
                        }
                    } else {
                        // Fallback: remove by direct index if provided
                        if (imageIdOrIndex >= 0 && imageIdOrIndex < this.selectedImages.length && !this.selectedImages[imageIdOrIndex].id) {
                            this.selectedImages.splice(imageIdOrIndex, 1);
                        }
                    }
                }
                this.updateFileInput();
                // Dispatch event to update Alpine.js
                window.dispatchEvent(new CustomEvent('images-changed'));
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

                // Ensure existing_images hidden inputs are created for images that should be kept
                const form = document.getElementById('productForm');
                if (form) {
                    // Remove all existing hidden inputs first
                    form.querySelectorAll('input[name="existing_images[]"]').forEach(input => {
                        input.remove();
                    });

                    // Add hidden inputs for images that should be kept (have an id and are still in selectedImages)
                    this.selectedImages.forEach(image => {
                        if (image.id) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'existing_images[]';
                            hiddenInput.value = image.id;
                            form.appendChild(hiddenInput);
                        }
                    });

                    // Ensure variation values hidden inputs are properly set
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
                                    variantIdInput.name =
                                        `variations[${index}][values][${vIndex}][variant_id]`;
                                    variantIdInput.value = value.variant_id;
                                    form.appendChild(variantIdInput);

                                    const optionIdInput = document.createElement('input');
                                    optionIdInput.type = 'hidden';
                                    optionIdInput.name =
                                        `variations[${index}][values][${vIndex}][option_id]`;
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
                        this.updateStepDisplay();
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
                    this.updateStepDisplay();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            },

            goToStep(step) {
                if (step <= this.currentStep || step === this.currentStep + 1) {
                    this.currentStep = step;
                    this.updateStepDisplay();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            },

            validateCurrentStep() {
                if (this.currentStep === 1) {
                    const vendorInput = document.getElementById('vendor_id');
                    const nameEn = document.getElementById('name_en');
                    const nameAr = document.getElementById('name_ar');
                    return vendorInput && vendorInput.value && nameEn && nameEn.value && nameAr && nameAr.value;
                } else if (this.currentStep === 2) {
                    const typeInput = document.getElementById('type');
                    if (typeInput && typeInput.value === 'simple') {
                        const priceInput = document.getElementById('price');
                        return priceInput && priceInput.value && parseFloat(priceInput.value) >= 0;
                    }
                    return true;
                }
                return true;
            },

            handleThumbnailChange(event) {
                const file = event.target.files[0];
                if (file) {
                    this.thumbnailFile = file;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewDiv = document.getElementById('thumbnailPreview');
                        const previewImg = document.getElementById('thumbnailPreviewImg');
                        if (previewDiv && previewImg) {
                            previewImg.src = e.target.result;
                            previewDiv.style.display = 'block';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            },

            removeThumbnail() {
                this.thumbnailFile = null;
                const thumbnailInput = document.getElementById('thumbnail');
                const previewDiv = document.getElementById('thumbnailPreview');
                if (thumbnailInput) thumbnailInput.value = '';
                if (previewDiv) previewDiv.style.display = 'none';
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
                            this.updateImagesDisplay();
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },

            removeImage(index) {
                this.selectedImages.splice(index, 1);
                this.updateImagesDisplay();
            },

            updateImagesDisplay() {
                const container = document.getElementById('selected-images-container');
                const grid = document.getElementById('selected-images-grid');
                if (!container || !grid) return;

                if (this.selectedImages.length === 0) {
                    container.style.display = 'none';
                    return;
                }

                container.style.display = 'block';
                grid.innerHTML = this.selectedImages.map((image, index) => `
                    <div class="col-md-3" data-image-id="${image.id || ''}">
                        <div class="position-relative">
                            <img src="${image.preview}" alt="Preview"
                                class="img-thumbnail w-100"
                                style="height: 150px; object-fit: cover;">
                            <button type="button"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                onclick="productWizard.removeImage(${index})">
                                <i class="bi bi-x"></i>
                            </button>
                            ${image.id ? `<input type="hidden" name="existing_images[]" value="${image.id}">` : ''}
                        </div>
                    </div>
                `).join('');
            },

            handleSubmit(event) {
                this.ensureFilesBeforeSubmit();
                return true;
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

                // Ensure existing_images hidden inputs are created for images that should be kept
                const form = document.getElementById('productForm');
                if (form) {
                    // Remove all existing hidden inputs first
                    form.querySelectorAll('input[name="existing_images[]"]').forEach(input => input.remove());

                    // Add hidden inputs for images that should be kept (have an id and are still in selectedImages)
                    this.selectedImages.forEach(image => {
                        if (image.id) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'existing_images[]';
                            hiddenInput.value = image.id;
                            form.appendChild(hiddenInput);
                        }
                    });
                }
            }
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            productWizard.init();
        });
    </script>
@endpush
