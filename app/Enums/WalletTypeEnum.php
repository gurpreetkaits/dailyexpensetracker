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
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::BANK => 'Bank',
            self::CRYPTO => 'Crypto',
            self::INVESTMENT => 'Investment',
            self::OTHER => 'Other',
        };
    }
}
