<?php

namespace App\Models;

use Database\Factories\PreOperationalItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['section', 'label', 'order'])]
class PreOperationalItem extends Model
{
    /** @use HasFactory<PreOperationalItemFactory> */
    use HasFactory;

    public function answers(): HasMany
    {
        return $this->hasMany(PreOperationalChecklistAnswer::class, 'item_id');
    }
}
