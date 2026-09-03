<?php

namespace App\Livewire\Assets;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Exports\AssetMaintenanceExport;
use App\Exports\PreOperationalChecklistExport;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        $correctivos = $workOrders->where('type', WorkOrderType::Correctivo);

        return view('livewire.assets.show', [
            'workOrders' => $workOrders,
            'correctivos' => $correctivos,
            'preventivos' => $workOrders->where('type', WorkOrderType::Preventivo),
            'preOperationalChecklists' => $this->asset->preOperationalChecklists()
                ->with('performedBy')
                ->orderByDesc('inspected_at')
                ->limit(10)
                ->get(),
            'mtbfHours' => $this->mtbfHours($correctivos),
            'mttrHours' => $this->mttrHours($correctivos),
            'nextPreventiveDate' => $this->nextPreventiveDate(),
        ]);
    }

    /**
     * @param  Collection<int, WorkOrder>  $correctivos
     */
    private function mtbfHours(Collection $correctivos): ?float
    {
        $failures = $correctivos->count();

        if ($failures === 0) {
            return null;
        }

        $hoursObserved = $this->asset->created_at->diffInHours(now());

        return $hoursObserved > 0 ? round($hoursObserved / $failures, 1) : null;
    }

    /**
     * @param  Collection<int, WorkOrder>  $correctivos
     */
    private function mttrHours(Collection $correctivos): ?float
    {
        $minutes = $correctivos
            ->where('status', WorkOrderStatus::Completada)
            ->avg(fn ($wo) => $wo->repair_minutes);

        return $minutes ? round($minutes / 60, 1) : null;
    }

    private function nextPreventiveDate(): ?Carbon
    {
        return $this->asset->maintenancePlans()
            ->where('active', true)
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date')
            ->first()
            ?->next_due_date;
    }
}
