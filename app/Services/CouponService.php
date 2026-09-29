<?php

namespace App\Services;

use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponService
{
    protected CouponRepository $couponRepository;

    public function __construct(CouponRepository $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    /**
     * Get all coupons
     */
    public function getAllCoupons(): Collection
    {
        return $this->couponRepository->getAllCoupons();
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
        return $this->couponRepository->getPaginatedCoupons($perPage, $filters);
    }

    /**
     * Get coupon by ID
     */
    public function getCouponById(int $id): ?Coupon
    {
        return $this->couponRepository->getCouponById($id);
    }

    /**
     * Get coupon by code
     */
    public function getCouponByCode(string $code): ?Coupon
    {
        return $this->couponRepository->getCouponByCode($code);
    }

    /**
     * Create a new coupon
     *
     * @param  array<string, mixed>  $data
     */
    public function createCoupon(array $data): Coupon
    {
        // Ensure code is uppercase
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        // Convert date strings to datetime if provided
        if (isset($data['start_date']) && is_string($data['start_date'])) {
            $data['start_date'] = $data['start_date'] ? now()->parse($data['start_date']) : null;
        }

        if (isset($data['end_date']) && is_string($data['end_date'])) {
            $data['end_date'] = $data['end_date'] ? now()->parse($data['end_date']) : null;
        }

        return $this->couponRepository->createCoupon($data);
    }

    /**
     * Update a coupon
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCoupon(Coupon $coupon, array $data): bool
    {
        // Ensure code is uppercase if provided
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        // Convert date strings to datetime if provided
        if (isset($data['start_date']) && is_string($data['start_date'])) {
            $data['start_date'] = $data['start_date'] ? now()->parse($data['start_date']) : null;
        }

        if (isset($data['end_date']) && is_string($data['end_date'])) {
            $data['end_date'] = $data['end_date'] ? now()->parse($data['end_date']) : null;
        }

        return $this->couponRepository->updateCoupon($coupon, $data);
    }

    /**
     * Delete a coupon
     */
    public function deleteCoupon(Coupon $coupon): bool
    {
        return $this->couponRepository->deleteCoupon($coupon);
    }
}
