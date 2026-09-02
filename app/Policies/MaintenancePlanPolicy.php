<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MaintenancePlan;
use App\Models\User;

class MaintenancePlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function update(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function delete(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }
}
