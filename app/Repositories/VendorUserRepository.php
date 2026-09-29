<?php

namespace App\Repositories;

use App\Models\VendorUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorUserRepository
{
    /**
     * Get all vendor users for a specific vendor
     */
    public function getVendorUsersByVendor(int $vendorId): Collection
    {
        return VendorUser::where('vendor_id', $vendorId)
            ->with(['user', 'vendor'])
            ->latest()
            ->get();
    }

    /**
     * Get paginated vendor users for a specific vendor
     */
    public function getPaginatedVendorUsersByVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = VendorUser::where('vendor_id', $vendorId)
            ->with(['user', 'vendor']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get vendor user by ID
     */
    public function getVendorUserById(int $id): ?VendorUser
    {
        return VendorUser::with(['user', 'vendor'])->find($id);
    }

    /**
     * Get vendor user by vendor ID and user ID
     */
    public function getVendorUserByVendorAndUser(int $vendorId, int $userId): ?VendorUser
    {
        return VendorUser::where('vendor_id', $vendorId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Check if user is already associated with vendor
     */
    public function userExistsForVendor(int $vendorId, int $userId): bool
    {
        return VendorUser::where('vendor_id', $vendorId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Create a new vendor user
     */
    public function create(array $data): VendorUser
    {
        return VendorUser::create($data);
    }

    /**
     * Update a vendor user
     */
    public function update(VendorUser $vendorUser, array $data): bool
    {
        return $vendorUser->update($data);
    }

    /**
     * Delete a vendor user (soft delete)
     */
    public function delete(VendorUser $vendorUser): bool
    {
        return $vendorUser->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(VendorUser $vendorUser): VendorUser
    {
        $vendorUser->is_active = !$vendorUser->is_active;
        $vendorUser->save();

        return $vendorUser;
    }
}
