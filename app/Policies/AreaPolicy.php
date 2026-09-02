<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Area $area): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function update(User $user, Area $area): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function delete(User $user, Area $area): bool
    {
        return $user->role === UserRole::Admin;
    }
}
