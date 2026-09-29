<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();

        return view('admin.settings.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
            'app_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
            'profit_type' => 'required|string|in:subscription,commission',
            'profit_value' => 'required_if:profit_type,commission|nullable|numeric|min:0',
            'referral_points' => 'nullable|numeric|min:0',
            'cache_back_points_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            // Update app_name
            $setting = Setting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name, 'type' => 'string']
            );
            // Clear cache and store in Redis
            $this->updateCache('app_name', $setting->value);

            // Update profit_type
            $setting = Setting::updateOrCreate(
                ['key' => 'profit_type'],
                ['value' => $request->profit_type, 'type' => 'string']
            );
            $this->updateCache('profit_type', $setting->value);

            // Update profit_value
            $setting = Setting::updateOrCreate(
                ['key' => 'profit_value'],
                ['value' => $request->profit_value ?? 0, 'type' => 'number']
            );
            $this->updateCache('profit_value', $setting->value);

            // Update referral_points
            $setting = Setting::updateOrCreate(
                ['key' => 'referral_points'],
                ['value' => $request->referral_points ?? 0, 'type' => 'number']
            );
            $this->updateCache('referral_points', $setting->value);

            // Update cache_back_points_rate
            $setting = Setting::updateOrCreate(
                ['key' => 'cache_back_points_rate'],
                ['value' => $request->cache_back_points_rate ?? 0, 'type' => 'number']
            );
            $this->updateCache('cache_back_points_rate', $setting->value);

            // Handle app_logo upload
            if ($request->hasFile('app_logo')) {
                $logoPath = $request->file('app_logo')->store('settings', 'public');
                $setting = Setting::updateOrCreate(
                    ['key' => 'app_logo'],
                    ['value' => $logoPath, 'type' => 'image']
                );
                $this->updateCache('app_logo', $setting->value);
            }

            // Handle app_icon upload
            if ($request->hasFile('app_icon')) {
                $iconPath = $request->file('app_icon')->store('settings', 'public');
                $setting = Setting::updateOrCreate(
                    ['key' => 'app_icon'],
                    ['value' => $iconPath, 'type' => 'image']
                );
                $this->updateCache('app_icon', $setting->value);
            }

            // Clear the all settings cache
            Cache::forget('settings:all');

            return back()->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Update cache for a specific setting key
     *
     * @param  mixed  $value
     */
    private function updateCache(string $key, $value): void
    {
        $cacheKey = "setting:{$key}";

        // Store in cache with 1 hour expiration (or you can set it to forever)
        // Using Redis if configured, otherwise uses default cache driver
        Cache::put($cacheKey, $value, now()->addHours(24));
    }
}
