<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class OrderController extends Controller
{
    protected OrderService $service;

    protected BranchService $branchService;

    public function __construct(OrderService $service, BranchService $branchService)
    {
        $this->service = $service;
        $this->branchService = $branchService;
    }

    /**
     * Display a listing of vendor orders
     */
    public function index(Request $request): View
    {
        // $vendorUser = currentVendorUser();
        $user = Auth::user();
        $vendorUser = $user?->vendor();

        if (! $vendorUser) {
            abort(403, __('You are not associated with a vendor.'));
        }

        $vendorId = $vendorUser->id;
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
            'branch_id' => $request->get('branch_id', ''),
            'order_id' => $request->get('order_id', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_total' => $request->get('min_total', ''),
            'max_total' => $request->get('max_total', ''),
            'payment_status' => (string) $request->get('payment_status', ''),
            'payment_method' => (string) $request->get('payment_method', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        // Filter by branch if user is branch user
        $branch = currentBranch();
        if ($branch) {
            $filters['branch_id'] = $branch->id;
        }

        $vendorOrders = $this->service->getPaginatedVendorOrdersForVendor($vendorId, $perPage, $filters);
        $branches = $this->branchService->getBranchesByVendor($vendorId);

        return view('vendor.orders.index', compact('vendorOrders', 'branches'));
    }

    /**
     * Display the specified vendor order
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $vendorUser = $user?->vendor();

        if (! $vendorUser) {
            abort(403, __('You are not associated with a vendor.'));
        }

        $vendorId = $vendorUser->id;
        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendorId);

        if (! $vendorOrder) {
            abort(404, __('Vendor order not found.'));
        }

        // Check authorization for branch users
        $branch = currentBranch();
        if ($branch && $vendorOrder->branch_id !== $branch->id) {
            abort(403, __('You do not have permission to view this order.'));
        }

        $vendorOrder->loadMissing([
            'logs.user',
        ]);

        return view('vendor.orders.show', compact('vendorOrder'));
    }

    /**
     * Vendor order invoice.
     */
    public function invoice(int $id)
    {
        $user = Auth::user();
        $vendorUser = $user?->vendor();

        if (! $vendorUser) {
            abort(403, __('You are not associated with a vendor.'));
        }

        $vendorId = $vendorUser->id;
        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendorId);

        if (! $vendorOrder) {
            abort(404, __('Vendor order not found.'));
        }

        // Check authorization for branch users
        $branch = currentBranch();
        if ($branch && $vendorOrder->branch_id !== $branch->id) {
            abort(403, __('You do not have permission to view this order.'));
        }

        $pdf = PDF::loadView('vendor.orders.invoice', [
            'vendorOrder' => $vendorOrder,
            'asPdf' => true,
        ]);

        return $pdf->stream('invoice-'.$vendorOrder->id.'.pdf');
    }

    /**
     * Update vendor order
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $vendorUser = $user?->vendor();

        if (! $vendorUser) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('You are not associated with a vendor.'));
        }

        $vendorId = $vendorUser->id;
        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendorId);

        if (! $vendorOrder) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('Vendor order not found.'));
        }

        // Check authorization for branch users
        $branch = currentBranch();
        if ($branch && $vendorOrder->branch_id !== $branch->id) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('You do not have permission to update this order.'));
        }

        $request->validate([
            'status' => ['nullable', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = $request->only(['status', 'notes']);

        $this->service->updateVendorOrder($vendorOrder, $data);

        return redirect()->route('vendor.orders.show', $id)
            ->with('success', __('Vendor order updated successfully.'));
    }

    /**
     * Update vendor order status
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $user = Auth::user();
        $vendorUser = $user?->vendor();

        if (! $vendorUser) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('You are not associated with a vendor.'));
        }

        $vendorId = $vendorUser->id;
        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendorId);

        if (! $vendorOrder) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('Vendor order not found.'));
        }

        // Check authorization for branch users
        $branch = currentBranch();
        if ($branch && $vendorOrder->branch_id !== $branch->id) {
            return redirect()->route('vendor.orders.index')
                ->with('error', __('You do not have permission to update this order.'));
        }

        $this->service->updateVendorOrderStatus($vendorOrder, $request->status);

        return redirect()->route('vendor.orders.show', $id)
            ->with('success', __('Vendor order status updated successfully.'));
    }
}
