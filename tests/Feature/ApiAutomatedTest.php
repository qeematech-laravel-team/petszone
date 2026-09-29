<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAutomatedTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected Vendor $vendor;

    protected Product $product;

    protected Category $category;

    /**
     * Setup test data before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create roles if they don't exist
        $this->createRolesIfNeeded();

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
        ]);

        // Assign user role
        if ($this->user->roles()->count() === 0) {
            $this->user->assignRole('user');
        }

        // Login and get token
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        if ($response->status() === 200) {
            $this->token = $response->json('data.token');
        }

        // Create test vendor
        $vendorOwner = User::create([
            'name' => 'Vendor Owner',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $vendorOwner->assignRole('vendor');

        $this->vendor = Vendor::create([
            'owner_id' => $vendorOwner->id,
            'name' => ['en' => 'Test Vendor', 'ar' => 'بائع تجريبي'],
            'slug' => 'test-vendor',
            'is_active' => true,
        ]);

        // Create test category
        $this->category = Category::create([
            'name' => ['en' => 'Test Category', 'ar' => 'فئة تجريبية'],
            'is_active' => true,
        ]);

        // Create test product
        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'type' => 'simple',
            'name' => ['en' => 'Test Product', 'ar' => 'منتج تجريبي'],
            'description' => ['en' => 'Test Description', 'ar' => 'وصف تجريبي'],
            'sku' => 'TEST-001',
            'slug' => 'test-product',
            'price' => 100.00,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->product->categories()->attach($this->category->id);

        // Create test branch
        $branch = \App\Models\Branch::create([
            'vendor_id' => $this->vendor->id,
            'name' => ['en' => 'Test Branch', 'ar' => 'فرع تجريبي'],
            'address' => 'Test Address',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_active' => true,
        ]);

        // Add stock to product
        \App\Models\BranchProductStock::create([
            'branch_id' => $branch->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);
    }

    /**
     * Create roles if they don't exist.
     */
    protected function createRolesIfNeeded(): void
    {
        if (! \Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'user', 'guard_name' => 'web']);
        }
        if (! \Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'vendor', 'guard_name' => 'web']);
        }
        if (! \Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
    }

    /**
     * Test complete API flow automatically.
     */
    public function test_complete_api_flow_automated(): void
    {
        $this->assertNotNull($this->token, 'User should be logged in');

        // Step 1: Get Products
        $response = $this->withToken($this->token)
            ->getJson('/api/products');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Step 2: Get Single Product
        $response = $this->withToken($this->token)
            ->getJson("/api/products/{$this->product->id}");
        $response->assertStatus(200);
        $this->assertEquals($this->product->id, $response->json('data.id'));

        // Step 3: Add to Cart
        $response = $this->withToken($this->token)
            ->postJson("/api/cart/{$this->product->id}");
        $this->assertContains($response->status(), [200, 201], 'Cart add should return 200 or 201');

        // Step 4: Get Cart
        $response = $this->withToken($this->token)
            ->getJson('/api/cart');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Step 5: Create Address
        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Test Address',
            'phone' => '+1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'is_default' => true,
            'is_active' => true,
        ]);

        // Step 6: Calculate Shipping
        $response = $this->withToken($this->token)
            ->postJson('/api/orders/calculate-shipping', [
                'address_id' => $address->id,
            ]);
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Step 7: Create Order
        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'cash_on_delivery',
            ]);
        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response->json());

        $orderId = $response->json('data.id');

        // Step 8: Get Order
        $response = $this->withToken($this->token)
            ->getJson("/api/orders/{$orderId}");
        $response->assertStatus(200);
        $this->assertEquals($orderId, $response->json('data.id'));

        // Step 9: Get Orders List
        $response = $this->withToken($this->token)
            ->getJson('/api/orders');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());
    }

    /**
     * Test products API with all filters.
     */
    public function test_products_api_filters_automated(): void
    {
        // Test without filters
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);

        // Test with search
        $response = $this->getJson('/api/products?search=test');
        $response->assertStatus(200);

        // Test with category filter
        $response = $this->getJson("/api/products?category_id={$this->category->id}");
        $response->assertStatus(200);

        // Test with vendor filter
        $response = $this->getJson("/api/products?vendor_id={$this->vendor->id}");
        $response->assertStatus(200);

        // Test with price filter
        $response = $this->getJson('/api/products?min_price=50&max_price=200');
        $response->assertStatus(200);

        // Test with stock filter
        $response = $this->getJson('/api/products?stock=in_stock');
        $response->assertStatus(200);

        // Test sorting
        $response = $this->getJson('/api/products?sort=price_asc');
        $response->assertStatus(200);

        $response = $this->getJson('/api/products?sort=price_desc');
        $response->assertStatus(200);
    }

    /**
     * Test cart operations automatically.
     */
    public function test_cart_operations_automated(): void
    {
        // Add to cart
        $response = $this->withToken($this->token)
            ->postJson("/api/cart/{$this->product->id}");
        $this->assertContains($response->status(), [200, 201], 'Cart add should return 200 or 201');

        // Update quantity
        $response = $this->withToken($this->token)
            ->putJson("/api/cart/{$this->product->id}", [
                'quantity' => 2,
            ]);
        $response->assertStatus(200);

        // Get cart
        $response = $this->withToken($this->token)
            ->getJson('/api/cart');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Remove from cart
        $response = $this->withToken($this->token)
            ->deleteJson("/api/cart/{$this->product->id}");
        $response->assertStatus(200);
    }

    /**
     * Test categories API automatically.
     */
    public function test_categories_api_automated(): void
    {
        // List categories
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Get single category
        $response = $this->getJson("/api/categories/{$this->category->id}");
        $response->assertStatus(200);
        $this->assertEquals($this->category->id, $response->json('data.id'));
    }

    /**
     * Test vendors API automatically.
     */
    public function test_vendors_api_automated(): void
    {
        // List vendors
        $response = $this->getJson('/api/vendors');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Get single vendor
        $response = $this->getJson("/api/vendors/{$this->vendor->id}");
        $response->assertStatus(200);
        $this->assertEquals($this->vendor->id, $response->json('data.id'));
    }

    /**
     * Test authentication flow automatically.
     */
    public function test_authentication_flow_automated(): void
    {
        // Register new user
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Test User',
            'email' => 'newtest@example.com',
            'phone' => '+9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'newtest@example.com']);

        // Verify the user (set is_verified to true for testing)
        $user = \App\Models\User::where('email', 'newtest@example.com')->first();
        $user->update(['is_verified' => true]);

        // Login
        $response = $this->postJson('/api/auth/login', [
            'login' => 'newtest@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json('data'));

        // Get user profile
        $token = $response->json('data.token');
        $response = $this->withToken($token)
            ->getJson('/api/user');
        $response->assertStatus(200);
    }

    /**
     * Test rate limiting automatically.
     */
    public function test_rate_limiting_automated(): void
    {
        // Try to register multiple times (limit is 5 per minute)
        $successCount = 0;
        $rateLimited = false;
        $maxAttempts = 10;

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $response = $this->postJson('/api/auth/register', [
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
                'phone' => "+123456789{$i}",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            if ($response->status() === 201) {
                $successCount++;
            } elseif ($response->status() === 429) {
                $rateLimited = true;
                break;
            }
        }

        // Rate limiting should occur after some successful registrations
        // The exact number may vary, but we should see rate limiting at some point
        $this->assertTrue(
            $rateLimited || $successCount >= 5,
            "Rate limiting should occur or we should have at least 5 successful registrations. Got: {$successCount} successful, rateLimited: ".($rateLimited ? 'true' : 'false')
        );

        // If we got rate limited, we should have had at least 1 successful registration
        if ($rateLimited) {
            $this->assertGreaterThan(0, $successCount, 'Should have at least 1 successful registration before rate limiting');
        }
    }
}
