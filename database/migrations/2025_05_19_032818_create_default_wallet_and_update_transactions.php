<?php

use App\Enums\WalletTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;

return new class extends Migration
{
    public function up(): void
    {
        // Create default wallet for each user
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if($user->wallets()->exists()) {
                    continue;
                }
                // Create default cash wallet
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'name' => WalletTypeEnum::CASH->label(),
                    'type' =>  WalletTypeEnum::CASH->label(),
                    'currency' => $user->currency ?? 'USD',
                    'balance' => 0
                ]);

                // Get all transactions for this user
                $transactions = Transaction::where('user_id', $user->id)->get();
                if($transactions->isEmpty()) {
                    continue;
                }
                // Calculate wallet balance based on transactions
                $balance = 0;
                foreach ($transactions as $transaction) {
                    // Update transaction with wallet_id
                    $transaction->update(['wallet_id' => $wallet->id]);

                    // Update wallet balance
                    if ($transaction->type === \App\Enums\TransactionTypeEnum::INCOME->value) {
                        $balance += $transaction->amount;
                    } else {
                        $balance -= $transaction->amount;
                    }
                }

                $wallet->update(['balance' => $balance]);
            }
        });
    }

    public function down(): void
    {
        // Remove wallet_id from transactions
        DB::table('transactions')->update(['wallet_id' => null]);
        Wallet::truncate();
    }
};
