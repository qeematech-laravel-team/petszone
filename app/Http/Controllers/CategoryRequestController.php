<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequestApproveRequest;
use App\Http\Requests\CategoryRequestStoreRequest;
use App\Models\Category;
use App\Models\CategoryRequest;
use App\Models\Vendor;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryRequestController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of category requests (Admin)
     */
    public function index(): View
    {
        $status = request()->get('status', 'all');

        $query = CategoryRequest::with(['vendor', 'reviewer']);

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
            'all' => CategoryRequest::count(),
            'pending' => CategoryRequest::pending()->count(),
            'approved' => CategoryRequest::approved()->count(),
            'rejected' => CategoryRequest::rejected()->count(),
        ];

        return view('admin.category-requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Display a listing of vendor's category requests
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

        $query = CategoryRequest::where('vendor_id', $vendor->id)
            ->with('reviewer');

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        }

        $requests = $query->latest()->paginate(15);

        return view('vendor.category-requests.index', compact('requests', 'status', 'vendor'));
    }

    /**
     * Store a new category request
     */
    public function store(CategoryRequestStoreRequest $request): RedirectResponse
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

            CategoryRequest::create([
                'vendor_id' => $vendor->id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return redirect()->back()
                ->with('success', __('Category request submitted successfully. Admin will review it soon.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to submit category request: :error', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    /**
     * Approve a category request
     */
    public function approve(CategoryRequestApproveRequest $request, CategoryRequest $categoryRequest): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $categoryRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            // If admin wants to create category immediately, redirect to create page with pre-filled data
            if ($request->boolean('create_category')) {
                // Extract name translations from the category request (already cast as array)
                $name_en = $categoryRequest->getTranslation('name', 'en');
                $name_ar = $categoryRequest->getTranslation('name', 'ar');
                return redirect()->route('admin.categories.create')
                    ->with('category_request_data', [
                        'name_en' => $name_en,
                        'name_ar' => $name_ar,
                        'description' => $categoryRequest->description,
                    ])
                    ->with('success', __('Category request approved. Please complete the category creation form.'));
            }

            return redirect()->back()
                ->with('success', __('Category request approved successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('Failed to approve category request: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Reject a category request
     */
    public function reject(CategoryRequestApproveRequest $request, CategoryRequest $categoryRequest): RedirectResponse
    {
        try {
            $categoryRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes ?? __('Request rejected by admin.'),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', __('Category request rejected successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to reject category request: :error', ['error' => $e->getMessage()]));
        }
    }
}
