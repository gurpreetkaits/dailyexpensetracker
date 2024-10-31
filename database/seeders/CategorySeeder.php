<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $categories = [
            // Income Categories
            [
                'name' => 'Salary',
                'type' => 'income',
                'color' => '#4CAF50',
                'icon' => 'Wallet'
            ],
            [
                'name' => 'Business',
                'type' => 'income',
                'color' => '#FF9800',
                'icon' => 'HandCoins'
            ],
            [
                'name' => 'Investments',
                'type' => 'income',
                'color' => '#4CAF50',
                'icon' => 'ChartCandlestick'
            ],
            [
                'name' => 'Extra Income',
                'type' => 'income',
                'color' => '#4CAF50',
                'icon' => 'Wallet'
            ],
            [
                'name' => 'Loan',
                'type' => 'income',
                'color' => '#E91E63',
                'icon' => 'Landmark'
            ],
            [
                'name' => 'Parental Leave',
                'type' => 'income',
                'color' => '#FF4081',
                'icon' => 'Wallet'
            ],
            [
                'name' => 'Insurance Payout',
                'type' => 'income',
                'color' => '#2196F3',
                'icon' => 'BadgeDollarSign'
            ],
            [
                'name' => 'Other',
                'type' => 'income',
                'color' => '#9E9E9E',
                'icon' => 'BadgeDollarSign'
            ],

            // Expense Categories
            [
                'name' => 'Food & Drink',
                'type' => 'expense',
                'color' => '#FF9800',
                'icon' => 'Citrus'
            ],
            [
                'name' => 'Shopping',
                'type' => 'expense',
                'color' => '#E91E63',
                'icon' => 'ShoppingBag'
            ],
            [
                'name' => 'Transport',
                'type' => 'expense',
                'color' => '#FFC107',
                'icon' => 'Car'
            ],
            [
                'name' => 'Home',
                'type' => 'expense',
                'color' => '#795548',
                'icon' => 'House'
            ],
            [
                'name' => 'Bills & Fees',
                'type' => 'expense',
                'color' => '#5EC4AC',
                'icon' => 'Receipt'
            ],
            [
                'name' => 'Entertainment',
                'type' => 'expense',
                'color' => '#FF9800',
                'icon' => 'Clapperboard'
            ],
            [
                'name' => 'Car',
                'type' => 'expense',
                'color' => '#45A7E6',
                'icon' => 'Car'
            ],
            [
                'name' => 'Travel',
                'type' => 'expense',
                'color' => '#E91E63',
                'icon' => 'Plane'
            ],
            [
                'name' => 'Family & Personal',
                'type' => 'expense',
                'color' => '#45A7E6',
                'icon' => 'Contact'
            ],
            [
                'name' => 'Healthcare',
                'type' => 'expense',
                'color' => '#E06476',
                'icon' => 'Cross'
            ],
            [
                'name' => 'Education',
                'type' => 'expense',
                'color' => '#3A75AD',
                'icon' => 'Book'
            ],
            [
                'name' => 'Groceries',
                'type' => 'expense',
                'color' => '#DD8138',
                'icon' => 'ShoppingCart'
            ],
            [
                'name' => 'Gifts',
                'type' => 'expense',
                'color' => '#18B272',
                'icon' => 'Gift'
            ],
            [
                'name' => 'Sport & Hobbies',
                'type' => 'expense',
                'color' => '#4CAF50',
                'icon' => 'Dumbbell'
            ],
            [
                'name' => 'Beauty',
                'type' => 'expense',
                'color' => '#7944D0',
                'icon' => 'Sparkles'
            ],
            [
                'name' => 'Work',
                'type' => 'expense',
                'color' => '#607D8B',
                'icon' => 'BriefcaseBusiness'
            ],
            [
                'name' => 'Other',
                'type' => 'expense',
                'color' => '#9E9E9E',
                'icon' => 'ShoppingBag'
            ]
        ];
        foreach ($categories as $category) {
            $name = strtolower($category['name']);
            if (Category::where('name', $name)->doesntExist()) {
                $category['name'] = $name;
                Category::create($category);
            }
        }


    }
}
