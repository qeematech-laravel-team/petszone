<?php

namespace App\Repositories;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VariantRepository
{
    /**
     * Get all variants
     */
    public function getAllVariants(): Collection
    {
        return Variant::with('options')->get();
    }

    /**
     * Get paginated variants
     */
    public function getPaginatedVariants(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Variant::with('options');

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        // Apply required filter
        if (isset($filters['required']) && $filters['required'] !== '') {
            $query->where('is_required', $filters['required'] === '1');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get variant by ID
     */
    public function getVariantById(int $id): ?Variant
    {
        return Variant::with('options')->find($id);
    }

    /**
     * Get active variants
     */
    public function getActiveVariants(): Collection
    {
        return Variant::active()->with('options')->get();
    }

    /**
     * Get required variants
     */
    public function getRequiredVariants(): Collection
    {
        return Variant::required()->with('options')->get();
    }

    /**
     * Create a new variant
     */
    public function create(array $data): Variant
    {
        return Variant::create($data);
    }

    /**
     * Update a variant
     */
    public function update(Variant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    /**
     * Delete a variant (soft delete)
     */
    public function delete(Variant $variant): bool
    {
        return $variant->delete();
    }

    /**
     * Force delete a variant
     */
    public function forceDelete(Variant $variant): bool
    {
        return $variant->forceDelete();
    }

    /**
     * Restore a soft deleted variant
     */
    public function restore(Variant $variant): bool
    {
        return $variant->restore();
    }

    /**
     * Search variants by name
     */
    public function search(string $search): Collection
    {
        return Variant::where(function ($q) use ($search) {
            $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
        })
        ->with('options')
        ->get();
    }
}
