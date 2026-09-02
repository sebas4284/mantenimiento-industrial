<?php

namespace App\Models;

use App\Models\Concerns\ScopedToPlant;
use Database\Factories\PlantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'location', 'code'])]
class Plant extends Model
{
    /** @use HasFactory<PlantFactory> */
    use HasFactory, ScopedToPlant;

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Derive a short, unique uppercase code from a plant name (initials of
     * each word), disambiguating with a numeric suffix on collision.
     */
    public static function generateUniqueCode(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        if (mb_strlen($initials) < 2) {
            $initials = mb_strtoupper(mb_substr(trim($name), 0, 2));
        }

        $code = $initials;
        $suffix = 1;

        while (static::withoutGlobalScopes()->where('code', $code)->exists()) {
            $suffix++;
            $code = $initials.$suffix;
        }

        return $code;
    }

    /**
     * Atomically allocate the next work-order sequence number for this
     * plant and return the formatted order number (e.g. "PN0001").
     */
    public function allocateNextWorkOrderNumber(): string
    {
        return DB::transaction(function () {
            $plant = static::query()->whereKey($this->id)->lockForUpdate()->first();
            $sequence = $plant->next_work_order_sequence;

            $plant->increment('next_work_order_sequence');

            return sprintf('%s%04d', $plant->code, $sequence);
        });
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->where('id', $plantId);
    }
}
