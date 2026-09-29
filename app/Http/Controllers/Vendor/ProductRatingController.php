<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductRatingController extends Controller
{
    public function index(): View
    {
        $vendor = Auth::user()->vendor();

        $ratings = ProductRating::with(['product', 'user'])
            ->whereHas('product', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id ?? 0);
            })
            ->latest()
            ->paginate(20);

        return view('vendor.reports.product_ratings', compact('ratings'));
    }

    public function toggleVisibility(ProductRating $productRating): RedirectResponse
    {
        $vendor = Auth::user()->vendor();

        if (! $vendor || $productRating->product?->vendor_id !== $vendor->id) {
            return redirect()->back()->with('error', __('You do not have permission to update this rating.'));
        }

        $productRating->is_visible = ! $productRating->is_visible;
        $productRating->save();

        return redirect()->back()->with('success', __('Rating visibility updated.'));
    }
}
