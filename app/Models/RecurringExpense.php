<?php

namespace App\Models;

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
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'first_payment_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}