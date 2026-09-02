<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\Provider;
use App\Models\WorkOrder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $typeFilter = '';

    public bool $showModal = false;

    public ?int $asset_id = null;

    public string $type = 'correctivo';

    public string $priority = 'media';

    public string $execution_type = 'interno';

    public ?int $provider_id = null;

    public string $failure_description = '';

    public function create(): void
    {
        $this->authorize('create', WorkOrder::class);

        $this->reset(['asset_id', 'priority', 'failure_description', 'execution_type', 'provider_id']);
        $this->type = 'correctivo';
        $this->priority = 'media';
        $this->execution_type = 'interno';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize('create', WorkOrder::class);

        $validated = $this->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', new Enum(WorkOrderType::class)],
            'priority' => ['required', new Enum(WorkOrderPriority::class)],
            'execution_type' => ['required', new Enum(WorkOrderExecutionType::class)],
            'provider_id' => [Rule::requiredIf($this->execution_type === WorkOrderExecutionType::Externo->value), 'nullable', 'exists:providers,id'],
            'failure_description' => ['required', 'string', 'max:2000'],
        ]);

        WorkOrder::create([
            ...$validated,
            'reported_by' => auth()->id(),
            'status' => WorkOrderStatus::Abierta,
            'opened_at' => now(),
        ]);

        $this->showModal = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function take(WorkOrder $workOrder): void
    {
        $this->authorize('update', $workOrder);

        $workOrder->update([
            'assigned_to' => auth()->id(),
            'status' => WorkOrderStatus::EnProgreso,
            'started_at' => $workOrder->started_at ?? now(),
        ]);
    }

    public function transition(WorkOrder $workOrder, string $status): void
    {
        $this->authorize('update', $workOrder);

        $newStatus = WorkOrderStatus::from($status);

        $workOrder->status = $newStatus;

        if ($newStatus === WorkOrderStatus::EnProgreso && ! $workOrder->started_at) {
            $workOrder->started_at = now();
        }

        if ($newStatus === WorkOrderStatus::Completada) {
            $workOrder->completed_at = now();
        }

        $workOrder->save();
    }

    public function render()
    {
        $workOrders = WorkOrder::query()
            ->with(['asset.area.plant', 'assignedTo', 'reportedBy'])
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderByDesc('opened_at')
            ->get()
            ->groupBy(fn (WorkOrder $wo) => $wo->status->value);

        return view('livewire.work-orders.index', [
            'columns' => WorkOrderStatus::cases(),
            'workOrdersByStatus' => $workOrders,
            'assets' => Asset::with('area')->orderBy('name')->get(),
            'types' => WorkOrderType::cases(),
            'priorities' => WorkOrderPriority::cases(),
            'executionTypes' => WorkOrderExecutionType::cases(),
            'providers' => Provider::orderBy('name')->get(),
        ]);
    }
}
