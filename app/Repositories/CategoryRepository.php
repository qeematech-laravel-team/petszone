<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return Category::with(['parent', 'children'])->get();
    }

    /**
     * Get paginated categories
     */
    public function getPaginatedCategories(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Category::with(['parent', 'children']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] != '') {
            $query->where('is_active', $filters['status'] == 'active');
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured'] != '') {
            $query->where('is_featured', $filters['featured'] == 1);
        }

        // Apply parent filter
        if (isset($filters['parent_id']) && $filters['parent_id'] != '') {
            if ($filters['parent_id'] == 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Sorting
        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $id, bool $activeProductsOnly = false): ?Category
    {
        $productsConstraint = fn ($query) => $activeProductsOnly
            ? Product::constrainApiVisible($query->with(Product::apiListRelations()))
            : Product::constrainFavoriteExists($query->with(Product::apiListRelations()));

        return Category::with([
            'parent',
            'children',
            'products' => $productsConstraint,
        ])->find($id);
    }

    /**
     * Get active categories
     */
    public function getActiveCategories(): Collection
    {
        return Category::active()->with(['parent', 'children'])->get();
    }

    /**
     * Get featured categories
     */
    public function getFeaturedCategories(): Collection
    {
        return Category::featured()
            ->active()
            ->with(['parent', 'children'])
            ->get();
    }

    /**
     * Get root categories (categories with no parent)
     */
    public function getRootCategories(): Collection
    {
        return Category::root()
            ->with('children')
            ->get();
    }

    /**
     * Get categories with their parent
     */
    public function getCategoriesWithParent(): Collection
    {
        return Category::with('parent')->get();
    }

    /**
     * Get categories with their children
     */
    public function getCategoriesWithChildren(): Collection
    {
        return Category::with('children')->get();
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete a category (soft delete)
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Force delete a category
     */
    public function forceDelete(Category $category): bool
    {
        return $category->forceDelete();
    }

    /**
     * Restore a soft deleted category
     */
    public function restore(Category $category): bool
    {
        return $category->restore();
    }

    /**
     * Search categories by name
     */
    public function search(string $search): Collection
    {
        return Category::where('name', 'like', "%{$search}%")
            ->with(['parent', 'children'])
            ->get();
    }

    /**
     * Get category tree (nested structure)
     */
    public function getCategoryTree(): Collection
    {
        return Category::root()
            ->with('children')
            ->active()
            ->get();
    }

    /**
     * Check if category has children
     */
    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    /**
     * Get category by slug (if you add slug field later)
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        // Note: This assumes you might add a slug field later
        return Category::where('slug', $slug)->first();
    }

    /**
     * Get all categories with filters for export (no pagination)
     */
    public function getAllCategoriesForExport(array $filters = []): Collection
    {
        $query = Category::with(['parent', 'children', 'products']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] != '') {
            $query->where('is_active', $filters['status'] == 'active');
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured'] != '') {
            $query->where('is_featured', $filters['featured'] == 1);
        }

        // Apply parent filter
        if (isset($filters['parent_id']) && $filters['parent_id'] != '') {
            if ($filters['parent_id'] == 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Sorting
        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            default => $query->latest(),
        };

        return $query->get();
    }
}
