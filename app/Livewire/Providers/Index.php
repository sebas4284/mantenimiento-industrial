<?php

namespace App\Livewire\Providers;

use App\Models\Provider;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?Provider $editing = null;

    public string $name = '';

    public string $contact_name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $specialty = '';

    public function create(): void
    {
        $this->authorize('create', Provider::class);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Provider $provider): void
    {
        $this->authorize('update', $provider);

        $this->editing = $provider;
        $this->name = $provider->name;
        $this->contact_name = (string) $provider->contact_name;
        $this->phone = (string) $provider->phone;
        $this->email = (string) $provider->email;
        $this->address = (string) $provider->address;
        $this->specialty = (string) $provider->specialty;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? Provider::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Provider::create($validated);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Provider $provider): void
    {
        $this->authorize('delete', $provider);

        $provider->delete();
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
        $this->contact_name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->specialty = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $providers = Provider::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('specialty', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.providers.index', [
            'providers' => $providers,
        ]);
    }
}
