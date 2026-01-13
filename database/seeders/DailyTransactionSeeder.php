<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DailyTransactionSeeder extends Seeder
{
    /**
     * Seed income and expense transactions for the last 60 days.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $user = User::first();
        if (!$user) {
            $this->command->error('No user found. Please run UserSeeder first.');
            return;
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            $this->command->error('No wallet found for the user. Please run DatabaseSeeder first.');
            return;
        }

        $incomeCategories = Category::where('type', 'income')
            ->where('name', '!=', 'wallet transfer')
            ->pluck('id')
            ->toArray();

        $expenseCategories = Category::where('type', 'expense')
            ->pluck('id')
            ->toArray();

        if (empty($incomeCategories) || empty($expenseCategories)) {
            $this->command->error('No categories found. Please run CategorySeeder first.');
            return;
        }

        $incomeNotes = [
            'Salary payment',
            'Freelance project',
            'Investment dividend',
            'Side gig payment',
            'Bonus received',
            'Refund received',
            'Gift money',
            'Cashback reward',
        ];

        $expenseNotes = [
            'Grocery shopping',
            'Restaurant dinner',
            'Gas station',
            'Online shopping',
            'Utility bill',
            'Coffee shop',
            'Subscription payment',
            'Medical expense',
            'Transportation fare',
            'Home supplies',
        ];

        $today = Carbon::today();

        for ($i = 0; $i < 60; $i++) {
            $date = $today->copy()->subDays($i);

            // Create income transaction
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'income',
                'amount' => $faker->randomFloat(2, 50, 500),
                'note' => $faker->randomElement($incomeNotes),
                'category_id' => $faker->randomElement($incomeCategories),
                'transaction_date' => $date,
            ]);

            // Create expense transaction
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'expense',
                'amount' => $faker->randomFloat(2, 10, 200),
                'note' => $faker->randomElement($expenseNotes),
                'category_id' => $faker->randomElement($expenseCategories),
                'transaction_date' => $date,
            ]);
        }

        $this->command->info('Successfully seeded 120 transactions (60 income + 60 expense) for the last 60 days.');
    }
}
