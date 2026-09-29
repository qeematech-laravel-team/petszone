<?php

namespace Tests\Feature;

use App\Http\Resources\ProductResource;
use App\Http\Resources\VendorResource;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Repositories\VendorSubscriptionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EagerLoadingPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_and_price_helpers_use_eager_loaded_relations(): void
    {
        $vendor = Vendor::factory()->create();
        $branch = Branch::query()->create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Main Branch'],
            'address' => 'Cairo',
            'is_active' => true,
        ]);

        $simpleProduct = Product::query()->create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Simple Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'SKU-SIMPLE-1',
            'slug' => 'simple-product-1',
            'price' => 50,
        ]);
        BranchProductStock::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $simpleProduct->id,
            'quantity' => 5,
        ]);

        $variableProduct = Product::query()->create([
            'vendor_id' => $vendor->id,
            'type' => 'variable',
            'name' => ['en' => 'Variable Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'SKU-VARIABLE-1',
            'slug' => 'variable-product-1',
            'price' => 0,
        ]);

        $variantSmall = ProductVariant::query()->create([
            'product_id' => $variableProduct->id,
            'name' => ['en' => 'Small'],
            'sku' => 'SKU-VAR-S',
            'slug' => 'variable-product-1-small',
            'price' => 10,
        ]);
        $variantLarge = ProductVariant::query()->create([
            'product_id' => $variableProduct->id,
            'name' => ['en' => 'Large'],
            'sku' => 'SKU-VAR-L',
            'slug' => 'variable-product-1-large',
            'price' => 20,
        ]);

        BranchProductVariantStock::query()->create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variantSmall->id,
            'quantity' => 3,
        ]);
        BranchProductVariantStock::query()->create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variantLarge->id,
            'quantity' => 0,
        ]);

        $products = Product::query()
            ->with(['branchProductStocks', 'variants.branchVariantStocks'])
            ->orderBy('id')
            ->get();

        [$loadedSimple, $loadedVariable] = [$products->firstWhere('type', 'simple'), $products->firstWhere('type', 'variable')];

        DB::enableQueryLog();

        $this->assertTrue($loadedSimple->isInStock());
        $this->assertSame(5, $loadedSimple->manager()->stock());
        $this->assertEquals(50, (float) $loadedSimple->manager()->price());

        $this->assertTrue($loadedVariable->isInStock());
        $this->assertSame(3, $loadedVariable->manager()->stock());
        $this->assertEquals(10, (float) $loadedVariable->manager()->price());
        $this->assertSame(3, $loadedVariable->variants->firstWhere('sku', 'SKU-VAR-S')->total_stock);
        $this->assertTrue($loadedVariable->variants->firstWhere('sku', 'SKU-VAR-S')->hasStock());
        $this->assertFalse($loadedVariable->variants->firstWhere('sku', 'SKU-VAR-L')->hasStock());

        $this->assertCount(0, DB::getQueryLog(), 'Stock/price helpers must not run queries when relations are eager loaded.');

        DB::disableQueryLog();
    }

    public function test_stock_and_price_helpers_still_work_without_eager_loading(): void
    {
        $vendor = Vendor::factory()->create();
        $branch = Branch::query()->create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Main Branch'],
            'address' => 'Cairo',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Simple Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'SKU-SIMPLE-2',
            'slug' => 'simple-product-2',
            'price' => 75,
        ]);
        BranchProductStock::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 8,
        ]);

        $freshProduct = Product::query()->findOrFail($product->id);

        $this->assertTrue($freshProduct->isInStock());
        $this->assertSame(8, $freshProduct->manager()->stock());
        $this->assertEquals(75, (float) $freshProduct->manager()->price());
    }

    public function test_subscription_lists_eager_load_vendor_and_plan(): void
    {
        $vendor = Vendor::factory()->create();
        $plan = Plan::query()->create([
            'name' => ['en' => 'Gold'],
            'slug' => 'gold-plan',
            'description' => ['en' => 'Gold plan'],
            'price' => 100,
            'duration_days' => 30,
        ]);
        VendorSubscription::query()->create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'price' => 100,
            'status' => 'active',
        ]);

        $repository = app(VendorSubscriptionRepository::class);

        $adminList = $repository->getPaginatedSubscriptions();
        $this->assertTrue($adminList->first()->relationLoaded('vendor'));
        $this->assertTrue($adminList->first()->relationLoaded('plan'));

        $vendorList = $repository->getSubscriptionByVendorId($vendor->id);
        $this->assertTrue($vendorList->first()->relationLoaded('plan'));
    }

    public function test_api_product_list_eager_loads_avoid_extra_queries_during_serialization(): void
    {
        $vendor = Vendor::factory()->create();
        $branch = Branch::query()->create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Main Branch'],
            'address' => 'Cairo',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'API Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'SKU-API-1',
            'slug' => 'api-product-1',
            'price' => 40,
            'is_active' => true,
            'is_approved' => true,
        ]);
        BranchProductStock::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);
        ProductImage::query()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
            'path' => 'products/test.jpg',
        ]);

        $products = Product::constrainFavoriteExists(
            Product::query()->with(Product::apiListRelations())
        )->get();

        DB::enableQueryLog();

        ProductResource::collection($products)->resolve();

        $this->assertLessThanOrEqual(
            0,
            count(DB::getQueryLog()),
            'Serializing an eager-loaded product list must not trigger additional queries.'
        );

        DB::disableQueryLog();
    }

    public function test_vendor_resource_uses_rating_aggregates_without_extra_queries(): void
    {
        $vendor = Vendor::constrainVisibleRatingAggregates(
            Vendor::query()->whereKey(Vendor::factory()->create()->id)
        )->firstOrFail();

        DB::enableQueryLog();

        (new VendorResource($vendor))->resolve();

        $this->assertCount(0, DB::getQueryLog(), 'VendorResource must use preloaded rating aggregates.');

        DB::disableQueryLog();
    }
}
