<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * @var array<string, string>
     */
    private const ROUTE_PERMISSIONS = [
        'dashboard' => 'view-admin-dashboard',
        'categories' => 'manage-admin-catalog',
        'variants' => 'manage-admin-catalog',
        'products' => 'manage-admin-catalog',
        'product-ratings' => 'manage-admin-catalog',
        'product-reports' => 'manage-admin-catalog',
        'sliders' => 'manage-admin-content',
        'coupons' => 'manage-admin-content',
        'vendors' => 'manage-admin-vendors',
        'branches' => 'manage-admin-vendors',
        'vendor-ratings' => 'manage-admin-vendors',
        'vendor-reports' => 'manage-admin-vendors',
        'customers' => 'manage-admin-customers',
        'orders' => 'manage-admin-orders',
        'order-refund-requests' => 'manage-admin-orders',
        'vendor-withdrawals' => 'manage-admin-finance',
        'plans' => 'manage-admin-subscriptions',
        'subscriptions' => 'manage-admin-subscriptions',
        'category-requests' => 'manage-admin-requests',
        'variant-requests' => 'manage-admin-requests',
        'tickets' => 'manage-admin-support',
        'reports' => 'manage-admin-reports',
        'settings' => 'manage-admin-settings',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, __('Your account is inactive.'));

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        abort_unless($user->hasRole('admin_employee'), 403);

        $routeName = $request->route()?->getName();
        $resource = $routeName ? explode('.', $routeName)[1] ?? null : null;
        $permission = $resource ? self::ROUTE_PERMISSIONS[$resource] ?? null : null;

        abort_unless($permission && $user->hasPermissionTo($permission), 403);

        return $next($request);
    }
}
