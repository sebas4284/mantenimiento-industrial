<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Corporativo = 'corporativo';
    case Supervisor = 'supervisor';
    case Tecnico = 'tecnico';
    case Operador = 'operador';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Corporativo => 'Corporativo',
            self::Supervisor => 'Supervisor',
            self::Tecnico => 'Técnico',
            self::Operador => 'Operador',
        };
    }

    public function seesAllPlants(): bool
    {
        return match ($this) {
            self::Admin, self::Corporativo => true,
            default => false,
        };
    }
}
