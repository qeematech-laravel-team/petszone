<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository
{
    /**
     * Get all branches with their relationships.
     */
    public function getAllBranches(): Collection
    {
        return Branch::with(['vendor'])->get();
    }

    /**
     * Get paginated branches with filters.
     */
    public function getPaginatedBranches(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Branch::with(['vendor']);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (isset($filters['vendor_id']) && $filters['vendor_id'] !== '') {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a branch by ID with its relationships.
     */
    public function getBranchById(int $id): ?Branch
    {
        return Branch::with(['vendor'])->find($id);
    }

    /**
     * Get branches by vendor ID.
     */
    public function getBranchesByVendor(int $vendorId): Collection
    {
        return Branch::where('vendor_id', $vendorId)->with(['vendor'])->get();
    }

    /**
     * Get active branches.
     */
    public function getActiveBranches(): Collection
    {
        return Branch::where('is_active', true)->with(['vendor'])->get();
    }

    /**
     * Create a new branch.
     */
    public function create(array $data): Branch
    {
        return Branch::create($data);
    }

    /**
     * Update an existing branch.
     */
    public function update(Branch $branch, array $data): bool
    {
        return $branch->update($data);
    }

    /**
     * Delete a branch (soft delete).
     */
    public function delete(Branch $branch): bool
    {
        return $branch->delete();
    }

    /**
     * Force delete a branch.
     */
    public function forceDelete(Branch $branch): bool
    {
        return $branch->forceDelete();
    }

    /**
     * Restore a soft deleted branch.
     */
    public function restore(Branch $branch): bool
    {
        return $branch->restore();
    }

    /**
     * Search branches by name, address, or phone.
     */
    public function search(string $search): Collection
    {
        return Branch::where(function ($query) use ($search) {
            $query->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                ->orWhere('address', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%");
        })->with(['vendor'])->get();
    }
}
