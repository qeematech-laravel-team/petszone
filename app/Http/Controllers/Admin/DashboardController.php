<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryRequest;
use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\VendorWithdrawal;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index(): View
    {
        $now = CarbonImmutable::now();
        $today = $now->startOfDay();
        $thisMonth = $now->startOfMonth();
        $thisYear = $now->startOfYear();

        // Overall Stats
        $totalVendors = Vendor::query()->count();
        $activeVendors = Vendor::query()->where('is_active', true)->count();
        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->where('is_active', true)->where('is_approved', true)->count();
        $totalCustomers = User::query()->where('role', 'user')->count();
        $activeCustomers = User::query()->where('role', 'user')->where('is_active', true)->count();

        // Today Stats
        $todayOrders = Order::query()->whereDate('created_at', $today)->count();
        $todayRevenue = (float) Order::query()->whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total');
        $todayCommission = (float) Order::query()->whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_commission');

        // This Month Stats
        $monthOrders = Order::query()->where('created_at', '>=', $thisMonth)->count();
        $monthRevenue = (float) Order::query()->where('created_at', '>=', $thisMonth)->where('payment_status', 'paid')->sum('total');
        $monthCommission = (float) Order::query()->where('created_at', '>=', $thisMonth)->where('payment_status', 'paid')->sum('total_commission');
        $monthDelivered = Order::query()->where('created_at', '>=', $thisMonth)->where('status', 'delivered')->where('payment_status', 'paid')->count();

        // This Year Stats
        $yearRevenue = (float) Order::query()->where('created_at', '>=', $thisYear)->where('payment_status', 'paid')->sum('total');
        $yearCommission = (float) Order::query()->where('created_at', '>=', $thisYear)->where('payment_status', 'paid')->sum('total_commission');

        // Pending Items
        $pendingCategoryRequests = CategoryRequest::query()->pending()->count();
        $pendingVariantRequests = \App\Models\VariantRequest::query()->pending()->count();
        $pendingRefundRequests = OrderRefundRequest::query()->where('status', 'pending')->count();
        $pendingWithdrawals = VendorWithdrawal::query()->where('status', 'pending')->count();
        $pendingOrders = Order::query()->where('status', 'pending')->count();
        $pendingProducts = Product::query()->where('is_approved', false)->where('is_active', true)->count();

        // Recent Orders
        $recentOrders = Order::query()
            ->with(['user', 'vendorOrders.vendor'])
            ->latest()
            ->take(10)
            ->get();

        // Top Vendors (This Month)
        $topVendors = VendorOrder::query()
            ->with('vendor')
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth)
                    ->where('payment_status', 'paid');
            })
            ->selectRaw('vendor_id, SUM(total) as total_sales, SUM(commission) as total_commission, COUNT(*) as orders_count')
            ->groupBy('vendor_id')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get()
            ->map(function ($vo) {
                return [
                    'vendor' => $vo->vendor,
                    'total_sales' => (float) $vo->total_sales,
                    'total_commission' => (float) $vo->total_commission,
                    'orders_count' => (int) $vo->orders_count,
                ];
            });

        // Category Requests
        $pendingRequests = CategoryRequest::with(['vendor', 'reviewer'])
            ->pending()
            ->latest()
            ->take(10)
            ->get();

        $recentRequests = CategoryRequest::with(['vendor', 'reviewer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalVendors',
            'activeVendors',
            'totalProducts',
            'activeProducts',
            'totalCustomers',
            'activeCustomers',
            'todayOrders',
            'todayRevenue',
            'todayCommission',
            'monthOrders',
            'monthRevenue',
            'monthCommission',
            'monthDelivered',
            'yearRevenue',
            'yearCommission',
            'pendingCategoryRequests',
            'pendingVariantRequests',
            'pendingRefundRequests',
            'pendingWithdrawals',
            'pendingOrders',
            'pendingProducts',
            'recentOrders',
            'topVendors',
            'pendingRequests',
            'recentRequests'
        ));
    }
}
