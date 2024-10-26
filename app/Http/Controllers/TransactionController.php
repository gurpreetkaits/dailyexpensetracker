<?php

namespace App\Http\Controllers;

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
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $transaction = $this->transactionService->createTransaction($validated);

        return response()->json(['data' => $transaction], 201);
    }
}
