<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAllCategories();
    }

    /**
     * Get paginated categories
     */
    public function getPaginatedCategories(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginatedCategories($perPage, $filters);
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $id, bool $activeProductsOnly = false): ?Category
    {
        return $this->categoryRepository->getCategoryById($id, $activeProductsOnly);
    }

    /**
     * Get active categories
     */
    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    /**
     * Get featured categories
     */
    public function getFeaturedCategories(): Collection
    {
        return $this->categoryRepository->getFeaturedCategories();
    }

    /**
     * Get root categories
     */
    public function getRootCategories(): Collection
    {
        return $this->categoryRepository->getRootCategories();
    }

    /**
     * Get category tree
     */
    public function getCategoryTree(): Collection
    {
        return $this->categoryRepository->getCategoryTree();
    }

    /**
     * Get all categories with filters for export
     */
    public function getAllCategoriesForExport(array $filters = []): Collection
    {
        return $this->categoryRepository->getAllCategoriesForExport($filters);
    }

    /**
     * Create a new category
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function createCategory($request): Category
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'parent_id' => $request->parent_id ?? null,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = $this->categoryRepository->create($data);

            DB::commit();

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a category
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function updateCategory($request, Category $category): Category
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'parent_id' => $request->parent_id ?? null,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                $oldImage = $category->getOriginal('image');
                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            // Prevent category from being its own parent
            if ($data['parent_id'] == $category->id) {
                throw new \Exception('A category cannot be its own parent.');
            }

            // Prevent circular reference (category cannot be parent of its ancestor)
            // if ($data['parent_id'] && $this->isDescendant($category, $data['parent_id'])) {
            //     throw new \Exception('A category cannot be a parent of its own descendant.');
            // }

            $this->categoryRepository->update($category, $data);
            $category->refresh();

            DB::commit();

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category): bool
    {
        DB::beginTransaction();
        try {
            // Check if category has children
            if ($this->categoryRepository->hasChildren($category)) {
                throw new \Exception('Cannot delete category that has children. Please delete or move children first.');
            }

            // Delete image if exists
            $oldImage = $category->getOriginal('image');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            $deleted = $this->categoryRepository->delete($category);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Force delete a category
     */
    public function forceDeleteCategory(Category $category): bool
    {
        DB::beginTransaction();
        try {
            // Delete image if exists
            $oldImage = $category->getOriginal('image');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            $deleted = $this->categoryRepository->forceDelete($category);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft deleted category
     */
    public function restoreCategory(Category $category): bool
    {
        return $this->categoryRepository->restore($category);
    }

    /**
     * Search categories
     */
    public function searchCategories(string $search): Collection
    {
        return $this->categoryRepository->search($search);
    }

    /**
     * Toggle category active status
     */
    public function toggleActive(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }

    /**
     * Toggle category featured status
     */
    public function toggleFeatured(Category $category): Category
    {
        $category->update(['is_featured' => ! $category->is_featured]);

        return $category->fresh();
    }

    /**
     * Check if a category is a descendant of another category
     */
    protected function isDescendant(Category $category, int $parentId): bool
    {
        $parent = $this->categoryRepository->getCategoryById($parentId);
        if (! $parent) {
            return false;
        }

        $currentParent = $category->parent;
        while ($currentParent) {
            if ($currentParent->id === $parentId) {
                return true;
            }
            $currentParent = $currentParent->parent;
        }

        return false;
    }
}
