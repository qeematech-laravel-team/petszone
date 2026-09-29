<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Products\CreateRequest;
use App\Http\Requests\Vendor\Products\UpdateRequest;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\BranchService;
use App\Services\CategoryService;
use App\Services\InventoryAlertService;
use App\Services\ProductService;
use App\Services\VariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected ProductService $service;

    protected CategoryService $categoryService;

    protected VariantService $variantService;

    protected BranchService $branchService;

    public function __construct(
        ProductService $service,
        CategoryService $categoryService,
        VariantService $variantService,
        BranchService $branchService,
        protected InventoryAlertService $inventoryAlertService
    ) {
        $this->service = $service;
        $this->categoryService = $categoryService;
        $this->variantService = $variantService;
        $this->branchService = $branchService;
    }

    /**
     * Get the vendor for the authenticated user
     */
    protected function getVendor(): ?Vendor
    {
        return Auth::user()->vendor();

    }

    /**
     * Display a listing of the vendor's products.
     */
    public function index(): View|JsonResponse
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Check if user is a branch user
        $branch = currentBranch();

        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'featured' => request()->get('featured', ''),
            'approved' => request()->get('approved', ''),
            'type' => request()->get('type', ''),
            'vendor_id' => $vendor->id, // Force filter by vendor
            'category_id' => request()->get('category_id', ''),
            'stock' => request()->get('stock', ''),
            'branch_id' => $branch?->id, // Filter by branch if user is branch user
            'min_price' => request()->get('min_price', ''),
            'max_price' => request()->get('max_price', ''),
            'is_new' => request()->get('is_new', ''),
            'is_bookable' => request()->get('is_bookable', ''),
            'sort' => request()->get('sort', ''),
        ];

        $products = $this->service->getPaginatedProducts(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('vendor.products.partials.table', compact('products'))->render(),
                'pagination' => view('vendor.products.partials.pagination', compact('products'))->render(),
            ]);
        }

        $categories = $this->categoryService->getCategoryTree();

        $can_add_products = setting('profit_type') == 'subscription' ? $vendor->plan->max_products_count > $vendor->products()->active()->count() : true;

        return view('vendor.products.index', compact('products', 'filters', 'categories', 'vendor', 'can_add_products', 'branch'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): RedirectResponse|View
    {
        // Check if user can create products (only owners, not branch users)
        if (! canCreateProducts()) {
            abort(403, __('Only vendor owners can create products. Branch users cannot create products.'));
        }

        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }
        $can_add_products = setting('profit_type') == 'subscription' ? $vendor->plan->max_products_count > $vendor->products()->active()->count() : true;

        if (! $can_add_products) {
            return redirect()->route('vendor.products.index')
                ->with('error', __('You have reached the maximum number of products. Please delete or deactivate some products to add a new one.'));
        }

        $categories = $this->categoryService->getCategoryTree();
        $variants = $this->variantService->getActiveVariants()->load('options');
        $allProducts = $this->service->getProductsByVendor($vendor->id); // Only vendor's own products for related
        $can_feature_products = setting('profit_type') == 'subscription' ? $vendor->plan->can_feature_products : true;

        return view('vendor.products.create', compact('categories', 'variants', 'allProducts', 'vendor', 'can_feature_products'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        // Check if user can create products (only owners, not branch users)
        if (! canCreateProducts()) {
            return redirect()->back()
                ->with('error', __('Only vendor owners can create products. Branch users cannot create products.'));
        }

        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            // Force vendor_id to be the authenticated vendor's ID
            $request->merge(['vendor_id' => $vendor->id]);

            $this->service->createProduct($request);

            return redirect()->route('vendor.products.index')
                ->with('success', __('Product created successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to create product: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Ensure the product belongs to this vendor
        if ($product->vendor_id !== $vendor->id) {
            abort(403, __('You do not have permission to view this product.'));
        }

        $product = $this->service->getProductById($product->id);

        // Get vendor branches for owner users to show branch stock breakdown
        $vendorBranches = null;
        $currentVendorUser = currentVendorUser();
        if ($currentVendorUser && $currentVendorUser->user_type === 'owner') {
            $vendorBranches = $this->branchService->getBranchesByVendor($vendor->id);
        }

        return view('vendor.products.show', compact('product', 'vendor', 'vendorBranches'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        // Check if user can create/edit products (only owners, not branch users)
        if (! canCreateProducts()) {
            abort(403, __('Only vendor owners can edit products. Branch users cannot edit products.'));
        }

        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Ensure the product belongs to this vendor
        if ($product->vendor_id !== $vendor->id) {
            abort(403, __('You do not have permission to edit this product.'));
        }

        $product = $this->service->getProductById($product->id);
        $categories = $this->categoryService->getCategoryTree();
        $variants = $this->variantService->getActiveVariants()->load('options');
        $allProducts = $this->service->getProductsByVendor($vendor->id); // Only vendor's own products for related products
        $vendorBranches = $this->branchService->getBranchesByVendor($vendor->id); // Get vendor branches

        // Load product relationships for editing
        $product->load([
            'variants.values.variantOption',
            'images',
            'categories',
            'relations',
            'branchProductStocks',
            'variants.branchVariantStocks',
        ]);

        // Prepare existing product data for JavaScript (same as admin)
        $existingVariations = [];
        $selectedVariantOptions = [];
        if ($product->type === 'variable' && $product->variants && $product->variants->count() > 0) {
            foreach ($product->variants as $variant) {
                $optionIds = [];
                $variantId = null;

                // Get variant option IDs from variant values
                if ($variant->values && $variant->values->count() > 0) {
                    foreach ($variant->values as $value) {
                        // Ensure variantOption is loaded
                        if (! $value->relationLoaded('variantOption') && $value->variant_option_id) {
                            $value->load('variantOption');
                        }

                        if ($value->variantOption) {
                            $variantId = $value->variantOption->variant_id;
                            $optionIds[] = $value->variant_option_id;
                        }
                    }
                }

                if (! empty($optionIds) && $variantId) {
                    if (! isset($selectedVariantOptions[$variantId])) {
                        $selectedVariantOptions[$variantId] = [];
                    }
                    $selectedVariantOptions[$variantId] = array_unique(array_merge($selectedVariantOptions[$variantId], $optionIds));
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
                        if (! $value->relationLoaded('variantOption') && $value->variant_option_id) {
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

        $can_feature_products = setting('profit_type') == 'subscription' ? $vendor->plan->can_feature_products : true;
        
        return view('vendor.products.edit', compact(
            'product',
            'categories',
            'variants',
            'allProducts',
            'vendor',
            'existingVariations',
            'selectedVariantOptions',
            'branchStocksData',
            'relatedProductsIds',
            'crossSellProductsIds',
            'upsellProductsIds',
            'can_feature_products',
            'vendorBranches'
        ));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateRequest $request, Product $product): RedirectResponse
    {
        // Check if user can create/edit products (only owners, not branch users)
        if (! canCreateProducts()) {
            return redirect()->back()
                ->with('error', __('Only vendor owners can edit products. Branch users cannot edit products.'));
        }

        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                abort(403, __('You do not have permission to update this product.'));
            }

            // Force vendor_id to be the authenticated vendor's ID
            $request->merge(['vendor_id' => $vendor->id]);

            $can_feature_products = setting('profit_type') == 'subscription' ? $vendor->plan->can_feature_products : true;
            if (! $can_feature_products && $request->boolean('is_featured', $product->is_featured)) {
                return redirect()->back()
                    ->with('error', __('You are not allowed to feature products. Please subscribe to a plan that allows you to feature products.'));
            }
            $can_add_products = setting('profit_type') == 'subscription' ? $vendor->plan->max_products_count > $vendor->products()->active()->count() : true;
            if (! $can_add_products && $request->boolean('is_active', $product->is_active)) {
                return redirect()->back()
                    ->with('error', __('You have reached the maximum number of products. Please delete or deactivate some products to add a new one.'));
            }

            $this->service->updateProduct($request, $product);

            return redirect()->route('vendor.products.index')
                ->with('success', __('Product updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to update product: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        // Check if user can create/edit products (only owners, not branch users)
        if (! canCreateProducts()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Only vendor owners can delete products. Branch users cannot delete products.'),
                ], 403);
            }

            return redirect()->back()
                ->with('error', __('Only vendor owners can delete products. Branch users cannot delete products.'));
        }

        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                abort(403, __('You do not have permission to delete this product.'));
            }

            $this->service->deleteProduct($product);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product deleted successfully.'),
                ]);
            }

            return redirect()->route('vendor.products.index')
                ->with('success', __('Product deleted successfully.'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete product: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()->back()
                ->with('error', __('Failed to delete product: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleActive(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to update this product.'),
                ], 403);
            }

            $product = $this->service->toggleActive($product);

            return response()->json([
                'success' => true,
                'message' => __('Product status updated successfully.'),
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update product status: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Toggle product featured status.
     */
    public function toggleFeatured(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to update this product.'),
                ], 403);
            }

            $product = $this->service->toggleFeatured($product);

            return response()->json([
                'success' => true,
                'message' => __('Product featured status updated successfully.'),
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update product featured status: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Get branches by vendor ID (AJAX) - for vendor's own branches
     */
    public function getBranchesByVendor(): JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            $branches = $this->branchService->getBranchesByVendor($vendor->id);

            return response()->json([
                'success' => true,
                'branches' => $branches->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to load branches: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Update branch stock for a product
     */
    public function updateBranchStock(Product $product, Request $request): JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to update this product.'),
                ], 403);
            }

            // Check if user is branch user
            $branch = currentBranch();
            if (! $branch) {
                return response()->json([
                    'success' => false,
                    'message' => __('Branch not found.'),
                ], 404);
            }

            // Check if branch user can edit stock
            if (! vendorSetting('allow_branch_user_to_edit_stock', false)) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to edit stock.'),
                ], 403);
            }

            // Validate request
            $request->validate([
                'branch_id' => ['required', 'exists:branches,id'],
                'type' => ['required', 'in:simple,variable'],
            ]);

            if ($request->type === 'simple') {
                $request->validate([
                    'quantity' => ['required', 'integer', 'min:0'],
                ]);

                // Update or create branch product stock
                $stock = \App\Models\BranchProductStock::updateOrCreate(
                    [
                        'branch_id' => $request->branch_id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => $request->quantity,
                    ]
                );

                $stock->refresh();
                $this->inventoryAlertService->checkSimpleStock($stock, $product);
            } else {
                $request->validate([
                    'variants' => ['required', 'array'],
                    'variants.*' => ['required', 'integer', 'min:0'],
                ]);

                // Update or create branch variant stocks
                foreach ($request->variants as $variantId => $quantity) {
                    $stock = \App\Models\BranchProductVariantStock::updateOrCreate(
                        [
                            'branch_id' => $request->branch_id,
                            'product_variant_id' => $variantId,
                        ],
                        [
                            'quantity' => $quantity,
                        ]
                    );

                    $variant = $product->variants()->find($variantId);
                    if ($variant) {
                        $stock->refresh();
                        $this->inventoryAlertService->checkVariantStock($stock, $variant);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => __('Stock updated successfully.'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update stock: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Update branch price for a product
     */
    public function updateBranchPrice(Product $product, Request $request): JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            // Ensure the product belongs to this vendor
            if ($product->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to update this product.'),
                ], 403);
            }

            // Check if user is branch user
            $branch = currentBranch();
            if (! $branch) {
                return response()->json([
                    'success' => false,
                    'message' => __('Branch not found.'),
                ], 404);
            }

            // Check if branch user can edit price
            if (! vendorSetting('allow_branch_user_to_edit_price', false)) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to edit price.'),
                ], 403);
            }

            // Validate request
            $request->validate([
                'branch_id' => ['required', 'exists:branches,id'],
                'type' => ['required', 'in:simple,variable'],
            ]);

            if ($request->type === 'simple') {
                $request->validate([
                    'price' => ['nullable', 'numeric', 'min:0'],
                    'discount' => ['nullable', 'numeric', 'min:0'],
                    'discount_type' => ['nullable', 'in:percentage,fixed'],
                ]);

                // Update or create branch product stock with price
                $stock = \App\Models\BranchProductStock::updateOrCreate(
                    [
                        'branch_id' => $request->branch_id,
                        'product_id' => $product->id,
                    ],
                    [
                        'price' => $request->price && $request->price !== '' ? $request->price : null,
                        'discount' => $request->discount && $request->discount !== '' ? $request->discount : null,
                        'discount_type' => $request->discount_type ?? null,
                    ]
                );
            } else {
                $request->validate([
                    'variants' => ['required', 'array'],
                    'variants.*.price' => ['nullable', 'numeric', 'min:0'],
                    'variants.*.discount' => ['nullable', 'numeric', 'min:0'],
                    'variants.*.discount_type' => ['nullable', 'in:percentage,fixed'],
                ]);

                // Update or create branch variant stocks with prices
                foreach ($request->variants as $variantId => $priceData) {
                    $stock = \App\Models\BranchProductVariantStock::updateOrCreate(
                        [
                            'branch_id' => $request->branch_id,
                            'product_variant_id' => $variantId,
                        ],
                        [
                            'price' => isset($priceData['price']) && $priceData['price'] !== '' ? $priceData['price'] : null,
                            'discount' => isset($priceData['discount']) && $priceData['discount'] !== '' ? $priceData['discount'] : null,
                            'discount_type' => $priceData['discount_type'] ?? null,
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => __('Price updated successfully.'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while updating price.'),
            ], 500);
        }
    }
}
