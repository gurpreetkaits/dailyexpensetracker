<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CategoryStoreData extends Data
{
    public function __construct(
        public string $name,
        public string $type,
        public string $icon,
        public string $color,

    ) {
    }
}
