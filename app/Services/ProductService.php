<?php

namespace App\Services;

use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Product;
use App\Models\ProductRelation;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products
     */
    public function getAllProducts(): Collection
    {
        return $this->productRepository->getAllProducts();
    }

    /**
     * Get paginated products
     */
    public function getPaginatedProducts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->getPaginatedProducts($perPage, $filters);
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->getProductById($id);
    }

    /**
     * Get an active product by ID for the customer API.
     */
    public function getApiProductById(int $id): ?Product
    {
        return $this->productRepository->getApiProductById($id);
    }

    /**
     * Get product by slug
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->getProductBySlug($slug);
    }

    /**
     * Get active products
     */
    public function getActiveProducts(): Collection
    {
        return $this->productRepository->getActiveProducts();
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(): Collection
    {
        return $this->productRepository->getFeaturedProducts();
    }

    /**
     * Get new products
     */
    public function getNewProducts(int $limit = 10): Collection
    {
        return $this->productRepository->getNewProducts($limit);
    }

    /**
     * Get products by vendor
     */
    public function getProductsByVendor(int $vendorId): Collection
    {
        return $this->productRepository->getProductsByVendor($vendorId);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->productRepository->getProductsByCategory($categoryId);
    }

    /**
     * Create a new product
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function createProduct($request): Product
    {
        DB::beginTransaction();
        try {
            // Check if user is vendor - vendors cannot approve products
            $isVendor = Auth::user()->hasRole('vendor');

            $data = [
                'vendor_id' => $request->vendor_id ?? (Auth::user()->ownedVendor->id ?? null),
                'type' => $request->type ?? 'simple',
                'name' => $request->name,
                'description' => $request->description ?? [],
                'sku' => $request->sku ?? $this->generateUniqueSku(),
                'slug' => $request->slug ?? $this->generateSlug($request->name['en'] ?? $request->name['ar'] ?? 'product'),
                'price' => $request->price ?? 0,
                'discount' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'percentage',
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'is_new' => $request->boolean('is_new', false),
                'is_approved' => $isVendor ? false : $request->boolean('is_approved', false), // Vendors cannot approve products
                'is_bookable' => $request->boolean('is_bookable', false),
            ];

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
            }

            $product = $this->productRepository->create($data);

            // Sync categories
            if ($request->has('categories') && is_array($request->categories)) {
                $this->productRepository->syncCategories($product, $request->categories);
            }

            // Handle product images
            if ($request->hasFile('images')) {
                $this->storeProductImages($product, $request->file('images'));
            }

            // Handle branch stocks and prices for simple products
            if ($product->type === 'simple') {
                $branchStocks = $request->input('branch_stocks', []);
                $branchPrices = $request->input('branch_prices', []);
                // Always sync branch stocks, even if empty (will delete all existing)
                $this->syncBranchStocks($product, $branchStocks, $branchPrices);
            }

            // Handle variations for variable products
            if ($product->type === 'variable' && $request->has('variations')) {
                $this->syncProductVariations($product, $request->variations);
            }

            // Handle related products
            $this->syncRelatedProducts($product, $request);

            DB::commit();

            return $product->load(['categories', 'images', 'variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a product
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function updateProduct($request, Product $product): Product
    {

        DB::beginTransaction();
        try {
            // Check if user is vendor - vendors cannot approve products
            $isVendor = Auth::user()->hasRole('vendor');

            $data = [
                'vendor_id' => $request->vendor_id ?? $product->vendor_id,
                'type' => $request->type ?? $product->type,
                'name' => $request->name ?? $product->name,
                'description' => $request->description ?? $product->description,
                'sku' => $request->sku ?? $product->sku,
                'slug' => $request->slug ?? $product->slug,
                'price' => $request->price ?? $product->price,
                'discount' => $request->discount ?? $product->discount,
                'discount_type' => $request->discount_type ?? $product->discount_type,
                'is_active' => $request->boolean('is_active', $product->is_active),
                'is_featured' => $request->boolean('is_featured', $product->is_featured),
                'is_new' => $request->boolean('is_new', $product->is_new),
                'is_approved' => $isVendor ? $product->is_approved : $request->boolean('is_approved', $product->is_approved), // Vendors cannot change approval status
                'is_bookable' => $request->boolean('is_bookable', $product->is_bookable),
            ];

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                $oldThumbnail = $product->getOriginal('thumbnail');
                if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                    Storage::disk('public')->delete($oldThumbnail);
                }
                $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
            }

            // Update slug if name changed
            if ($request->has('name') && $request->name !== $product->name) {
                $nameEn = $request->name['en'] ?? $request->name['ar'] ?? 'product';
                $data['slug'] = $this->generateSlug($nameEn, $product->id);
            }

            $this->productRepository->update($product, $data);
            $product->refresh();

            // Sync categories
            if ($request->has('categories') && is_array($request->categories)) {
                $this->productRepository->syncCategories($product, $request->categories);
            }

            // Handle existing images deletion - delete images that are NOT in the existing_images array
            $existingImageIds = $product->images->pluck('id')->toArray();
            $imagesToKeep = $request->input('existing_images', []);
            if (! empty($existingImageIds)) {
                $imagesToDelete = array_diff($existingImageIds, $imagesToKeep);
                if (! empty($imagesToDelete)) {
                    $this->deleteProductImages($product, array_values($imagesToDelete));
                }
            }

            // Handle product images
            if ($request->hasFile('images')) {
                $this->storeProductImages($product, $request->file('images'));
            }
            // Handle branch stocks and prices for simple products (check request type, not product type)
            $productType = $request->input('type', $product->type);
            if ($productType === 'simple') {
                $branchStocks = $request->input('branch_stocks', []);
                $branchPrices = $request->input('branch_prices', []);
                // Always sync branch stocks, even if empty (will delete all existing)
                $this->syncBranchStocks($product, $branchStocks, $branchPrices);
            }

            // Handle variations for variable products (check request type, not product type)
            if ($productType === 'variable' && $request->has('variations')) {
                $this->syncProductVariations($product, $request->variations);
            }

            // Handle related products
            $this->syncRelatedProducts($product, $request);

            DB::commit();

            return $product->load(['categories', 'images', 'variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Product $product): bool
    {
        DB::beginTransaction();
        try {
            // Delete thumbnail if exists
            $oldThumbnail = $product->getOriginal('thumbnail');
            if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                Storage::disk('public')->delete($oldThumbnail);
            }

            // Delete product images
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            $deleted = $this->productRepository->delete($product);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle product active status
     */
    public function toggleActive(Product $product): Product
    {
        $product->update(['is_active' => ! $product->is_active]);

        return $product->fresh();
    }

    /**
     * Toggle product featured status
     */
    public function toggleFeatured(Product $product): Product
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return $product->fresh();
    }

    /**
     * Toggle product approved status
     */
    public function toggleApproved(Product $product): Product
    {
        $product->update(['is_approved' => ! $product->is_approved]);

        return $product->fresh();
    }

    /**
     * Store product images
     */
    protected function storeProductImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            $product->images()->create(['path' => $path]);
        }
    }

    /**
     * Generate unique SKU
     */
    protected function generateUniqueSku(): string
    {
        $sku = 'PRD-'.strtoupper(Str::random(8));
        $counter = 1;

        while (Product::where('sku', $sku)->exists()) {
            $sku = 'PRD-'.strtoupper(Str::random(8)).'-'.$counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        $query = Product::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;

            $query = Product::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Sync branch stocks and prices for a product
     */
    protected function syncBranchStocks(Product $product, array $branchStocks, array $branchPrices = []): void
    {
        // Delete existing branch stocks
        BranchProductStock::where('product_id', $product->id)->delete();

        // Create new branch stocks (including 0 quantities) with prices
        foreach ($branchStocks as $branchId => $quantity) {
            $priceData = $branchPrices[$branchId] ?? [];

            BranchProductStock::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'quantity' => (int) ($quantity ?? 0),
                'price' => isset($priceData['price']) && $priceData['price'] !== '' ? $priceData['price'] : null,
                'discount' => isset($priceData['discount']) && $priceData['discount'] !== '' ? $priceData['discount'] : null,
                'discount_type' => $priceData['discount_type'] ?? null,
            ]);
        }
    }

    /**
     * Sync product variations
     */
    protected function syncProductVariations(Product $product, array $variations): void
    {
        // Refresh product with variants and their values loaded
        $product->load('variants.values');

        // Get existing variation IDs to track which ones to keep
        $existingVariationIds = $product->variants->pluck('id')->toArray();
        $updatedVariationIds = [];

        foreach ($variations as $variationData) {
            $variation = null;

            // First, try to find by SKU if provided (most reliable)
            if (! empty($variationData['sku'])) {
                $variation = $product->variants->firstWhere('sku', $variationData['sku']);
            }

            // If not found by SKU, try to find by matching values
            if (! $variation && isset($variationData['values']) && is_array($variationData['values'])) {
                $variation = $this->findExistingVariation($product, $variationData['values']);
            }

            if ($variation) {
                // Update existing variation
                $updateData = [
                    'name' => $variationData['name'] ?? $variation->name,
                    'sku' => $variationData['sku'] ?? $variation->sku,
                    'price' => $variationData['price'] ?? $variation->price,
                ];

                // Handle thumbnail
                if (isset($variationData['thumbnail']) && $variationData['thumbnail']) {
                    $oldThumbnail = $variation->getOriginal('thumbnail');
                    if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                        Storage::disk('public')->delete($oldThumbnail);
                    }
                    $updateData['thumbnail'] = $variationData['thumbnail']->store('products/variants', 'public');
                }

                $variation->update($updateData);

                // Sync variation values - delete existing and create new ones
                if (isset($variationData['values']) && is_array($variationData['values'])) {
                    // Delete existing values
                    $variation->values()->delete();

                    // Create new values
                    foreach ($variationData['values'] as $value) {
                        if (isset($value['option_id'])) {
                            ProductVariantValue::create([
                                'product_variant_id' => $variation->id,
                                'variant_option_id' => $value['option_id'],
                            ]);
                        }
                    }
                }

                $updatedVariationIds[] = $variation->id;
            } else {
                // Create new variation
                $createData = [
                    'slug' => $variationData['slug'] ?? Str::slug($variationData['name']['en'] ?? 'variant'),
                    'product_id' => $product->id,
                    'name' => $variationData['name'] ?? [],
                    'sku' => $variationData['sku'] ?? '',
                    'price' => $variationData['price'] ?? $product->price,
                    'is_active' => true,
                ];

                // Handle thumbnail
                if (isset($variationData['thumbnail']) && $variationData['thumbnail']) {
                    $createData['thumbnail'] = $variationData['thumbnail']->store('products/variants', 'public');
                }

                $variation = ProductVariant::create($createData);

                // Create variation values
                if (isset($variationData['values']) && is_array($variationData['values'])) {
                    foreach ($variationData['values'] as $value) {
                        ProductVariantValue::create([
                            'product_variant_id' => $variation->id,
                            'variant_option_id' => $value['option_id'],
                        ]);
                    }
                }

                $updatedVariationIds[] = $variation->id;
            }

            // Sync branch stocks and prices for this variation
            if (isset($variationData['branch_stocks']) && is_array($variationData['branch_stocks'])) {
                $branchPrices = $variationData['branch_prices'] ?? [];
                $this->syncVariantBranchStocks($variation, $variationData['branch_stocks'], $branchPrices);
            }
        }

        // Delete variations that were not updated
        $variationsToDelete = array_diff($existingVariationIds, $updatedVariationIds);
        if (! empty($variationsToDelete)) {
            ProductVariant::whereIn('id', $variationsToDelete)->delete();
        }
    }

    /**
     * Find existing variation by values
     */
    protected function findExistingVariation(Product $product, array $values): ?ProductVariant
    {
        $optionIds = array_column($values, 'option_id');
        sort($optionIds);

        // Ensure variants have values loaded
        foreach ($product->variants as $variant) {
            // Load values if not already loaded
            if (! $variant->relationLoaded('values')) {
                $variant->load('values');
            }

            $variantOptionIds = $variant->values->pluck('variant_option_id')->toArray();
            sort($variantOptionIds);

            if ($optionIds === $variantOptionIds) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * Sync branch stocks and prices for a variation
     */
    protected function syncVariantBranchStocks(ProductVariant $variation, array $branchStocks, array $branchPrices = []): void
    {
        // Delete existing branch stocks for this variation
        BranchProductVariantStock::where('product_variant_id', $variation->id)->delete();

        // Create new branch stocks (including 0 quantities) with prices
        foreach ($branchStocks as $branchId => $quantity) {
            $priceData = $branchPrices[$branchId] ?? [];

            BranchProductVariantStock::create([
                'product_variant_id' => $variation->id,
                'branch_id' => $branchId,
                'quantity' => (int) ($quantity ?? 0),
                'price' => isset($priceData['price']) && $priceData['price'] !== '' ? $priceData['price'] : null,
                'discount' => isset($priceData['discount']) && $priceData['discount'] !== '' ? $priceData['discount'] : null,
                'discount_type' => $priceData['discount_type'] ?? null,
            ]);
        }
    }

    /**
     * Sync related products
     */
    protected function syncRelatedProducts(Product $product, $request): void
    {
        // Delete existing relations
        ProductRelation::where('product_id', $product->id)->delete();

        // Create related products
        if ($request->has('related_products') && is_array($request->related_products)) {
            foreach ($request->related_products as $relatedProductId) {
                ProductRelation::create([
                    'product_id' => $product->id,
                    'related_product_id' => $relatedProductId,
                    'type' => 'related',
                ]);
            }
        }

        // Create cross-sell products
        if ($request->has('cross_sell_products') && is_array($request->cross_sell_products)) {
            foreach ($request->cross_sell_products as $crossSellProductId) {
                ProductRelation::create([
                    'product_id' => $product->id,
                    'related_product_id' => $crossSellProductId,
                    'type' => 'cross_sell',
                ]);
            }
        }

        // Create upsell products
        if ($request->has('upsell_products') && is_array($request->upsell_products)) {
            foreach ($request->upsell_products as $upsellProductId) {
                ProductRelation::create([
                    'product_id' => $product->id,
                    'related_product_id' => $upsellProductId,
                    'type' => 'upsell',
                ]);
            }
        }
    }

    /**
     * Delete product images
     */
    protected function deleteProductImages(Product $product, array $imageIds): void
    {
        $imagesToDelete = $product->images()->whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }
    }
}
