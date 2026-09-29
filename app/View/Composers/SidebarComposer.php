<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Cache vendor data per request to avoid duplicate queries
     * Made public static so helper functions can access it
     */
    public static ?array $cachedData = null;

    /**
     * Get or initialize vendor data
     * This method can be called early to populate the cache before views render
     */
    public static function getVendorData(): array
    {
        // Return cached data if already queried in this request
        if (self::$cachedData !== null) {
            return self::$cachedData;
        }

        $vendorUser = null;
        $isBranchUser = false;
        $vendor = null;

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('vendor') || $user->hasRole('vendor_employee')) {
                // Query once and get both vendorUser and vendor
                $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->with(['branch', 'vendor'])
                    ->first();

                if ($vendorUser) {
                    $isBranchUser = $vendorUser->user_type === 'branch';
                    $vendor = $vendorUser->vendor;

                    // Cache the vendor in the user model to avoid duplicate queries
                    if ($vendor && ! isset($user->cachedVendor)) {
                        $user->cachedVendor = $vendor;
                    }
                } else {
                    $user->loadMissing('ownedVendor');

                    if ($user->ownedVendor) {
                        $vendor = $user->ownedVendor;
                        if (! isset($user->cachedVendor)) {
                            $user->cachedVendor = $vendor;
                        }
                    }
                }
            }
        }

        // Cache the data for this request
        // Use different variable names to avoid conflicts with controller variables
        self::$cachedData = [
            'currentVendorUser' => $vendorUser,
            'isBranchUser' => $isBranchUser,
            'currentVendor' => $vendor,
        ];

        return self::$cachedData;
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Get vendor data (will use cache if already loaded)
        $data = self::getVendorData();
        $view->with($data);
    }
}
