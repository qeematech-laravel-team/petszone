<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VendorUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(403, __('Unauthorized.'));
        }

        $user = Auth::user();

        // Admin can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user is vendor owner
        if ($user->hasRole('vendor') && $user->ownedVendor) {
            return $next($request);
        }

        // Check if user is a vendor employee (vendor_employee role)
        if ($user->hasRole('vendor_employee')) {
            $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($vendorUser && $vendorUser->vendor && $vendorUser->vendor->is_active) {
                // If user is a branch user, restrict access to branch-specific routes
                if ($vendorUser->user_type === 'branch') {
                    // Allow access to branch dashboard and branch-specific routes
                    // Block access to full vendor dashboard
                    if ($request->routeIs('vendor.dashboard')) {
                        abort(403, __('Branch users can only access branch dashboard.'));
                    }
                }

                return $next($request);
            }
        }

        // Fallback: Check if user is a vendor user (for backward compatibility)
        $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $vendorUser) {
            abort(403, __('You do not have access to vendor area.'));
        }

        // Check if vendor is active
        if (! $vendorUser->vendor || ! $vendorUser->vendor->is_active) {
            abort(403, __('Your vendor account is not active.'));
        }

        // If user is a branch user, restrict access to branch-specific routes
        if ($vendorUser->user_type === 'branch') {
            // Allow access to branch dashboard and branch-specific routes
            // Block access to full vendor dashboard
            if ($request->routeIs('vendor.dashboard')) {
                abort(403, __('Branch users can only access branch dashboard.'));
            }
        }

        return $next($request);
    }
}
