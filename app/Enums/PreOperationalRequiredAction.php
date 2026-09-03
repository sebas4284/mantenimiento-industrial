<?php

namespace App\Enums;

enum PreOperationalRequiredAction: string
{
    case Ninguna = 'ninguna';
    case CorreccionInmediata = 'correccion_inmediata';
    case ReportarMantenimiento = 'reportar_mantenimiento';
    case SacarServicio = 'sacar_servicio';
    case InspeccionEspecializada = 'inspeccion_especializada';

    public function label(): string
    {
        return match ($this) {
            self::Ninguna => 'Ninguna',
            self::CorreccionInmediata => 'Corrección inmediata',
            self::ReportarMantenimiento => 'Reportar a mantenimiento',
            self::SacarServicio => 'Sacar de servicio',
            self::InspeccionEspecializada => 'Requiere inspección especializada',
        };
    }
}
