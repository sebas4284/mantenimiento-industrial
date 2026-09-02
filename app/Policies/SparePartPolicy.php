<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SparePart;
use App\Models\User;

class SparePartPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SparePart $sparePart): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function update(User $user, SparePart $sparePart): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function delete(User $user, SparePart $sparePart): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function registerUsage(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor, UserRole::Tecnico], true);
    }
}
