<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponRepository
{
    /**
     * Get all coupons
     */
    public function getAllCoupons(): Collection
    {
        return Coupon::with('orders')->get();
    }

    /**
     * Get paginated coupons with filters
     *
     * @param  array{
     *   search?: string,
     *   type?: string,
     *   is_active?: string|bool,
     *   from_date?: string,
     *   to_date?: string,
     * }  $filters
     */
    public function getPaginatedCoupons(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Coupon::with('orders');

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        // Apply is_active filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        // Apply date range filters
        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get coupon by ID
     */
    public function getCouponById(int $id): ?Coupon
    {
        return Coupon::with('orders')->find($id);
    }

    /**
     * Get coupon by code
     */
    public function getCouponByCode(string $code): ?Coupon
    {
        return Coupon::where('code', $code)->first();
    }

    /**
     * Create a new coupon
     *
     * @param  array<string, mixed>  $data
     */
    public function createCoupon(array $data): Coupon
    {
        return Coupon::create($data);
    }

    /**
     * Update a coupon
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCoupon(Coupon $coupon, array $data): bool
    {
        return $coupon->update($data);
    }

    /**
     * Delete a coupon
     */
    public function deleteCoupon(Coupon $coupon): bool
    {
        return $coupon->delete();
    }
}
