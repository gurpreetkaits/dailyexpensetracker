<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class PolarCustomerStateResponseData extends Data
{
    public function __construct(
        public string  $subscription_id,
        public ?string  $amount,
        public string  $json_data,
        public ?string  $started_at = null,
        public ?string $ends_at = null,
        public ?string $canceled_at = null
    ) {}
}
