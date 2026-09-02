<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ChecklistTemplate;
use App\Models\User;

class ChecklistTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ChecklistTemplate $checklistTemplate): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function update(User $user, ChecklistTemplate $checklistTemplate): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }

    public function delete(User $user, ChecklistTemplate $checklistTemplate): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Supervisor], true);
    }
}
