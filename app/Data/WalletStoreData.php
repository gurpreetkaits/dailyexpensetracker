<?php

namespace App\Data;

use App\Models\Setting;
use Spatie\LaravelData\Data;

class WalletStoreData extends Data
{
    public function __construct(
        public string $name,
        public string $type,
        public float $balance,
        public ?string $currency
    ) {
        $this->currency = Setting::where('user_id', auth()->id())->first()->currency_code;
    }
}
