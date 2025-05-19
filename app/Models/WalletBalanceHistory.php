<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletBalanceHistory extends Model
{
    protected $table = 'wallet_balance_history';

    protected $fillable = [
        'wallet_id',
        'balance',
        'created_at'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
