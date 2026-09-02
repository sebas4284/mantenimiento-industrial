<?php

namespace App\Livewire\Team;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(in_array(auth()->user()->role, [UserRole::Admin, UserRole::Supervisor], true), 403);
    }

    public function render()
    {
        $authUser = auth()->user();

        $members = User::query()
            ->whereIn('role', [UserRole::Tecnico, UserRole::Supervisor])
            ->when(! $authUser->role->seesAllPlants(), fn ($q) => $q->where('plant_id', $authUser->plant_id))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount([
                'assignedWorkOrders as active_assigned_count' => fn ($q) => $q->where('status', WorkOrderStatus::EnProgreso),
                'supportedWorkOrders as active_support_count' => fn ($q) => $q->where('status', WorkOrderStatus::EnProgreso),
            ])
            ->with('plant')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.team.index', [
            'members' => $members,
        ]);
    }
}
