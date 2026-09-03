<?php

namespace App\Enums;

enum PreOperationalResult: string
{
    case Apto = 'apto';
    case NoApto = 'no_apto';

    public function label(): string
    {
        return match ($this) {
            self::Apto => 'Apto para operar',
            self::NoApto => 'No apto para operar',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Apto => 'green',
            self::NoApto => 'red',
        };
    }
}
