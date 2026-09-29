<?php

namespace App\Repositories;

use App\Models\VendorSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorSubscriptionRepository
{
    /**
     * Get all subscriptions
     */
    public function getAllSubscriptions(): Collection
    {
        return VendorSubscription::query()->get();
    }

    /**
     * Get paginated subscriptions
     */
    public function getPaginatedSubscriptions(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = VendorSubscription::query()->with(['vendor', 'plan']);

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Apply vendor ID filter
        if (isset($filters['vendor_id']) && $filters['vendor_id'] !== '') {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get subscription by ID
     */
    public function getSubscriptionById(int $id): ?VendorSubscription
    {
        return VendorSubscription::with(['vendor', 'plan'])->find($id);
    }

    /**
     * Get active subscriptions
     */
    public function getActiveSubscriptions(): Collection
    {
        return VendorSubscription::active()->get();
    }

    /**
     * Get subscription by vendor ID
     */
    public function getSubscriptionByVendorId(int $vendorId, array $filters = [], int $perPage = 15)
    {
        $query = VendorSubscription::with('plan')->where('vendor_id', $vendorId);

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }
}
