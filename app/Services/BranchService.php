<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\BranchRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BranchService
{
    protected BranchRepository $branchRepository;

    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * Get all branches.
     */
    public function getAllBranches(): Collection
    {
        return $this->branchRepository->getAllBranches();
    }

    /**
     * Get paginated branches with filters.
     */
    public function getPaginatedBranches(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->branchRepository->getPaginatedBranches($perPage, $filters);
    }

    /**
     * Get a branch by ID.
     */
    public function getBranchById(int $id): ?Branch
    {
        return $this->branchRepository->getBranchById($id);
    }

    /**
     * Get branches by vendor ID.
     */
    public function getBranchesByVendor(int $vendorId): Collection
    {
        return $this->branchRepository->getBranchesByVendor($vendorId);
    }

    /**
     * Get active branches.
     */
    public function getActiveBranches(): Collection
    {
        return $this->branchRepository->getActiveBranches();
    }

    /**
     * Create a new branch.
     */
    public function createBranch(array $branchData): Branch
    {
        DB::beginTransaction();
        try {
            $branch = $this->branchRepository->create($branchData);
            DB::commit();
            return $branch->load('vendor');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing branch.
     */
    public function updateBranch(Branch $branch, array $branchData): Branch
    {
        DB::beginTransaction();
        try {
            $this->branchRepository->update($branch, $branchData);
            DB::commit();
            return $branch->load('vendor');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a branch.
     */
    public function deleteBranch(Branch $branch): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->branchRepository->delete($branch);
            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle branch active status.
     */
    public function toggleActive(Branch $branch): Branch
    {
        $branch->update(['is_active' => !$branch->is_active]);
        return $branch->fresh(['vendor']);
    }
}
