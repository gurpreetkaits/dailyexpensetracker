<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::query()->upsert([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ],['email' => 'test@example.com']);
    }
}
