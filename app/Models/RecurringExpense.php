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
        'recurrence'
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
        'monthly_payment_total'  // Added this
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
     * Get totals for all recurring expenses of a user
     */

}
