<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reports\IndexRequest;
use App\Http\Requests\Admin\Reports\ProductPerformanceRequest;
use App\Http\Requests\Admin\Reports\VendorPerformanceRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\ReportingService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected ReportingService $reportingService) {}

    public function index(IndexRequest $request): View
    {
        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->adminReport($from, $to);

        return view('admin.reports.index', [
            'report' => $report,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
            ],
        ]);
    }

    public function earnings(IndexRequest $request): View
    {
        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->adminReport($from, $to);

        return view('admin.reports.earnings', [
            'report' => $report,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
            ],
        ]);
    }

    public function productPerformance(ProductPerformanceRequest $request): View
    {
        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->productPerformanceAdmin($from, $to, [
            'product_id' => $request->input('product_id'),
            'category_id' => $request->input('category_id'),
            'vendor_id' => $request->input('vendor_id'),
            'payment_status' => $request->input('payment_status'),
            'order_status' => $request->input('order_status'),
        ]);

        $products = Product::query()
            ->select(['id', 'name'])
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $categories = Category::query()
            ->active()
            ->root()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('id');
            }])
            ->orderBy('id')
            ->get();

        $vendors = Vendor::query()
            ->where('is_active', '=', true, 'and')
            ->select(['id', 'name'])
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return view('admin.reports.product_performance', [
            'report' => $report,
            'products' => $products,
            'categories' => $categories,
            'vendors' => $vendors,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'product_id' => $request->input('product_id'),
                'category_id' => $request->input('category_id'),
                'vendor_id' => $request->input('vendor_id'),
                'payment_status' => $request->input('payment_status'),
                'order_status' => $request->input('order_status'),
            ],
        ]);
    }

    public function vendorPerformance(VendorPerformanceRequest $request): View
    {
        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->vendorPerformanceAdmin($from, $to, [
            'vendor_id' => $request->input('vendor_id'),
            'payment_status' => $request->input('payment_status'),
            'order_status' => $request->input('order_status'),
        ]);

        $vendors = Vendor::query()
            ->where('is_active', '=', true, 'and')
            ->select(['id', 'name'])
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return view('admin.reports.vendor_performance', [
            'report' => $report,
            'vendors' => $vendors,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'vendor_id' => $request->input('vendor_id'),
                'payment_status' => $request->input('payment_status'),
                'order_status' => $request->input('order_status'),
            ],
        ]);
    }
}
