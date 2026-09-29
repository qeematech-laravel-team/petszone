<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User|null $user */
            $user = Auth::user();

            if ($user && ($user->hasRole('admin') || $user->hasRole('admin_employee'))) {
                return redirect()->route(adminHomeRoute());
            }

            if ($user && ($user->hasRole('vendor') || $user->hasRole('vendor_employee'))) {
                return redirect()->route(vendorHomeRoute());
            }
        }

        return view('landing.index');
    }

    public function features(): View
    {
        return view('landing.features');
    }

    public function pricing(): View
    {
        $plans = Plan::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('price')
            ->get();

        return view('landing.pricing', [
            'plans' => $plans,
        ]);
    }
}
