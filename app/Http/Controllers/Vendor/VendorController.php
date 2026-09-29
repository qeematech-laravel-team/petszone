<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Vendors\RegisterRequest;
use App\Models\Plan;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    protected VendorService $service;

    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    public function edit(): View
    {
        $user = auth()->user();
        $vendor = $user->vendor();
        return view('vendor.profile.edit', compact('user', 'vendor'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'    => 'required|array',
            'name.*'  => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
        ]);

        $vendor = $request->user()->vendor();
        $vendor->update($request->all());

        if ($request->hasFile('image')) {
            $vendor->image = $request->file('image')->store('vendors', 'public');
            $vendor->save();
        }

        return redirect()->route('vendor.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Display the vendor registration form
     */
    public function showRegistrationForm(): View
    {
        $plans = Plan::active()->get();
        return view('vendors.register', compact('plans'));
    }

    /**
     * Handle vendor registration (Self-registration)
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $vendor = $this->service->registerVendor($request);

            // If user was created, log them in
            if (!auth()->check() && isset($vendor->owner)) {
                auth()->login($vendor->owner);
            }

            return redirect()->route('vendors.show', $vendor)
                ->with('success', 'Vendor registration submitted successfully. Please wait for admin approval.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to register vendor: '.$e->getMessage());
        }
    }

}
