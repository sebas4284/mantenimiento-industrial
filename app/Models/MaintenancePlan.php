<?php

namespace App\Models;

use App\Models\Concerns\ScopedToPlant;
use Database\Factories\MaintenancePlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['asset_id', 'checklist_template_id', 'name', 'frequency_days', 'next_due_date', 'active'])]
class MaintenancePlan extends Model
{
    /** @use HasFactory<MaintenancePlanFactory> */
    use HasFactory, ScopedToPlant;

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
            'active' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->whereHas('asset.area', fn (Builder $query) => $query->where('plant_id', $plantId));
    }
}
