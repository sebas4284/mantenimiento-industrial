<?php

namespace App\Livewire\Providers;

use App\Models\Provider;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Provider $provider;

    public function mount(Provider $provider): void
    {
        $this->authorize('view', $provider);

        $this->provider = $provider;
    }

    public function render()
    {
        return view('livewire.providers.show', [
            'workOrders' => $this->provider->workOrders()
                ->with(['asset.area.plant'])
                ->orderByDesc('opened_at')
                ->get(),
        ]);
    }
}
