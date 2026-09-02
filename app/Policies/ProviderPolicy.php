<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Provider $provider): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Provider $provider): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $user->role === UserRole::Admin;
    }
}
