<?php

namespace Tests\Feature\Controller;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransactionService;
use App\Services\WalletService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $wallet;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user, wallet, and category for testing
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000.00
        ]);
        $this->seed(CategorySeeder::class);
        $this->category = Category::where('name', 'Wallet Transfer')->first();
    }

    /**
     * Test creating a new transaction (store method)
     */
    public function test_create_transaction()
    {
        $this->actingAs($this->user);

        $transactionData = [
            'type' => 'income',
            'amount' => 500.00,
            'note' => 'Test income transaction',
            'reference_number' => 'REF123456',
            'category_id' => $this->category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $this->wallet->id
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201)
                ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 500.00,
            'note' => 'Test income transaction',
            'reference_number' => 'REF123456',
            'category_id' => $this->category->id,
            'wallet_id' => $this->wallet->id
        ]);

        // Verify wallet balance was updated
        $updatedWallet = Wallet::find($this->wallet->id);
        $this->assertEquals(1500.00, $updatedWallet->balance);
    }

    /**
     * Test validation for duplicate reference number
     */
    public function test_duplicate_reference_number_validation()
    {
        $this->actingAs($this->user);

        // Create first transaction with reference number
        $firstTransaction = [
            'type' => 'income',
            'amount' => 500.00,
            'note' => 'First transaction',
            'reference_number' => 'DUPLICATE-REF',
            'category_id' => $this->category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $this->wallet->id
        ];

        $response = $this->postJson('/api/transactions', $firstTransaction);
        $response->assertStatus(201);

        // Try to create second transaction with same reference number
        $secondTransaction = [
            'type' => 'expense',
            'amount' => 200.00,
            'note' => 'Second transaction',
            'reference_number' => 'DUPLICATE-REF', // Same reference number
            'category_id' => $this->category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $this->wallet->id
        ];

        $response = $this->postJson('/api/transactions', $secondTransaction);
        
        // Should fail with validation error
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reference_number']);
    }

    /**
     * Test updating an existing transaction (update method)
     */
    public function test_update_transaction()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()
            ->state([
                'balance' => 1000.00
            ])
            ->create();

        $transaction = [
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 200.00,
            'reference_number' => 'UPDATE-REF-1',
            'category_id' => $this->category->id,
            'note' => 'Original transaction'
        ];

        $response = $this->postJson('/api/transactions', $transaction);
        $transactionId = $response->json()['data']['id'];

        $wallet->refresh();
        $category = Category::factory()->create();

        $updatedData = [
            'id' => $transactionId,
            'type' => 'income',
            'amount' => 300.00,
            'note' => 'Updated transaction',
            'reference_number' => 'UPDATE-REF-2', // Updated reference number
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $wallet->id
        ];

        $response = $this->putJson("/api/transactions/{$transactionId}", $updatedData);

        $response->assertStatus(201)
                ->assertJsonStructure(['data']);


        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 300.00,
            'note' => 'Updated transaction',
            'reference_number' => 'UPDATE-REF-2'
        ]);
        $expectedBalance = 1300.00;

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'id' => $wallet->id,
            'balance' => $expectedBalance
        ]);
    }

    /**
     * Test validation when updating with a duplicate reference number
     */
    public function test_update_with_duplicate_reference_number()
    {
        $this->actingAs($this->user);

        // Create first transaction
        $firstTransaction = Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $this->wallet->id,
            'type' => 'income',
            'amount' => 100.00,
            'reference_number' => 'EXISTING-REF',
            'category_id' => $this->category->id,
            'note' => 'First transaction'
        ]);

        // Create second transaction
        $secondTransaction = Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $this->wallet->id,
            'type' => 'expense',
            'amount' => 50.00,
            'reference_number' => 'UNIQUE-REF',
            'category_id' => $this->category->id,
            'note' => 'Second transaction'
        ]);

        // Try to update second transaction with reference number from first
        $updateData = [
            'id' => $secondTransaction->id,
            'type' => 'expense',
            'amount' => 75.00,
            'note' => 'Updated transaction',
            'reference_number' => 'EXISTING-REF', // Already used by first transaction
            'category_id' => $this->category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $this->wallet->id
        ];

        $response = $this->putJson("/api/transactions/{$secondTransaction->id}", $updateData);
        
        // Should fail with validation error
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reference_number']);
    }

    /**
     * Test deleting a transaction (destroy method)
     */
    public function test_delete_transaction()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()
            ->state([
                'balance' => 1000.00
            ])
            ->create();

        $transaction = [
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 200.00,
            'category_id' => $this->category->id,
            'note' => 'Original transaction'
        ];

        $response = $this->postJson('/api/transactions', $transaction);
        $transactionId = $response->json()['data']['id'];

        $response = $this->deleteJson("/api/transactions/{$transactionId}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId,
        ]);

        $updatedWallet = Wallet::find($wallet->id);
        $expectedBalance = 1000;

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'id' => $wallet->id,
            'balance' => $expectedBalance
        ]);
    }

    /**
     * Test unauthorized access to another user's transaction
     */
    public function test_unauthorized_access()
    {
        $this->actingAs($this->user);

        // Create another user and their transaction
        $anotherUser = User::factory()->create();
        $anotherWallet = Wallet::factory()->create(['user_id' => $anotherUser->id]);
        $anotherTransaction = Transaction::factory()->create([
            'user_id' => $anotherUser->id,
            'wallet_id' => $anotherWallet->id
        ]);

        $response = $this->putJson("/api/transactions/{$anotherTransaction->id}", [
            'id' => $anotherTransaction->id,
            'type' => 'income',
            'amount' => 300.00,
            'note' => 'Trying to hack',
            'category_id' => $this->category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'wallet_id' => $anotherWallet->id
        ]);

        $response->assertStatus(403);

        $response = $this->deleteJson("/api/transactions/{$anotherTransaction->id}");
        $response->assertStatus(403);
    }
}
