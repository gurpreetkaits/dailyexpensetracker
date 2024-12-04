<?php

namespace App\Enum;

enum TransactionFilterEnum: string
{
    case TODAY = 'today';
    case MONTHLY = 'monthly';
    case WEEKLY = 'weekly';
    case YEARLY = 'yearly';
    case ALL = 'all';
    case YESTERDAY = 'yesterday';
    public static function values(): string
    {
        return collect(self::cases())
            ->map(fn($case) => $case->value)
            ->join(',');
    }
}
