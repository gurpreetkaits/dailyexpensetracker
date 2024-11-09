<?php

namespace App\Enum;

enum TransactionFilterEnum: string
{
    const TODAY = 'today';
    const MONTHLY = 'monthly';
    const WEEKLY = 'weekly';
    const YEARLY = 'yearly';
    const ALL = 'all';

    public static function values(): string
    {
        return collect(self::cases())
            ->map(fn($case) => $case->value)
            ->join(',');
    }
}
