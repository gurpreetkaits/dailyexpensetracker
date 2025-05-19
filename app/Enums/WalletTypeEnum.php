<?php

namespace App\Enums;

enum WalletTypeEnum
{
    case CASH;
    case BANK;

    case CARD;
    case CRYPTO;
    case INVESTMENT;
    case OTHER;

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'cash',
            self::CARD => 'card',
            self::BANK => 'bank',
            self::CRYPTO => 'crypto',
            self::INVESTMENT => 'investment',
            self::OTHER => 'other',
        };
    }
}
