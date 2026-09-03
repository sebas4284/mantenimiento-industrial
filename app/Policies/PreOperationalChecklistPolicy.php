<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PreOperationalChecklist;
use App\Models\User;

class PreOperationalChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PreOperationalChecklist $preOperationalChecklist): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role !== UserRole::Corporativo;
    }

    public function delete(User $user, PreOperationalChecklist $preOperationalChecklist): bool
    {
        return $user->role === UserRole::Admin;
    }
}
