<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QuickReport extends Component
{
    public Asset $asset;

    public string $priority = 'alta';

    public string $failure_description = '';

    public bool $submitted = false;

    public function mount(Asset $asset): void
    {
        $this->asset = $asset->load('area.plant');
    }

    public function report(): void
    {
        $validated = $this->validate([
            'priority' => ['required', new Enum(WorkOrderPriority::class)],
            'failure_description' => ['required', 'string', 'max:2000'],
        ]);

        WorkOrder::create([
            ...$validated,
            'asset_id' => $this->asset->id,
            'reported_by' => auth()->id(),
            'type' => WorkOrderType::Correctivo,
            'status' => WorkOrderStatus::Abierta,
            'opened_at' => now(),
        ]);

        $this->submitted = true;
        $this->reset('failure_description');
    }

    public function render()
    {
        return view('livewire.work-orders.quick-report', [
            'priorities' => WorkOrderPriority::cases(),
        ]);
    }
}
