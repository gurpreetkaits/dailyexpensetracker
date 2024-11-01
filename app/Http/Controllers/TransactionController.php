<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');

        $transactions = $this->transactionService->getUserTransactions(
            auth()->id(),
            $month,
            $year
        );

        $summary = $this->transactionService->getTransactionSummary(auth()->id());

        return response()->json([
            'transactions' => $transactions,
            'summary' => $summary
        ]);
    }

    public function store(Request $request)
    {
        // TODO: check category_id is from categories table
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'category_id' => 'nullable|int',
            'transaction_date' => 'nullable|date'
        ]);

        $validated['user_id'] = auth()->id();
        $transaction = $this->transactionService->createTransaction($validated);

        return response()->json(['data' => $transaction], 201);
    }

    public function update(Transaction $transaction, Request $request)
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
            'id' => 'exists:transactions,id'
        ]);
        $transaction->type = $validated['type'];
        $transaction->amount = $validated['amount'];
        $transaction->note = $validated['note'];
        $transaction->note = $validated['note'];
        $transaction->category_id = $validated['category_id'];
        $transaction->transaction_date = $validated['transaction_date'];
        $transaction->save();

        // Clear the cache
        $this->transactionService->clearUserTransactionCache($transaction->user_id);

        return response()->json(['data' => $transaction], status: 201);
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'unauthorized');
        }
        $transaction->delete();
        $this->transactionService->clearUserTransactionCache($transaction->user_id);
        return response()->json(['data' => $transaction], 200);
    }

    public function show($id)
    {
        return Transaction::with('category')->findOrFail($id);
    }

    public function showStats(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:day,week,month,year,custom'],
            'startDate' => ['nullable|required_if:type,custom'],
            'endDate' => ['nullable|required_if:type,custom']
        ]);
    }
}
