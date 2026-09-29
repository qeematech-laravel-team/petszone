<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Product;
use Illuminate\View\View;

class BranchDashboardController extends Controller
{
    /**
     * Get the branch for the authenticated user
     */
    protected function getBranch(): ?Branch
    {
        return currentBranch();
    }

    /**
     * Display the branch dashboard
     */
    public function index(): View
    {
        $branch = $this->getBranch();

        if (! $branch) {
            abort(404, __('Branch not found.'));
        }

        // Get branch products count
        $branchProductsCount = Product::whereHas('branchProductStocks', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->orWhereHas('variants.branchVariantStocks', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->count();

        // Get total stock quantity for this branch
        $totalStock = BranchProductStock::where('branch_id', $branch->id)->sum('quantity')
            + BranchProductVariantStock::where('branch_id', $branch->id)->sum('quantity');

        // Get low stock items (less than 10)
        $lowStockCount = BranchProductStock::where('branch_id', $branch->id)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->count()
            + BranchProductVariantStock::where('branch_id', $branch->id)
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', 10)
                ->count();

        // Get out of stock items
        $outOfStockCount = BranchProductStock::where('branch_id', $branch->id)
            ->where('quantity', '<=', 0)
            ->count()
            + BranchProductVariantStock::where('branch_id', $branch->id)
                ->where('quantity', '<=', 0)
                ->count();

        return view('vendor.branch-dashboard', compact('branch', 'branchProductsCount', 'totalStock', 'lowStockCount', 'outOfStockCount'));
    }
}
