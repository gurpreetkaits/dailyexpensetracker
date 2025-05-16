<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CheckoutResponseData extends Data
{
    public function __construct(
        public string $id,
    ) {}
}
