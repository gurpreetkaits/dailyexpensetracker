<?php

namespace Tests\Feature\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    protected User $user;
    public function setUp(): void
    {
        parent::setUp();
        $this->user=  User::factory()->create();
        $this->actingAs($this->user);
    }
    public function test_it_can_get_transactions_with_today_filter(): void
    {
        $todayTransactions = Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' =>'',
            'transaction_date' => now(),
            'created_at' => now(),
        ]);

        $yesterdayTransactions = Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => '',
            'transaction_date' => now()->subDay(),
            'created_at' => now()->subDay(),
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions?filter=today');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Verify only today's transactions are returned
        $transactionIds = collect($response->json('data'))->pluck('id');
        $this->assertEquals(
            $todayTransactions->pluck('id')->sort()->values(),
            $transactionIds->sort()->values()
        );
    }
}
