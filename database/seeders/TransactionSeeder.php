<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $users = User::pluck('id');
        $categories = Category::pluck('id');
        $types = ['expense', 'income'];

        foreach (range(1, 100) as $index) {
            Transaction::create([
                'user_id' => $faker->randomElement($users),
                'type' => $faker->randomElement($types),
                'amount' => $faker->randomFloat(2, 10, 1000),
                'note' => $faker->sentence(3),
                'category_id' => $faker->randomElement($categories),
                'transaction_date' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}
