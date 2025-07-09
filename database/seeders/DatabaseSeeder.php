<?php

namespace Database\Seeders;

use App\Enums\WalletTypeEnum;
use App\Models\User;
use App\Models\Wallet;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Wallet::firstOrCreate([
            'name' => 'Main Wallet',
            'user_id' => 2,
            'balance' => 100000,
            'type' => WalletTypeEnum::CARD,
            'currency' => 'USD',
        ]);
    }
}