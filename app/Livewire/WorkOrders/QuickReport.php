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
class QuickReport extends Component
{
    public Asset $asset;

    public string $type = 'correctivo';

    public string $priority = 'alta';

    public string $execution_type = 'interno';

    public ?int $provider_id = null;

    public string $failure_description = '';

    public bool $submitted = false;

    public function mount(Asset $asset): void
    {
        $this->asset = $asset->load('area.plant');
    }

    public function report(): void
    {
        $validated = $this->validate([
            'type' => ['required', new Enum(WorkOrderType::class)],
            'priority' => ['required', new Enum(WorkOrderPriority::class)],
            'execution_type' => ['required', new Enum(WorkOrderExecutionType::class)],
            'provider_id' => [Rule::requiredIf($this->execution_type === WorkOrderExecutionType::Externo->value), 'nullable', 'exists:providers,id'],
            'failure_description' => ['required', 'string', 'max:2000'],
        ]);

        WorkOrder::create([
            ...$validated,
            'asset_id' => $this->asset->id,
            'reported_by' => auth()->id(),
            'status' => WorkOrderStatus::Abierta,
            'opened_at' => now(),
        ]);

        $this->submitted = true;
        $this->reset(['type', 'priority', 'execution_type', 'provider_id', 'failure_description']);
        $this->type = 'correctivo';
        $this->priority = 'alta';
        $this->execution_type = 'interno';
    }

    public function render()
    {
        return view('livewire.work-orders.quick-report', [
            'types' => WorkOrderType::cases(),
            'priorities' => WorkOrderPriority::cases(),
            'executionTypes' => WorkOrderExecutionType::cases(),
            'providers' => Provider::orderBy('name')->get(),
        ]);
    }
}
