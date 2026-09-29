<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Settings\UpdateRequest;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Get the vendor for the authenticated user
     */
    protected function getVendor(): ?Vendor
    {
        return Auth::user()->vendor();
    }

    /**
     * Display the vendor settings page
     */
    public function index(): View
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        // Get all vendor settings grouped by category
        $settings = VendorSetting::where('vendor_id', $vendor->id)
            ->get()
            ->keyBy('key');

        // Default settings structure
        $defaultSettings = $this->getDefaultSettings();

        // Merge defaults with existing settings
        foreach ($defaultSettings as $category => $categorySettings) {
            foreach ($categorySettings as $key => $default) {
                if (! $settings->has($key)) {
                    $settings[$key] = (object) [
                        'key' => $key,
                        'value' => $default['value'],
                        'type' => $default['type'],
                    ];
                }
            }
        }

        return view('vendor.settings.index', compact('vendor', 'settings', 'defaultSettings'));
    }

    /**
     * Update vendor settings
     */
    public function update(UpdateRequest $request): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        try {
            $data = $request->validated();

            foreach ($data as $key => $value) {
                // Skip non-setting fields
                if (in_array($key, ['_token', '_method'])) {
                    continue;
                }

                // Get setting type from defaults
                $defaultSettings = $this->getDefaultSettings();
                $type = 'string';

                foreach ($defaultSettings as $categorySettings) {
                    if (isset($categorySettings[$key])) {
                        $type = $categorySettings[$key]['type'];
                        break;
                    }
                }

                // Handle boolean values
                // For checkboxes, hidden input sends "0" when unchecked, checkbox sends "1" when checked
                // The last value in the request will be the checkbox value if checked, or "0" from hidden input if unchecked
                if ($type === 'boolean') {
                    // Convert string "0" or 0 to false, string "1" or 1 to true
                    $value = ($value === '1' || $value === 1 || $value === true) ? true : false;
                }

                // Update or create setting
                VendorSetting::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'key' => $key,
                    ],
                    [
                        'value' => $value,
                        'type' => $type,
                    ]
                );
            }

            // Clear vendor settings cache
            Cache::forget("vendor_settings:{$vendor->id}");

            return redirect()->route('vendor.settings.index')
                ->with('success', __('Settings updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to update settings: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Get default settings structure
     */
    protected function getDefaultSettings(): array
    {
        return [
            'branch' => [
                'allow_branch_user_to_edit_stock' => [
                    'value' => false,
                    'type' => 'boolean',
                    'label' => __('Allow Branch Users to Edit Stock'),
                    'description' => __('Enable this to allow branch users to edit product stock in their branch.'),
                ],
                'allow_branch_user_to_edit_price' => [
                    'value' => false,
                    'type' => 'boolean',
                    'label' => __('Allow Branch Users to Edit Price'),
                    'description' => __('Enable this to allow branch users to edit product price in their branch.'),
                ],
            ],
            'inventory' => [
                'enable_inventory_alerts' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => __('Enable Inventory Alerts'),
                    'description' => __('Send email and database notifications when stock becomes low or out of stock.'),
                ],
                'low_stock_threshold' => [
                    'value' => 10,
                    'type' => 'number',
                    'label' => __('Low Stock Threshold'),
                    'description' => __('Trigger low-stock alerts when quantity is less than or equal to this number.'),
                ],
            ],
            'shipping' => [
                'allow_free_shipping_threshold' => [
                    'value' => false,
                    'type' => 'boolean',
                    'label' => __('Allow Free Shipping Threshold'),
                    'description' => __('Enable free shipping when order amount reaches the threshold.'),
                ],
                'free_shipping_threshold' => [
                    'value' => 0,
                    'type' => 'number',
                    'label' => __('Minimum Order Amount for Free Shipping'),
                    'description' => __('Minimum order amount required for free shipping.'),
                ],
                'shipping_cost_per_km' => [
                    'value' => 0,
                    'type' => 'number',
                    'label' => __('Shipping Cost Per KM'),
                    'description' => __('Cost per kilometer for shipping calculation.'),
                ],
                'minimum_shipping_cost' => [
                    'value' => 0,
                    'type' => 'number',
                    'label' => __('Minimum Shipping Cost'),
                    'description' => __('Minimum shipping cost regardless of distance.'),
                ],
                'maximum_shipping_cost' => [
                    'value' => 0,
                    'type' => 'number',
                    'label' => __('Maximum Shipping Cost'),
                    'description' => __('Maximum shipping cost cap (0 = no limit).'),
                ],
            ],
        ];
    }
}
