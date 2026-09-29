<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'name' => [
                'en' => fake()->company(),
                'ar' => fake('ar_SA')->company(),
            ],
            'owner_id' => User::factory(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'image' => null,
            'is_active' => true,
            'is_featured' => false,
            'balance' => fake()->randomFloat(2, 0, 10000),
            'commission_rate' => fake()->randomFloat(2, 0, 30),
            'plan_id' => null,
            'subscription_start' => null,
            'subscription_end' => null,
        ];
    }

    public function withOwner($ownerId): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $ownerId,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
