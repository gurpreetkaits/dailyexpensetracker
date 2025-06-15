<?php

namespace Tests\Feature\Controller;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $wallet;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()
            ->withSettings()
            ->create();
        $this->seed(CategorySeeder::class);
        $this->seed(CurrencySeeder::class);
        $this->category = Category::first();
    }

    /**
     * Test listing wallets (index method)
     */
    public function test_index_wallets()
    {
        $this->actingAs($this->user);

        // Create some wallets for the user
        Wallet::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'total'
                ]);
        $this->assertEquals(3, count($response->json('data')));
    }

    /**
     * Test creating a new wallet (store method)
     */
    public function test_create_wallet()
    {
        $this->actingAs($this->user);

        $walletData = [
            'name' => 'Test Wallet',
            'type' => 'cash',
            'balance' => 1000.00,
        ];

        $response = $this->postJson('/api/wallets', $walletData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Test Wallet',
                    'type' => 'cash',
                    'balance' => 1000.00,
                    'currency' => 'USD'
                ]);

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'user_id' => $this->user->id,
            'name' => 'Test Wallet',
            'type' => 'cash',
            'balance' => 1000.00,
            'currency' => 'USD'
        ]);
    }

    /**
     * Test showing a wallet (show method)
     */
    public function test_show_wallet()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Wallet',
            'balance' => 500.00
        ]);

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(200);
    }

    /**
     * Test updating a wallet (update method)
     */
    public function test_update_wallet()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Wallet',
            'balance' => 500.00
        ]);

        $updatedData = [
            'name' => 'Updated Wallet',
            'type' => 'bank',
            'balance' => 700.00,
        ];

        $response = $this->putJson("/api/wallets/{$wallet->id}", $updatedData);
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'user_id',
                    'name',
                    'balance',
                    'currency',
                    'type',
                    'is_default',
                    'created_at',
                    'updated_at',
                    'transactions'
                ]);

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'id' => $wallet->id,
            'name' => 'Updated Wallet',
            'balance' => 700.00,
        ]);
    }

    /**
     * Test deleting a wallet (destroy method)
     */
    public function test_delete_wallet()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Wallet to Delete',
            'balance' => 100.00
        ]);

        $response = $this->deleteJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('wallets', [
            'id' => $wallet->id,
        ]);
    }

    /**
     * Test cannot delete wallet with transactions
     */
    public function test_cannot_delete_wallet_with_transactions()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Wallet with Transactions',
            'balance' => 500.00
        ]);

        Transaction::factory()->state(['wallet_id' => $wallet->id])->count(2)->create();

        $response = $this->deleteJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(500)
                ->assertJsonFragment([
                    'message' => 'Cannot delete wallet with transactions'
                ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id
        ]);
    }

    /**
     * Test wallet transactions listing
     */
    public function test_wallet_transactions()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000.00
        ]);

        // Create some transactions for this wallet
        Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'amount' => 100.00,
            'type' => 'expense',
            'category_id' => $this->category->id,
            'note' => 'Transaction 1',
            'transaction_date' => now()
        ]);

        Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'amount' => 200.00,
            'type' => 'income',
            'category_id' => $this->category->id,
            'note' => 'Transaction 2',
            'transaction_date' => now()
        ]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'total'
                ]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /**
     * Test wallet transfer functionality
     */
    public function test_wallet_transfer()
    {
        $this->actingAs($this->user);

        $sourceWallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Source Wallet',
            'balance' => 1000.00,
        ]);

        $destWallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Destination Wallet',
            'balance' => 500.00,
        ]);

        $transferAmount = 300.00;
        $transferData = [
            'fromWalletId' => $sourceWallet->id,
            'toWalletId' => $destWallet->id,
            'amount' => $transferAmount,
            'note' => 'Test transfer'
        ];

        $response = $this->postJson('/api/wallets/transfer', $transferData);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Transfer completed successfully'
                ]);

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'id' => $sourceWallet->id,
            'balance' => 700.00
        ]);

        $this->assertDatabaseHas((new Wallet())->getTable(), [
            'id' => $destWallet->id,
            'balance' => 800.00
        ]);

        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'wallet_id' => $sourceWallet->id,
            'type' => 'expense',
            'amount' => $transferAmount,
        ]);

        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'wallet_id' => $destWallet->id,
            'type' => 'income',
            'amount' => $transferAmount,
        ]);
    }

    /**
     * Test insufficient balance for transfer
     */
    public function test_transfer_insufficient_balance()
    {
        $this->actingAs($this->user);

        // Create source and destination wallets
        $sourceWallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Source Wallet',
            'balance' => 100.00,
            'currency' => 'USD'
        ]);

        $destWallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Destination Wallet',
            'balance' => 500.00,
            'currency' => 'USD'
        ]);

        $transferData = [
            'fromWalletId' => $sourceWallet->id,
            'toWalletId' => $destWallet->id,
            'amount' => 200.00, // More than available balance
            'note' => 'Test transfer'
        ];

        $response = $this->postJson('/api/wallets/transfer', $transferData);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'message' => 'Insufficient balance in source wallet'
                ]);

        // Check that  balances remain unchanged
        $this->assertDatabaseHas('wallets', [
            'id' => $sourceWallet->id,
            'balance' => 100.00
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $destWallet->id,
            'balance' => 500.00
        ]);
    }

    /**
     * Test unauthorized access to wallets
     */
    public function test_unauthorized_access()
    {
        $this->actingAs($this->user);

        // Create a wallet for this user
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Wallet',
            'balance' => 1000.00
        ]);

        // Create another user and wallet
        $anotherUser = User::factory()->withSettings()->create();
        $anotherWallet = Wallet::factory()->create([
            'user_id' => $anotherUser->id,
            'name' => 'Another User Wallet',
            'balance' => 500.00
        ]);

        // Try to delete another user's wallet - this should check authorization before validation
        $response = $this->deleteJson("/api/wallets/{$anotherWallet->id}");
        $response->assertStatus(403);

        // Try to transfer from another user's wallet
        $response = $this->postJson('/api/wallets/transfer', [
            'fromWalletId' => $anotherWallet->id,
            'toWalletId' => $wallet->id,
            'amount' => 100.00,
            'note' => 'Test transfer'
        ]);
        // Should get 404 because findOrFail with auth()->id() filter
        $response->assertStatus(404);
    }

    /**
     * Test balance history
     */
    public function test_balance_history()
    {
        $this->actingAs($this->user);

        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000.00
        ]);

        // Create a transaction to trigger balance history
        Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'amount' => 100.00,
            'type' => 'expense',
            'category_id' => $this->category->id,
            'note' => 'Test transaction',
            'transaction_date' => now()
        ]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/balance-history");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'total'
                ]);
    }
}
