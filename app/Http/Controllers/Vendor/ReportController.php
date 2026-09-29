<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Reports\IndexRequest;
use App\Http\Requests\Vendor\Reports\ProductPerformanceRequest;
use App\Http\Requests\Vendor\Reports\VendorPerformanceRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ReportingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected ReportingService $reportingService) {}

    public function index(IndexRequest $request): View
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->vendorReport($vendor->id, $from, $to);

        return view('vendor.reports.index', [
            'vendor' => $vendor,
            'report' => $report,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
            ],
        ]);
    }

    public function earnings(IndexRequest $request): View
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->vendorReport($vendor->id, $from, $to);

        return view('vendor.reports.earnings', [
            'vendor' => $vendor,
            'report' => $report,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
            ],
        ]);
    }

    public function productPerformance(ProductPerformanceRequest $request): View
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->productPerformanceVendor($vendor->id, $from, $to, [
            'product_id' => $request->input('product_id'),
            'category_id' => $request->input('category_id'),
            'payment_status' => $request->input('payment_status'),
            'order_status' => $request->input('order_status'),
        ]);

        $products = Product::query()
            ->select(['id', 'name'])
            ->where('vendor_id', '=', $vendor->id, 'and')
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

        return view('vendor.reports.product_performance', [
            'vendor' => $vendor,
            'report' => $report,
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'product_id' => $request->input('product_id'),
                'category_id' => $request->input('category_id'),
                'payment_status' => $request->input('payment_status'),
                'order_status' => $request->input('order_status'),
            ],
        ]);
    }

    public function vendorPerformance(VendorPerformanceRequest $request): View
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $from = $request->input('from_date')
            ? CarbonImmutable::parse($request->input('from_date'))->startOfDay()
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $to = $request->input('to_date')
            ? CarbonImmutable::parse($request->input('to_date'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $report = $this->reportingService->vendorPerformanceVendor($vendor->id, $from, $to, [
            'payment_status' => $request->input('payment_status'),
            'order_status' => $request->input('order_status'),
        ]);

        return view('vendor.reports.vendor_performance', [
            'vendor' => $vendor,
            'report' => $report,
            'filters' => [
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'payment_status' => $request->input('payment_status'),
                'order_status' => $request->input('order_status'),
            ],
        ]);
    }
}
