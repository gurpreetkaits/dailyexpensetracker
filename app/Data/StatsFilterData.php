<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class StatsFilterData extends Data
{
  public function __construct(
    #
    public string $type,
  ) {
  }
}
