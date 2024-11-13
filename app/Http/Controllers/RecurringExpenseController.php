<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use Illuminate\Http\Request;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        return auth()->user()->recurringExpenses;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_day' => 'required|integer|min:1|max:31',
            'first_payment_date' => 'required|date',
        ]);

        return auth()->user()->recurringExpenses()->create($validated);
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_day' => 'required|integer|min:1|max:31',
            'first_payment_date' => 'required|date',
        ]);

        $recurringExpense->update($validated);
        return $recurringExpense;
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $recurringExpense->delete();
        return response()->noContent();
    }
}