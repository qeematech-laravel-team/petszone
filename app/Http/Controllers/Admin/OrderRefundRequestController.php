<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderRefundRequest;
use App\Services\OrderRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderRefundRequestController extends Controller
{
    public function __construct(protected OrderRefundService $service) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'status' => (string) $request->get('status', ''),
            'order_id' => $request->get('order_id', ''),
            'user_id' => $request->get('user_id', ''),
        ];

        $requests = $this->service->listForAdmin($perPage, $filters);

        return view('admin.order_refunds.index', compact('requests', 'filters'));
    }

    public function approve(OrderRefundRequest $orderRefundRequest): RedirectResponse
    {
        try {
            $this->service->approve($orderRefundRequest);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Refund request approved and order refunded.'));
    }

    public function reject(OrderRefundRequest $orderRefundRequest, Request $request): RedirectResponse
    {
        $reason = (string) $request->get('reason', '');

        $this->service->reject($orderRefundRequest, $reason ?: null);

        return back()->with('success', __('Refund request rejected.'));
    }
}
