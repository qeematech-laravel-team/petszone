<?php

namespace App\Http\Controllers\Vendor;

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
            'status' => $request->get('status', ''),
        ];

        $subscriptions = $this->service->getSubscriptionByVendorId(auth()->user()->ownedVendor->id,$filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('vendor.subscriptions.partials.table', compact('subscriptions'))->render(),
                'pagination' => view('vendor.subscriptions.partials.pagination', compact('subscriptions'))->render(),
            ]);
        }
        return view('vendor.subscriptions.index', compact('subscriptions', 'filters'));
    }

    public function show(VendorSubscription $subscription): View
    {
        $subscription = $this->service->getSubscriptionById($subscription->id);

        return view('vendor.subscriptions.show', compact('subscription'));
    }
}
