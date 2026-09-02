<?php

namespace App\Livewire\SpareParts;

use App\Models\Plant;
use App\Models\SparePart;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $lowStockOnly = false;

    public bool $showModal = false;

    public ?SparePart $editing = null;

    public ?int $plant_id = null;

    public string $code = '';

    public string $name = '';

    public int $stock_quantity = 0;

    public int $minimum_stock = 5;

    public function create(): void
    {
        $this->authorize('create', SparePart::class);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(SparePart $sparePart): void
    {
        $this->authorize('update', $sparePart);

        $this->editing = $sparePart;
        $this->plant_id = $sparePart->plant_id;
        $this->code = $sparePart->code;
        $this->name = $sparePart->name;
        $this->stock_quantity = $sparePart->stock_quantity;
        $this->minimum_stock = $sparePart->minimum_stock;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? SparePart::class);

        $validated = $this->validate([
            'plant_id' => ['required', 'exists:plants,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('spare_parts', 'code')->where('plant_id', $this->plant_id)->ignore($this->editing?->id)],
            'name' => ['required', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            SparePart::create($validated);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(SparePart $sparePart): void
    {
        $this->authorize('delete', $sparePart);

        $sparePart->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->plant_id = null;
        $this->code = '';
        $this->name = '';
        $this->stock_quantity = 0;
        $this->minimum_stock = 5;
        $this->resetErrorBag();
    }

    public function render()
    {
        $spareParts = SparePart::query()
            ->with('plant')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('code', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")))
            ->when($this->lowStockOnly, fn ($q) => $q->whereColumn('stock_quantity', '<=', 'minimum_stock'))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.spare-parts.index', [
            'spareParts' => $spareParts,
            'plants' => Plant::orderBy('name')->get(),
        ]);
    }
}
