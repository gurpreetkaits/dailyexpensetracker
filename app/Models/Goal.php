<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'name',
        'target',
        'saved',
        'monthly_contribution',
        'target_date',
        'is_completed',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'saved' => 'decimal:2',
        'target_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        return round(($this->saved / $this->target) * 100, 2);
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->target - $this->saved;
    }

    // public function getMonthsRemainingAttribute(): int
    // {
    //     return ceil($this->remaining_amount / $this->monthly_contribution);
    // }
}