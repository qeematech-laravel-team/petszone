<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorReportController extends Controller
{
    public function index(): View
    {
        $reports = VendorReport::with(['vendor', 'user', 'handler'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.vendor_reports', compact('reports'));
    }

    public function updateStatus(VendorReport $vendorReport, string $status): RedirectResponse
    {
        $allowed = ['pending', 'reviewed', 'ignored'];

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', __('Invalid status.'));
        }

        $vendorReport->status = $status;
        $vendorReport->handled_by = Auth::id();
        $vendorReport->handled_at = now();
        $vendorReport->save();

        return redirect()->back()->with('success', __('Report status updated.'));
    }
}
