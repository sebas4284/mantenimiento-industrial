<?php

namespace App\Enums;

enum PreOperationalAnswer: string
{
    case Buena = 'buena';
    case Mala = 'mala';
    case Na = 'na';

    public function label(): string
    {
        return match ($this) {
            self::Buena => 'Buena',
            self::Mala => 'Mala',
            self::Na => 'N/A',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Buena => 'green',
            self::Mala => 'red',
            self::Na => 'zinc',
        };
    }
}
