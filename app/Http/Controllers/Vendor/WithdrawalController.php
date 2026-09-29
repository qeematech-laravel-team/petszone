<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorWithdrawalService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(protected VendorWithdrawalService $service) {}

    public function index(Request $request): View
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $perPage = (int) $request->get('per_page', 15);

        /** @var LengthAwarePaginator $withdrawals */
        $withdrawals = $this->service->listForVendor($vendor->id, $perPage);

        return view('vendor.withdrawals.index', compact('vendor', 'withdrawals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->service->createRequestForVendor(
                $vendor,
                (float) $validated['amount'],
                $validated['method'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Withdrawal request submitted successfully.'));
    }
}
