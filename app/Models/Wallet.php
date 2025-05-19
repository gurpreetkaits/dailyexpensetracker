<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property float $balance
 * @property string $currency
 * @property int $user_id
 */
class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'name',
        'type',
        'balance',
        'currency',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function balanceHistory(): HasMany
    {
        return $this->hasMany(WalletBalanceHistory::class);
    }
}
