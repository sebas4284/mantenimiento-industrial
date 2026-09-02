<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderStatus;
use App\Models\Attachment;
use App\Models\Provider;
use App\Models\SparePart;
use App\Models\SparePartUsage;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistResult;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public WorkOrder $workOrder;

    public string $resolution_notes = '';

    public ?int $assigned_to = null;

    public ?int $provider_id = null;

    public ?int $support_collaborator_id = null;

    /** @var array<int, array{passed: bool|null, notes: string|null}> */
    public array $checklist = [];

    public $newPhoto = null;

    public ?int $spare_part_id = null;

    public int $spare_part_quantity = 1;

    public function mount(WorkOrder $workOrder): void
    {
        $this->authorize('view', $workOrder);

        $this->workOrder = $workOrder->load(['asset.area.plant', 'maintenancePlan.checklistTemplate.items', 'checklistResults', 'attachments.uploadedBy', 'reportedBy', 'assignedTo', 'provider', 'supportCollaborator', 'sparePartUsages.sparePart', 'sparePartUsages.usedBy']);
        $this->resolution_notes = (string) $workOrder->resolution_notes;
        $this->assigned_to = $workOrder->assigned_to;
        $this->provider_id = $workOrder->provider_id;
        $this->support_collaborator_id = $workOrder->support_collaborator_id;

        if ($template = $this->workOrder->maintenancePlan?->checklistTemplate) {
            $existing = $this->workOrder->checklistResults->keyBy('checklist_item_id');

            foreach ($template->items as $item) {
                $result = $existing->get($item->id);
                $this->checklist[$item->id] = [
                    'passed' => $result?->passed,
                    'notes' => $result?->notes,
                ];
            }
        }
    }

    public function saveChecklist(): void
    {
        $this->authorize('update', $this->workOrder);

        foreach ($this->checklist as $itemId => $result) {
            WorkOrderChecklistResult::updateOrCreate(
                ['work_order_id' => $this->workOrder->id, 'checklist_item_id' => $itemId],
                ['passed' => $result['passed'], 'notes' => $result['notes']],
            );
        }

        $this->workOrder->refresh();
    }

    public function saveResolution(): void
    {
        $this->authorize('update', $this->workOrder);

        $this->validate([
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->workOrder->update(['resolution_notes' => $this->resolution_notes]);
    }

    public function complete(): void
    {
        $this->authorize('update', $this->workOrder);

        $this->workOrder->update([
            'status' => WorkOrderStatus::Completada,
            'resolution_notes' => $this->resolution_notes,
            'completed_at' => now(),
            'started_at' => $this->workOrder->started_at ?? now(),
        ]);
    }

    public function uploadPhoto(): void
    {
        $this->authorize('update', $this->workOrder);

        $this->validate(['newPhoto' => ['required', 'image', 'max:4096']]);

        Attachment::create([
            'attachable_type' => WorkOrder::class,
            'attachable_id' => $this->workOrder->id,
            'uploaded_by' => auth()->id(),
            'path' => $this->newPhoto->store('work-orders', 'public'),
        ]);

        $this->newPhoto = null;
        $this->workOrder->refresh();
        $this->workOrder->load('attachments.uploadedBy');
    }

    public function assign(): void
    {
        $this->authorize('update', $this->workOrder);

        if ($this->workOrder->execution_type === WorkOrderExecutionType::Externo) {
            $this->validate([
                'provider_id' => ['required', 'exists:providers,id'],
                'support_collaborator_id' => ['nullable', 'exists:users,id'],
            ]);

            $this->workOrder->update([
                'provider_id' => $this->provider_id,
                'support_collaborator_id' => $this->support_collaborator_id,
            ]);
        } else {
            $this->validate(['assigned_to' => ['nullable', 'exists:users,id']]);

            $this->workOrder->update(['assigned_to' => $this->assigned_to]);
        }

        $this->workOrder->refresh();
    }

    public function addSparePartUsage(): void
    {
        $this->authorize('registerUsage', SparePart::class);

        $validated = $this->validate([
            'spare_part_id' => ['required', 'exists:spare_parts,id'],
            'spare_part_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $registered = DB::transaction(function () use ($validated) {
            $sparePart = SparePart::withoutGlobalScopes()->lockForUpdate()->findOrFail($validated['spare_part_id']);

            if ($validated['spare_part_quantity'] > $sparePart->stock_quantity) {
                $this->addError('spare_part_quantity', "Solo hay {$sparePart->stock_quantity} unidades disponibles en inventario.");

                return false;
            }

            SparePartUsage::create([
                'work_order_id' => $this->workOrder->id,
                'spare_part_id' => $sparePart->id,
                'used_by' => auth()->id(),
                'quantity' => $validated['spare_part_quantity'],
            ]);

            $sparePart->decrement('stock_quantity', $validated['spare_part_quantity']);

            return true;
        });

        if (! $registered) {
            return;
        }

        $this->spare_part_id = null;
        $this->spare_part_quantity = 1;
        $this->workOrder->load('sparePartUsages.sparePart', 'sparePartUsages.usedBy');
    }

    public function render()
    {
        $plantId = $this->workOrder->asset->area->plant_id;

        return view('livewire.work-orders.show', [
            'technicians' => User::where('plant_id', $plantId)
                ->whereIn('role', ['tecnico', 'supervisor'])
                ->withCount([
                    'assignedWorkOrders as active_assigned_count' => fn ($q) => $q
                        ->where('status', WorkOrderStatus::EnProgreso)
                        ->where('id', '!=', $this->workOrder->id),
                    'supportedWorkOrders as active_support_count' => fn ($q) => $q
                        ->where('status', WorkOrderStatus::EnProgreso)
                        ->where('id', '!=', $this->workOrder->id),
                ])
                ->orderBy('name')
                ->get(),
            'providers' => Provider::orderBy('name')->get(),
            'spareParts' => SparePart::withoutGlobalScopes()->where('plant_id', $plantId)->orderBy('name')->get(),
        ]);
    }
}
