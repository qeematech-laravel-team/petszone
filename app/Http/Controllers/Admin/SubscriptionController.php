<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorSubscription;
use App\Services\SubscriptionService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;
    protected VendorService $vendorService;
    public function __construct(SubscriptionService $service, VendorService $vendorService)
    {
        $this->service = $service;
        $this->vendorService = $vendorService;
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'vendor_id' => $request->get('vendor_id', ''),
        ];
        $vendor_id = $request->get('vendor_id', '');

        $subscriptions = $this->service->getPaginatedSubscriptions(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.subscriptions.partials.table', compact('subscriptions'))->render(),
                'pagination' => view('admin.subscriptions.partials.pagination', compact('subscriptions'))->render(),
            ]);
        }
        $vendors = $this->vendorService->getAllVendors();
        return view('admin.subscriptions.index', compact('subscriptions', 'filters', 'vendors', 'vendor_id'));
    }

    public function show(VendorSubscription $subscription): View
    {
        $subscription = $this->service->getSubscriptionById($subscription->id);

        return view('admin.subscriptions.show', compact('subscription'));
    }
}
