<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupons\CreateRequest;
use App\Http\Requests\Admin\Coupons\UpdateRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    protected CouponService $service;

    public function __construct(CouponService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of coupons
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'type' => (string) $request->get('type', ''),
            'is_active' => $request->get('is_active', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
        ];

        $coupons = $this->service->getPaginatedCoupons($perPage, $filters);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon
     */
    public function create(): View
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        $this->service->createCoupon($request->validated());

        return redirect()->route('admin.coupons.index')
            ->with('success', __('Coupon created successfully.'));
    }

    /**
     * Display the specified coupon
     */
    public function show(int $id): View
    {
        $coupon = $this->service->getCouponById($id);

        if (! $coupon) {
            abort(404, __('Coupon not found.'));
        }

        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon
     */
    public function edit(int $id): View
    {
        $coupon = $this->service->getCouponById($id);

        if (! $coupon) {
            abort(404, __('Coupon not found.'));
        }

        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon
     */
    public function update(UpdateRequest $request, int $id): RedirectResponse
    {
        $coupon = $this->service->getCouponById($id);

        if (! $coupon) {
            return redirect()->route('admin.coupons.index')
                ->with('error', __('Coupon not found.'));
        }

        $this->service->updateCoupon($coupon, $request->validated());

        return redirect()->route('admin.coupons.index')
            ->with('success', __('Coupon updated successfully.'));
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(int $id): RedirectResponse
    {
        $coupon = $this->service->getCouponById($id);

        if (! $coupon) {
            return redirect()->route('admin.coupons.index')
                ->with('error', __('Coupon not found.'));
        }

        $this->service->deleteCoupon($coupon);

        return redirect()->route('admin.coupons.index')
            ->with('success', __('Coupon deleted successfully.'));
    }
}
