<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    public function index(): View
    {
        $reports = ProductReport::with(['product', 'user', 'handler'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.product_reports', compact('reports'));
    }

    public function updateStatus(ProductReport $productReport, string $status): RedirectResponse
    {
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
