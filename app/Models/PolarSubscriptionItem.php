<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolarSubscriptionItem extends Model
{
    protected $fillable = [
        'subscription_id',
        'polar_id',
        'product_id',
        'plan_id',
        'quantity',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'amount' => 'float',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PolarSubscription::class, 'subscription_id');
    }
}
