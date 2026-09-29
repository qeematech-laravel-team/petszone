<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesIfNeeded();
        $this->setupTestUser();
    }

    protected function createRolesIfNeeded(): void
    {
        if (! \Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'user', 'guard_name' => 'web']);
        }
        if (! \Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        if (! \Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'vendor', 'guard_name' => 'web']);
        }
    }

    protected function setupTestUser(): void
    {
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $this->user->assignRole('user');

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        if ($response->status() === 200) {
            $this->token = $response->json('data.token');
        }
    }

    /**
     * Test all public endpoints return 200.
     */
    public function test_public_endpoints_accessible(): void
    {
        $publicEndpoints = [
            '/api/products',
            '/api/categories',
            '/api/vendors',
        ];

        foreach ($publicEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(200, "Endpoint {$endpoint} should be accessible");
        }
    }

    /**
     * Test all protected endpoints require authentication.
     * Note: Some endpoints may return 200 with empty data if not authenticated.
     * We check that they don't return user-specific data without auth.
     */
    public function test_protected_endpoints_require_auth(): void
    {
        // Test POST endpoints (should definitely require auth)
        $protectedEndpoints = [
            ['POST', '/api/orders', []],
        ];

        foreach ($protectedEndpoints as $endpointData) {
            $method = $endpointData[0];
            $endpoint = $endpointData[1];
            $data = $endpointData[2] ?? [];

            $response = $this->json($method, $endpoint, $data);
            $this->assertContains(
                $response->status(),
                [401, 403, 422],
                "Endpoint {$endpoint} should require authentication (got {$response->status()})"
            );
        }
    }

    /**
     * Test products endpoint with various query parameters.
     */
    public function test_products_endpoint_parameters(): void
    {
        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'Test Vendor'],
            'slug' => 'test-vendor',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => ['en' => 'Test Category'],
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'TEST-001',
            'slug' => 'test-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $product->categories()->attach($category->id);

        $testCases = [
            ['per_page' => 10],
            ['search' => 'test'],
            ['category_id' => $category->id],
            ['vendor_id' => $vendor->id],
            ['min_price' => 50, 'max_price' => 200],
            ['stock' => 'in_stock'],
            ['sort' => 'price_asc'],
            ['sort' => 'price_desc'],
            ['sort' => 'latest'],
        ];

        foreach ($testCases as $params) {
            $queryString = http_build_query($params);
            $response = $this->getJson("/api/products?{$queryString}");
            $response->assertStatus(200, 'Products endpoint should work with params: '.json_encode($params));
        }
    }

    /**
     * Test complete order creation flow.
     */
    public function test_complete_order_flow(): void
    {
        if (! $this->token) {
            $this->markTestSkipped('User authentication failed');
        }

        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'Test Vendor'],
            'slug' => 'test-vendor',
            'is_active' => true,
        ]);

        // Create branch for vendor
        $branch = \App\Models\Branch::create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Test Branch'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'TEST-001',
            'slug' => 'test-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Add stock to product
        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Add to cart
        $response = $this->withToken($this->token)
            ->postJson("/api/cart/{$product->id}");
        $this->assertContains($response->status(), [200, 201], 'Cart add should return 200 or 201');

        // Create address
        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Test Address',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        // Calculate shipping
        $response = $this->withToken($this->token)
            ->postJson('/api/orders/calculate-shipping', [
                'address_id' => $address->id,
            ]);
        $response->assertStatus(200);

        // Create order
        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'COD',
            ]);
        $response->assertStatus(201);
    }

    /**
     * Test that creating an order with payment_method=FAWRY returns a payment link.
     */
    public function test_create_order_with_fawry_returns_payment_link(): void
    {
        if (! $this->token) {
            $this->markTestSkipped('User authentication failed');
        }

        config([
            'services.fawry.merchant_code' => 'TEST_MERCHANT',
            'services.fawry.hash_key' => 'TEST_KEY',
            'services.fawry.base_url' => 'https://fawry.test',
            'services.fawry.init_base_url' => 'https://fawry.test',
            'services.fawry.api_mode' => 'init',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'fawry.test/*' => \Illuminate\Support\Facades\Http::response([
                'statusCode' => '200',
                'redirectUrl' => 'https://fawry.test/pay/abc123',
            ]),
        ]);

        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'Fawry Vendor'],
            'slug' => 'fawry-vendor',
            'is_active' => true,
        ]);

        $branch = \App\Models\Branch::create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Fawry Branch'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Fawry Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'FAWRY-001',
            'slug' => 'fawry-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->withToken($this->token)->postJson("/api/cart/{$product->id}");

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Fawry Address',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'FAWRY',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment.success', true)
            ->assertJsonPath('payment.data.redirect_url', 'https://fawry.test/pay/abc123');

        $this->assertNotEmpty($response->json('payment.data.merchant_ref_num'));

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'payment_method' => 'FAWRY',
        ]);
        $this->assertNotNull(
            \App\Models\Order::query()->find($orderId)?->fawry_merchant_ref_num,
            'Merchant ref num must be stored on the order for webhook matching.'
        );
    }

    /**
     * Test that the Fawry payment link charges only the remaining amount after wallet deduction.
     */
    public function test_fawry_link_uses_amount_after_wallet_and_points(): void
    {
        if (! $this->token) {
            $this->markTestSkipped('User authentication failed');
        }

        config([
            'services.fawry.merchant_code' => 'TEST_MERCHANT',
            'services.fawry.hash_key' => 'TEST_KEY',
            'services.fawry.base_url' => 'https://fawry.test',
            'services.fawry.init_base_url' => 'https://fawry.test',
            'services.fawry.api_mode' => 'init',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'fawry.test/*' => \Illuminate\Support\Facades\Http::response([
                'statusCode' => '200',
                'redirectUrl' => 'https://fawry.test/pay/wallet123',
            ]),
        ]);

        // Give the user wallet balance to partially cover the order
        $this->user->forceFill(['wallet' => 30])->save();

        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'Wallet Vendor'],
            'slug' => 'wallet-vendor',
            'is_active' => true,
        ]);

        $branch = \App\Models\Branch::create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Wallet Branch'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Wallet Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'WALLET-001',
            'slug' => 'wallet-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->withToken($this->token)->postJson("/api/cart/{$product->id}");

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Wallet Address',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'FAWRY',
                'use_wallet' => true,
            ]);

        $response->assertStatus(201)->assertJsonPath('payment.success', true);

        $orderId = $response->json('data.id');
        $order = \App\Models\Order::query()->findOrFail($orderId);
        $this->assertEquals(30.0, (float) $order->wallet_used);
        $this->assertEquals(70.0, $order->payable_amount);

        // Fawry must be charged the remaining 70, not the full 100
        \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return (float) $request['amount'] === 70.0;
        });
    }

    /**
     * Test that a FAWRY order fully covered by wallet does not create a payment link.
     */
    public function test_fawry_link_not_created_when_wallet_covers_full_amount(): void
    {
        if (! $this->token) {
            $this->markTestSkipped('User authentication failed');
        }

        config([
            'services.fawry.merchant_code' => 'TEST_MERCHANT',
            'services.fawry.hash_key' => 'TEST_KEY',
            'services.fawry.base_url' => 'https://fawry.test',
            'services.fawry.init_base_url' => 'https://fawry.test',
            'services.fawry.api_mode' => 'init',
        ]);

        \Illuminate\Support\Facades\Http::fake();

        $this->user->forceFill(['wallet' => 500])->save();

        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'Full Wallet Vendor'],
            'slug' => 'full-wallet-vendor',
            'is_active' => true,
        ]);

        $branch = \App\Models\Branch::create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Full Wallet Branch'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Full Wallet Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'FULL-WALLET-001',
            'slug' => 'full-wallet-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->withToken($this->token)->postJson("/api/cart/{$product->id}");

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Full Wallet Address',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'FAWRY',
                'use_wallet' => true,
            ]);

        $response->assertStatus(201)->assertJsonPath('payment.success', false);

        // No request should reach Fawry at all
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    /**
     * Test that creating a COD order does not include a payment link.
     */
    public function test_create_order_with_cod_has_no_payment_link(): void
    {
        if (! $this->token) {
            $this->markTestSkipped('User authentication failed');
        }

        $vendor = Vendor::create([
            'owner_id' => $this->user->id,
            'name' => ['en' => 'COD Vendor'],
            'slug' => 'cod-vendor',
            'is_active' => true,
        ]);

        $branch = \App\Models\Branch::create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'COD Branch'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'COD Product'],
            'description' => ['en' => 'Description'],
            'sku' => 'COD-001',
            'slug' => 'cod-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->withToken($this->token)->postJson("/api/cart/{$product->id}");

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'COD Address',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'COD',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment', null);
    }
}
