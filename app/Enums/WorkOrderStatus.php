<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case Abierta = 'abierta';
    case EnProgreso = 'en_progreso';
    case EnEspera = 'en_espera';
    case Completada = 'completada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Abierta => 'Abierta',
            self::EnProgreso => 'En progreso',
            self::EnEspera => 'En espera',
            self::Completada => 'Completada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Abierta => 'sky',
            self::EnProgreso => 'amber',
            self::EnEspera => 'zinc',
            self::Completada => 'green',
            self::Cancelada => 'red',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Completada, self::Cancelada], true);
    }

    public function tagVariant(): string
    {
        return match ($this) {
            self::Completada => 'neutral',
            self::Cancelada => 'outline',
            default => 'accent',
        };
    }
}
