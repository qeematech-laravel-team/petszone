<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\VendorWithdrawal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * @return array{
     *   from: CarbonImmutable,
     *   to: CarbonImmutable,
     *   kpis: array{
     *     paid_orders_count: int,
     *     paid_orders_total: float,
     *     delivered_paid_orders_count: int,
     *     total_commission: float,
     *     refunded_orders_count: int,
     *     refunded_total: float,
     *     pending_refund_requests: int,
     *     pending_withdrawals_count: int,
     *     pending_withdrawals_total: float,
     *   },
     *   top_vendors: Collection<int, array{vendor_id:int, vendor_name:string, gross:float, commission:float, net:float}>,
     *   top_products: Collection<int, array{product_id:int, product_name:string, quantity:int, revenue:float}>,
     *   daily_sales: Collection<int, array{date:string, total:float}>,
     * }
     */
    public function adminReport(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $paidOrdersQuery = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('payment_status', 'paid');

        $paidOrdersCount = (int) (clone $paidOrdersQuery)->count();
        $paidOrdersTotal = (float) (clone $paidOrdersQuery)->sum('total');
        $deliveredPaidOrdersCount = (int) (clone $paidOrdersQuery)->where('status', 'delivered')->count();
        $totalCommission = (float) (clone $paidOrdersQuery)->sum('total_commission');

        $refundedOrdersQuery = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('refund_status', 'refunded');

        $refundedOrdersCount = (int) (clone $refundedOrdersQuery)->count();
        $refundedTotal = (float) (clone $refundedOrdersQuery)->sum('refunded_total');

        $pendingRefundRequests = (int) OrderRefundRequest::query()->where('status', 'pending')->count();

        $pendingWithdrawalsQuery = VendorWithdrawal::query()->where('status', 'pending');
        $pendingWithdrawalsCount = (int) (clone $pendingWithdrawalsQuery)->count();
        $pendingWithdrawalsTotal = (float) (clone $pendingWithdrawalsQuery)->sum('amount');

        $profitType = (string) setting('profit_type');

        $topVendorsRaw = DB::table('vendor_orders')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->where('orders.payment_status', '=', 'paid')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('vendor_orders.vendor_id as vendor_id, SUM(vendor_orders.total) as gross, SUM(vendor_orders.commission) as commission')
            ->groupBy('vendor_orders.vendor_id')
            ->orderByDesc('gross')
            ->limit(10)
            ->get();

        $vendorIds = $topVendorsRaw->pluck('vendor_id')->map(fn ($id) => (int) $id)->all();
        $vendorsById = Vendor::query()->whereIn('id', $vendorIds)->get()->keyBy('id');

        $topVendors = $topVendorsRaw->map(function ($row) use ($vendorsById, $profitType) {
            $vendor = $vendorsById->get((int) $row->vendor_id);
            $vendorName = '-';

            if ($vendor) {
                $name = $vendor->name;
                $vendorName = is_array($name) ? ((string) ($name[app()->getLocale()] ?? $name['en'] ?? reset($name) ?? '')) : (string) $name;
            }

            $gross = (float) $row->gross;
            $commission = (float) $row->commission;
            $net = $profitType === 'commission' ? max(0, $gross - $commission) : $gross;

            return [
                'vendor_id' => (int) $row->vendor_id,
                'vendor_name' => $vendorName,
                'gross' => round($gross, 2),
                'commission' => round($commission, 2),
                'net' => round($net, 2),
            ];
        });

        $topProducts = DB::table('vendor_order_items')
            ->join('vendor_orders', 'vendor_order_items.vendor_order_id', '=', 'vendor_orders.id')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->join('products', 'vendor_order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', '=', 'paid')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('vendor_order_items.product_id as product_id, SUM(vendor_order_items.quantity) as quantity, SUM(vendor_order_items.total) as revenue, products.name as product_name')
            ->groupBy('vendor_order_items.product_id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $name = $row->product_name;
                $productName = is_string($name) ? $name : (string) $name;

                return [
                    'product_id' => (int) $row->product_id,
                    'product_name' => $productName,
                    'quantity' => (int) $row->quantity,
                    'revenue' => round((float) $row->revenue, 2),
                ];
            });

        $dailySales = DB::table('orders')
            ->where('payment_status', '=', 'paid')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => (string) $row->date, 'total' => round((float) $row->total, 2)]);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'paid_orders_count' => $paidOrdersCount,
                'paid_orders_total' => round($paidOrdersTotal, 2),
                'delivered_paid_orders_count' => $deliveredPaidOrdersCount,
                'total_commission' => round($totalCommission, 2),
                'refunded_orders_count' => $refundedOrdersCount,
                'refunded_total' => round($refundedTotal, 2),
                'pending_refund_requests' => $pendingRefundRequests,
                'pending_withdrawals_count' => $pendingWithdrawalsCount,
                'pending_withdrawals_total' => round($pendingWithdrawalsTotal, 2),
            ],
            'top_vendors' => $topVendors,
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
        ];
    }

    /**
     * Product performance (admin, optional vendor/category/product filters).
     *
     * @param  array{
     *   product_id?: int|string|null,
     *   category_id?: int|string|null,
     *   vendor_id?: int|string|null,
     *   payment_status?: string|null,
     *   order_status?: string|null,
     * }  $filters
     * @return array{
     *   from: CarbonImmutable,
     *   to: CarbonImmutable,
     *   kpis: array{
     *     revenue: float,
     *     orders_count: int,
     *     quantity: int,
     *     commission: float,
     *     net: float,
     *     refunded_amount: float,
     *     refunded_orders: int,
     *   },
     *   daily_sales: Collection<int, array{date:string,total:float,qty:int}>,
     * }
     */
    public function productPerformanceAdmin(CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $base = DB::table('vendor_order_items')
            ->join('vendor_orders', 'vendor_order_items.vendor_order_id', '=', 'vendor_orders.id')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->join('products', 'vendor_order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()]);

        if (! empty($filters['product_id'])) {
            $base->where('vendor_order_items.product_id', '=', $filters['product_id']);
        }

        if (! empty($filters['category_id'])) {
            $base->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('category_product')
                    ->whereColumn('category_product.product_id', 'vendor_order_items.product_id')
                    ->where('category_product.category_id', '=', $filters['category_id']);
            });
        }

        if (! empty($filters['vendor_id'])) {
            $base->where('vendor_orders.vendor_id', '=', $filters['vendor_id']);
        }

        if (! empty($filters['payment_status'])) {
            $base->where('orders.payment_status', '=', $filters['payment_status']);
        } else {
            $base->where('orders.payment_status', '=', 'paid');
        }

        if (! empty($filters['order_status'])) {
            $base->where('orders.status', '=', $filters['order_status']);
        }

        $aggregates = (clone $base)
            ->selectRaw('
                SUM(vendor_order_items.total) as revenue,
                SUM(vendor_order_items.quantity) as quantity,
                COUNT(DISTINCT orders.id) as orders_count,
                SUM(
                    CASE
                        WHEN vendor_orders.total > 0 THEN (vendor_order_items.total / vendor_orders.total) * vendor_orders.commission
                        ELSE 0
                    END
                ) as commission
            ')
            ->first();

        $revenue = (float) ($aggregates->revenue ?? 0);
        $quantity = (int) ($aggregates->quantity ?? 0);
        $ordersCount = (int) ($aggregates->orders_count ?? 0);
        $commission = (float) ($aggregates->commission ?? 0);
        $net = max(0, $revenue - $commission);

        $refunded = (clone $base)
            ->where('orders.refund_status', '=', 'refunded')
            ->selectRaw('
                SUM(vendor_order_items.total) as refunded_amount,
                COUNT(DISTINCT orders.id) as refunded_orders
            ')
            ->first();

        $refundedAmount = (float) ($refunded->refunded_amount ?? 0);
        $refundedOrders = (int) ($refunded->refunded_orders ?? 0);

        $dailySales = (clone $base)
            ->selectRaw('DATE(orders.created_at) as date, SUM(vendor_order_items.total) as total, SUM(vendor_order_items.quantity) as qty')
            ->groupByRaw('DATE(orders.created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'total' => round((float) $row->total, 2),
                'qty' => (int) $row->qty,
            ]);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'revenue' => round($revenue, 2),
                'orders_count' => $ordersCount,
                'quantity' => $quantity,
                'commission' => round($commission, 2),
                'net' => round($net, 2),
                'refunded_amount' => round($refundedAmount, 2),
                'refunded_orders' => $refundedOrders,
            ],
            'daily_sales' => $dailySales,
        ];
    }

    /**
     * Product performance (vendor scoped).
     *
     * @param  array{
     *   product_id?: int|string|null,
     *   category_id?: int|string|null,
     *   payment_status?: string|null,
     *   order_status?: string|null,
     * }  $filters
     */
    public function productPerformanceVendor(int $vendorId, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $filters['vendor_id'] = $vendorId;

        $report = $this->productPerformanceAdmin($from, $to, $filters);

        return $report;
    }

    /**
     * Vendor performance (admin scope, optional vendor filter).
     */
    public function vendorPerformanceAdmin(CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $vendorId = $filters['vendor_id'] ?? null;
        $paymentStatus = $filters['payment_status'] ?? 'paid';
        $orderStatus = $filters['order_status'] ?? null;

        $vendorOrdersQuery = VendorOrder::query()
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->whereHas('order', function ($q) use ($from, $to, $paymentStatus, $orderStatus) {
                $q->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                if ($paymentStatus) {
                    $q->where('payment_status', $paymentStatus);
                }
                if ($orderStatus) {
                    $q->where('status', $orderStatus);
                }
            });

        $paidVendorOrdersCount = (int) (clone $vendorOrdersQuery)->count();
        $grossSales = (float) (clone $vendorOrdersQuery)->sum('total');
        $commission = (float) (clone $vendorOrdersQuery)->sum('commission');
        $netEarnings = max(0, $grossSales - $commission);
        $deliveredCount = (int) (clone $vendorOrdersQuery)->where('status', 'delivered')->count();

        $refundedOrdersCount = (int) VendorOrder::query()
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('refund_status', 'refunded')
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
            })
            ->count();

        $pendingWithdrawalsQuery = VendorWithdrawal::query()
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->where('status', 'pending');

        $pendingWithdrawalsCount = (int) (clone $pendingWithdrawalsQuery)->count();
        $pendingWithdrawalsTotal = (float) (clone $pendingWithdrawalsQuery)->sum('amount');

        $approvedWithdrawalsTotal = (float) VendorWithdrawal::query()
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->where('status', 'approved')
            ->whereBetween('processed_at', [$from->startOfDay(), $to->endOfDay()])
            ->sum('amount');

        $currentBalance = null;
        if ($vendorId) {
            $vendor = Vendor::query()->find($vendorId);
            $currentBalance = $vendor?->balance;
        }

        // Calculate percentages only when a specific vendor is selected
        $salesPercentage = null;
        $refundsPercentage = null;
        $totalPlatformSales = null;
        $totalPlatformRefundedCount = null;

        if ($vendorId) {
            // Calculate total platform sales (all vendors) for percentage calculation
            // Use same filters (payment_status, order_status) for fair comparison
            $totalPlatformSales = (float) VendorOrder::query()
                ->whereHas('order', function ($q) use ($from, $to, $paymentStatus, $orderStatus) {
                    $q->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                    if ($paymentStatus) {
                        $q->where('payment_status', $paymentStatus);
                    }
                    if ($orderStatus) {
                        $q->where('status', $orderStatus);
                    }
                })
                ->sum('total');

            // Calculate total platform refunds (all vendors) for percentage calculation
            $totalPlatformRefundedCount = (int) VendorOrder::query()
                ->whereHas('order', function ($q) use ($from, $to) {
                    $q->where('refund_status', 'refunded')
                        ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                })
                ->count();

            // Calculate vendor's percentage of sales
            $salesPercentage = $totalPlatformSales > 0 ? round(($grossSales / $totalPlatformSales) * 100, 2) : 0.0;

            // Calculate vendor's percentage of refunds
            $refundsPercentage = $totalPlatformRefundedCount > 0 ? round(($refundedOrdersCount / $totalPlatformRefundedCount) * 100, 2) : 0.0;
        }

        $dailySales = VendorOrder::query()
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->when($paymentStatus, fn ($q) => $q->where('orders.payment_status', $paymentStatus))
            ->when($orderStatus, fn ($q) => $q->where('orders.status', $orderStatus))
            ->selectRaw('DATE(orders.created_at) as date, SUM(vendor_orders.total) as total')
            ->groupByRaw('DATE(orders.created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => (string) $row->date, 'total' => round((float) $row->total, 2)]);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'gross_sales' => round($grossSales, 2),
                'commission' => round($commission, 2),
                'net_earnings' => round($netEarnings, 2),
                'paid_vendor_orders_count' => $paidVendorOrdersCount,
                'delivered_count' => $deliveredCount,
                'refunded_orders_count' => $refundedOrdersCount,
                'pending_withdrawals_count' => $pendingWithdrawalsCount,
                'pending_withdrawals_total' => round($pendingWithdrawalsTotal, 2),
                'approved_withdrawals_total' => round($approvedWithdrawalsTotal, 2),
                'current_balance' => $currentBalance !== null ? round((float) $currentBalance, 2) : null,
                'sales_percentage' => $salesPercentage,
                'refunds_percentage' => $refundsPercentage,
                'total_platform_sales' => $totalPlatformSales !== null ? round($totalPlatformSales, 2) : null,
                'total_platform_refunded_count' => $totalPlatformRefundedCount,
            ],
            'daily_sales' => $dailySales,
        ];
    }

    /**
     * Vendor performance (vendor scoped).
     */
    public function vendorPerformanceVendor(int $vendorId, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $filters['vendor_id'] = $vendorId;

        return $this->vendorPerformanceAdmin($from, $to, $filters);
    }

    /**
     * @return array{
     *   from: CarbonImmutable,
     *   to: CarbonImmutable,
     *   kpis: array{
     *     paid_vendor_orders_count: int,
     *     gross_sales: float,
     *     commission: float,
     *     net_earnings: float,
     *     delivered_count: int,
     *     refunded_orders_count: int,
     *     pending_withdrawals_count: int,
     *     pending_withdrawals_total: float,
     *     approved_withdrawals_total: float,
     *     current_balance: float,
     *     sales_percentage: float,
     *     refunds_percentage: float,
     *     total_platform_sales: float,
     *     total_platform_refunded_count: int,
     *   },
     *   top_products: Collection<int, array{product_id:int, product_name:string, quantity:int, revenue:float}>,
     *   daily_sales: Collection<int, array{date:string, total:float}>,
     * }
     */
    public function vendorReport(int $vendorId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $profitType = (string) setting('profit_type');

        $vendorOrdersQuery = VendorOrder::query()
            ->where('vendor_id', $vendorId)
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
            });

        $paidVendorOrdersCount = (int) (clone $vendorOrdersQuery)->count();
        $grossSales = (float) (clone $vendorOrdersQuery)->sum('total');
        $commission = (float) (clone $vendorOrdersQuery)->sum('commission');
        $netEarnings = $profitType === 'commission' ? max(0, $grossSales - $commission) : $grossSales;
        $deliveredCount = (int) (clone $vendorOrdersQuery)->where('status', 'delivered')->count();

        $refundedOrdersCount = (int) VendorOrder::query()
            ->where('vendor_id', $vendorId)
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('refund_status', 'refunded')
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
            })
            ->count();

        $pendingWithdrawalsQuery = VendorWithdrawal::query()
            ->where('vendor_id', $vendorId)
            ->where('status', 'pending');

        $pendingWithdrawalsCount = (int) (clone $pendingWithdrawalsQuery)->count();
        $pendingWithdrawalsTotal = (float) (clone $pendingWithdrawalsQuery)->sum('amount');

        $approvedWithdrawalsTotal = (float) VendorWithdrawal::query()
            ->where('vendor_id', $vendorId)
            ->where('status', 'approved')
            ->whereBetween('processed_at', [$from->startOfDay(), $to->endOfDay()])
            ->sum('amount');

        $vendor = Vendor::query()->find($vendorId);
        $currentBalance = (float) ($vendor?->balance ?? 0);

        // Calculate total platform sales (all vendors) for percentage calculation
        $totalPlatformSales = (float) VendorOrder::query()
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
            })
            ->sum('total');

        // Calculate total platform refunds (all vendors) for percentage calculation
        $totalPlatformRefundedCount = (int) VendorOrder::query()
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('refund_status', 'refunded')
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
            })
            ->count();

        // Calculate vendor's percentage of sales
        $salesPercentage = $totalPlatformSales > 0 ? round(($grossSales / $totalPlatformSales) * 100, 2) : 0.0;

        // Calculate vendor's percentage of refunds
        $refundsPercentage = $totalPlatformRefundedCount > 0 ? round(($refundedOrdersCount / $totalPlatformRefundedCount) * 100, 2) : 0.0;

        $topProducts = DB::table('vendor_order_items')
            ->join('vendor_orders', 'vendor_order_items.vendor_order_id', '=', 'vendor_orders.id')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->join('products', 'vendor_order_items.product_id', '=', 'products.id')
            ->where('vendor_orders.vendor_id', '=', $vendorId)
            ->where('orders.payment_status', '=', 'paid')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('vendor_order_items.product_id as product_id, SUM(vendor_order_items.quantity) as quantity, SUM(vendor_order_items.total) as revenue, products.name as product_name')
            ->groupBy('vendor_order_items.product_id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $name = $row->product_name;
                $productName = is_string($name) ? $name : (string) $name;

                return [
                    'product_id' => (int) $row->product_id,
                    'product_name' => $productName,
                    'quantity' => (int) $row->quantity,
                    'revenue' => round((float) $row->revenue, 2),
                ];
            });

        $dailySales = DB::table('vendor_orders')
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->where('vendor_orders.vendor_id', '=', $vendorId)
            ->where('orders.payment_status', '=', 'paid')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(orders.created_at) as date, SUM(vendor_orders.total) as total')
            ->groupByRaw('DATE(orders.created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => (string) $row->date, 'total' => round((float) $row->total, 2)]);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'paid_vendor_orders_count' => $paidVendorOrdersCount,
                'gross_sales' => round($grossSales, 2),
                'commission' => round($commission, 2),
                'net_earnings' => round($netEarnings, 2),
                'delivered_count' => $deliveredCount,
                'refunded_orders_count' => $refundedOrdersCount,
                'pending_withdrawals_count' => $pendingWithdrawalsCount,
                'pending_withdrawals_total' => round($pendingWithdrawalsTotal, 2),
                'approved_withdrawals_total' => round($approvedWithdrawalsTotal, 2),
                'current_balance' => round($currentBalance, 2),
                'sales_percentage' => $salesPercentage,
                'refunds_percentage' => $refundsPercentage,
                'total_platform_sales' => round($totalPlatformSales, 2),
                'total_platform_refunded_count' => $totalPlatformRefundedCount,
            ],
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
        ];
    }
}
