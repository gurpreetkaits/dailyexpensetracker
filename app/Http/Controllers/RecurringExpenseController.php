<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use App\Services\RecurringExpenseDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RecurringExpenseController extends Controller
{
    private $model = RecurringExpense::class;

    public function index(): JsonResponse
    {
        $expenses = auth()->user()->recurringExpenses()
            ->with(['category', 'wallet'])
            ->latest()
            ->get();

        return $this->success('Recurring expenses fetched successfully', [
            'recurring_expenses' => $expenses,
            'total' => auth()->user()->getTotalExpenseDetails(),
            'grouped' => $this->groupByType($expenses)
        ]);
    }

    /**
     * Get suggested recurring expenses from transaction patterns
     */
    public function suggestions(RecurringExpenseDetectionService $service): JsonResponse
    {
        $suggestions = $service->detectRecurringPatterns(auth()->id());

        return $this->success('Suggestions fetched successfully', [
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Group expenses by type
     */
    private function groupByType($expenses): array
    {
        return [
            'subscriptions' => $expenses->where('type', 'subscription')->values(),
            'emis' => $expenses->where('type', 'emi')->values(),
            'bills' => $expenses->where('type', 'bill')->values(),
            'other' => $expenses->where('type', 'other')->values()
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['subscription', 'emi', 'bill', 'other'])],
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_day' => 'required|integer|min:1|max:31',
            'first_payment_date' => 'required|date',
            'recurrence' => ['required', 'string', Rule::in(['monthly', 'quarterly', 'yearly'])],

            // EMI specific validations
            'principal_amount' => 'required_if:type,emi|nullable|numeric|min:0',
            'interest_rate' => 'required_if:type,emi|nullable|numeric|min:0|max:100',
            'tenure_months' => 'required_if:type,emi|nullable|integer|min:1|max:720',
            'total_interest' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date|after:first_payment_date|required_if:type,emi',

            // New fields
            'category_id' => 'nullable|exists:categories,id',
            'wallet_id' => 'nullable|exists:wallets,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20'
        ]);

        // Remove null values from validated data
        $validated = array_filter($validated, function ($value) {
            return $value !== null;
        });

        $expense = auth()->user()->recurringExpenses()->create($validated);

        return $expense->load(['category', 'wallet']);
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
            'recurrence' => ['required', 'string', Rule::in(['monthly', 'quarterly', 'yearly'])],

            // EMI specific validations
            'principal_amount' => 'required_if:type,emi|nullable|numeric|min:0',
            'interest_rate' => 'required_if:type,emi|nullable|numeric|min:0|max:100',
            'tenure_months' => 'required_if:type,emi|nullable|integer|min:1|max:720',
            'total_interest' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date|after:first_payment_date|required_if:type,emi',

            // New fields
            'category_id' => 'nullable|exists:categories,id',
            'wallet_id' => 'nullable|exists:wallets,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20'
        ]);

        // Remove null values from validated data
        $validated = array_filter($validated, function ($value) {
            return $value !== null;
        });

        $recurringExpense->update($validated);
        return $recurringExpense->fresh()->load(['category', 'wallet']);
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

    /**
     * Get detailed loan information with amortization schedule
     */
    public function getLoanDetails(RecurringExpense $recurringExpense): JsonResponse
    {
        abort_if($recurringExpense->user_id !== auth()->id(), Response::HTTP_FORBIDDEN);
        abort_if($recurringExpense->type !== 'emi', Response::HTTP_BAD_REQUEST, 'This is not an EMI/Loan');

        return $this->success('Loan details fetched successfully', [
            'loan' => $recurringExpense->getLoanSummary(),
            'amortization_schedule' => $recurringExpense->getAmortizationSchedule(),
            'expense' => $recurringExpense
        ]);
    }
}
