<?php

namespace App\Http\Controllers;

use App\Data\GetTransactionFilterData;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Enum\TransactionFilterEnum;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function search(Request $request)
    {
        $dateFilter = $request->date;
        $query = $request->query;

        $results = $this->transactionService->searchTransaction($dateFilter, $query);

        return response()->json([
            'transactions' => $results->count() ? $results : [],
            'summary' => []
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'filter' => ['nullable', 'in:today,monthly,yearly,all,weekly,yesterday'],
        ]);
        $transactions = $this->transactionService->getUserTransactions(
            auth()->id(),
            $validated['filter'] ?? 'today'
        );

        $summary = $this->transactionService->getTransactionSummary(auth()->id(),$validated['filter'] ?? 'today');

        return response()->json([
            'transactions' => $transactions,
            'summary' => $summary
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'category_id' => 'nullable|int',
            'transaction_date' => 'nullable|date',
            'wallet_id' => 'required|exists:wallets,id'
        ]);

        $validated['user_id'] = auth()->id();
        $transaction = $this->transactionService->createTransaction($validated);

        $this->transactionService->clearUserTransactionCache(auth()->id());

        return response()->json(['data' => $transaction], 201);
    }

    public function update(Transaction $transaction, Request $request): JsonResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'unauthorized');
        }
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'transaction_date' => 'nullable|date',
            'category_id' => 'nullable|int',
            'id' => 'exists:transactions,id',
            'wallet_id' => 'required|exists:wallets,id'
        ]);
        $oldTransaction = Transaction::find($validated['id'])->first();

        $transaction = DB::transaction(function () use ($validated, $transaction,$oldTransaction) {
            $transaction->type = $validated['type'];
            $transaction->amount = $validated['amount'];
            $transaction->note = $validated['note'];
            $transaction->category_id = $validated['category_id'];
            $transaction->transaction_date = $validated['transaction_date'];
            $transaction->wallet_id = $validated['wallet_id'];
            $transaction->save();

            $walletService = app(WalletService::class);
            $walletService->syncWallet($transaction,'update',$oldTransaction);

            return $transaction;
        });
        // Clear the cache
        $this->transactionService->clearUserTransactionCache($transaction->user_id);

        return response()->json(['data' => $transaction], status: 201);
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'unauthorized');
        }
        $this->transactionService->deleteTransaction($transaction);
        $this->transactionService->clearUserTransactionCache($transaction->user_id);
        return response()->json(['data' => null], 200);
    }

    public function show($id)
    {
        return Transaction::with('category')->where('user_id', Auth::id())->findOrFail($id);
    }

    public function showStats(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:day,week,month,year,custom'],
            'startDate' => ['nullable|required_if:type,custom'],
            'endDate' => ['nullable|required_if:type,custom']
        ]);
    }

    public function getTransactions(GetTransactionFilterData $data)
    {

        $transactions = $this->transactionService->getCategoryTransactions($data);

        return response()->json(['transactions' => $transactions]);
    }

    public function activityBarDataV2(Request $request)
    {

        $period = $request->query('period', 'D');
        $bar = $request->query('bar');
        $userId = auth()->id();
        $data = $this->transactionService->getActivityBarDataV2($userId, $period);
        $barTransactions = [];
        if (!empty($bar)) {
            $barArr = json_decode($bar, true);
            if (is_array($barArr) && count($barArr) === 2) {
                $barTransactions = $this->transactionService->getTransactionsForBar($userId, $barArr[0], $barArr[1]);
            }
        }
        return response()->json(['data' => $data, 'barTransactions' => $barTransactions]);
    }
}
