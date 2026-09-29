<?php

namespace App\Services;

use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorSetting;
use App\Notifications\InventoryAlertNotification;
use Illuminate\Support\Facades\Cache;

class InventoryAlertService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function checkSimpleStock(BranchProductStock $stock, Product $product): void
    {
        $vendorId = (int) $product->vendor_id;

        if (! $this->inventoryAlertsEnabled($vendorId)) {
            return;
        }

        $threshold = $this->lowStockThreshold($vendorId);

        $level = $stock->quantity <= 0 ? 'out' : ($stock->isLowStock($threshold) ? 'low' : null);
        if (! $level) {
            return;
        }

        if (! $this->shouldSend($level, "simple:{$stock->id}", (int) $stock->quantity)) {
            return;
        }

        $notification = new InventoryAlertNotification(
            branchId: (int) $stock->branch_id,
            vendorId: $vendorId,
            product: $product,
            variant: null,
            quantity: (int) $stock->quantity,
            threshold: $threshold,
            level: $level
        );

        $this->notificationService->notifyVendorUsers($vendorId, $notification);
        $this->notificationService->notifyAdmins($notification);
    }

    public function checkVariantStock(BranchProductVariantStock $stock, ProductVariant $variant): void
    {
        $product = $variant->product ?? $variant->product()->first();
        $vendorId = (int) ($product?->vendor_id ?? 0);

        if (! $vendorId) {
            return;
        }

        if (! $this->inventoryAlertsEnabled($vendorId)) {
            return;
        }

        $threshold = $this->lowStockThreshold($vendorId);

        $level = $stock->quantity <= 0 ? 'out' : ($stock->isLowStock($threshold) ? 'low' : null);
        if (! $level) {
            return;
        }

        if (! $this->shouldSend($level, "variant:{$stock->id}", (int) $stock->quantity)) {
            return;
        }

        $notification = new InventoryAlertNotification(
            branchId: (int) $stock->branch_id,
            vendorId: $vendorId,
            product: $product,
            variant: $variant,
            quantity: (int) $stock->quantity,
            threshold: $threshold,
            level: $level
        );

        $this->notificationService->notifyVendorUsers($vendorId, $notification);
        $this->notificationService->notifyAdmins($notification);
    }

    protected function lowStockThreshold(int $vendorId): int
    {
        $vendorThreshold = $this->vendorSetting($vendorId, 'low_stock_threshold');
        if (is_numeric($vendorThreshold)) {
            return max(0, (int) $vendorThreshold);
        }

        $global = setting('low_stock_threshold', 10);

        return is_numeric($global) ? max(0, (int) $global) : 10;
    }

    protected function inventoryAlertsEnabled(int $vendorId): bool
    {
        $enabled = $this->vendorSetting($vendorId, 'enable_inventory_alerts');

        if ($enabled === null) {
            return true;
        }

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    protected function vendorSetting(int $vendorId, string $key): mixed
    {
        $cacheKey = "vendor_settings:{$vendorId}";

        $settings = Cache::remember($cacheKey, 3600, function () use ($vendorId) {
            return VendorSetting::query()
                ->where('vendor_id', $vendorId)
                ->get()
                ->keyBy('key')
                ->map(fn (VendorSetting $setting) => $setting->value)
                ->toArray();
        });

        return $settings[$key] ?? null;
    }

    protected function shouldSend(string $level, string $stockKey, int $quantity): bool
    {
        $key = "inventory_alert_sent:{$level}:{$stockKey}:{$quantity}";

        return Cache::add($key, true, now()->addHours(6));
    }
}
