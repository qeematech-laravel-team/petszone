<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorWithdrawal;
use App\Services\VendorWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorWithdrawalController extends Controller
{
    public function __construct(protected VendorWithdrawalService $service) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'status' => (string) $request->get('status', ''),
            'vendor_id' => $request->get('vendor_id', ''),
        ];

        $withdrawals = $this->service->listForAdmin($perPage, $filters);

        return view('admin.vendor_withdrawals.index', compact('withdrawals', 'filters'));
    }

    public function approve(VendorWithdrawal $vendorWithdrawal): RedirectResponse
    {
        $result = $this->service->approve($vendorWithdrawal->id, request()->user());

        if (! $result) {
            return back()->with('error', __('Unable to approve this withdrawal.'));
        }

        return back()->with('success', __('Withdrawal approved and balance updated.'));
    }

    public function reject(VendorWithdrawal $vendorWithdrawal, Request $request): RedirectResponse
    {
        $notes = (string) $request->get('notes', '');

        $result = $this->service->reject($vendorWithdrawal->id, $request->user(), $notes ?: null);

        if (! $result) {
            return back()->with('error', __('Unable to reject this withdrawal.'));
        }

        return back()->with('success', __('Withdrawal rejected.'));
    }
}
