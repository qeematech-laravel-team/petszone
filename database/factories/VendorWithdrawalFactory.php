<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorWithdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorWithdrawal>
 */
class VendorWithdrawalFactory extends Factory
{
    protected $model = VendorWithdrawal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 5000);
        $balanceBefore = fake()->randomFloat(2, $amount, 10000);

        return [
            'vendor_id' => Vendor::factory(),
            'amount' => $amount,
            'status' => 'pending',
            'method' => fake()->randomElement(['bank_transfer', 'paypal', 'stripe', 'cash']),
            'notes' => fake()->optional()->sentence(),
            'processed_by' => null,
            'processed_at' => null,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore,
            'payload' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'];
            $balanceBefore = $attributes['balance_before'] ?? $amount;

            return [
                'status' => 'approved',
                'balance_after' => $balanceBefore - $amount,
                'processed_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'processed_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'notes' => fake()->sentence(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_by' => null,
            'processed_at' => null,
        ]);
    }
}
