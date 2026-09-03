<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\PreOperationalChecklist;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PreOperationalChecklistExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?Asset $asset,
        private ?Carbon $from,
        private ?Carbon $to,
    ) {}

    public function collection(): Collection
    {
        return PreOperationalChecklist::query()
            ->when($this->asset, fn ($q) => $q->where('asset_id', $this->asset->id))
            ->when($this->from, fn ($q) => $q->where('inspected_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->where('inspected_at', '<=', $this->to))
            ->with(['asset.area.plant', 'performedBy'])
            ->orderByDesc('inspected_at')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Fecha',
            'Activo',
            'Código',
            'Planta',
            'Área',
            'Resultado',
            'Acción requerida',
            'Responsable',
            'Observaciones',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($checklist): array
    {
        /** @var PreOperationalChecklist $checklist */
        return [
            $checklist->inspected_at->format('d/m/Y H:i'),
            $checklist->asset->name,
            $checklist->asset->code,
            $checklist->asset->area->plant->name,
            $checklist->asset->area->name,
            $checklist->result->label(),
            $checklist->required_action->label(),
            $checklist->performedBy->name,
            $checklist->anomaly_notes ?? '—',
        ];
    }
}
