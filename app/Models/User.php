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

        $totalInterestPaid = 0;
        $totalAmountPaid = 0;
        $totalPendingPayments = 0;
        $totalRemainingBalance = 0;
        $allUpcomingPayments = [];

        foreach ($expenses as $expense) {
            $totalInterestPaid += $expense->interest_paid_till_date;
            $totalAmountPaid += $expense->total_amount_paid;
            $totalPendingPayments += $expense->pending_payments;
            $totalRemainingBalance += $expense->remaining_balance;
            $allUpcomingPayments = array_merge($allUpcomingPayments, $expense->getUpcomingPayments(3));
        }

        // Sort upcoming payments by date
        usort($allUpcomingPayments, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return [
            'total_interest_paid' => round($totalInterestPaid, 2),
            'total_amount_paid' => round($totalAmountPaid, 2),
            'total_pending_payments' => round($totalPendingPayments, 2),
            'total_remaining_balance' => round($totalRemainingBalance, 2),
            'upcoming_payments' => array_slice($allUpcomingPayments, 0, 3), // Only return next 3 payments
            'monthly_payment_total' => round($expenses->sum('amount'), 2)
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
