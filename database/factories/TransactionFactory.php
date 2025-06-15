<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'note' => $this->faker->sentence(),
            'category_id' => Category::first()->id,
            'transaction_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Define an income transaction
     */
    public function income()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'income',
            ];
        });
    }

    /**
     * Define an expense transaction
     */
    public function expense()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'expense',
            ];
        });
    }

    /**
     * Define a transaction with a specific amount
     */
    public function withAmount($amount)
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                'amount' => $amount,
            ];
        });
    }
}
