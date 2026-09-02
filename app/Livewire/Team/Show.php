<?php

namespace App\Livewire\Team;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public User $member;

    public function mount(User $member): void
    {
        abort_unless(in_array(auth()->user()->role, [UserRole::Admin, UserRole::Supervisor], true), 403);

        $this->member = $member->load('plant');
    }

    public function render()
    {
        $workOrders = $this->member->assignedWorkOrders()
            ->with(['asset.area.plant'])
            ->orderByDesc('opened_at')
            ->get()
            ->each(fn ($wo) => $wo->collaboratorRole = 'Principal')
            ->merge(
                $this->member->supportedWorkOrders()
                    ->with(['asset.area.plant'])
                    ->orderByDesc('opened_at')
                    ->get()
                    ->each(fn ($wo) => $wo->collaboratorRole = 'Apoyo')
            )
            ->sortByDesc('opened_at')
            ->values();

        $isBusy = $workOrders->contains(fn ($wo) => $wo->status === WorkOrderStatus::EnProgreso);

        return view('livewire.team.show', [
            'workOrders' => $workOrders,
            'isBusy' => $isBusy,
        ]);
    }
}
