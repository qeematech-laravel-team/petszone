<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Branches\CreateRequest;
use App\Http\Requests\Vendor\Branches\UpdateRequest;
use App\Models\Branch;
use App\Models\Vendor;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BranchController extends Controller
{
    protected BranchService $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * Get the vendor for the authenticated user
     */
    protected function getVendor(): ?Vendor
    {
        $user = Auth::user();

        return $user->vendor();
    }

    /**
     * Display a listing of the vendor's branches.
     */
    public function index(): View
    {

        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $branches = $this->branchService->getBranchesByVendor($vendor->id);

        return view('vendor.branches.index', compact('branches', 'vendor'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create(): View
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        return view('vendor.branches.create', compact('vendor'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            $data = [
                'vendor_id' => $vendor->id, // Force vendor_id to be the authenticated vendor's ID
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

            $this->branchService->createBranch($data);

            return redirect()->route('vendor.branches.index')
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
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Ensure the branch belongs to this vendor
        if ($branch->vendor_id !== $vendor->id) {
            abort(403, __('You do not have permission to view this branch.'));
        }

        return view('vendor.branches.show', compact('branch', 'vendor'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch): View
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Ensure the branch belongs to this vendor
        if ($branch->vendor_id !== $vendor->id) {
            abort(403, __('You do not have permission to edit this branch.'));
        }

        return view('vendor.branches.edit', compact('branch', 'vendor'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(UpdateRequest $request, Branch $branch): RedirectResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            // Ensure the branch belongs to this vendor
            if ($branch->vendor_id !== $vendor->id) {
                abort(403, __('You do not have permission to update this branch.'));
            }

            $data = [
                'vendor_id' => $vendor->id, // Force vendor_id to be the authenticated vendor's ID
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

            $this->branchService->updateBranch($branch, $data);

            return redirect()->route('vendor.branches.index')
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
    public function destroy(Branch $branch): RedirectResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            // Ensure the branch belongs to this vendor
            if ($branch->vendor_id !== $vendor->id) {
                abort(403, __('You do not have permission to delete this branch.'));
            }

            $this->branchService->deleteBranch($branch);

            return redirect()->route('vendor.branches.index')
                ->with('success', __('Branch deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to delete branch: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Toggle branch active status.
     */
    public function toggleActive(Branch $branch): \Illuminate\Http\JsonResponse
    {
        try {
            $vendor = $this->getVendor();

            if (! $vendor) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vendor account not found.'),
                ], 404);
            }

            // Ensure the branch belongs to this vendor
            if ($branch->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have permission to update this branch.'),
                ], 403);
            }

            $branch = $this->branchService->toggleActive($branch);

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
}
