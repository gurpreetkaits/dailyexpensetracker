<?php

namespace App\Data;

use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class GetTransactionFilterData extends Data
{
  public function __construct(
    public string|Optional $start_date,
    public string|Optional $end_date,
    public string|Optional $category,
  ) {
  }
}
