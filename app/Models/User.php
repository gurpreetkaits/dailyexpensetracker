<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Controllers\RecurringExpenseController;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements AuthMustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmail;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'otp',
        'google_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function recurringExpenses(){
        return $this->hasMany(RecurringExpense::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function customCategories()
    {
        return $this->categories()->where('is_custom', true);
    }

    public function defaultCategories()
    {
        return $this->categories()->where('is_custom', false);
    }

    public function getTotalExpenseDetails(): array
    {
        $expenses = RecurringExpense::where('user_id', $this->id)
            ->where('is_active', true)
            ->get();

        $thisMonthTotal = 0;
        $totalRemainingBalance = 0;
        $totalEmiPaymentsMade = 0;
        $totalEmiPaymentsTotal = 0;
        $allUpcomingPayments = [];

        // Count by type
        $subscriptionCount = 0;
        $billCount = 0;
        $emiCount = 0;
        $otherCount = 0;

        // EMI specific aggregations
        $totalMonthlyEmi = 0;
        $totalInterestPaid = 0;
        $totalInterestRemaining = 0;
        $totalPrincipalPaid = 0;
        $totalPrincipalRemaining = 0;
        $totalLoanAmount = 0;
        $totalPayable = 0;
        $highestInterestRate = 0;
        $highestInterestLoan = null;
        $soonestEndingLoan = null;
        $soonestEndDate = null;
        $emiLoans = [];

        foreach ($expenses as $expense) {
            // Calculate this month's contribution based on recurrence
            $monthlyContribution = match ($expense->recurrence) {
                'monthly' => $expense->amount,
                'quarterly' => $expense->amount / 3,
                'yearly' => $expense->amount / 12,
                default => $expense->amount
            };
            $thisMonthTotal += $monthlyContribution;

            // Count by type
            match ($expense->type) {
                'subscription' => $subscriptionCount++,
                'bill' => $billCount++,
                'emi' => $emiCount++,
                default => $otherCount++
            };

            // EMI specific calculations
            if ($expense->type === 'emi') {
                $totalRemainingBalance += $expense->remaining_balance;
                $totalEmiPaymentsMade += $expense->payments_completed;
                $totalEmiPaymentsTotal += $expense->tenure_months;
                $totalMonthlyEmi += $expense->amount;
                $totalInterestPaid += $expense->interest_paid_till_date;
                $totalInterestRemaining += $expense->interest_remaining;
                $totalPrincipalPaid += $expense->principal_paid_till_date;
                $totalPrincipalRemaining += $expense->remaining_balance;
                $totalLoanAmount += $expense->principal_amount;
                $totalPayable += $expense->total_payable;

                // Track highest interest rate loan
                if ($expense->interest_rate > $highestInterestRate) {
                    $highestInterestRate = $expense->interest_rate;
                    $highestInterestLoan = [
                        'id' => $expense->id,
                        'name' => $expense->name,
                        'interest_rate' => $expense->interest_rate
                    ];
                }

                // Track soonest ending loan
                if ($expense->loan_end_date && $expense->payments_remaining > 0) {
                    $endDate = \Carbon\Carbon::parse($expense->loan_end_date);
                    if (!$soonestEndDate || $endDate->lessThan($soonestEndDate)) {
                        $soonestEndDate = $endDate;
                        $soonestEndingLoan = [
                            'id' => $expense->id,
                            'name' => $expense->name,
                            'end_date' => $expense->loan_end_date,
                            'payments_remaining' => $expense->payments_remaining
                        ];
                    }
                }

                // Add to loans list for detailed view
                $emiLoans[] = [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'principal_amount' => $expense->principal_amount,
                    'interest_rate' => $expense->interest_rate,
                    'emi_amount' => $expense->amount,
                    'tenure_months' => $expense->tenure_months,
                    'payments_completed' => $expense->payments_completed,
                    'payments_remaining' => $expense->payments_remaining,
                    'completion_percentage' => $expense->completion_percentage,
                    'remaining_balance' => $expense->remaining_balance,
                    'interest_paid' => $expense->interest_paid_till_date,
                    'loan_end_date' => $expense->loan_end_date
                ];
            }

            $allUpcomingPayments = array_merge($allUpcomingPayments, $expense->getUpcomingPayments(2));
        }

        // Sort upcoming payments by date and get the next one
        usort($allUpcomingPayments, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $nextPayment = !empty($allUpcomingPayments) ? $allUpcomingPayments[0] : null;

        // Calculate overall EMI completion percentage
        $overallEmiCompletion = $totalEmiPaymentsTotal > 0
            ? round(($totalEmiPaymentsMade / $totalEmiPaymentsTotal) * 100, 1)
            : 0;

        return [
            'this_month_total' => round($thisMonthTotal, 2),
            'active_count' => $expenses->count(),
            'subscription_count' => $subscriptionCount,
            'bill_count' => $billCount,
            'emi_count' => $emiCount,
            'other_count' => $otherCount,
            'next_payment' => $nextPayment,
            'total_remaining_balance' => round($totalRemainingBalance, 2),
            'emi_payments_made' => $totalEmiPaymentsMade,
            'emi_payments_total' => $totalEmiPaymentsTotal,
            // Enhanced EMI summary
            'emi_summary' => [
                'total_monthly_emi' => round($totalMonthlyEmi, 2),
                'total_loan_amount' => round($totalLoanAmount, 2),
                'total_payable' => round($totalPayable, 2),
                'total_interest_paid' => round($totalInterestPaid, 2),
                'total_interest_remaining' => round($totalInterestRemaining, 2),
                'total_principal_paid' => round($totalPrincipalPaid, 2),
                'total_principal_remaining' => round($totalPrincipalRemaining, 2),
                'overall_completion' => $overallEmiCompletion,
                'highest_interest_loan' => $highestInterestLoan,
                'soonest_ending_loan' => $soonestEndingLoan,
                'loans' => $emiLoans
            ]
        ];
    }

    public function scopeActive($query){
        return $query->whereHas('transactions', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        });
    }

    public function subscription($type = 'default'): \Illuminate\Database\Eloquent\Relations\HasMany
    {
       return $this->hasMany(PolarSubscription::class)->with('items');
    }

    public function activeSubscription(){
        return $this->hasOne(PolarSubscription::class)
        ->where('status', 'active')
        ->where('canceled_at', null)
        ->where('ends_at', '>', now());
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }
}
