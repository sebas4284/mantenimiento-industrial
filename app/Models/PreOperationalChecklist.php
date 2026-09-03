<?php

namespace App\Models;

use App\Enums\PreOperationalRequiredAction;
use App\Enums\PreOperationalResult;
use App\Models\Concerns\ScopedToPlant;
use Database\Factories\PreOperationalChecklistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'asset_id',
    'performed_by',
    'inspected_at',
    'result',
    'anomaly_notes',
    'required_action',
    'additional_notes',
])]
class PreOperationalChecklist extends Model
{
    /** @use HasFactory<PreOperationalChecklistFactory> */
    use HasFactory, ScopedToPlant;

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
            'result' => PreOperationalResult::class,
            'required_action' => PreOperationalRequiredAction::class,
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PreOperationalChecklistAnswer::class, 'checklist_id');
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->whereHas('asset.area', fn (Builder $query) => $query->where('plant_id', $plantId));
    }
}
