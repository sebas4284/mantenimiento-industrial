<?php

namespace App\Livewire\Admin\Plants;

use App\Models\Area;
use App\Models\Plant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $selectedPlantId = null;

    public bool $showPlantModal = false;

    public ?Plant $editingPlant = null;

    public string $plantName = '';

    public string $plantLocation = '';

    public bool $showAreaModal = false;

    public ?Area $editingArea = null;

    public string $areaName = '';

    public function mount(): void
    {
        $this->authorize('create', Plant::class);
    }

    public function selectPlant(int $plantId): void
    {
        $this->selectedPlantId = $plantId;
    }

    public function createPlant(): void
    {
        $this->editingPlant = null;
        $this->plantName = '';
        $this->plantLocation = '';
        $this->showPlantModal = true;
    }

    public function editPlant(Plant $plant): void
    {
        $this->editingPlant = $plant;
        $this->plantName = $plant->name;
        $this->plantLocation = (string) $plant->location;
        $this->showPlantModal = true;
    }

    public function savePlant(): void
    {
        $validated = $this->validate([
            'plantName' => ['required', 'string', 'max:255'],
            'plantLocation' => ['nullable', 'string', 'max:255'],
        ]);

        $plant = $this->editingPlant ?? new Plant;
        $plant->name = $validated['plantName'];
        $plant->location = $validated['plantLocation'];

        if (! $plant->code) {
            $plant->code = Plant::generateUniqueCode($validated['plantName']);
        }

        $plant->save();

        $this->showPlantModal = false;
    }

    public function deletePlant(Plant $plant): void
    {
        $plant->delete();

        if ($this->selectedPlantId === $plant->id) {
            $this->selectedPlantId = null;
        }
    }

    public function createArea(): void
    {
        $this->editingArea = null;
        $this->areaName = '';
        $this->showAreaModal = true;
    }

    public function editArea(Area $area): void
    {
        $this->editingArea = $area;
        $this->areaName = $area->name;
        $this->showAreaModal = true;
    }

    public function saveArea(): void
    {
        $validated = $this->validate([
            'areaName' => ['required', 'string', 'max:255'],
        ]);

        $area = $this->editingArea ?? new Area(['plant_id' => $this->selectedPlantId]);
        $area->name = $validated['areaName'];
        $area->save();

        $this->showAreaModal = false;
    }

    public function deleteArea(Area $area): void
    {
        $area->delete();
    }

    public function render()
    {
        $plants = Plant::withCount('areas')->orderBy('name')->get();

        return view('livewire.admin.plants.index', [
            'plants' => $plants,
            'areas' => $this->selectedPlantId
                ? Area::where('plant_id', $this->selectedPlantId)->withCount('assets')->orderBy('name')->get()
                : collect(),
        ]);
    }
}
