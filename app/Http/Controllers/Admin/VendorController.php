<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vendors\CreateRequest;
use App\Http\Requests\Admin\Vendors\UpdateRequest;
use App\Models\Plan;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    protected VendorService $service;

    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of vendors (Admin)
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'featured' => $request->get('featured', ''),
            'plan_id' => $request->get('plan_id', ''),
            'min_balance' => $request->get('min_balance', ''),
            'max_balance' => $request->get('max_balance', ''),
            'min_commission_rate' => $request->get('min_commission_rate', ''),
            'max_commission_rate' => $request->get('max_commission_rate', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $vendors = $this->service->getPaginatedVendors(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.vendors.partials.table', compact('vendors'))->render(),
                'pagination' => view('admin.vendors.partials.pagination', compact('vendors'))->render(),
            ]);
        }

        // Get plans for filter dropdown
        $plans = Plan::active()->get();

        return view('admin.vendors.index', compact('vendors', 'plans', 'filters'));
    }

    /**
     * Show the form for creating a new vendor (Admin)
     */
    public function create(): View
    {
        $plans = Plan::active()->get();

        return view('admin.vendors.create', compact('plans'));
    }

    /**
     * Store a newly created vendor (Admin)
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $this->service->createVendor($request);

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create vendor: '.$e->getMessage());
        }
    }

    /**
     * Display the specified vendor
     */
    public function show(Vendor $vendor): View
    {
        $vendor = $this->service->getVendorById($vendor->id);

        return view('admin.vendors.show', compact('vendor'));
    }

    /**
     * Show the form for editing the specified vendor (Admin)
     */
    public function edit(Vendor $vendor): View
    {
        $vendor = $this->service->getVendorById($vendor->id);
        $plans = Plan::active()->get();

        return view('admin.vendors.edit', compact('vendor', 'plans'));
    }

    /**
     * Update the specified vendor (Admin)
     */
    public function update(UpdateRequest $request, Vendor $vendor): RedirectResponse
    {

        try {
            $this->service->updateVendor($request, $vendor);

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update vendor: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified vendor (Admin)
     */
    public function destroy(Vendor $vendor): RedirectResponse|JsonResponse
    {
        try {
            $this->service->deleteVendor($vendor);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Vendor deleted successfully.'),
                ]);
            }

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete vendor: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete vendor: '.$e->getMessage());
        }
    }
}
