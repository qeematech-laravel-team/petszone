<?php

namespace App\Services;

use App\Repositories\VendorSubscriptionRepository;
use App\Models\VendorSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class SubscriptionService
{
    protected VendorSubscriptionRepository $vendorSubscriptionRepository;

    public function __construct(VendorSubscriptionRepository $vendorSubscriptionRepository)
    {
        $this->vendorSubscriptionRepository = $vendorSubscriptionRepository;
    }

    /**
     * Get all vendor subscriptions
     */
    public function getAllVendorSubscriptions(): Collection
    {
        return $this->vendorSubscriptionRepository->getAllSubscriptions();
    }

    /**
     * Get paginated plans
     */
    public function getPaginatedSubscriptions(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->vendorSubscriptionRepository->getPaginatedSubscriptions($perPage, $filters);
    }

    /**
     * Get plan by ID
     */
    public function getSubscriptionById(int $id): ?VendorSubscription
    {
        return $this->vendorSubscriptionRepository->getSubscriptionById($id);
    }

    /**
     * Get subscription by Vendor ID
     */
    public function getSubscriptionByVendorId(int $vendorId, array $filters = []): ?LengthAwarePaginator
    {
        return $this->vendorSubscriptionRepository->getSubscriptionByVendorId($vendorId, $filters);
    }

    /**
     * Get active subscriptions
     */
    public function getActiveSubscriptions(): Collection
    {
        return $this->vendorSubscriptionRepository->getActiveSubscriptions();
    }


}
