<?php

namespace App\Repositories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlanRepository
{
    /**
     * Get all plans
     */
    public function getAllPlans(): Collection
    {
        return Plan::get();
    }

    /**
     * Get paginated plans
     */
    public function getPaginatedPlans(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Plan::query();

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('is_featured', $filters['featured'] === '1');
        }


        return $query->latest()->paginate($perPage);
    }

    /**
     * Get category by ID
     */
    public function getPlanById(int $id): ?Plan
    {
        return Plan::find($id);
    }

    /**
     * Get active plans
     */
    public function getActivePlans(): Collection
    {
        return Plan::active()->get();
    }
    /**
     * Get active plans
     */
    public function getFeaturedPlans(): Collection
    {
        return Plan::featured()->get();
    }

    /**
     * Get featured plans
     */
    public function getFeaturedCategories(): Collection
    {
        return Plan::featured()
            ->active()
            ->get();
    }

    /**
     * Create a new category
     */
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update a category
     */
    public function update(Plan $plan, array $data): bool
    {
        return $plan->update($data);
    }

    /**
     * Delete a category (soft delete)
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }

    /**
     * Force delete a category
     */
    public function forceDelete(Plan $plan): bool
    {
        return $plan->forceDelete();
    }

    /**
     * Restore a soft deleted category
     */
    public function restore(Plan $plan): bool
    {
        return $plan->restore();
    }

    /**
     * Search plans by name or description
     */
    public function search(string $search): Collection
    {
        return Plan::where(function ($q) use ($search) {
            $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"]);
        })->get();
    }

    /**
     * Get category by slug (if you add slug field later)
     */
    public function getPlanBySlug(string $slug): ?Plan
    {
        // Note: This assumes you might add a slug field later
        return Plan::where('slug', $slug)->first();
    }
}
