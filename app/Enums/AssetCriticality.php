<?php

namespace App\Enums;

enum AssetCriticality: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A - Crítico',
            self::B => 'B - Importante',
            self::C => 'C - Menor',
        };
    }
}
