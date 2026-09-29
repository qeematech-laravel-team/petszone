<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorUsers\CreateRequest;
use App\Http\Requests\Vendor\VendorUsers\UpdateRequest;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Services\BranchService;
use App\Services\VendorUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorUserController extends Controller
{
    public function __construct(
        protected VendorUserService $vendorUserService,
        protected BranchService $branchService
    ) {}

    /**
     * Get the vendor for the authenticated user (owner or employee).
     */
    protected function getVendor(): ?Vendor
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('admin')) {
            return null;
        }

        return $user->vendor();
    }

    public function index(): View
    {
        $vendor = $this->requireVendor();
        $vendorUsers = $this->vendorUserService->getVendorUsersByVendor($vendor->id);

        return view('vendor.vendor-users.index', compact('vendorUsers', 'vendor'));
    }

    public function create(): View
    {
        $vendor = $this->requireVendor();
        $permissions = $this->vendorUserService->groupedAssignablePermissions(Auth::user());
        $branches = $this->branchService->getBranchesByVendor($vendor->id);

        return view('vendor.vendor-users.create', compact('vendor', 'permissions', 'branches'));
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $vendor = $this->requireVendor();

            $this->vendorUserService->createVendorUser($vendor->id, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'is_active' => $request->boolean('is_active', true),
                'permissions' => $request->input('permissions', []),
                'user_type' => $request->input('user_type', 'owner'),
                'branch_id' => $request->input('user_type') === 'branch' ? $request->input('branch_id') : null,
            ], Auth::user());

            return redirect()->route('vendor.vendor-users.index')
                ->with('success', __('Vendor user created successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to create vendor user: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    public function show(VendorUser $vendorUser): View
    {
        $vendor = $this->requireVendor();
        $this->ensureBelongsToVendor($vendorUser, $vendor);

        $vendorUser->load(['user.permissions', 'branch']);

        return view('vendor.vendor-users.show', compact('vendorUser', 'vendor'));
    }

    public function edit(VendorUser $vendorUser): View
    {
        $vendor = $this->requireVendor();
        $this->ensureBelongsToVendor($vendorUser, $vendor);

        $permissions = $this->vendorUserService->groupedAssignablePermissions(Auth::user());
        $userPermissions = $vendorUser->user->permissions->pluck('name')->toArray();
        $branches = $this->branchService->getBranchesByVendor($vendor->id);

        return view('vendor.vendor-users.edit', compact('vendorUser', 'vendor', 'permissions', 'userPermissions', 'branches'));
    }

    public function update(UpdateRequest $request, VendorUser $vendorUser): RedirectResponse
    {
        try {
            $vendor = $this->requireVendor();
            $this->ensureBelongsToVendor($vendorUser, $vendor);

            $this->vendorUserService->updateVendorUser($vendorUser, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'is_active' => $request->boolean('is_active'),
                'permissions' => $request->input('permissions', []),
                'user_type' => $request->input('user_type', 'owner'),
                'branch_id' => $request->input('user_type') === 'branch' ? $request->input('branch_id') : null,
            ], Auth::user());

            return redirect()->route('vendor.vendor-users.index')
                ->with('success', __('Vendor user updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to update vendor user: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    public function destroy(VendorUser $vendorUser): RedirectResponse|JsonResponse
    {
        try {
            $vendor = $this->requireVendor();
            $this->ensureBelongsToVendor($vendorUser, $vendor);

            abort_if($vendorUser->user_id === Auth::id(), 403, __('You cannot delete your own account.'));

            $this->vendorUserService->deleteVendorUser($vendorUser);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Vendor user deleted successfully.'),
                ]);
            }

            return redirect()->route('vendor.vendor-users.index')
                ->with('success', __('Vendor user deleted successfully.'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete vendor user: :error', ['error' => $e->getMessage()]),
                ], 500);
            }

            return redirect()->back()
                ->with('error', __('Failed to delete vendor user: :error', ['error' => $e->getMessage()]));
        }
    }

    public function toggleActive(VendorUser $vendorUser): JsonResponse
    {
        try {
            $vendor = $this->requireVendor();
            $this->ensureBelongsToVendor($vendorUser, $vendor);

            abort_if($vendorUser->user_id === Auth::id(), 403, __('You cannot deactivate your own account.'));

            $vendorUser = $this->vendorUserService->toggleActive($vendorUser);

            return response()->json([
                'success' => true,
                'message' => __('User status updated successfully.'),
                'is_active' => $vendorUser->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update user status: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    protected function requireVendor(): Vendor
    {
        $vendor = $this->getVendor();

        abort_unless($vendor, 404, __('Vendor account not found.'));

        return $vendor;
    }

    protected function ensureBelongsToVendor(VendorUser $vendorUser, Vendor $vendor): void
    {
        abort_unless($vendorUser->vendor_id === $vendor->id, 403, __('You do not have permission to manage this user.'));
    }
}
