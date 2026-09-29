<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Notifications\InventoryAlertNotification;
use App\Services\InventoryAlertService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InventoryAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryAlertService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InventoryAlertService(new NotificationService);
    }

    public function test_check_simple_stock_sends_notification_when_out_of_stock(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $stock = BranchProductStock::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '1',
        ]);

        $this->service->checkSimpleStock($stock, $product);

        Notification::assertSentTo(
            $vendor->owner,
            InventoryAlertNotification::class,
            function ($notification) use ($product) {
                return $notification->product->id === $product->id
                    && $notification->variant === null
                    && $notification->quantity === 0
                    && $notification->level === 'out';
            }
        );
    }

    public function test_check_simple_stock_sends_notification_when_low_stock(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $stock = BranchProductStock::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '1',
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'low_stock_threshold',
            'value' => '10',
        ]);

        $this->service->checkSimpleStock($stock, $product);

        Notification::assertSentTo(
            $vendor->owner,
            InventoryAlertNotification::class,
            function ($notification) use ($product) {
                return $notification->product->id === $product->id
                    && $notification->variant === null
                    && $notification->quantity === 5
                    && $notification->level === 'low';
            }
        );
    }

    public function test_check_simple_stock_does_not_send_when_alerts_disabled(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $stock = BranchProductStock::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '0',
        ]);

        $this->service->checkSimpleStock($stock, $product);

        Notification::assertNothingSent();
    }

    public function test_check_simple_stock_does_not_send_when_stock_adequate(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $stock = BranchProductStock::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '1',
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'low_stock_threshold',
            'value' => '10',
        ]);

        $this->service->checkSimpleStock($stock, $product);

        Notification::assertNothingSent();
    }

    public function test_check_variant_stock_sends_notification_when_out_of_stock(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $stock = BranchProductVariantStock::factory()->create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '1',
        ]);

        $this->service->checkVariantStock($stock, $variant);

        Notification::assertSentTo(
            $vendor->owner,
            InventoryAlertNotification::class,
            function ($notification) use ($product, $variant) {
                return $notification->product->id === $product->id
                    && $notification->variant->id === $variant->id
                    && $notification->quantity === 0
                    && $notification->level === 'out';
            }
        );
    }

    public function test_check_variant_stock_does_not_send_when_alerts_disabled(): void
    {
        Notification::fake();
        Cache::flush();

        $vendor = Vendor::factory()->create();
        $branch = Branch::factory()->create(['vendor_id' => $vendor->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $stock = BranchProductVariantStock::factory()->create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        VendorSetting::factory()->create([
            'vendor_id' => $vendor->id,
            'key' => 'enable_inventory_alerts',
            'value' => '0',
        ]);

        $this->service->checkVariantStock($stock, $variant);

        Notification::assertNothingSent();
    }
}
