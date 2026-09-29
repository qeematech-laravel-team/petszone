<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Branches\CreateRequest;
use App\Http\Requests\Admin\Branches\UpdateRequest;
use App\Models\Branch;
use App\Services\BranchService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    protected BranchService $service;
    protected VendorService $vendorService;

    public function __construct(BranchService $service, VendorService $vendorService)
    {
        $this->service = $service;
        $this->vendorService = $vendorService;
    }

    /**
     * Display a listing of the branches.
     */
    public function index(): View|JsonResponse
    {
        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'vendor_id' => request()->get('vendor_id', ''),
        ];

        $branches = $this->service->getPaginatedBranches(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.branches.partials.table', compact('branches'))->render(),
                'pagination' => view('admin.branches.partials.pagination', compact('branches'))->render(),
            ]);
        }

        $vendors = $this->vendorService->getActiveVendors();
        return view('admin.branches.index', compact('branches', 'filters', 'vendors'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create(): View
    {
        $vendors = $this->vendorService->getActiveVendors();
        return view('admin.branches.create', compact('vendors'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $data = [
                'vendor_id' => $request->vendor_id,
                'name' => [
                    'en' => $request->name['en'],
                    'ar' => $request->name['ar'],
                ],
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'phone' => $request->phone,
                'is_active' => $request->boolean('is_active', true),
            ];

            $this->service->createBranch($data);

            return redirect()->route('admin.branches.index')
                ->with('success', __('Branch created successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to create branch: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch): View
    {
        $branch = $this->service->getBranchById($branch->id);
        return view('admin.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch): View
    {
        $branch = $this->service->getBranchById($branch->id);
        $vendors = $this->vendorService->getActiveVendors();
        return view('admin.branches.edit', compact('branch', 'vendors'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(UpdateRequest $request, Branch $branch): RedirectResponse
    {
        try {
            $data = [
                'vendor_id' => $request->vendor_id,
                'name' => [
                    'en' => $request->name['en'],
                    'ar' => $request->name['ar'],
                ],
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'phone' => $request->phone,
                'is_active' => $request->boolean('is_active'),
            ];

            $this->service->updateBranch($branch, $data);

            return redirect()->route('admin.branches.index')
                ->with('success', __('Branch updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to update branch: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(Branch $branch): RedirectResponse|JsonResponse
    {
        try {
            $this->service->deleteBranch($branch);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Branch deleted successfully.'),
                ]);
            }

            return redirect()->route('admin.branches.index')
                ->with('success', __('Branch deleted successfully.'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete branch: :error', ['error' => $e->getMessage()]),
                ], 500);
            }

            return redirect()->back()
                ->with('error', __('Failed to delete branch: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Toggle branch active status.
     */
    public function toggleActive(Branch $branch): JsonResponse
    {
        try {
            $branch = $this->service->toggleActive($branch);

            return response()->json([
                'success' => true,
                'message' => __('Branch status updated successfully.'),
                'is_active' => $branch->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update branch status: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Get branches by vendor ID (AJAX)
     */
    public function getBranchesByVendor($vendorId): JsonResponse
    {
        try {
            $branches = $this->service->getBranchesByVendor((int)$vendorId);
            
            return response()->json([
                'success' => true,
                'branches' => $branches->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to load branches: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}
