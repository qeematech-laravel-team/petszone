<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CategoryRequest;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\VendorWithdrawal;
use App\Services\CategoryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display the vendor dashboard
     */
    public function index(): View
    {
        // Get vendor information
        $user = Auth::user();
        $vendor = Vendor::where('owner_id', $user->id)->first();

        if (! $vendor) {
            abort(404, 'Vendor not found');
        }

        $now = CarbonImmutable::now();
        $today = $now->startOfDay();
        $thisMonth = $now->startOfMonth();
        $thisYear = $now->startOfYear();

        // Overall Stats
        $totalProducts = Product::query()->where('vendor_id', $vendor->id)->count();
        $activeProducts = Product::query()->where('vendor_id', $vendor->id)->where('is_active', true)->where('is_approved', true)->count();
        $pendingProducts = Product::query()->where('vendor_id', $vendor->id)->where('is_approved', false)->where('is_active', true)->count();

        // Today Stats
        $todayOrders = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            })
            ->count();
        $todayRevenue = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('created_at', $today)->where('payment_status', 'paid');
            })
            ->sum('total');

        // This Month Stats
        $monthOrders = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth);
            })
            ->count();
        $monthRevenue = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth)->where('payment_status', 'paid');
            })
            ->sum('total');
        $monthCommission = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth)->where('payment_status', 'paid');
            })
            ->sum('commission');
        $monthDelivered = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth)->where('status', 'delivered')->where('payment_status', 'paid');
            })
            ->count();

        $profitType = (string) setting('profit_type');
        $monthNetEarnings = $profitType === 'commission' ? max(0, $monthRevenue - $monthCommission) : $monthRevenue;

        // This Year Stats
        $yearRevenue = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisYear) {
                $q->where('created_at', '>=', $thisYear)->where('payment_status', 'paid');
            })
            ->sum('total');
        $yearCommission = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($thisYear) {
                $q->where('created_at', '>=', $thisYear)->where('payment_status', 'paid');
            })
            ->sum('commission');
        $yearNetEarnings = $profitType === 'commission' ? max(0, $yearRevenue - $yearCommission) : $yearRevenue;

        // Pending Items
        $pendingOrders = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) {
                $q->where('status', 'pending');
            })
            ->count();
        $pendingWithdrawals = VendorWithdrawal::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->count();
        $pendingWithdrawalsTotal = (float) VendorWithdrawal::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Recent Orders
        $recentOrders = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->with(['order.user', 'order.vendorOrders.vendor'])
            ->latest()
            ->take(10)
            ->get();

        // Top Products (This Month)
        $topProductsRaw = \Illuminate\Support\Facades\DB::table('vendor_order_items')
            ->join('vendor_orders', 'vendor_order_items.vendor_order_id', '=', 'vendor_orders.id')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->where('vendor_orders.vendor_id', '=', $vendor->id)
            ->where('orders.payment_status', '=', 'paid')
            ->where('orders.created_at', '>=', $thisMonth)
            ->selectRaw('vendor_order_items.product_id as product_id, SUM(vendor_order_items.quantity) as quantity, SUM(vendor_order_items.total) as revenue')
            ->groupBy('vendor_order_items.product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $productIds = $topProductsRaw->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $productsById = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $topProducts = $topProductsRaw->map(function ($row) use ($productsById) {
            $product = $productsById->get((int) $row->product_id);

            return [
                'product' => $product,
                'product_id' => (int) $row->product_id,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ];
        });

        // Category Requests
        $categoryRequests = CategoryRequest::where('vendor_id', $vendor->id)
            ->with('reviewer')
            ->latest()
            ->take(5)
            ->get();

        // Get active categories count
        $categories = $this->categoryService->getActiveCategories();

        return view('vendor.dashboard', compact(
            'vendor',
            'totalProducts',
            'activeProducts',
            'pendingProducts',
            'todayOrders',
            'todayRevenue',
            'monthOrders',
            'monthRevenue',
            'monthCommission',
            'monthNetEarnings',
            'monthDelivered',
            'yearRevenue',
            'yearCommission',
            'yearNetEarnings',
            'pendingOrders',
            'pendingWithdrawals',
            'pendingWithdrawalsTotal',
            'recentOrders',
            'topProducts',
            'categoryRequests',
            'categories'
        ));
    }
}
