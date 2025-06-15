<?php

namespace App\Services;

use App\Data\WalletStoreData;
use App\Data\WalletUpdateData;
use App\Enums\TransactionTypeEnum;
use App\Http\Integrations\OpenAiConnector\OpenAiConnector;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalanceHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Exception;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function all(User $user): LengthAwarePaginator
    {
        return Wallet::where('user_id', $user->id)
            ->withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function create(WalletStoreData $data): Wallet
    {
        return Wallet::create([
            'user_id' => auth()->id(),
            'name' => $data->name,
            'type' => $data->type,
            'balance' => $data->balance,
            'currency' => $data->currency
        ]);
    }

    public function show(Wallet $wallet): Wallet
    {
        return $wallet->loadCount('transactions');
    }

    public function update(Wallet $wallet, WalletUpdateData $data): Wallet
    {
        $wallet->update([
            'name' => $data->name,
            'type' => $data->type,
            'balance' => $data->balance,
            'currency' => $data->currency
        ]);

        return $wallet->fresh(['transactions']);
    }

    public function delete(Wallet $wallet): ?bool
    {

        if ($wallet->transactions()->count() > 0) {
            throw new Exception('Cannot delete wallet with transactions');
        }
        return $wallet->delete();
    }

    public function getTransactions(Wallet $wallet, int $page = 1): LengthAwarePaginator
    {
        return $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);
    }

    public function recordBalanceHistory($walletId, $balance): void
    {
        WalletBalanceHistory::create([
            'wallet_id' => $walletId,
            'balance' => $balance,
        ]);
    }

    public function getBalanceHistory(Wallet $wallet)
    {
        return $wallet->balanceHistory()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function syncWallet(Transaction $transaction, string $action, ?Transaction $oldTransaction = null): void
    {
        $wallet = Wallet::find($transaction->wallet_id);

        if (!$wallet)
            return;

        switch ($action) {
            case 'create':
                if ($transaction->type === TransactionTypeEnum::EXPENSE->value) {
                    $wallet->decrement('balance', $transaction->amount);
                } else {
                    $wallet->increment('balance', $transaction->amount);
                }
                break;

            case 'delete':
                if ($transaction->type === TransactionTypeEnum::EXPENSE->value) {
                    $wallet->increment('balance', $transaction->amount);
                } else {
                    $wallet->decrement('balance', $transaction->amount);
                }
                break;

            case 'update':
                if (!$oldTransaction)
                    return;
                if ($oldTransaction->type === TransactionTypeEnum::EXPENSE->value) {
                    $wallet->increment('balance', $oldTransaction->amount);
                } else {
                    $wallet->decrement('balance', $oldTransaction->amount);
                }

                if ($transaction->type === TransactionTypeEnum::EXPENSE->value) {
                    $wallet->decrement('balance', $transaction->amount);
                } else {
                    $wallet->increment('balance', $transaction->amount);
                }
                break;
        }

        $wallet->save();
        $this->recordBalanceHistory($wallet->id, $wallet->balance);
    }
}
