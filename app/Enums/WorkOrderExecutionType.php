<?php

namespace App\Enums;

enum WorkOrderExecutionType: string
{
    case Interno = 'interno';
    case Externo = 'externo';

    public function label(): string
    {
        return match ($this) {
            self::Interno => 'Interno',
            self::Externo => 'Externo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Interno => 'zinc',
            self::Externo => 'sky',
        };
    }
}
