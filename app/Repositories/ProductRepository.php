<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    /**
     * Get all products
     */
    public function getAllProducts(): Collection
    {
        return Product::constrainFavoriteExists(
            Product::query()->with(Product::apiListRelations())
        )->get();
    }

    /**
     * Get paginated products
     */
    public function getPaginatedProducts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        // Eager load relationships used by ProductResource
        $with = Product::apiListRelations();

        // If filtering by branch, we can optimize eager loading
        $branchId = $filters['branch_id'] ?? null;
        if ($branchId) {
            // Only load branch stocks for the specific branch
            $with = [
                'vendor' => fn ($query) => Vendor::constrainVisibleRatingAggregates($query),
                'categories',
                'images',
                'ratings.user',
                'variants.values.variantOption.variant',
                'branchProductStocks' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
                'variants.branchVariantStocks' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
            ];
        }

        $query = Product::constrainFavoriteExists(Product::query()->with($with));

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] != '') {
            $query->where('is_active', $filters['status'] == 'active');
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured'] != '') {
            $query->where('is_featured', $filters['featured'] == '1');
        }

        // Apply approved filter
        if (isset($filters['approved']) && $filters['approved'] != '') {
            $query->where('is_approved', $filters['approved'] == '1');
        }

        // Apply type filter
        if (isset($filters['type']) && $filters['type'] != '') {
            $query->where('type', $filters['type']);
        }

        // Apply vendor filter
        if (isset($filters['vendor_id']) && $filters['vendor_id'] != '') {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Apply category filter
        if (isset($filters['category_id']) && $filters['category_id'] != '') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        // Apply branch filter - only show products with stock in specific branch
        if (isset($filters['branch_id']) && $filters['branch_id'] != '') {
            $branchId = $filters['branch_id'];
            $query->where(function ($q) use ($branchId) {
                // Products with stock in this branch (simple products)
                $q->whereHas('branchProductStocks', function ($subQ) use ($branchId) {
                    $subQ->where('branch_id', $branchId);
                })
                // Or products with variants that have stock in this branch
                    ->orWhereHas('variants.branchVariantStocks', function ($subQ) use ($branchId) {
                        $subQ->where('branch_id', $branchId);
                    });
            });
        }

        // Apply stock filter (based on branch stocks)
        if (isset($filters['stock']) && $filters['stock'] != '') {
            $branchId = $filters['branch_id'] ?? null;

            if ($filters['stock'] == 'in_stock') {
                $query->where(function ($q) use ($branchId) {
                    if ($branchId) {
                        // Filter by specific branch
                        $q->whereHas('branchProductStocks', function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)
                                ->where('quantity', '>', 0);
                        })->orWhereHas('variants.branchVariantStocks', function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)
                                ->where('quantity', '>', 0);
                        });
                    } else {
                        // Filter across all branches
                        $q->whereHas('branchProductStocks', function ($subQ) {
                            $subQ->where('quantity', '>', 0);
                        })->orWhereHas('variants.branchVariantStocks', function ($subQ) {
                            $subQ->where('quantity', '>', 0);
                        });
                    }
                });
            } elseif ($filters['stock'] == 'out_of_stock') {
                $query->where(function ($q) use ($branchId) {
                    if ($branchId) {
                        // Filter by specific branch
                        $q->whereDoesntHave('branchProductStocks', function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)
                                ->where('quantity', '>', 0);
                        })->whereDoesntHave('variants.branchVariantStocks', function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)
                                ->where('quantity', '>', 0);
                        });
                    } else {
                        // Filter across all branches
                        $q->whereDoesntHave('branchProductStocks', function ($subQ) {
                            $subQ->where('quantity', '>', 0);
                        })->whereDoesntHave('variants.branchVariantStocks', function ($subQ) {
                            $subQ->where('quantity', '>', 0);
                        });
                    }
                });
            }
        }

        // Price range
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Flags
        if (isset($filters['is_new']) && $filters['is_new'] !== '') {
            $query->where('is_new', (bool) $filters['is_new']);
        }

        if (isset($filters['is_bookable']) && $filters['is_bookable'] !== '') {
            $query->where('is_bookable', (bool) $filters['is_bookable']);
        }

        // Sorting
        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): ?Product
    {
        $query = Product::constrainFavoriteExists(
            Product::query()->with([
                ...Product::apiListRelations(),
                'relatedProducts.relatedProduct' => fn ($relatedQuery) => $relatedQuery->with(Product::apiListRelations()),
            ])
        );

        return $query->find($id);
    }

    /**
     * Get an active product by ID for the customer API.
     */
    public function getApiProductById(int $id): ?Product
    {
        return Product::constrainApiVisible(
            Product::query()->with([
                ...Product::apiListRelations(),
                'relatedProducts.relatedProduct' => fn ($relatedQuery) => Product::constrainApiVisible(
                    $relatedQuery->with(Product::apiListRelations())
                ),
            ])
        )->find($id);
    }

    /**
     * Get product by slug
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return Product::constrainFavoriteExists(
            Product::query()->with([
                ...Product::apiListRelations(),
                'relatedProducts.relatedProduct' => fn ($relatedQuery) => $relatedQuery->with(Product::apiListRelations()),
            ])
        )->where('slug', $slug)->first();
    }

    /**
     * Get active products
     */
    public function getActiveProducts(): Collection
    {
        return Product::active()
            ->approved()
            ->with(['vendor', 'categories', 'images'])
            ->get();
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(): Collection
    {
        return Product::featured()
            ->active()
            ->approved()
            ->with(['vendor', 'categories', 'images'])
            ->get();
    }

    /**
     * Get new products
     */
    public function getNewProducts(int $limit = 10): Collection
    {
        return Product::new()
            ->active()
            ->approved()
            ->with(['vendor', 'categories', 'images'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get products by vendor
     */
    public function getProductsByVendor(int $vendorId): Collection
    {
        return Product::byVendor($vendorId)
            ->with(['categories', 'images', 'variants'])
            ->get();
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })
            ->active()
            ->approved()
            ->with(['vendor', 'images'])
            ->get();
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product (soft delete)
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Force delete a product
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Restore a soft deleted product
     */
    public function restore(Product $product): bool
    {
        return $product->restore();
    }

    /**
     * Search products
     */
    public function search(string $search): Collection
    {
        return Product::where(function ($q) use ($search) {
            $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                ->orWhere('sku', 'LIKE', "%{$search}%");
        })
            ->with(['vendor', 'categories', 'images'])
            ->get();
    }

    /**
     * Sync product categories
     */
    public function syncCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    /**
     * Attach category to product
     */
    public function attachCategory(Product $product, int $categoryId): void
    {
        if (! $product->categories()->where('categories.id', $categoryId)->exists()) {
            $product->categories()->attach($categoryId);
        }
    }

    /**
     * Detach category from product
     */
    public function detachCategory(Product $product, int $categoryId): void
    {
        $product->categories()->detach($categoryId);
    }
}
