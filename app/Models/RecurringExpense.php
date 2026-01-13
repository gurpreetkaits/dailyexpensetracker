<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringExpense extends Model
{
    use SoftDeletes;

    protected $table = "recurring_expenses";
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'payment_day',
        'first_payment_date',
        'is_active',
        'principal_amount',
        'interest_rate',
        'tenure_months',
        'total_interest',
        'type',
        'recurrence',
        'category_id',
        'wallet_id',
        'icon',
        'color'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'first_payment_date' => 'date',
        'is_active' => 'boolean',
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_interest' => 'decimal:2'
    ];

    protected $appends = [
        'interest_paid_till_date',
        'total_amount_paid',
        'pending_payments',
        'remaining_balance',
        'monthly_payment_total',
        'yearly_cost',
        'next_payment_date',
        'principal_paid_till_date',
        'interest_remaining',
        'total_payable',
        'loan_end_date',
        'payments_completed',
        'payments_remaining',
        'completion_percentage',
        'current_month_interest',
        'current_month_principal'
    ];

    protected $with = ['category', 'wallet'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get yearly cost based on recurrence
     */
    public function getYearlyCostAttribute()
    {
        if (!$this->is_active) {
            return 0;
        }

        $multiplier = match ($this->recurrence) {
            'monthly' => 12,
            'quarterly' => 4,
            'yearly' => 1,
            default => 12
        };

        return round($this->amount * $multiplier, 2);
    }

    /**
     * Get next payment date
     */
    public function getNextPaymentDateAttribute()
    {
        $today = Carbon::today();
        $nextPaymentDate = $today->copy()->setDay(min($this->payment_day, $today->daysInMonth));

        // If we've passed this month's payment date, move to next month
        if ($today->day > $this->payment_day) {
            $nextPaymentDate->addMonth();
        }

        // For EMI, check if we're still within tenure
        if ($this->type === 'emi') {
            $monthsFromStart = $this->first_payment_date->diffInMonths($nextPaymentDate);
            if ($monthsFromStart >= $this->tenure_months) {
                return null; // EMI completed
            }
        }

        return $nextPaymentDate->format('Y-m-d');
    }

    /**
     * Get monthly payment total
     */
    public function getMonthlyPaymentTotalAttribute()
    {
        return static::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->sum('amount');
    }

    /**
     * Get interest paid till date
     */
    public function getInterestPaidTillDateAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        $startDate = $this->first_payment_date;
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return 0;
        }

        $monthsPassed = $startDate->diffInMonths($today);

        // Calculate monthly interest component
        $monthlyRate = ($this->interest_rate / 12) / 100;
        $emi = $this->amount;
        $principal = $this->principal_amount;
        $totalInterestPaid = 0;

        for ($i = 1; $i <= min($monthsPassed, $this->tenure_months); $i++) {
            $interestForMonth = $principal * $monthlyRate;
            $principalForMonth = $emi - $interestForMonth;
            $totalInterestPaid += $interestForMonth;
            $principal -= $principalForMonth;
        }

        return round($totalInterestPaid, 2);
    }

    /**
     * Get total amount paid till date
     */
    public function getTotalAmountPaidAttribute()
    {
        $startDate = $this->first_payment_date;
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return 0;
        }

        $monthsPassed = $startDate->diffInMonths($today);

        if ($this->type === 'emi') {
            return round($this->amount * min($monthsPassed, $this->tenure_months), 2);
        }

        return round($this->amount * $monthsPassed, 2);
    }

    /**
     * Get pending payments
     */
    public function getPendingPaymentsAttribute()
    {
        $today = Carbon::today();
        $currentDay = $today->day;
        $paymentDay = $this->payment_day;

        if ($this->type === 'emi') {
            $startDate = $this->first_payment_date;
            $monthsPassed = $startDate->diffInMonths($today);
            $remainingMonths = max(0, $this->tenure_months - $monthsPassed);
            return round($this->amount * $remainingMonths, 2);
        }

        $pendingAmount = 0;
        if ($currentDay > $paymentDay) {
            $pendingAmount += $this->amount;
        }

        return round($pendingAmount, 2);
    }

    /**
     * Get remaining balance for EMI
     */
    public function getRemainingBalanceAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        $startDate = $this->first_payment_date;
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return $this->principal_amount;
        }

        $monthsPassed = $startDate->diffInMonths($today);

        // Calculate remaining principal
        $monthlyRate = ($this->interest_rate / 12) / 100;
        $emi = $this->amount;
        $principal = $this->principal_amount;

        for ($i = 1; $i <= min($monthsPassed, $this->tenure_months); $i++) {
            $interestForMonth = $principal * $monthlyRate;
            $principalForMonth = $emi - $interestForMonth;
            $principal -= $principalForMonth;
        }

        return max(0, round($principal, 2));
    }

    /**
     * Get upcoming payments for next few months
     */
    public function getUpcomingPayments($months = 3)
    {
        $upcoming = [];
        $today = Carbon::today();
        $nextPaymentDate = $today->copy()->setDay($this->payment_day);

        // If we've passed this month's payment date, move to next month
        if ($today->day > $this->payment_day) {
            $nextPaymentDate->addMonth();
        }

        for ($i = 0; $i < $months; $i++) {
            $paymentDate = $nextPaymentDate->copy()->addMonths($i);

            // For EMI, check if we're within tenure
            if ($this->type === 'emi') {
                $monthsFromStart = $this->first_payment_date->diffInMonths($paymentDate);
                if ($monthsFromStart >= $this->tenure_months) {
                    continue;
                }
            }

            // Only add if the expense is active
            if ($this->is_active) {
                $upcoming[] = [
                    'date' => $paymentDate->format('Y-m-d'),
                    'amount' => $this->amount,
                    'name' => $this->name,
                    'type' => $this->type,
                    'payment_day' => $this->payment_day
                ];
            }
        }

        return $upcoming;
    }
    /**
     * Get principal paid till date for EMI
     */
    public function getPrincipalPaidTillDateAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        return round($this->total_amount_paid - $this->interest_paid_till_date, 2);
    }

    /**
     * Get remaining interest to be paid
     */
    public function getInterestRemainingAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        return max(0, round($this->total_interest - $this->interest_paid_till_date, 2));
    }

    /**
     * Get total payable amount (principal + total interest)
     */
    public function getTotalPayableAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        return round($this->principal_amount + $this->total_interest, 2);
    }

    /**
     * Get loan end date
     */
    public function getLoanEndDateAttribute()
    {
        if (!$this->type || $this->type !== 'emi' || !$this->first_payment_date) {
            return null;
        }

        return $this->first_payment_date->copy()
            ->addMonths($this->tenure_months - 1)
            ->setDay($this->payment_day)
            ->format('Y-m-d');
    }

    /**
     * Get number of payments completed
     */
    public function getPaymentsCompletedAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        $startDate = $this->first_payment_date;
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return 0;
        }

        $monthsPassed = $startDate->diffInMonths($today);
        return min($monthsPassed, $this->tenure_months);
    }

    /**
     * Get number of payments remaining
     */
    public function getPaymentsRemainingAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        return max(0, $this->tenure_months - $this->payments_completed);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if (!$this->type || $this->type !== 'emi' || $this->tenure_months == 0) {
            return 0;
        }

        return round(($this->payments_completed / $this->tenure_months) * 100, 1);
    }

    /**
     * Get current month's interest component
     */
    public function getCurrentMonthInterestAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        $monthlyRate = ($this->interest_rate / 12) / 100;
        return round($this->remaining_balance * $monthlyRate, 2);
    }

    /**
     * Get current month's principal component
     */
    public function getCurrentMonthPrincipalAttribute()
    {
        if (!$this->type || $this->type !== 'emi') {
            return 0;
        }

        return round($this->amount - $this->current_month_interest, 2);
    }

    /**
     * Get full amortization schedule
     */
    public function getAmortizationSchedule()
    {
        if (!$this->type || $this->type !== 'emi') {
            return [];
        }

        $schedule = [];
        $monthlyRate = ($this->interest_rate / 12) / 100;
        $emi = $this->amount;
        $balance = $this->principal_amount;
        $paymentDate = $this->first_payment_date->copy();
        $today = Carbon::today();

        for ($i = 1; $i <= $this->tenure_months; $i++) {
            $interestComponent = round($balance * $monthlyRate, 2);
            $principalComponent = round($emi - $interestComponent, 2);
            $balance = max(0, round($balance - $principalComponent, 2));

            $isPaid = $paymentDate->lessThanOrEqualTo($today);

            $schedule[] = [
                'payment_number' => $i,
                'date' => $paymentDate->format('Y-m-d'),
                'emi' => $emi,
                'principal' => $principalComponent,
                'interest' => $interestComponent,
                'balance' => $balance,
                'is_paid' => $isPaid
            ];

            $paymentDate->addMonth();
        }

        return $schedule;
    }

    /**
     * Get loan summary for this EMI
     */
    public function getLoanSummary()
    {
        if (!$this->type || $this->type !== 'emi') {
            return null;
        }

        return [
            'name' => $this->name,
            'principal_amount' => $this->principal_amount,
            'interest_rate' => $this->interest_rate,
            'tenure_months' => $this->tenure_months,
            'emi_amount' => $this->amount,
            'total_interest' => $this->total_interest,
            'total_payable' => $this->total_payable,
            'payments_completed' => $this->payments_completed,
            'payments_remaining' => $this->payments_remaining,
            'completion_percentage' => $this->completion_percentage,
            'principal_paid' => $this->principal_paid_till_date,
            'interest_paid' => $this->interest_paid_till_date,
            'principal_remaining' => $this->remaining_balance,
            'interest_remaining' => $this->interest_remaining,
            'current_month_principal' => $this->current_month_principal,
            'current_month_interest' => $this->current_month_interest,
            'first_payment_date' => $this->first_payment_date->format('Y-m-d'),
            'loan_end_date' => $this->loan_end_date,
            'next_payment_date' => $this->next_payment_date,
            'is_active' => $this->is_active
        ];
    }
}
