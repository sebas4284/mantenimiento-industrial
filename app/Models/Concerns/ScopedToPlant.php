<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts query results to the authenticated user's plant, unless the
 * user's role can see every plant (admin, corporativo).
 */
trait ScopedToPlant
{
    protected static function bootScopedToPlant(): void
    {
        static::addGlobalScope('plant', function (Builder $builder): void {
            $user = Auth::user();

            if (! $user instanceof User || $user->role->seesAllPlants()) {
                return;
            }

            static::applyPlantScope($builder, $user->plant_id);
        });
    }

    abstract protected static function applyPlantScope(Builder $builder, ?int $plantId): void;
}
