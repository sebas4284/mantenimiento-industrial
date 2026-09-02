<?php

namespace App\Enums;

enum WorkOrderType: string
{
    case Correctivo = 'correctivo';
    case Preventivo = 'preventivo';

    public function label(): string
    {
        return match ($this) {
            self::Correctivo => 'Correctivo',
            self::Preventivo => 'Preventivo',
        };
    }
}
