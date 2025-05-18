<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolarSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'polar_id',
        'plan_id',
        'status',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'canceled_at',
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PolarSubscriptionItem::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->ends_at > now() &&
               !$this->canceled_at;
    }

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }
}
