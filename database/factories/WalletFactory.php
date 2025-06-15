<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Wallet',
            'type' => $this->faker->randomElement(['cash', 'bank', 'credit']),
            'balance' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'INR', 'GBP']),
            'user_id' => User::factory()
        ];
    }

    /**
     * Define a wallet with zero balance
     */
    public function empty()
    {
        return $this->state(function (array $attributes) {
            return [
                'balance' => 0
            ];
        });
    }

    /**
     * Define a wallet with a specific balance
     */
    public function withBalance($amount)
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                'balance' => $amount
            ];
        });
    }
}
