<?php

namespace App\Models;

use App\Models\Concerns\ScopedToPlant;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['plant_id', 'name'])]
class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory, ScopedToPlant;

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->where('plant_id', $plantId);
    }
}
