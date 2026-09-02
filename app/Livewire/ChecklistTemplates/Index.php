<?php

namespace App\Livewire\ChecklistTemplates;

use App\Models\ChecklistTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;

    public ?ChecklistTemplate $editing = null;

    public string $name = '';

    /** @var array<int, array{id: int|null, label: string}> */
    public array $items = [];

    public function create(): void
    {
        $this->authorize('create', ChecklistTemplate::class);

        $this->resetForm();
        $this->addItem();
        $this->showModal = true;
    }

    public function edit(ChecklistTemplate $template): void
    {
        $this->authorize('update', $template);

        $this->editing = $template;
        $this->name = $template->name;
        $this->items = $template->items()->orderBy('order')->get()
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->label])
            ->all();

        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => null, 'label' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? ChecklistTemplate::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.label' => ['required', 'string', 'max:255'],
        ]);

        $template = $this->editing ?? new ChecklistTemplate;
        $template->name = $validated['name'];
        $template->save();

        $keepIds = [];

        foreach ($validated['items'] as $order => $item) {
            $checklistItem = ! empty($item['id'])
                ? $template->items()->findOrFail($item['id'])
                : $template->items()->make();

            $checklistItem->fill(['label' => $item['label'], 'order' => $order]);
            $checklistItem->save();

            $keepIds[] = $checklistItem->id;
        }

        $template->items()->whereNotIn('id', $keepIds)->delete();

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(ChecklistTemplate $template): void
    {
        $this->authorize('delete', $template);

        $template->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->name = '';
        $this->items = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.checklist-templates.index', [
            'templates' => ChecklistTemplate::withCount('items')->orderBy('name')->paginate(12),
        ]);
    }
}
