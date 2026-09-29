<?php

namespace App\Http\Controllers;

use App\Http\Requests\VariantRequestApproveRequest;
use App\Http\Requests\VariantRequestStoreRequest;
use App\Models\Variant;
use App\Models\VariantRequest;
use App\Models\Vendor;
use App\Services\VariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VariantRequestController extends Controller
{
    protected VariantService $variantService;

    public function __construct(VariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * Display a listing of variant requests (Admin)
     */
    public function index(): View
    {
        $status = request()->get('status', 'all');

        $query = VariantRequest::with(['vendor', 'reviewer']);

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        }

        $requests = $query->latest()->paginate(15);

        // Get counts for filter tabs
        $counts = [
            'all' => VariantRequest::count(),
            'pending' => VariantRequest::pending()->count(),
            'approved' => VariantRequest::approved()->count(),
            'rejected' => VariantRequest::rejected()->count(),
        ];

        return view('admin.variant-requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Display a listing of vendor's variant requests
     */
    public function vendorIndex(): View
    {
        $user = Auth::user();

        // Get vendor - check if user is owner or vendor employee
        $vendor = Vendor::where('owner_id', $user->id)->first();

        if (!$vendor) {
            // Check if user is a vendor employee
            $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($vendorUser) {
                $vendor = $vendorUser->vendor;
            }
        }

        if (!$vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $status = request()->get('status', 'all');

        $query = VariantRequest::where('vendor_id', $vendor->id)
            ->with('reviewer');

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        }

        $requests = $query->latest()->paginate(15);

        // Get counts for filter tabs
        $counts = [
            'all' => VariantRequest::where('vendor_id', $vendor->id)->count(),
            'pending' => VariantRequest::where('vendor_id', $vendor->id)->pending()->count(),
            'approved' => VariantRequest::where('vendor_id', $vendor->id)->approved()->count(),
            'rejected' => VariantRequest::where('vendor_id', $vendor->id)->rejected()->count(),
        ];

        return view('vendor.variant-requests.index', compact('requests', 'status', 'vendor', 'counts'));
    }

    /**
     * Store a new variant request
     */
    public function store(VariantRequestStoreRequest $request): RedirectResponse
    {
        try {
            // Get the vendor for the authenticated user
            $user = Auth::user();

            // Get vendor - check if user is owner or vendor employee
            $vendor = Vendor::where('owner_id', $user->id)->first();

            if (!$vendor) {
                // Check if user is a vendor employee
                $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($vendorUser) {
                    $vendor = $vendorUser->vendor;
                }
            }

            if (!$vendor) {
                return redirect()->back()
                    ->with('error', __('Vendor account not found. Please contact administrator.'));
            }

            VariantRequest::create([
                'vendor_id' => $vendor->id,
                'name' => $request->name,
                'options' => $request->options ?? [],
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return redirect()->back()
                ->with('success', __('Variant request submitted successfully. Admin will review it soon.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to submit variant request: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    /**
     * Approve a variant request
     */
    public function approve(VariantRequestApproveRequest $request, VariantRequest $variantRequest): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $variantRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            // If admin wants to create variant immediately, redirect to create page with pre-filled data
            if ($request->boolean('create_variant')) {
                $name_en = $variantRequest->getTranslation('name', 'en');
                $name_ar = $variantRequest->getTranslation('name', 'ar');

                return redirect()->route('admin.variants.create')
                    ->with('variant_request_data', [
                        'name_en' => $name_en,
                        'name_ar' => $name_ar,
                        'options' => $variantRequest->options ?? [],
                        'description' => $variantRequest->description,
                    ])
                    ->with('success', __('Variant request approved. Please complete the variant creation form.'));
            }

            return redirect()->back()
                ->with('success', __('Variant request approved successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('Failed to approve variant request: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Reject a variant request
     */
    public function reject(VariantRequestApproveRequest $request, VariantRequest $variantRequest): RedirectResponse
    {
        try {
            $variantRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes ?? __('Request rejected by admin.'),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', __('Variant request rejected successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to reject variant request: :error', ['error' => $e->getMessage()]));
        }
    }
}
