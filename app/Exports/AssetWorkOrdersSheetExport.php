<?php

namespace App\Exports;

use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssetWorkOrdersSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private Asset $asset,
        private WorkOrderType $type,
        private ?Carbon $from,
        private ?Carbon $to,
    ) {}

    public function collection(): Collection
    {
        return $this->asset->workOrders()
            ->where('type', $this->type)
            ->when($this->from, fn ($q) => $q->where('opened_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->where('opened_at', '<=', $this->to))
            ->with(['assignedTo', 'reportedBy', 'maintenancePlan'])
            ->orderByDesc('opened_at')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'N° de orden',
            'Código de activo',
            'Fecha de apertura',
            'Descripción / Plan',
            'Prioridad',
            'Estado',
            'Reportada por',
            'Técnico asignado',
            'Fecha de inicio',
            'Fecha de completado',
            'Notas de resolución',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($workOrder): array
    {
        /** @var WorkOrder $workOrder */
        return [
            $workOrder->order_number,
            $this->asset->code,
            $workOrder->opened_at->format('d/m/Y H:i'),
            $workOrder->failure_description ?? $workOrder->maintenancePlan?->name ?? '—',
            $workOrder->priority->label(),
            $workOrder->status->label(),
            $workOrder->reportedBy->name,
            $workOrder->assignedTo->name ?? '—',
            $workOrder->started_at?->format('d/m/Y H:i') ?? '—',
            $workOrder->completed_at?->format('d/m/Y H:i') ?? '—',
            $workOrder->resolution_notes ?? '—',
        ];
    }

    public function title(): string
    {
        return $this->type === WorkOrderType::Correctivo ? 'Correctivos' : 'Preventivos';
    }
}
