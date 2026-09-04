<?php

namespace App\Livewire\Assets;

use App\Enums\AssetCriticality;
use App\Enums\AssetStatus;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Exports\AssetMaintenanceExport;
use App\Exports\PreOperationalChecklistExport;
use App\Models\Area;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Asset $asset;

    public bool $showHistory = false;

    public string $exportFrom = '';

    public string $exportTo = '';

    public string $preopExportFrom = '';

    public string $preopExportTo = '';

    public bool $showEditModal = false;

    public ?int $edit_area_id = null;

    public string $edit_code = '';

    public string $edit_name = '';

    public ?string $edit_manufacturer = null;

    public ?string $edit_model = null;

    public ?string $edit_serial_number = null;

    public string $edit_criticality = 'B';

    public string $edit_status = 'operativo';

    public $edit_photo = null;

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

    public function openEditModal(): void
    {
        $this->authorize('update', $this->asset);

        $this->edit_area_id = $this->asset->area_id;
        $this->edit_code = $this->asset->code;
        $this->edit_name = $this->asset->name;
        $this->edit_manufacturer = $this->asset->manufacturer;
        $this->edit_model = $this->asset->model;
        $this->edit_serial_number = $this->asset->serial_number;
        $this->edit_criticality = $this->asset->criticality->value;
        $this->edit_status = $this->asset->status === AssetStatus::Inactivo ? AssetStatus::Inactivo->value : AssetStatus::Operativo->value;
        $this->edit_photo = null;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $this->authorize('update', $this->asset);

        $validated = $this->validate([
            'edit_area_id' => ['required', 'exists:areas,id'],
            'edit_code' => ['required', 'string', 'max:50', 'unique:assets,code,'.$this->asset->id.',id'],
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_manufacturer' => ['nullable', 'string', 'max:255'],
            'edit_model' => ['nullable', 'string', 'max:255'],
            'edit_serial_number' => ['nullable', 'string', 'max:255'],
            'edit_criticality' => ['required', new Enum(AssetCriticality::class)],
            'edit_status' => ['required', 'in:'.AssetStatus::Operativo->value.','.AssetStatus::Inactivo->value],
            'edit_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $this->asset->fill([
            'area_id' => $validated['edit_area_id'],
            'code' => $validated['edit_code'],
            'name' => $validated['edit_name'],
            'manufacturer' => $validated['edit_manufacturer'],
            'model' => $validated['edit_model'],
            'serial_number' => $validated['edit_serial_number'],
            'criticality' => $validated['edit_criticality'],
            'status' => $validated['edit_status'],
        ]);
        $this->asset->generateQrCode();

        if ($this->edit_photo) {
            $this->asset->photo_path = $this->edit_photo->store('assets', 'public');
        }

        $this->asset->save();
        $this->asset->refresh()->load('area.plant');

        $this->showEditModal = false;
    }

    public function render()
    {
        $workOrders = $this->asset->workOrders()
            ->with(['assignedTo', 'reportedBy', 'maintenancePlan'])
            ->orderByDesc('opened_at')
            ->get();

        $correctivos = $workOrders->where('type', WorkOrderType::Correctivo);
        $preventivos = $workOrders->where('type', WorkOrderType::Preventivo);

        $hasActiveWorkOrder = $workOrders->contains(fn (WorkOrder $wo) => $wo->status === WorkOrderStatus::EnProgreso);
        $observationStart = $this->observationStart($workOrders);
        $totalMaintenance = $correctivos->count() + $preventivos->count();

        return view('livewire.assets.show', [
            'workOrders' => $workOrders,
            'correctivos' => $correctivos,
            'preventivos' => $preventivos,
            'preOperationalChecklists' => $this->asset->preOperationalChecklists()
                ->with('performedBy')
                ->orderByDesc('inspected_at')
                ->limit(10)
                ->get(),
            'mtbfHours' => $this->mtbfHours($correctivos, $observationStart),
            'mttrHours' => $this->mttrHours($correctivos),
            'nextPreventiveDate' => $this->nextPreventiveDate(),
            'availabilityPercent' => $this->availabilityPercent($correctivos, $observationStart),
            'displayStatus' => $this->asset->computedStatus($hasActiveWorkOrder),
            'correctivoPercent' => $totalMaintenance > 0 ? round($correctivos->count() / $totalMaintenance * 100, 1) : null,
            'preventivoPercent' => $totalMaintenance > 0 ? round($preventivos->count() / $totalMaintenance * 100, 1) : null,
            'areas' => Area::with('plant')->orderBy('name')->get(),
            'criticalities' => AssetCriticality::cases(),
            'editStatuses' => [AssetStatus::Operativo, AssetStatus::Inactivo],
        ]);
    }

    /**
     * @param  Collection<int, WorkOrder>  $correctivos
     */
    private function mtbfHours(Collection $correctivos, Carbon $observationStart): ?float
    {
        $failures = $correctivos->count();

        if ($failures === 0) {
            return null;
        }

        $hoursObserved = $observationStart->diffInHours(now());

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

    /**
     * The point in time MTBF/availability should be measured from: the
     * earliest work order on record, or the asset's creation date if that's
     * earlier (or there's no history yet). Using the asset row's own
     * `created_at` alone understates the observed window whenever historical
     * work orders were backdated before it (e.g. seeded/imported data).
     *
     * @param  Collection<int, WorkOrder>  $workOrders
     */
    private function observationStart(Collection $workOrders): Carbon
    {
        $earliestOpened = $workOrders->min('opened_at');

        return $earliestOpened && $earliestOpened->lt($this->asset->created_at)
            ? $earliestOpened
            : $this->asset->created_at;
    }

    /**
     * @param  Collection<int, WorkOrder>  $correctivos
     */
    private function availabilityPercent(Collection $correctivos, Carbon $observationStart): ?float
    {
        $hoursObserved = $observationStart->diffInHours(now());

        if ($hoursObserved <= 0) {
            return null;
        }

        $downtimeMinutes = $correctivos
            ->where('status', WorkOrderStatus::Completada)
            ->sum(fn ($wo) => $wo->repair_minutes ?? 0);

        $availability = (($hoursObserved - ($downtimeMinutes / 60)) / $hoursObserved) * 100;

        return round(max(0, min(100, $availability)), 1);
    }
}
