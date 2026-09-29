<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    public function index(): View
    {
        $vendor = Auth::user()->vendor();

        $reports = ProductReport::with(['product', 'user', 'handler'])
            ->whereHas('product', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id ?? 0);
            })
            ->latest()
            ->paginate(20);

        return view('vendor.reports.product_reports', compact('reports'));
    }

    public function updateStatus(ProductReport $productReport, string $status): RedirectResponse
    {
        $vendor = Auth::user()->vendor();

        if (! $vendor || $productReport->product?->vendor_id !== $vendor->id) {
            return redirect()->back()->with('error', __('You do not have permission to update this report.'));
        }

        $allowed = ['pending', 'reviewed', 'ignored'];

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', __('Invalid status.'));
        }

        $productReport->status = $status;
        $productReport->handled_by = Auth::id();
        $productReport->handled_at = now();
        $productReport->save();

        return redirect()->back()->with('success', __('Report status updated.'));
    }
}
