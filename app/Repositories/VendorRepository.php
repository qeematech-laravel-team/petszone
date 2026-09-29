<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorRepository
{
    /**
     * Get all vendors
     */
    public function getAllVendors(): Collection
    {
        return Vendor::with(['owner', 'plan'])->get();
    }

    /**
     * Get paginated vendors
     */
    public function getPaginatedVendors(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Vendor::constrainVisibleRatingAggregates(
            Vendor::query()->with(['owner', 'plan'])
        );

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
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

        // Apply plan filter
        if (isset($filters['plan_id']) && $filters['plan_id'] !== '') {
            $query->where('plan_id', $filters['plan_id']);
        }

        // Balance range
        if (isset($filters['min_balance']) && $filters['min_balance'] !== '') {
            $query->where('balance', '>=', $filters['min_balance']);
        }

        if (isset($filters['max_balance']) && $filters['max_balance'] !== '') {
            $query->where('balance', '<=', $filters['max_balance']);
        }

        // Commission rate range
        if (isset($filters['min_commission_rate']) && $filters['min_commission_rate'] !== '') {
            $query->where('commission_rate', '>=', $filters['min_commission_rate']);
        }

        if (isset($filters['max_commission_rate']) && $filters['max_commission_rate'] !== '') {
            $query->where('commission_rate', '<=', $filters['max_commission_rate']);
        }

        // Created date range
        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'balance_desc' => $query->orderBy('balance', 'desc'),
            'balance_asc' => $query->orderBy('balance', 'asc'),
            'commission_desc' => $query->orderBy('commission_rate', 'desc'),
            'commission_asc' => $query->orderBy('commission_rate', 'asc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    // /**
    //  * Get vendor by ID
    //  */
    // public function getVendorById(int $id): ?Vendor
    // {
    //     return Vendor::with(['owner', 'plan', 'subscriptions', 'products', 'branches'])->find($id);
    // }

    /**
     * Get vendor by ID
     */
    public function getVendorById(int $id, bool $activeProductsOnly = false): ?Vendor
    {
        $productsConstraint = fn ($query) => $activeProductsOnly
            ? Product::constrainApiVisible($query->with(Product::apiListRelations()))
            : Product::constrainFavoriteExists($query->with(Product::apiListRelations()));

        $vendor = Vendor::constrainVisibleRatingAggregates(
            Vendor::query()->with([
                'owner',
                'plan',
                'subscriptions',
                'products' => $productsConstraint,
                'branches',
            ])
        )->find($id);

        if ($vendor) {
            $vendor->setRelation('categories', $this->getCategoriesForVendorProducts($vendor->id));
        }

        return $vendor;
    }

    /**
     * Distinct categories used by a vendor's active, approved products.
     */
    public function getCategoriesForVendorProducts(int $vendorId): Collection
    {
        return Category::query()
            ->forVendorProducts($vendorId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Get vendor by slug
     */
    public function getVendorBySlug(string $slug): ?Vendor
    {
        return Vendor::with(['owner', 'plan', 'subscriptions'])->where('slug', '=', $slug, 'and')->first();
    }

    /**
     * Get active vendors
     */
    public function getActiveVendors(): Collection
    {
        return Vendor::query()->where('is_active', '=', true, 'and')->with(['owner', 'plan'])->get();
    }

    /**
     * Get featured vendors
     */
    public function getFeaturedVendors(): Collection
    {
        return Vendor::query()->where('is_featured', '=', true, 'and')
            ->where('is_active', '=', true, 'and')
            ->with(['owner', 'plan'])
            ->get();
    }

    /**
     * Get vendors by owner
     */
    public function getVendorsByOwner(int $ownerId): Collection
    {
        return Vendor::query()->where('owner_id', '=', $ownerId, 'and')->with(['plan'])->get();
    }

    /**
     * Get vendors by plan
     */
    public function getVendorsByPlan(int $planId): Collection
    {
        return Vendor::query()->where('plan_id', '=', $planId, 'and')->with(['owner'])->get();
    }

    /**
     * Create a new vendor
     */
    public function create(array $data): Vendor
    {
        return Vendor::create($data);
    }

    /**
     * Update a vendor
     */
    public function update(Vendor $vendor, array $data): bool
    {
        return $vendor->update($data);
    }

    /**
     * Delete a vendor (soft delete)
     */
    public function delete(Vendor $vendor): bool
    {
        return Vendor::destroy($vendor->id) > 0;
    }

    /**
     * Force delete a vendor
     */
    public function forceDelete(Vendor $vendor): bool
    {
        return $vendor->forceDelete();
    }

    /**
     * Restore a soft deleted vendor
     */
    public function restore(Vendor $vendor): bool
    {
        return $vendor->restore();
    }

    /**
     * Search vendors
     */
    public function search(string $search): Collection
    {
        return Vendor::query()->where(function ($q) use ($search) {
            $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        })
            ->with(['owner', 'plan'])
            ->get();
    }

    /**
     * Check if owner already has a vendor
     */
    public function ownerHasVendor(int $ownerId): bool
    {
        return Vendor::query()->where('owner_id', '=', $ownerId, 'and')->exists();
    }

    /**
     * Get vendor by owner ID
     */
    public function getVendorByOwner(int $ownerId): ?Vendor
    {
        return Vendor::query()->where('owner_id', '=', $ownerId, 'and')->with(['plan'])->first();
    }
}
