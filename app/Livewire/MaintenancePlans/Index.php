<?php

namespace App\Livewire\MaintenancePlans;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\MaintenancePlan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;

    public ?MaintenancePlan $editing = null;

    public ?int $asset_id = null;

    public ?int $checklist_template_id = null;

    public string $name = '';

    public int $frequency_days = 30;

    public string $next_due_date = '';

    public bool $active = true;

    public function create(): void
    {
        $this->authorize('create', MaintenancePlan::class);

        $this->resetForm();
        $this->next_due_date = now()->addDays(30)->toDateString();
        $this->showModal = true;
    }

    public function edit(MaintenancePlan $plan): void
    {
        $this->authorize('update', $plan);

        $this->editing = $plan;
        $this->asset_id = $plan->asset_id;
        $this->checklist_template_id = $plan->checklist_template_id;
        $this->name = $plan->name;
        $this->frequency_days = $plan->frequency_days;
        $this->next_due_date = $plan->next_due_date->toDateString();
        $this->active = $plan->active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? MaintenancePlan::class);

        $validated = $this->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'checklist_template_id' => ['nullable', 'exists:checklist_templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'frequency_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'next_due_date' => ['required', 'date'],
            'active' => ['boolean'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            MaintenancePlan::create($validated);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(MaintenancePlan $plan): void
    {
        $this->authorize('delete', $plan);

        $plan->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->asset_id = null;
        $this->checklist_template_id = null;
        $this->name = '';
        $this->frequency_days = 30;
        $this->next_due_date = '';
        $this->active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.maintenance-plans.index', [
            'plans' => MaintenancePlan::with(['asset.area.plant', 'checklistTemplate'])->orderBy('next_due_date')->paginate(12),
            'assets' => Asset::with('area')->orderBy('name')->get(),
            'templates' => ChecklistTemplate::orderBy('name')->get(),
        ]);
    }
}
