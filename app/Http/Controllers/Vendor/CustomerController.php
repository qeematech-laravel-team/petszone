<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    public function index(Request $request): View
    {
        $vendor = Auth::user()->vendor();
        $perPage = (int) $request->get('per_page', 15);
        $search = trim((string) $request->get('search', ''));

        $filters = [
            'search' => $search,
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_orders_count' => $request->get('min_orders_count', ''),
            'max_orders_count' => $request->get('max_orders_count', ''),
            'min_orders_total' => $request->get('min_orders_total', ''),
            'max_orders_total' => $request->get('max_orders_total', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $customers = $this->customerService->getPaginatedCustomersForVendor(
            $vendor->id ?? 0,
            $perPage,
            $filters
        );

        return view('vendor.customers.index', compact('customers', 'search', 'filters'));
    }

    public function show(User $user): View
    {
        $vendor = Auth::user()->vendor();

        [$customer, $orders] = $this->customerService->getCustomerWithOrdersForVendor($user->id, $vendor->id ?? 0, 15);

        return view('vendor.customers.show', [
            'customer' => $customer ?? $user,
            'orders' => $orders,
        ]);
    }
}
