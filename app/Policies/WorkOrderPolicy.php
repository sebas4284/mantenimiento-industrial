<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role !== UserRole::Corporativo;
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor, UserRole::Tecnico], true);
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->role === UserRole::Admin;
    }
}
