<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RecurringExpenseController extends Controller
{
    private $model = RecurringExpense::class;
    public function index(): JsonResponse
    {
        return $this->success('Recurring expenses fetched successfully',[
            'recurring_expenses' => auth()->user()->recurringExpenses,
            'total' => auth()->user()->getTotalExpenseDetails()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['subscription', 'emi', 'bill', 'other'])],
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_day' => 'required|integer|min:1|max:31',
            'first_payment_date' => 'required|date',
            'recurrence' => ['required', 'string', Rule::in(['monthly'])],

            // EMI specific validations
            'principal_amount' => 'required_if:type,emi|nullable|numeric|min:0',
            'interest_rate' => 'required_if:type,emi|nullable|numeric|min:0|max:100',
            'tenure_months' => 'required_if:type,emi|nullable|integer|min:1|max:360',
            'total_interest' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date|after:first_payment_date|required_if:type,emi'
        ]);

        // Remove null values from validated data
        $validated = array_filter($validated, function ($value) {
            return $value !== null;
        });

        return auth()->user()->recurringExpenses()->create($validated);
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        abort_if($recurringExpense->user_id !== auth()->id(), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['subscription', 'emi', 'bill', 'other'])],
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_day' => 'required|integer|min:1|max:31',
            'first_payment_date' => 'required|date',
            'recurrence' => ['required', 'string', Rule::in(['monthly'])],

            // EMI specific validations
            'principal_amount' => 'required_if:type,emi|nullable|numeric|min:0',
            'interest_rate' => 'required_if:type,emi|nullable|numeric|min:0|max:100',
            'tenure_months' => 'required_if:type,emi|nullable|integer|min:1|max:360',
            'total_interest' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date|after:first_payment_date|required_if:type,emi'
        ]);

        // Remove null values from validated data
        $validated = array_filter($validated, function ($value) {
            return $value !== null;
        });

        $recurringExpense->update($validated);
        return $recurringExpense->fresh();
    }

    public function destroy(RecurringExpense $recurringExpense): Response
    {
        abort_if($recurringExpense->user_id !== auth()->id(), Response::HTTP_FORBIDDEN);
        $recurringExpense->delete();
        return response()->noContent();
    }

    // In your controller
    public function getExpenseDetails(RecurringExpense $recurringExpense): array
    {
        return [
            'expense' => $recurringExpense,
            'interest_paid' => $recurringExpense->interest_paid_till_date,
            'total_paid' => $recurringExpense->getTotalAmountPaidAttribute(),
            'pending_payments' => $recurringExpense->pending_payments,
            'remaining_balance' => $recurringExpense->remaining_balance,
            'upcoming_payments' => $recurringExpense->getUpcomingPayments(3)
        ];
    }

//    public function getDashboardStats()
//    {
//        $userId = auth()->id();
//        return [
//            'monthly_payment_total' => RecurringExpense::getMonthlyPaymentTotal($userId),
//            'recurring_expenses' => auth()->user()->recurringExpenses
//        ];
//    }
}
