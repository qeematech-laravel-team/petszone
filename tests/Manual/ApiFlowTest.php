<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete user registration and order flow.
     */
    public function test_complete_user_order_flow(): void
    {
        // Step 1: Register user
        $registerResponse = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Step 2: Verify email (mock - in real test you'd use actual verification code)
        // For testing, you might need to manually verify or use a test verification code

        // Step 3: Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');
        $this->assertNotNull($token);

        // Step 4: Get products
        $productsResponse = $this->withToken($token)
            ->getJson('/api/products?per_page=5');

        $productsResponse->assertStatus(200);
        $this->assertArrayHasKey('data', $productsResponse->json());

        // Step 5: Add product to cart (if products exist)
        $products = $productsResponse->json('data.data');
        if (! empty($products)) {
            $productId = $products[0]['id'];

            $cartResponse = $this->withToken($token)
                ->postJson("/api/cart/{$productId}");

            $cartResponse->assertStatus(200);
        }

        // Step 6: Get cart
        $cartResponse = $this->withToken($token)
            ->getJson('/api/cart');

        $cartResponse->assertStatus(200);
    }

    /**
     * Test product listing with filters.
     */
    public function test_product_listing_with_filters(): void
    {
        // Create test data
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Test without filters
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'price'],
                ],
            ],
        ]);

        // Test with search filter
        $response = $this->getJson('/api/products?search='.$product->sku);
        $response->assertStatus(200);

        // Test with vendor filter
        $response = $this->getJson("/api/products?vendor_id={$vendor->id}");
        $response->assertStatus(200);

        // Test with price filter
        $response = $this->getJson('/api/products?min_price=0&max_price=1000');
        $response->assertStatus(200);
    }

    /**
     * Test rate limiting on login endpoint.
     */
    public function test_rate_limiting_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Try to login 6 times (limit is 5)
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

            if ($i <= 5) {
                $response->assertStatus(422); // Validation error
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test authentication required for protected routes.
     */
    public function test_authentication_required(): void
    {
        // Try to access protected route without token
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);

        // Try with invalid token
        $response = $this->withToken('invalid_token')
            ->getJson('/api/orders');
        $response->assertStatus(401);
    }

    /**
     * Test cart operations.
     */
    public function test_cart_operations(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Add to cart
        $response = $this->withToken($token)
            ->postJson("/api/cart/{$product->id}");
        $response->assertStatus(200);

        // Update cart quantity
        $response = $this->withToken($token)
            ->putJson("/api/cart/{$product->id}", [
                'quantity' => 2,
            ]);
        $response->assertStatus(200);

        // Get cart
        $response = $this->withToken($token)
            ->getJson('/api/cart');
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());

        // Remove from cart
        $response = $this->withToken($token)
            ->deleteJson("/api/cart/{$product->id}");
        $response->assertStatus(200);
    }

    /**
     * Test shipping calculation endpoint.
     */
    public function test_shipping_calculation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create address for user
        $address = $user->addresses()->create([
            'name' => 'Test Address',
            'phone' => '+1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        // Calculate shipping
        $response = $this->withToken($token)
            ->postJson('/api/orders/calculate-shipping', [
                'address_id' => $address->id,
            ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());
    }
}
