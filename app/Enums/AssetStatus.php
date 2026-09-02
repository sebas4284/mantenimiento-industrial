<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Operativo = 'operativo';
    case Mantenimiento = 'mantenimiento';
    case FueraServicio = 'fuera_servicio';

    public function label(): string
    {
        return match ($this) {
            self::Operativo => 'Operativo',
            self::Mantenimiento => 'En mantenimiento',
            self::FueraServicio => 'Fuera de servicio',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Operativo => 'green',
            self::Mantenimiento => 'amber',
            self::FueraServicio => 'red',
        };
    }
}
