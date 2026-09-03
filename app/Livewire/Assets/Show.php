<?php

namespace App\Livewire\Assets;

use App\Enums\WorkOrderType;
use App\Exports\AssetMaintenanceExport;
use App\Exports\PreOperationalChecklistExport;
use App\Models\Asset;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Show extends Component
{
    public Asset $asset;

    public bool $showHistory = false;

    public string $exportFrom = '';

    public string $exportTo = '';

    public string $preopExportFrom = '';

    public string $preopExportTo = '';

    public function mount(Asset $asset): void
    {
        $this->authorize('view', $asset);

        $this->asset = $asset->load('area.plant');
    }

    public function openHistory(): void
    {
        $this->showHistory = true;
    }

    public function closeHistory(): void
    {
        $this->showHistory = false;
    }

    public function exportHistory()
    {
        $validated = $this->validate([
            'exportFrom' => ['nullable', 'date'],
            'exportTo' => ['nullable', 'date', 'after_or_equal:exportFrom'],
        ]);

        $from = $validated['exportFrom'] ? Carbon::parse($validated['exportFrom'])->startOfDay() : null;
        $to = $validated['exportTo'] ? Carbon::parse($validated['exportTo'])->endOfDay() : null;

        return Excel::download(
            new AssetMaintenanceExport($this->asset, $from, $to),
            "mantenimiento-{$this->asset->code}.xlsx",
        );
    }

    public function exportPreOperationalChecklists()
    {
        $validated = $this->validate([
            'preopExportFrom' => ['nullable', 'date'],
            'preopExportTo' => ['nullable', 'date', 'after_or_equal:preopExportFrom'],
        ]);

        $from = $validated['preopExportFrom'] ? Carbon::parse($validated['preopExportFrom'])->startOfDay() : null;
        $to = $validated['preopExportTo'] ? Carbon::parse($validated['preopExportTo'])->endOfDay() : null;

        return Excel::download(
            new PreOperationalChecklistExport($this->asset, $from, $to),
            "preoperacionales-{$this->asset->code}.xlsx",
        );
    }

    public function render()
    {
        $workOrders = $this->asset->workOrders()
            ->with(['assignedTo', 'reportedBy', 'maintenancePlan'])
            ->orderByDesc('opened_at')
            ->get();

        return view('livewire.assets.show', [
            'workOrders' => $workOrders,
            'correctivos' => $workOrders->where('type', WorkOrderType::Correctivo),
            'preventivos' => $workOrders->where('type', WorkOrderType::Preventivo),
            'preOperationalChecklists' => $this->asset->preOperationalChecklists()
                ->with('performedBy')
                ->orderByDesc('inspected_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
