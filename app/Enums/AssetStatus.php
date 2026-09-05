<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Operativo = 'operativo';
    case Mantenimiento = 'mantenimiento';
    case Inactivo = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Operativo => 'Operativo',
            self::Mantenimiento => 'En mantenimiento',
            self::Inactivo => 'Inactivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Operativo => 'green',
            self::Mantenimiento => 'amber',
            self::Inactivo => 'zinc',
        };
    }

    public function tagVariant(): string
    {
        return match ($this) {
            self::Operativo => 'accent',
            self::Mantenimiento => 'outline',
            self::Inactivo => 'neutral',
        };
    }
}
