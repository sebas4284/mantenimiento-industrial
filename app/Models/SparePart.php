<?php

namespace App\Models;

use App\Models\Concerns\ScopedToPlant;
use Database\Factories\SparePartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['plant_id', 'code', 'name', 'stock_quantity', 'minimum_stock'])]
class SparePart extends Model
{
    /** @use HasFactory<SparePartFactory> */
    use HasFactory, ScopedToPlant;

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SparePartUsage::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->where('plant_id', $plantId);
    }
}
