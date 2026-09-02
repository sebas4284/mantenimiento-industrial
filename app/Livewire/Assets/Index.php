<?php

namespace App\Livewire\Assets;

use App\Enums\AssetCriticality;
use App\Enums\AssetStatus;
use App\Models\Area;
use App\Models\Asset;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $areaFilter = null;

    public bool $showModal = false;

    public ?Asset $editing = null;

    public ?int $area_id = null;

    public string $code = '';

    public string $name = '';

    public ?string $manufacturer = null;

    public ?string $model = null;

    public ?string $serial_number = null;

    public string $criticality = 'B';

    public string $status = 'operativo';

    public $photo = null;

    public function create(): void
    {
        $this->authorize('create', Asset::class);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Asset $asset): void
    {
        $this->authorize('update', $asset);

        $this->editing = $asset;
        $this->area_id = $asset->area_id;
        $this->code = $asset->code;
        $this->name = $asset->name;
        $this->manufacturer = $asset->manufacturer;
        $this->model = $asset->model;
        $this->serial_number = $asset->serial_number;
        $this->criticality = $asset->criticality->value;
        $this->status = $asset->status->value;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? Asset::class);

        $validated = $this->validate([
            'area_id' => ['required', 'exists:areas,id'],
            'code' => ['required', 'string', 'max:50', 'unique:assets,code,'.($this->editing?->id ?? 'NULL').',id'],
            'name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'criticality' => ['required', new Enum(AssetCriticality::class)],
            'status' => ['required', new Enum(AssetStatus::class)],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $asset = $this->editing ?? new Asset;
        $asset->fill($validated);
        $asset->generateQrCode();

        if ($this->photo) {
            $asset->photo_path = $this->photo->store('assets', 'public');
        }

        $asset->save();

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Asset $asset): void
    {
        $this->authorize('delete', $asset);

        $asset->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->area_id = null;
        $this->code = '';
        $this->name = '';
        $this->manufacturer = null;
        $this->model = null;
        $this->serial_number = null;
        $this->criticality = 'B';
        $this->status = 'operativo';
        $this->photo = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $assets = Asset::query()
            ->with('area.plant')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('code', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")))
            ->when($this->areaFilter, fn ($q) => $q->where('area_id', $this->areaFilter))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.assets.index', [
            'assets' => $assets,
            'areas' => Area::with('plant')->orderBy('name')->get(),
            'criticalities' => AssetCriticality::cases(),
            'statuses' => AssetStatus::cases(),
        ]);
    }
}
