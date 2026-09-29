<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorRatingController extends Controller
{
    public function index(): View
    {
        $ratings = VendorRating::with(['vendor', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.vendor_ratings', compact('ratings'));
    }

    public function toggleVisibility(VendorRating $vendorRating): RedirectResponse
    {
        $vendorRating->is_visible = ! $vendorRating->is_visible;
        $vendorRating->save();

        return redirect()->back()->with('success', __('Rating visibility updated.'));
    }
}
