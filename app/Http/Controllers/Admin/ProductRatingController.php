<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\View\View;

class ProductRatingController extends Controller
{
    public function index(): View
    {
        $ratings = ProductRating::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.product_ratings', compact('ratings'));
    }

    public function toggleVisibility(ProductRating $productRating)
    {
        $productRating->is_visible = ! $productRating->is_visible;
        $productRating->save();

        return redirect()->back()->with('success', __('Rating visibility updated.'));
    }
}
