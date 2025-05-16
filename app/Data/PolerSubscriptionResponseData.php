<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class PolerSubscriptionResponseData extends Data
{
    public function __construct(
        public string $id,
        public Carbon $canceled_at,
        public Carbon $ends_at
    ) {}
}
